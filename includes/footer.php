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
          <img src="<?= BASE_URL ?>/assets/images/wintom-logo.png" alt="Wintom Curtain" width="1286" height="465" class="h-[48px] md:h-[56px] w-auto block">
        </div>
        <!-- Vertical Divider -->
        <div class="hidden sm:block w-px h-[40px] bg-[rgba(34,34,32,0.2)]"></div>
        <!-- CTA Text -->
        <div class="flex flex-col">
          <span class="font-jakarta font-semibold text-muted text-[11px] tracking-[2.42px] uppercase leading-[16.5px]">MARI WUJUDKAN</span>
          <span class="font-playfair font-normal text-dark text-[20px] sm:text-[24px] uppercase leading-[1.3]">RUANG YANG LEBIH INDAH</span>
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
          <svg class="size-5" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
            <path d="M18.308 14.792c0 .316-.069.624-.215.924a3.57 3.57 0 0 1-.59.855c-.393.43-.823.647-1.28.655-.326 0-.68-.083-1.06-.256a10.526 10.526 0 0 1-1.06-.59c-.376-.247-.77-.53-1.18-.855a17.3 17.3 0 0 1-1.171-1.163 16.98 16.98 0 0 1-.847-1.171 10.59 10.59 0 0 1-.59-1.052c-.17-.376-.256-.735-.256-1.06 0-.317.077-.625.23-.907a3.6 3.6 0 0 1 .623-.846c.359-.352.752-.53 1.163-.53.162 0 .325.034.47.103.154.068.29.17.393.316l1.368 1.924c.102.145.18.282.23.41.051.12.077.24.077.35 0 .137-.034.273-.102.402a1.57 1.57 0 0 1-.282.41l-.376.393a.252.252 0 0 0-.076.188c0 .034.008.077.025.12.026.043.051.077.069.111.103.188.282.42.538.693.265.273.53.538.812.795.29.256.563.478.845.674.273.196.47.316.667.376a.4.4 0 0 0 .12.034c.06 0 .12-.025.18-.077l.376-.368c.12-.12.248-.222.376-.282a.92.92 0 0 1 .4-.102.9.9 0 0 1 .35.068c.128.05.264.12.41.214l1.949 1.385c.145.102.248.222.316.367.06.146.094.299.094.47Z" fill="#242424" stroke="#242424" stroke-width=".5"/>
          </svg>
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

<!-- ===== FLOATING WHATSAPP BUTTON ===== -->
<a href="<?= WA_URL ?>?text=Halo+Wintom%2C+saya+ingin+konsultasi+gorden"
   target="_blank" rel="noopener"
   class="wa-float fixed bottom-4 right-4 sm:bottom-6 sm:right-6 z-50 bg-[#25d366] rounded-full p-[14px] shadow-lg hover:shadow-xl hover:scale-105 transition-all duration-200"
   aria-label="Chat WhatsApp Wintom">
  <svg class="size-6" viewBox="0 0 24 24" fill="white" xmlns="http://www.w3.org/2000/svg">
    <path d="M12 2C6.477 2 2 6.477 2 12c0 1.89.525 3.66 1.438 5.168L2 22l4.832-1.438A9.946 9.946 0 0 0 12 22c5.523 0 10-4.477 10-10S17.523 2 12 2Z"/>
    <path d="M8.94 7.8c.2-.5.75-1 1.5-1 .6 0 1 .3 1.25.6l1.25 2c.25.5.05 1-.25 1.3l-.5.5c.5.9 1.35 1.75 2.25 2.25l.5-.5c.3-.3.8-.5 1.3-.25l2 1.25c.3.25.6.65.6 1.25 0 .75-.5 1.3-1 1.5C17.25 17 14.75 17.5 12 14.75c-2.75-2.75-2.25-5.25-1.8-6.08C10.4 8.42 9.24 8 8.94 7.8Z" fill="#242424" opacity="0"/>
    <path d="M8.94 7.8c.2-.5.75-1 1.5-1 .6 0 1 .3 1.25.6l1.25 2c.25.5.05 1-.25 1.3l-.5.5c.5.9 1.35 1.75 2.25 2.25l.5-.5c.3-.3.8-.5 1.3-.25l2 1.25c.3.25.6.65.6 1.25 0 .75-.5 1.3-1 1.5C17.25 17 14.75 17.5 12 14.75c-2.75-2.75-2.25-5.25-1.8-6.08C10.4 8.42 9.24 8 8.94 7.8Z" fill="white"/>
  </svg>
</a>

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
