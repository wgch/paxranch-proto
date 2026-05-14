// Pax Ranch House — enquiry form submission.
// Posts the booking and contact forms to enquiry.php (Resend-backed),
// with inline loading / success / error states.

(function () {
  var forms = document.querySelectorAll('.enquiry-form');
  if (!forms.length) return;

  forms.forEach(function (form) {
    var statusEl = form.querySelector('.form-status');
    var button = form.querySelector('button[type="submit"]');
    var originalLabel = button ? button.textContent : 'Send';

    function setStatus(msg, kind) {
      if (!statusEl) return;
      statusEl.textContent = msg;
      statusEl.className = 'full form-status show ' + kind;
    }

    form.addEventListener('submit', function (e) {
      e.preventDefault();

      // Build payload from named fields.
      var payload = {};
      var fields = form.querySelectorAll('input, select, textarea');
      fields.forEach(function (field) {
        if (field.name) payload[field.name] = field.value;
      });
      payload.source = form.getAttribute('data-source') || 'website';

      if (button) {
        button.disabled = true;
        button.textContent = 'Sending…';
      }
      if (statusEl) statusEl.className = 'full form-status';

      fetch('enquiry.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(payload)
      })
        .then(function (res) {
          return res.json().catch(function () { return { ok: false }; });
        })
        .then(function (data) {
          if (data && data.ok) {
            form.reset();
            setStatus('Thank you — your enquiry has been sent. We will reply personally, usually within 24 hours.', 'ok');
          } else {
            setStatus(
              (data && data.error) ||
                'Something went wrong. Please try again, or email us directly at stays@paxranch.com.',
              'err'
            );
          }
        })
        .catch(function () {
          setStatus(
            'We could not reach the server. Please try again, or email us directly at stays@paxranch.com.',
            'err'
          );
        })
        .finally(function () {
          if (button) {
            button.disabled = false;
            button.textContent = originalLabel;
          }
        });
    });
  });
})();
