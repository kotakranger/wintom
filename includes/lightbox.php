<?php
// includes/lightbox.php — Lightbox foto layar penuh (tanpa crop) yang bisa digeser.
// Pakai di halaman publik: include sekali, lalu panggil dari JS:
//   openLightbox(daftarUrlFoto, indeksAwal, judul, kategori, onClose)
// onClose (opsional) dipanggil dengan indeks foto terakhir yang dilihat.
// Perilaku sama dengan lightbox di galeri: panah (desktop), geser jari / trackpad, ←/→, Esc, klik area gelap.
?>
<div id="lightbox" class="fixed inset-0 z-[9999] bg-black/90 hidden flex-col items-center justify-center" role="dialog" aria-modal="true" aria-labelledby="lightbox-title">
  <button type="button" data-lb-close aria-label="Tutup"
          class="absolute top-3 right-3 sm:top-5 sm:right-5 z-10 size-11 flex items-center justify-center rounded-full text-white/70 hover:text-white hover:bg-white/10 transition-colors">
    <svg class="size-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"><path d="M6 6l12 12M18 6 6 18"/></svg>
  </button>
  <div class="relative w-full max-w-[1200px] px-0 sm:px-16">
    <div id="lb-track" class="flex overflow-x-auto snap-x snap-mandatory no-scrollbar"></div>
    <button type="button" data-lb-prev aria-label="Foto sebelumnya"
            class="hidden sm:flex absolute left-2 top-1/2 -translate-y-1/2 size-11 items-center justify-center rounded-full bg-white/10 text-white hover:bg-white/20 transition-colors">
      <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="m15 18-6-6 6-6"/></svg>
    </button>
    <button type="button" data-lb-next aria-label="Foto berikutnya"
            class="hidden sm:flex absolute right-2 top-1/2 -translate-y-1/2 size-11 items-center justify-center rounded-full bg-white/10 text-white hover:bg-white/20 transition-colors">
      <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="m9 18 6-6-6-6"/></svg>
    </button>
  </div>
  <div class="mt-4 px-4 text-center">
    <p id="lightbox-cat" class="font-jakarta font-semibold text-white/50 text-[10px] tracking-[2px] uppercase mb-1"></p>
    <p id="lightbox-title" class="font-playfair font-normal text-white text-[16px] sm:text-[18px] m-0"></p>
    <p id="lightbox-count" class="font-jakarta text-white/50 text-[12px] mt-2 m-0"></p>
  </div>
</div>

<script>
(function () {
  const lb = document.getElementById('lightbox');
  const lbTrack = document.getElementById('lb-track');
  const lbCount = document.getElementById('lightbox-count');
  const lbPrev = lb.querySelector('[data-lb-prev]'), lbNext = lb.querySelector('[data-lb-next]');
  let lbTotal = 0, lastFocus = null, onCloseCb = null;

  const slideTo = function (dir) { lbTrack.scrollBy({ left: dir * lbTrack.clientWidth, behavior: 'smooth' }); };
  const lbIndex = function () { return Math.round(lbTrack.scrollLeft / Math.max(1, lbTrack.clientWidth)); };
  const lbUpdate = function () {
    const i = lbIndex();
    lbCount.textContent = lbTotal > 1 ? (i + 1) + ' / ' + lbTotal : '';
    lbPrev.style.visibility = lbTotal > 1 && i > 0 ? 'visible' : 'hidden';
    lbNext.style.visibility = lbTotal > 1 && i < lbTotal - 1 ? 'visible' : 'hidden';
  };

  window.openLightbox = function (images, start, title, cat, onClose) {
    lastFocus = document.activeElement;
    onCloseCb = typeof onClose === 'function' ? onClose : null;
    lbTotal = images.length;
    lbTrack.replaceChildren.apply(lbTrack, images.map(function (src, k) {
      const slide = document.createElement('div');
      slide.className = 'shrink-0 w-full snap-center flex items-center justify-center h-[70vh] sm:h-[76vh] px-3 sm:px-0';
      const img = document.createElement('img');
      img.src = src;
      img.alt = title + (images.length > 1 ? ' — foto ' + (k + 1) : '');
      img.className = 'max-w-full max-h-full object-contain rounded-[3px] shadow-2xl select-none';   // utuh, tidak terpotong
      img.draggable = false;
      slide.appendChild(img);
      return slide;
    }));
    document.getElementById('lightbox-title').textContent = title || '';
    document.getElementById('lightbox-cat').textContent = cat || '';
    lb.classList.remove('hidden');
    lb.classList.add('flex');
    document.documentElement.style.overflow = 'hidden';
    lbTrack.scrollLeft = (start || 0) * lbTrack.clientWidth;
    lbUpdate();
    lb.querySelector('[data-lb-close]').focus({ preventScroll: true });
  };

  window.closeLightbox = function () {
    if (lb.classList.contains('hidden')) return;
    const last = lbIndex();
    lb.classList.add('hidden');
    lb.classList.remove('flex');
    document.documentElement.style.overflow = '';
    if (lastFocus && lastFocus.focus) lastFocus.focus({ preventScroll: true });
    if (onCloseCb) onCloseCb(last);
  };

  lbTrack.addEventListener('scroll', lbUpdate, { passive: true });
  lbPrev.addEventListener('click', function () { slideTo(-1); });
  lbNext.addEventListener('click', function () { slideTo(1); });
  lb.querySelector('[data-lb-close]').addEventListener('click', closeLightbox);
  lb.addEventListener('click', function (e) {   // klik area gelap di luar foto = tutup
    if (e.target === lb || e.target.parentElement === lbTrack) closeLightbox();
  });
  document.addEventListener('keydown', function (e) {
    if (lb.classList.contains('hidden')) return;
    if (e.key === 'Escape') closeLightbox();
    else if (e.key === 'ArrowLeft') slideTo(-1);
    else if (e.key === 'ArrowRight') slideTo(1);
  });
})();
</script>
