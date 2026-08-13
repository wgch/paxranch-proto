// Pax Ranch House — shared behaviour: nav, scroll state, reveals, footer newsletter, carousels.
(function () {
  var nav = document.getElementById('nav');
  var mt = document.getElementById('menuToggle');
  if (mt && nav) mt.addEventListener('click', function () {
    var o = nav.classList.toggle('open');
    mt.setAttribute('aria-expanded', o);
  });
  if (nav) window.addEventListener('scroll', function () {
    nav.classList.toggle('scrolled', window.scrollY > 60);
  });

  var reveals = document.querySelectorAll('.reveal');
  if (reveals.length) {
    var io = new IntersectionObserver(function (es) {
      es.forEach(function (e) { if (e.isIntersecting) e.target.classList.add('in'); });
    }, { threshold: 0.15 });
    reveals.forEach(function (el) { io.observe(el); });
  }

  // Footer newsletter — prototype only: confirm inline, no backend yet.
  var nl = document.querySelector('.nl-form');
  if (nl) nl.addEventListener('submit', function (e) {
    e.preventDefault();
    var s = nl.querySelector('.nl-status');
    if (s) { s.textContent = 'Thank you — you’re on the list.'; s.classList.add('show'); }
    nl.reset();
  });

  // Card carousels — arrows scroll one card at a time.
  document.querySelectorAll('.carousel').forEach(function (c) {
    var track = c.querySelector('.car-track');
    if (!track) return;
    var step = function () {
      var card = track.querySelector('.car-card');
      return card ? card.getBoundingClientRect().width + 22 : 340;
    };
    var prev = c.querySelector('.car-btn.prev');
    var next = c.querySelector('.car-btn.next');
    if (prev) prev.addEventListener('click', function () { track.scrollBy({ left: -step(), behavior: 'smooth' }); });
    if (next) next.addEventListener('click', function () { track.scrollBy({ left: step(), behavior: 'smooth' }); });
  });
})();
