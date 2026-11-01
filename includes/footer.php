<?php
// includes/footer.php — Global footer
require_once __DIR__ . '/config.php';
?>

<!-- ===== PRE-FOOTER CONTACT BANNER ===== -->
<section class="bg-[#ebe7df] border-t border-[#ece8e1] pt-12 pb-12 md:pt-[65px] md:pb-[64px]">
  <div class="max-w-[1280px] mx-auto px-4 sm:px-6 lg:px-12">

    <!-- Top row: Logo + CTA text + Button -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-6 pb-10 md:pb-[49px] border-b border-[rgba(34,34,32,0.15)]">
      <div class="flex flex-col sm:flex-row sm:items-center gap-4 sm:gap-6">
        <!-- Brand Logo -->
        <div class="flex flex-col items-start shrink-0">
          <picture><source srcset="<?= BASE_URL ?>/assets/images/wintom-logo-480.webp" type="image/webp"><img src="<?= BASE_URL ?>/assets/images/wintom-logo-480.png" alt="Wintom Curtain" width="480" height="174" class="h-[48px] md:h-[56px] w-auto block"></picture>
        </div>
        <!-- Vertical Divider -->
        <div class="hidden sm:block w-px h-[40px] bg-[rgba(34,34,32,0.2)]"></div>
        <!-- CTA Text -->
        <div class="flex flex-col">
          <span class="font-jakarta font-semibold text-muted text-[11px] tracking-[2.42px] uppercase leading-[16.5px]">MARI WUJUDKAN</span>
          <span class="font-playfair font-normal text-dark text-[22px] sm:text-[24px] leading-[1.25]">Ruang yang Lebih Indah</span>
        </div>
      </div>
      <!-- Contact Button -->
      <button type="button" data-open-quote
         class="self-stretch sm:self-start md:self-auto flex items-center justify-center gap-2 bg-brand text-white font-semibold text-[12px] tracking-[1.8px] uppercase px-8 py-[14px] rounded-[2px] hover:bg-[#6a041c] transition-colors duration-200 whitespace-nowrap">
        HUBUNGI KAMI <span aria-hidden="true">→</span>
      </button>
    </div>

    <!-- Bottom row: 4 Contact Channels -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 pt-8 md:pt-10 gap-6 lg:gap-8">

      <!-- WhatsApp -->
      <a href="<?= WA_URL ?>" target="_blank" rel="noopener" class="flex items-center min-w-0 group">
        <div class="border border-[rgba(34,34,32,0.2)] rounded-full flex items-center justify-center size-[40px] shrink-0 group-hover:border-brand transition-colors duration-200">
          <svg class="size-5" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
            <path d="M10 1.667A8.333 8.333 0 0 0 2.92 13.742L1.667 18.333l4.725-1.237A8.333 8.333 0 1 0 10 1.667Z" fill="#25D366" stroke="#25D366" stroke-width="0.5" stroke-linejoin="round"/>
            <path d="M7.5 6.667c.167-.417.625-.834 1.25-.834.5 0 .833.25 1.042.5l1.041 1.667c.208.417.042.833-.208 1.083l-.417.417c.417.75 1.125 1.458 1.875 1.875l.417-.417c.25-.25.667-.417 1.083-.208l1.667 1.041c.25.209.5.542.5 1.042 0 .625-.417 1.083-.833 1.25C13.75 14.167 11.25 14.583 8.333 11.667 5.417 8.75 5.833 6.25 6.25 5.417Z" fill="white"/>
          </svg>
        </div>
        <div class="flex flex-col gap-[5.5px] pl-3 pb-[2.5px] min-w-0">
          <span class="font-jakarta font-semibold text-muted text-[10px] tracking-[1.5px] uppercase leading-[15px]">WHATSAPP</span>
          <span class="font-jakarta font-medium text-dark text-[12px] leading-[16px] whitespace-nowrap"><?= SITE_PHONE ?></span>
        </div>
      </a>

      <!-- Call Us -->
      <a href="tel:<?= SITE_PHONE_RAW ?>" class="flex items-center min-w-0 group">
        <div class="border border-[rgba(34,34,32,0.2)] rounded-full flex items-center justify-center size-[40px] shrink-0 group-hover:border-brand transition-colors duration-200">
          <svg class="size-5" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M2.25 6.75c0 8.284 6.716 15 15 15h2.25a2.25 2.25 0 0 0 2.25-2.25v-1.372c0-.516-.351-.966-.852-1.091l-4.423-1.106c-.44-.11-.902.055-1.173.417l-.97 1.293c-.282.376-.769.542-1.21.38a12.035 12.035 0 0 1-7.143-7.143c-.162-.441.004-.928.38-1.21l1.293-.97c.363-.271.527-.734.417-1.173L6.963 3.102a1.125 1.125 0 0 0-1.091-.852H4.5A2.25 2.25 0 0 0 2.25 4.5v2.25Z" stroke="#242424" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
        </div>
        <div class="flex flex-col gap-[5.5px] pl-3 pb-[2.5px] min-w-0">
          <span class="font-jakarta font-semibold text-muted text-[10px] tracking-[1.5px] uppercase leading-[15px]">TELEPON</span>
          <span class="font-jakarta font-medium text-dark text-[12px] leading-[16px] whitespace-nowrap"><?= SITE_PHONE ?></span>
        </div>
      </a>

      <!-- Email -->
      <a href="mailto:<?= SITE_EMAIL ?>" class="flex items-center min-w-0 group">
        <div class="border border-[rgba(34,34,32,0.2)] rounded-full flex items-center justify-center size-[40px] shrink-0 group-hover:border-brand transition-colors duration-200">
          <svg class="size-5" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
            <path d="M1.667 5.833A2.5 2.5 0 0 1 4.167 3.333h11.666a2.5 2.5 0 0 1 2.5 2.5v8.334a2.5 2.5 0 0 1-2.5 2.5H4.167a2.5 2.5 0 0 1-2.5-2.5V5.833Z" stroke="#242424" stroke-width="1.25" stroke-linejoin="round"/>
            <path d="m1.667 5.833 7.024 5.185a2.5 2.5 0 0 0 2.952 0l7.024-5.185" stroke="#242424" stroke-width="1.25" stroke-linejoin="round"/>
          </svg>
        </div>
        <div class="flex flex-col gap-[5.5px] pl-3 pb-[2.5px] min-w-0">
          <span class="font-jakarta font-semibold text-muted text-[10px] tracking-[1.5px] uppercase leading-[15px]">EMAIL</span>
          <span class="font-jakarta font-medium text-dark text-[12px] leading-[16px] break-all"><?= SITE_EMAIL ?></span>
        </div>
      </a>

      <!-- Showroom -->
      <div class="flex items-center min-w-0">
        <div class="border border-[rgba(34,34,32,0.2)] rounded-full flex items-center justify-center size-[40px] shrink-0">
          <svg class="size-5" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
            <path d="M10 1.667a6.25 6.25 0 0 0-6.25 6.25c0 4.688 6.25 10.416 6.25 10.416s6.25-5.728 6.25-10.417A6.25 6.25 0 0 0 10 1.667Z" stroke="#242424" stroke-width="1.25" stroke-linejoin="round"/>
            <circle cx="10" cy="7.917" r="2.083" stroke="#242424" stroke-width="1.25"/>
          </svg>
        </div>
        <div class="flex flex-col gap-[5.5px] pl-3 pb-[2.5px] min-w-0">
          <span class="font-jakarta font-semibold text-muted text-[10px] tracking-[1.5px] uppercase leading-[15px]">KUNJUNGI SHOWROOM</span>
          <span class="font-jakarta font-medium text-dark text-[12px] leading-[16px]"><?= SITE_ADDRESS_SHORT ?></span>
        </div>
      </div>

    </div>
  </div>
</section>

<!-- ===== MINIMALIST FOOTER ===== -->
<footer class="bg-[#e4dfd5] border-t border-[rgba(34,34,32,0.1)] py-[25px]">
  <div class="max-w-[1280px] mx-auto px-4 sm:px-6 md:px-12 flex flex-col-reverse md:flex-row items-center justify-between gap-4">
    <span class="font-jakarta font-normal text-muted text-[11px] tracking-[0.5px] leading-[15px]">
      &copy; <?= date('Y') ?> Wintom. Hak cipta dilindungi.
    </span>
    <div class="flex items-center gap-4 text-dark">
      <a href="<?= SITE_INSTAGRAM ?>" target="_blank" rel="noopener" aria-label="Instagram Wintom" class="hover:text-brand transition-colors"><svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><rect x="3" y="3" width="18" height="18" rx="5"/><circle cx="12" cy="12" r="4"/><circle cx="17.5" cy="6.5" r="1" fill="currentColor"/></svg></a>
      <a href="<?= SITE_TIKTOK ?>" target="_blank" rel="noopener" aria-label="TikTok Wintom" class="hover:text-brand transition-colors"><svg class="size-4" viewBox="0 0 24 24" fill="currentColor"><path d="M16.6 5.8A4.3 4.3 0 0 1 15.5 3h-3.1v12.4a2.6 2.6 0 1 1-2.6-2.6c.3 0 .5 0 .8.1V9.7a5.8 5.8 0 1 0 5 5.7V9.1a7.3 7.3 0 0 0 4.2 1.3V7.3a4.3 4.3 0 0 1-3.2-1.5Z"/></svg></a>
      <a href="<?= SITE_FACEBOOK ?>" target="_blank" rel="noopener" aria-label="Facebook Wintom" class="hover:text-brand transition-colors"><svg class="size-4" viewBox="0 0 24 24" fill="currentColor"><path d="M13.5 21v-7.5h2.6l.4-3h-3V8.6c0-.9.3-1.5 1.5-1.5h1.6V4.4c-.3 0-1.2-.1-2.3-.1-2.3 0-3.8 1.4-3.8 3.9v2.3H7.9v3h2.6V21h3Z"/></svg></a>
    </div>
  </div>
</footer>

<!-- ===== FLOATING WHATSAPP: pilih topik ===== -->
<?php include __DIR__ . '/wa-fab.php'; ?>

<?php include __DIR__ . '/inquiry-modal.php'; ?>

<script>
  // Animasi muncul saat scroll. Setelah selesai, atribut dilepas agar transisi hover kembali normal.
  (function () {
    const els = document.querySelectorAll('[data-reveal]');
    const done = function (el) {
      el.removeAttribute('data-reveal');
      el.classList.remove('is-visible');
      el.style.removeProperty('--d');
    };
    if (!('IntersectionObserver' in window) || window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
      els.forEach(done);
      return;
    }
    const pending = new Set(els);
    const show = function (el) {
      if (!pending.delete(el)) return;
      io.unobserve(el);
      el.addEventListener('transitionend', function handler(ev) {
        if (ev.target !== el || ev.propertyName !== 'opacity') return;
        el.removeEventListener('transitionend', handler);
        done(el);
      });
      el.classList.add('is-visible');
      window.setTimeout(function () { done(el); }, 1400);   // cadangan bila transitionend tidak terpicu
    };
    const io = new IntersectionObserver(function (entries) {
      entries.forEach(function (e) { if (e.isIntersecting) show(e.target); });
    }, { rootMargin: '0px 0px -8% 0px', threshold: 0.12 });
    els.forEach(function (el) { io.observe(el); });

    // Elemen yang terlewati (scroll cepat / loncat ke #anchor) langsung ditampilkan tanpa animasi
    let ticking = false;
    const sweep = function () {
      ticking = false;
      pending.forEach(function (el) {
        if (el.getBoundingClientRect().bottom < 0) { pending.delete(el); io.unobserve(el); done(el); }
      });
      if (!pending.size) window.removeEventListener('scroll', onScroll);
    };
    const onScroll = function () { if (!ticking) { ticking = true; requestAnimationFrame(sweep); } };
    window.addEventListener('scroll', onScroll, { passive: true });
    window.addEventListener('load', sweep);
  })();
</script>

</body>
</html>
