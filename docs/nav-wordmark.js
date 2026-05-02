/* ── CAIRN / ADVISORY width-matching (canonical brand rule) ──
   ADVISORY must letter-space to exactly match CAIRN's rendered width.
   Measure both elements' total box widths, zero ADVISORY LS, then
   distribute the deficit evenly across all 8 letter-spacing slots
   (one per letter, including the trailing slot CAIRN also carries). */
(function () {
  function doMatch() {
    document.querySelectorAll('[data-wm-cairn]').forEach(function (ce) {
      var id = ce.getAttribute('data-wm-cairn');
      var ae = document.querySelector('[data-wm-advisory="' + id + '"]');
      if (!ae) return;
      var cairnTotal = ce.getBoundingClientRect().width;
      if (cairnTotal <= 0) return;
      ae.style.letterSpacing = '0px';
      var advNat = ae.getBoundingClientRect().width;
      if (advNat <= 0) return;
      ae.style.letterSpacing = Math.max(0, (cairnTotal - advNat) / 8) + 'px';
    });
  }
  function matchWordmarks() {
    requestAnimationFrame(function () { requestAnimationFrame(doMatch); });
  }
  Promise.all([
    document.fonts.load('300 26px Figtree'),
    document.fonts.load('500 9px Figtree'),
    document.fonts.load('300 17px Figtree'),
    document.fonts.load('500 7px Figtree')
  ]).then(function () {
    matchWordmarks();
    setTimeout(matchWordmarks, 200);
  }).catch(function () {
    setTimeout(matchWordmarks, 400);
    setTimeout(matchWordmarks, 1000);
  });
  document.fonts.ready.then(matchWordmarks);
  window.addEventListener('load', function () {
    matchWordmarks();
    setTimeout(matchWordmarks, 300);
    setTimeout(matchWordmarks, 900);
  });
  window.addEventListener('resize', matchWordmarks);
  if (window.ResizeObserver) {
    var ro = new ResizeObserver(matchWordmarks);
    document.querySelectorAll('[data-wm-cairn]').forEach(function (el) { ro.observe(el); });
  }
})();
