<?php
/**
 * Pax Ranch House — enquiry form handler
 * Receives POST submissions from the booking and contact pages,
 * notifies staff and sends the guest an auto-reply, via the Resend API.
 *
 * Hosting: GoDaddy shared hosting (PHP). No Composer / SDK required —
 * the Resend REST API is called directly with cURL.
 *
 * Configuration is read from a .env file in the same directory:
 *   RESEND_API_KEY      — Resend API key
 *   ENQUIRY_FROM_EMAIL  — verified "from" address, e.g. stays@paxranch.com
 *   STAFF_NOTIFY_EMAIL  — where staff receive enquiries
 *
 * IMPORTANT: keep .env OUTSIDE the public web root if possible, or ensure
 * the server blocks direct access to it (see the .htaccess in this folder).
 */

header('Content-Type: application/json; charset=utf-8');

// ---- Only accept POST -------------------------------------------------------
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['ok' => false, 'error' => 'Method not allowed']);
    exit;
}

// ---- Load .env --------------------------------------------------------------
function load_env($path) {
    $vars = [];
    if (!is_readable($path)) {
        return $vars;
    }
    foreach (file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
        $line = trim($line);
        if ($line === '' || $line[0] === '#') {
            continue;
        }
        $pos = strpos($line, '=');
        if ($pos === false) {
            continue;
        }
        $key = trim(substr($line, 0, $pos));
        $val = trim(substr($line, $pos + 1));
        // strip surrounding quotes if present
        if (strlen($val) >= 2 && ($val[0] === '"' || $val[0] === "'") && substr($val, -1) === $val[0]) {
            $val = substr($val, 1, -1);
        }
        $vars[$key] = $val;
    }
    return $vars;
}

$env = load_env(__DIR__ . '/.env');
$RESEND_API_KEY     = $env['RESEND_API_KEY']     ?? getenv('RESEND_API_KEY');
$ENQUIRY_FROM_EMAIL = $env['ENQUIRY_FROM_EMAIL'] ?? getenv('ENQUIRY_FROM_EMAIL');
$STAFF_NOTIFY_EMAIL = $env['STAFF_NOTIFY_EMAIL'] ?? getenv('STAFF_NOTIFY_EMAIL');

if (!$RESEND_API_KEY || !$ENQUIRY_FROM_EMAIL || !$STAFF_NOTIFY_EMAIL) {
    error_log('enquiry.php: missing required .env configuration');
    http_response_code(500);
    echo json_encode(['ok' => false, 'error' => 'Email service is not configured.']);
    exit;
}

// ---- Read input (JSON body or form-encoded) ---------------------------------
$raw = file_get_contents('php://input');
$data = json_decode($raw, true);
if (!is_array($data)) {
    $data = $_POST;
}

function field($data, $key) {
    return isset($data[$key]) ? trim((string) $data[$key]) : '';
}

$name      = field($data, 'name');
$email     = field($data, 'email');
$phone     = field($data, 'phone');
$guests    = field($data, 'guests');
$arrival   = field($data, 'arrival');
$departure = field($data, 'departure');
$house     = field($data, 'house');
$subject   = field($data, 'subject');
$message   = field($data, 'message');
$source    = field($data, 'source') ?: 'website';
$honeypot  = field($data, 'company'); // hidden anti-spam field

// ---- Honeypot: bots fill hidden fields; pretend success -------------------
if ($honeypot !== '') {
    echo json_encode(['ok' => true]);
    exit;
}

// ---- Validate ---------------------------------------------------------------
if ($name === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    http_response_code(400);
    echo json_encode(['ok' => false, 'error' => 'Please provide your name and a valid email address.']);
    exit;
}

// ---- Build email bodies -----------------------------------------------------
function esc($s) {
    return htmlspecialchars($s, ENT_QUOTES, 'UTF-8');
}

$rows = [
    'Name'      => $name,
    'Email'     => $email,
    'Phone'     => $phone,
    'Guests'    => $guests,
    'Arrival'   => $arrival,
    'Departure' => $departure,
    'House'     => $house,
    'Subject'   => $subject,
    'Source'    => $source,
];

$detail_rows = '';
foreach ($rows as $k => $v) {
    if ($v === '') {
        continue;
    }
    $detail_rows .= '<tr>'
        . '<td style="padding:6px 16px 6px 0;color:#7a716a;font-size:13px;white-space:nowrap;vertical-align:top">' . esc($k) . '</td>'
        . '<td style="padding:6px 0;color:#2b2620;font-size:14px">' . esc($v) . '</td>'
        . '</tr>';
}

$message_block = $message !== ''
    ? '<div style="background:#f4efe6;padding:16px;border-radius:6px">'
      . '<div style="font-size:11px;letter-spacing:.15em;text-transform:uppercase;color:#a6854c;margin-bottom:6px">Message</div>'
      . '<div style="font-size:14px;line-height:1.6;white-space:pre-wrap">' . esc($message) . '</div></div>'
    : '';

$staff_html = '<div style="font-family:Georgia,serif;max-width:560px;margin:0 auto;color:#2b2620">'
    . '<h2 style="font-weight:400;color:#33402f;border-bottom:1px solid #d8cdb7;padding-bottom:12px">New enquiry &mdash; Pax Ranch House</h2>'
    . '<table style="width:100%;border-collapse:collapse;margin:16px 0">' . $detail_rows . '</table>'
    . $message_block
    . '<p style="font-size:12px;color:#7a716a;margin-top:20px">Reply directly to this email to respond to ' . esc($name) . '.</p>'
    . '</div>';

$first_name = esc(explode(' ', $name)[0]);
$guest_html = '<div style="font-family:Georgia,serif;max-width:520px;margin:0 auto;color:#2b2620">'
    . '<h2 style="font-weight:400;color:#33402f">Thank you, ' . $first_name . '.</h2>'
    . '<p style="font-size:15px;line-height:1.7">We have received your enquiry and a member of our small team will reply personally, usually within 24 hours.</p>'
    . '<p style="font-size:15px;line-height:1.7">In the meantime, we look forward to welcoming you to the quiet of the Rift Valley.</p>'
    . '<p style="font-size:14px;color:#7a716a;margin-top:24px">&mdash; Pax Ranch House<br>Gilgil, Nakuru County, Kenya</p>'
    . '</div>';

// ---- Resend API call helper -------------------------------------------------
// Uses file_get_contents with a stream context (no cURL dependency).
// Requires allow_url_fopen = On, which is the default on GoDaddy shared hosting.
function resend_send($api_key, $payload) {
    $context = stream_context_create([
        'http' => [
            'method'        => 'POST',
            'header'        => "Authorization: Bearer " . $api_key . "\r\n"
                             . "Content-Type: application/json\r\n",
            'content'       => json_encode($payload),
            'timeout'       => 15,
            'ignore_errors' => true, // so we can read the body on non-2xx responses
        ],
    ]);

    $body = @file_get_contents('https://api.resend.com/emails', false, $context);

    // Parse the HTTP status code from $http_response_header (set by the request).
    $http = 0;
    if (isset($http_response_header[0]) &&
        preg_match('{HTTP/\S+\s+(\d{3})}', $http_response_header[0], $m)) {
        $http = (int) $m[1];
    }

    $transport_error = ($body === false) ? 'Request failed (allow_url_fopen may be disabled).' : '';

    return [
        'ok'         => ($http >= 200 && $http < 300),
        'http'       => $http,
        'body'       => ($body === false ? '' : $body),
        'curl_error' => $transport_error,
    ];
}

// ---- 1. Notify staff (reply-to = guest, so staff can reply directly) --------
$staff_result = resend_send($RESEND_API_KEY, [
    'from'     => 'Pax Ranch House <' . $ENQUIRY_FROM_EMAIL . '>',
    'to'       => [$STAFF_NOTIFY_EMAIL],
    'reply_to' => [$email],
    'subject'  => 'New enquiry from ' . $name . ($house !== '' ? ' — ' . $house : ''),
    'html'     => $staff_html,
]);

if (!$staff_result['ok']) {
    error_log('enquiry.php: Resend staff send failed — HTTP ' . $staff_result['http'] . ' — ' . $staff_result['body'] . ' — ' . $staff_result['curl_error']);
    http_response_code(502);
    echo json_encode(['ok' => false, 'error' => 'Could not send your enquiry. Please try again, or email us directly.']);
    exit;
}

// ---- 2. Auto-reply to guest (non-fatal if it fails) -------------------------
$guest_result = resend_send($RESEND_API_KEY, [
    'from'    => 'Pax Ranch House <' . $ENQUIRY_FROM_EMAIL . '>',
    'to'      => [$email],
    'subject' => 'We have received your enquiry — Pax Ranch House',
    'html'    => $guest_html,
]);

if (!$guest_result['ok']) {
    error_log('enquiry.php: Resend auto-reply failed (non-fatal) — HTTP ' . $guest_result['http'] . ' — ' . $guest_result['body']);
}

// ---- Done -------------------------------------------------------------------
echo json_encode(['ok' => true]);
