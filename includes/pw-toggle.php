<?php
// includes/pw-toggle.php — Tombol mata (tampilkan/sembunyikan) otomatis di setiap <input type="password">.
// Cukup di-include sekali di bawah halaman. Pakai gaya inline agar tidak bergantung pada build Tailwind.
?>
<script>
(function () {
  var EYE = '<svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M2 12s3.6-7 10-7 10 7 10 7-3.6 7-10 7S2 12 2 12z"/><circle cx="12" cy="12" r="3"/></svg>';
  var EYE_OFF = '<svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M3 3l18 18"/><path d="M10.6 5.1A10.6 10.6 0 0 1 12 5c6.4 0 10 7 10 7a17 17 0 0 1-3.2 4.1M6.5 6.5C3.8 8.3 2 12 2 12s3.6 7 10 7a10 10 0 0 0 4.4-1"/><path d="M9.9 9.9a3 3 0 0 0 4.2 4.2"/></svg>';
  document.querySelectorAll('input[type="password"]').forEach(function (input) {
    var wrap = document.createElement('div');
    wrap.style.position = 'relative';
    input.parentNode.insertBefore(wrap, input);
    wrap.appendChild(input);
    input.style.paddingRight = '44px';
    var btn = document.createElement('button');
    btn.type = 'button';
    btn.setAttribute('aria-label', 'Tampilkan password');
    btn.setAttribute('aria-pressed', 'false');
    btn.innerHTML = EYE;
    btn.style.cssText = 'position:absolute;top:0;right:0;height:100%;width:42px;display:flex;align-items:center;justify-content:center;background:none;border:0;padding:0;cursor:pointer;color:#686561';
    btn.addEventListener('mouseenter', function () { btn.style.color = '#860524'; });
    btn.addEventListener('mouseleave', function () { btn.style.color = '#686561'; });
    btn.addEventListener('click', function () {
      var show = input.type === 'password';
      input.type = show ? 'text' : 'password';
      btn.innerHTML = show ? EYE_OFF : EYE;
      btn.setAttribute('aria-label', show ? 'Sembunyikan password' : 'Tampilkan password');
      btn.setAttribute('aria-pressed', show ? 'true' : 'false');
      input.focus({ preventScroll: true });
    });
    wrap.appendChild(btn);
  });
})();
</script>
