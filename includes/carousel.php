<?php
// includes/carousel.php — Carousel geser (scroll-snap) dipakai Testimoni & Konten Wintom.
// Cetak sekali per halaman, lalu panggil wtCarousel(elemenRoot, { pauseWhen }).
// Root berisi: [data-c-track] (daftar slide), [data-c-prev], [data-c-next], [data-c-dots].
// Fitur: autoplay (jeda saat hover / fokus keyboard / disentuh / tab tersembunyi / di luar layar),
// panah kiri-kanan, titik indikator per posisi geser, keyboard ←/→, hormati prefers-reduced-motion.
if (!empty($GLOBALS['__carousel_js'])) return;
$GLOBALS['__carousel_js'] = true;
?>
<script>
window.wtCarousel = function (root, opts) {
  opts = opts || {};
  const track = root.querySelector('[data-c-track]');
  const dotsBox = root.querySelector('[data-c-dots]');
  const label = opts.label || 'slide';
  const INTERVAL = opts.interval || 5000;   // jeda antar geser otomatis (ms)
  const reduced = window.matchMedia && matchMedia('(prefers-reduced-motion: reduce)').matches;
  let elapsed = 0, hover = false, focus = false, touching = false, visible = false, dots = [];

  const maxScroll = () => Math.max(0, track.scrollWidth - track.clientWidth);
  // titik henti = posisi kiri tiap slide yang masih bisa dicapai (+ ujung kanan); slide boleh beda lebar
  const points = () => {
    const kids = Array.from(track.children), base = kids[0].offsetLeft, max = maxScroll(), pts = [];
    kids.forEach(k => { const x = k.offsetLeft - base; if (x <= max + 2 && (!pts.length || x - pts[pts.length - 1] > 4)) pts.push(Math.min(x, max)); });
    if (max - pts[pts.length - 1] > 4) pts.push(max);
    return pts;
  };
  const current = () => {
    const pts = points(), at = track.scrollLeft; let best = 0;
    pts.forEach((x, i) => { if (Math.abs(x - at) < Math.abs(pts[best] - at)) best = i; });
    return best;
  };
  const scrollX = x => track.scrollTo({ left: x, behavior: reduced ? 'auto' : 'smooth' });

  function buildDots() {
    const n = points().length;
    dotsBox.replaceChildren(); dots = [];
    if (n < 2) { dotsBox.parentNode.style.display = 'none'; return; }
    dotsBox.parentNode.style.display = '';
    for (let i = 0; i < n; i++) {
      const d = document.createElement('button');
      d.type = 'button';
      d.setAttribute('aria-label', 'Ke ' + label + ' ' + (i + 1));
      d.className = 'size-2.5 rounded-full transition-all duration-300 bg-[#d9d2c5] hover:bg-[#b9ad9b]';
      d.addEventListener('click', () => { scrollX(points()[i]); elapsed = 0; });
      dotsBox.appendChild(d); dots.push(d);
    }
    paint();
  }
  function paint() {
    const cur = current();
    dots.forEach((d, i) => {
      const on = i === cur;
      d.classList.toggle('bg-brand', on);
      d.classList.toggle('scale-125', on);
      d.classList.toggle('bg-[#d9d2c5]', !on);
      if (on) d.setAttribute('aria-current', 'true'); else d.removeAttribute('aria-current');
    });
  }
  function go(dir) {
    const at = track.scrollLeft, max = maxScroll(), pts = points();
    let target;
    if (dir > 0) target = at >= max - 2 ? 0 : (pts.find(x => x > at + 4) ?? max);   // ujung kanan -> kembali ke awal
    else target = at <= 2 ? max : ([...pts].reverse().find(x => x < at - 4) ?? 0);  // ujung kiri -> loncat ke akhir
    scrollX(target); elapsed = 0;
  }

  root.querySelector('[data-c-prev]').addEventListener('click', () => go(-1));
  root.querySelector('[data-c-next]').addEventListener('click', () => go(1));
  track.addEventListener('keydown', e => {
    if (e.key === 'ArrowLeft') { e.preventDefault(); go(-1); }
    else if (e.key === 'ArrowRight') { e.preventDefault(); go(1); }
  });
  // geser manual (jari / trackpad) menyetel ulang hitungan autoplay
  let settle = 0;
  track.addEventListener('scroll', () => { paint(); clearTimeout(settle); settle = setTimeout(() => { elapsed = 0; }, 150); }, { passive: true });
  window.addEventListener('resize', buildDots);
  root.addEventListener('mouseenter', () => { hover = true; });
  root.addEventListener('mouseleave', () => { hover = false; });
  // hanya fokus keyboard yang menjeda (fokus bekas klik mouse pada tombol tidak boleh menghentikan autoplay)
  root.addEventListener('focusin', e => { focus = !!(e.target.matches && e.target.matches(':focus-visible')); });
  root.addEventListener('focusout', () => { focus = false; });
  root.addEventListener('touchstart', () => { touching = true; }, { passive: true });
  root.addEventListener('touchend', () => { setTimeout(() => { touching = false; }, 1500); }, { passive: true });
  if ('IntersectionObserver' in window) {
    new IntersectionObserver(es => { visible = es[0].isIntersecting; }, { threshold: 0.3 }).observe(root);
  } else { visible = true; }
  if (!reduced) {
    setInterval(() => {
      if (!visible || hover || focus || touching || document.hidden) return;
      if (opts.pauseWhen && opts.pauseWhen()) { elapsed = 0; return; }
      elapsed += 100;
      if (elapsed >= INTERVAL) go(1);
    }, 100);
  }
  buildDots();
};
</script>
