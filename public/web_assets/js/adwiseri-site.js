/* =====================================================================
   adwiseri — public site interactions
   Header (scroll state, mega menu, mobile drawer), scroll progress,
   back-to-top, reveal-on-scroll, and the homepage widgets. Every block
   is guarded, so the same file runs on every public page.
   ===================================================================== */
(function () {
  'use strict';

  var $ = function (s, c) { return (c || document).querySelector(s); };
  var $$ = function (s, c) { return Array.prototype.slice.call((c || document).querySelectorAll(s)); };
  var reduceMotion = window.matchMedia && matchMedia('(prefers-reduced-motion: reduce)').matches;
  var canHover = window.matchMedia && matchMedia('(hover: hover)').matches;

  /* ---------- Header offset (also set inline in nav.blade.php before paint) ---------- */
  var top = $('#siteTop');
  var announce = top ? $('.announce', top) : null;
  function syncOffsets() {
    if (!top) { return; }
    var root = document.documentElement;
    root.style.setProperty('--site-nav-height', top.offsetHeight + 'px');
    top.style.setProperty('--ann-h', (announce ? announce.offsetHeight : 0) + 'px');
  }
  syncOffsets();
  window.addEventListener('resize', syncOffsets);
  window.addEventListener('load', syncOffsets);

  /* ---------- Header state, progress bar, back-to-top, active link ---------- */
  var header = $('#siteHeader');
  var progress = $('#scrollProgress');
  var totop = $('#totop');
  var navLinks = $$('.site-nav > a[href^="#"]');
  var spySections = navLinks.map(function (a) { return document.getElementById(a.getAttribute('href').slice(1)); }).filter(Boolean);
  var ticking = false;

  function onScroll() {
    var y = window.scrollY || window.pageYOffset;
    var h = document.documentElement.scrollHeight - window.innerHeight;
    if (header) { header.classList.toggle('scrolled', y > 10); }
    if (top) { top.classList.toggle('collapsed', y > 10); }
    if (progress) { progress.style.transform = 'scaleX(' + (h > 0 ? y / h : 0) + ')'; }
    if (totop) { totop.classList.toggle('show', y > 900); }
    if (spySections.length) {
      var current = null;
      spySections.forEach(function (s) { if (s.getBoundingClientRect().top < window.innerHeight * 0.35) { current = s; } });
      navLinks.forEach(function (a) { a.classList.toggle('active', !!current && a.getAttribute('href') === '#' + current.id); });
    }
    ticking = false;
  }
  window.addEventListener('scroll', function () {
    // Header state is applied straight away (cheap class toggles), so a jump to an in-page
    // section never measures against the taller, uncollapsed header. The rest waits for a frame.
    var y = window.scrollY || window.pageYOffset;
    if (header) { header.classList.toggle('scrolled', y > 10); }
    if (top) { top.classList.toggle('collapsed', y > 10); }
    if (!ticking) { window.requestAnimationFrame(onScroll); ticking = true; }
  }, { passive: true });
  onScroll();
  if (totop) {
    totop.addEventListener('click', function () { window.scrollTo({ top: 0, behavior: reduceMotion ? 'auto' : 'smooth' }); });
  }

  /* ---------- Mobile drawer ---------- */
  var drawer = $('#drawer');
  var burger = $('#burger');
  function openDrawer() {
    if (!drawer) { return; }
    drawer.classList.add('open');
    drawer.setAttribute('aria-hidden', 'false');
    if (burger) { burger.setAttribute('aria-expanded', 'true'); }
    document.body.style.overflow = 'hidden';
    setTimeout(function () { var c = $('.drawer-panel [data-close]', drawer); if (c) { c.focus(); } }, 50);
  }
  function closeDrawer() {
    if (!drawer || !drawer.classList.contains('open')) { return; }
    drawer.classList.remove('open');
    drawer.setAttribute('aria-hidden', 'true');
    if (burger) { burger.setAttribute('aria-expanded', 'false'); burger.focus(); }
    document.body.style.overflow = '';
  }
  if (drawer && burger) {
    burger.addEventListener('click', openDrawer);
    $$('[data-close]', drawer).forEach(function (el) { el.addEventListener('click', closeDrawer); });
    var mAcc = $('#mAcc');
    if (mAcc) {
      $('button', mAcc).addEventListener('click', function (e) {
        var o = mAcc.classList.toggle('open');
        e.currentTarget.setAttribute('aria-expanded', o ? 'true' : 'false');
      });
    }
    window.addEventListener('resize', function () { if (window.innerWidth > 1100) { closeDrawer(); } });
  }

  /* ---------- Mega menu: hover on desktop, click + keyboard everywhere ---------- */
  var dd = $('#ucDropdown');
  var setFilter = function () {};
  if (dd) {
    var ddBtn = $('.dd-toggle', dd);
    var ddTimer;
    var openDD = function (o) { dd.classList.toggle('open', o); ddBtn.setAttribute('aria-expanded', o ? 'true' : 'false'); };
    dd.addEventListener('mouseenter', function () { if (canHover) { clearTimeout(ddTimer); openDD(true); } });
    dd.addEventListener('mouseleave', function () { ddTimer = setTimeout(function () { openDD(false); }, 160); });
    ddBtn.addEventListener('click', function () { openDD(!dd.classList.contains('open')); });
    document.addEventListener('click', function (e) { if (!dd.contains(e.target)) { openDD(false); } });
    $$('.mega-link', dd).forEach(function (a) {
      a.addEventListener('click', function () {
        openDD(false);
        if (a.dataset.uc) { setFilter(a.dataset.uc); }
      });
    });
    document.addEventListener('keydown', function (e) { if (e.key === 'Escape') { openDD(false); } });
  }
  document.addEventListener('keydown', function (e) { if (e.key === 'Escape') { closeDrawer(); } });

  /* ---------- Reveal on scroll ---------- */
  var revealEls = $$('.ds [data-reveal]');
  if ('IntersectionObserver' in window && !reduceMotion) {
    var io = new IntersectionObserver(function (entries) {
      entries.forEach(function (en) {
        if (en.isIntersecting) { en.target.classList.add('is-visible'); io.unobserve(en.target); }
      });
    }, { threshold: 0.12, rootMargin: '0px 0px -40px 0px' });
    revealEls.forEach(function (el) { io.observe(el); });
  } else {
    revealEls.forEach(function (el) { el.classList.add('is-visible'); });
  }

  /* ---------- Animated counters ---------- */
  var fmt = function (n, f) {
    if (f === 'k') { return n >= 1000 ? (n / 1000).toFixed(n % 1000 ? 1 : 0).replace('.0', '') + 'k' : String(n); }
    return n.toLocaleString();
  };
  var runCount = function (el) {
    var end = +el.dataset.count, f = el.dataset.format, dur = 1600, t0 = performance.now();
    if (reduceMotion || end === 0) { el.textContent = fmt(end, f); return; }
    var step = function (t) {
      var p = Math.min((t - t0) / dur, 1), e = 1 - Math.pow(1 - p, 3);
      el.textContent = fmt(Math.round(end * e), f);
      if (p < 1) { requestAnimationFrame(step); }
    };
    requestAnimationFrame(step);
  };
  // Final values are rendered server-side; only animate (from 0) when motion is allowed.
  var counters = reduceMotion ? [] : $$('[data-count]');
  counters.forEach(function (c) { if (+c.dataset.count > 0) { c.textContent = '0'; } });
  if ('IntersectionObserver' in window) {
    var cio = new IntersectionObserver(function (es) {
      es.forEach(function (en) { if (en.isIntersecting) { runCount(en.target); cio.unobserve(en.target); } });
    }, { threshold: 0.6 });
    counters.forEach(function (c) { cio.observe(c); });
  } else {
    counters.forEach(runCount);
  }

  /* ---------- Marquee: duplicate items for a seamless loop ---------- */
  var track = $('#marqueeTrack');
  if (track) {
    var n = track.children.length;
    track.innerHTML += track.innerHTML;
    $$('.chip', track).slice(n).forEach(function (c) { c.setAttribute('aria-hidden', 'true'); });
  }

  /* ---------- Hero mockup tilt ---------- */
  var mockWrap = $('#mockWrap'), mock = $('#mock');
  if (mockWrap && mock) {
    if (!reduceMotion && canHover) {
      mockWrap.addEventListener('mousemove', function (e) {
        var r = mockWrap.getBoundingClientRect();
        var x = (e.clientX - r.left) / r.width - 0.5, y = (e.clientY - r.top) / r.height - 0.5;
        mock.style.setProperty('--ry', (x * 6).toFixed(2) + 'deg');
        mock.style.setProperty('--rx', (4 - y * 6).toFixed(2) + 'deg');
      });
      mockWrap.addEventListener('mouseleave', function () { mock.style.setProperty('--ry', '0deg'); mock.style.setProperty('--rx', '0deg'); });
      setTimeout(function () { mock.style.setProperty('--rx', '0deg'); }, 1400);
    } else {
      mock.style.setProperty('--rx', '0deg');
    }
  }

  /* ---------- Feature card spotlight follows the cursor ---------- */
  $$('.ds .fcard').forEach(function (c) {
    c.addEventListener('pointermove', function (e) {
      var r = c.getBoundingClientRect();
      c.style.setProperty('--mx', (e.clientX - r.left) + 'px');
      c.style.setProperty('--my', (e.clientY - r.top) + 'px');
    });
  });

  /* ---------- Currency chips (invoice illustration) ---------- */
  $$('#currencies span').forEach(function (s) {
    s.addEventListener('mouseenter', function () {
      $$('#currencies span').forEach(function (x) { x.classList.remove('on'); });
      s.classList.add('on');
    });
  });

  /* ---------- Use-case filter ---------- */
  var filterBtns = $$('.uc-filter button');
  if (filterBtns.length) {
    setFilter = function (f) {
      filterBtns.forEach(function (b) {
        var on = b.dataset.filter === f;
        b.classList.toggle('on', on);
        b.setAttribute('aria-selected', on ? 'true' : 'false');
      });
      $$('#ucGrid .uc').forEach(function (c) {
        c.classList.toggle('hide', f !== 'all' && c.dataset.type !== f && c.dataset.type !== 'all');
      });
    };
    filterBtns.forEach(function (b) { b.addEventListener('click', function () { setFilter(b.dataset.filter); }); });
  }

  /* ---------- Pricing carousel arrows (tablet / mobile) ---------- */
  var plans = $('#plans');
  if (plans) {
    var scrollPlans = function (dir) {
      var card = $('.plan', plans);
      if (card) { plans.scrollBy({ left: dir * (card.offsetWidth + 16), behavior: 'smooth' }); }
    };
    var prev = $('#plansPrev'), next = $('#plansNext');
    if (prev) { prev.addEventListener('click', function () { scrollPlans(-1); }); }
    if (next) { next.addEventListener('click', function () { scrollPlans(1); }); }
    window.addEventListener('load', function () {
      var pop = $('.plan.popular', plans);
      if (pop && plans.scrollWidth > plans.clientWidth + 4) {
        plans.scrollLeft = pop.offsetLeft - plans.offsetLeft - (plans.clientWidth - pop.offsetWidth) / 2;
      }
    });
  }

  /* ---------- FAQ accordion (one open at a time) ---------- */
  var faqItems = $$('#faqList .qa');
  faqItems.forEach(function (qa) {
    $('button', qa).addEventListener('click', function () {
      var open = !qa.classList.contains('open');
      faqItems.forEach(function (q) { q.classList.remove('open'); $('button', q).setAttribute('aria-expanded', 'false'); });
      if (open) { qa.classList.add('open'); $('button', qa).setAttribute('aria-expanded', 'true'); }
    });
  });

  /* ---------- Newsletter: validate, then POST to email_subscription ---------- */
  var nl = $('#homepage-newsletter-form');
  if (nl) {
    var input = $('#homepage-newsletter-email');
    var row = $('#nlRow');
    var showError = function (message) {
      if (row) { row.classList.add('invalid'); }
      if (input) { input.setAttribute('aria-invalid', 'true'); input.focus(); }
      if (window.AdwiseriAlert && typeof window.AdwiseriAlert.oops === 'function') {
        window.AdwiseriAlert.oops(message);
      } else if (typeof window.Swal !== 'undefined') {
        window.Swal.fire({ icon: 'warning', title: 'Oops!', text: message, confirmButtonColor: '#695EEE', customClass: { icon: 'adwiseri-oops-icon' } });
      } else {
        window.alert(message);
      }
    };
    nl.addEventListener('submit', function (e) {
      if (nl.dataset.submitting === '1') { return; }
      e.preventDefault();
      var value = (input.value || '').trim();
      if (!value) { showError('This field is required.'); return; }
      if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(value)) { showError('Please enter a valid email address.'); return; }
      nl.dataset.submitting = '1';
      HTMLFormElement.prototype.submit.call(nl);
    });
    input.addEventListener('input', function () {
      if (row) { row.classList.remove('invalid'); }
      input.removeAttribute('aria-invalid');
    });
    if (nl.dataset.error) { showError(nl.dataset.error); }
  }
})();
