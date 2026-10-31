<?php
// includes/header.php — Global header + navbar
// Usage: include_once __DIR__ . '/includes/header.php';
// Expects: $page_title (string), $active_nav (string)

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/auth.php';
$page_title = $page_title ?? SITE_NAME;
$active_nav = $active_nav ?? '';

// Modal "Get a Quote" global (footer.php) butuh sesi CSRF — mulai sebelum ada output.
csrf_token();
$quote_categories = $quote_categories
    ?? get_db()->query('SELECT id, name, whatsapp FROM categories ORDER BY sort_order ASC, name ASC')->fetchAll();   // juga dipakai panel chat (wa-fab.php)
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title><?= htmlspecialchars($page_title) ?> | <?= SITE_NAME ?></title>
  <link rel="icon" type="image/png" href="<?= BASE_URL ?>/assets/images/favicon.png" />
  <link rel="apple-touch-icon" href="<?= BASE_URL ?>/assets/images/favicon.png" />
  <meta name="description" content="Wintom Curtain — Spesialis gorden premium, roller blind, dan motorized smart system untuk hunian dan komersial di Jabodetabek." />

  <!-- Font judul (Libre Caslon Text) di-host sendiri: tidak memblokir render & tanpa koneksi ke Google -->
  <link rel="preload" href="<?= BASE_URL ?>/assets/fonts/libre-caslon-text-400.woff2" as="font" type="font/woff2" crossorigin />

  <?php include __DIR__ . '/analytics.php'; ?>

  <!-- Penanda JS aktif: konten [data-reveal] hanya disembunyikan bila script animasi bisa berjalan -->
  <script>document.documentElement.classList.add('js');</script>

  <!-- Tailwind CSS hasil build (tailwind.config.js -> npm run build:css) -->
  <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/site.css?v=<?= @filemtime(__DIR__ . '/../assets/css/site.css') ?>" />
  <style>
    @font-face { font-family: 'Libre Caslon Text'; font-style: normal; font-weight: 400; font-display: swap; src: url(<?= BASE_URL ?>/assets/fonts/libre-caslon-text-400.woff2) format('woff2'); }
    @font-face { font-family: 'Libre Caslon Text'; font-style: normal; font-weight: 700; font-display: swap; src: url(<?= BASE_URL ?>/assets/fonts/libre-caslon-text-700.woff2) format('woff2'); }
    * { box-sizing: border-box; }
    :root { --wt-chevron: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='10' height='6' viewBox='0 0 10 6'%3E%3Cpath d='M1 1l4 4 4-4' fill='none' stroke='%23860524' stroke-width='1.6' stroke-linecap='round'/%3E%3C/svg%3E"); }
    /* Semua dropdown: panah chevron seragam & seimbang (sama dengan pemilih bahasa) */
    select:not(.goog-te-combo):not([multiple]) {
      appearance: none; -webkit-appearance: none;
      background-image: var(--wt-chevron); background-repeat: no-repeat;
      background-position: right 14px center; background-size: 10px 6px;
      padding-right: 40px;
    }
    /* overflow-x: clip mencegah geser horizontal di mobile tanpa mematikan position: sticky */
    html, body { overflow-x: clip; }
    body { background-color: #faf8f5; }
    .no-scrollbar { scrollbar-width: none; -ms-overflow-style: none; }
    .no-scrollbar::-webkit-scrollbar { display: none; }

    /* ===== Animasi ===== */
    @keyframes wt-rise { from { opacity: 0; transform: translateY(24px); } to { opacity: 1; transform: none; } }
    @keyframes wt-fade { from { opacity: 0; } to { opacity: 1; } }
    @keyframes wt-zoom-out { from { transform: scale(1.04); } to { transform: scale(1); } }
    @keyframes wt-sheet-up { from { transform: translateY(40px); opacity: 0; } to { transform: none; opacity: 1; } }
    @keyframes wt-pop { from { transform: translateY(12px) scale(.98); opacity: 0; } to { transform: none; opacity: 1; } }
    @keyframes wt-pulse-ring { 0% { box-shadow: 0 0 0 0 rgba(37,211,102,.45); } 70%, 100% { box-shadow: 0 0 0 14px rgba(37,211,102,0); } }
    /* Elemen hero muncul berurutan: beri kelas .wt-in + style="--d:<ms>" */
    .wt-in { opacity: 0; animation: wt-rise .9s cubic-bezier(.2,.7,.2,1) forwards; animation-delay: var(--d, 0ms); }
    .wt-zoom { animation: wt-zoom-out 2.4s cubic-bezier(.2,.7,.2,1) both; }
    /* Muncul saat di-scroll (lihat footer.php): geser pendek + fade, tanpa efek melompat */
    .js [data-reveal] { opacity: 0; transform: translateY(16px); transition: opacity .7s cubic-bezier(.22,.61,.36,1), transform .7s cubic-bezier(.22,.61,.36,1); transition-delay: var(--d, 0ms); }
    .js [data-reveal].is-visible { opacity: 1; transform: none; }
    #quote-modal:not(.hidden) [data-quote-backdrop] { animation: wt-fade .25s ease both; }
    #quote-modal:not(.hidden) [data-quote-panel] { animation: wt-sheet-up .35s cubic-bezier(.2,.7,.2,1) both; }
    @media (min-width: 640px) { #quote-modal:not(.hidden) [data-quote-panel] { animation-name: wt-pop; } }
    #mobile-menu:not(.hidden) { animation: wt-fade .2s ease both; }
    .wa-float { animation: wt-pulse-ring 2.4s ease-out 3s infinite; }
    /* Garis bawah animasi pada link navigasi desktop */
    .nav-link { position: relative; }
    .nav-link::after { content: ''; position: absolute; left: 40px; right: 0; bottom: -6px; height: 1.5px; background: #860524; transform: scaleX(0); transform-origin: left; transition: transform .3s ease; }
    .nav-link:hover::after, .nav-link.is-active::after { transform: scaleX(1); }
    @media (prefers-reduced-motion: reduce) {
      *, *::before, *::after { animation-duration: 1ms !important; animation-delay: 0ms !important; transition-duration: 1ms !important; scroll-behavior: auto !important; }
      .wt-in, .js [data-reveal] { opacity: 1; transform: none; }
    }
    /* Smooth scroll */
    html { scroll-behavior: smooth; }
    /* Sticky nav transition */
    #main-nav { transition: box-shadow 0.3s ease; }
    #main-nav.scrolled { box-shadow: 0 2px 20px rgba(0,0,0,0.08); }

    /* ===== Google Translate widget (header, kanan atas) ===== */
    #gt-wrap { display: flex; align-items: center; margin-left: 16px; flex-shrink: 0; }
    [data-gt-reset][hidden] { display: none !important; }
    #google_translate_element { line-height: 0; }
    #google_translate_element .gt-ph { text-align: left; }
    #google_translate_element .goog-te-gadget { font-size: 0; color: transparent; line-height: 0; }
    #google_translate_element .goog-te-gadget > span,
    #google_translate_element .goog-te-gadget > div:not(:first-child),
    #google_translate_element .goog-logo-link { display: none !important; }
    #google_translate_element :is(.goog-te-combo, .gt-ph) {
      appearance: none; -webkit-appearance: none;
      margin: 0; width: 132px; max-width: 100%; height: 38px;
      font: 600 11px/1 Arial, Helvetica, sans-serif; letter-spacing: 0.06em; text-transform: uppercase;
      color: #242424; background: #fff var(--wt-chevron) no-repeat right 12px center;
      border: 1px solid #ece8e1; border-radius: 3px; padding: 0 30px 0 12px; cursor: pointer;
      transition: border-color .2s;
    }
    #google_translate_element :is(.goog-te-combo, .gt-ph):hover,
    #google_translate_element :is(.goog-te-combo, .gt-ph):focus { border-color: #860524; outline: none; }
    /* Sembunyikan banner/tooltip bawaan Google & cegah layout bergeser */
    body { top: 0 !important; }
    .skiptranslate > iframe, iframe.goog-te-banner-frame, #goog-gt-tt, .goog-tooltip { display: none !important; }
    .goog-text-highlight { background: none !important; box-shadow: none !important; }
    @media (max-width: 1023px) {
      #gt-wrap { margin-left: 8px; }
      #google_translate_element :is(.goog-te-combo, .gt-ph) { width: 120px; padding: 0 26px 0 10px; font-size: 10px; background-position: right 10px center; }
    }
    @media (max-width: 479px) {
      #google_translate_element :is(.goog-te-combo, .gt-ph) { width: 116px; padding: 0 24px 0 9px; letter-spacing: 0.02em; background-position: right 9px center; }
    }
  </style>
</head>
<body class="font-jakarta text-dark antialiased">

<!-- ===== STICKY HEADER / NAVIGATION ===== -->
<header id="main-nav" class="fixed top-0 left-0 right-0 z-50 backdrop-blur-[6px] bg-[rgba(250,248,245,0.9)] border-b border-[rgba(236,232,225,0.7)]">
  <div class="h-[80px] max-w-[1280px] mx-auto w-full px-4 sm:px-6 lg:px-12 flex items-center justify-between gap-3">

    <!-- Brand Logo -->
    <a href="<?= BASE_URL ?>/" translate="no" class="notranslate flex flex-col items-start shrink-0 no-underline">
      <picture><source srcset="<?= BASE_URL ?>/assets/images/wintom-logo-480.webp" type="image/webp"><img src="<?= BASE_URL ?>/assets/images/wintom-logo-480.png" alt="Wintom Curtain" width="480" height="174" class="h-[36px] sm:h-[44px] w-auto block"></picture>
    </a>

    <!-- Primary Navigation -->
    <nav class="hidden lg:flex items-center gap-0">
      <?php
$nav_links = [
    // label => [url, key active_nav]
    'BERANDA'     => [BASE_URL . '/', 'home'],
    'PRODUK'      => [BASE_URL . '/products.php', 'products'],
    'GALERI'      => [BASE_URL . '/gallery.php', 'gallery'],
    'TENTANG KAMI'=> [BASE_URL . '/about.php', 'about'],
    'KONTAK'      => [BASE_URL . '/#contact', 'contact'],
];
foreach ($nav_links as $label => [$href, $key]):
    $is_active = ($active_nav === $key);
    ?>
        <a href="<?= $href ?>"
           class="nav-link font-semibold text-[11px] tracking-[1.65px] uppercase whitespace-nowrap pl-10 <?= $is_active ? 'is-active text-brand' : 'text-dark hover:text-brand' ?> transition-colors duration-200">
            <?= $label ?>
        </a>
        <?php endforeach; ?>
    </nav>

    <!-- CTA Button + Google Translate (kanan atas) -->
    <div class="flex items-center shrink-0">
    <button type="button" data-open-quote
       class="hidden md:inline-block bg-brand text-white font-semibold text-[12px] tracking-[1.8px] uppercase px-5 py-[10px] rounded-[3px] shadow-sm hover:bg-[#6a041c] transition-colors duration-200 whitespace-nowrap">
      MINTA PENAWARAN
    </button>

    <div id="gt-wrap" class="notranslate" translate="no">
      <div id="google_translate_element" aria-label="Pilih bahasa">
        <!-- Pengganti sementara: script Google Translate (±110 KB) baru dimuat saat pengunjung berinteraksi -->
        <button type="button" class="gt-ph" data-gt-load>Pilih Bahasa</button>
      </div>
      <!-- Muncul setelah pengunjung memilih bahasa lain: kembali ke Bahasa Indonesia (bawaan) -->
      <button type="button" data-gt-reset hidden title="Kembali ke Bahasa Indonesia" aria-label="Kembali ke Bahasa Indonesia"
              class="ml-1.5 size-[38px] shrink-0 flex items-center justify-center rounded-[3px] border border-[#ece8e1] bg-white text-dark hover:border-brand hover:text-brand transition-colors">
        <svg class="size-[18px]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M3 12a9 9 0 1 0 3-6.7"/><path d="M3 4v5h5"/></svg>
      </button>
    </div>

    <!-- Hamburger (mobile / tablet) -->
    <button id="nav-toggle" type="button" aria-label="Buka menu" aria-expanded="false" aria-controls="mobile-menu"
            class="lg:hidden ml-2 size-[38px] flex items-center justify-center rounded-[3px] border border-[#ece8e1] bg-white text-dark">
      <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"><path d="M4 7h16M4 12h16M4 17h16"/></svg>
    </button>
    </div>
  </div>

  <!-- Mobile menu -->
  <div id="mobile-menu" class="hidden lg:hidden border-t border-[rgba(236,232,225,0.9)] bg-[rgba(250,248,245,0.98)] px-4 sm:px-6 py-3">
    <?php foreach ($nav_links as $label => [$href, $key]):
        $is_active = ($active_nav === $key); ?>
    <a href="<?= $href ?>" class="block py-3 font-semibold text-[12px] tracking-[1.65px] uppercase border-b border-[#ece8e1] <?= $is_active ? 'text-brand' : 'text-dark' ?>"><?= $label ?></a>
    <?php endforeach; ?>
    <button type="button" data-open-quote
       class="md:hidden mt-3 block w-full text-center bg-brand text-white font-semibold text-[12px] tracking-[1.8px] uppercase px-5 py-3 rounded-[3px]">MINTA PENAWARAN</button>
    <button type="button" data-gt-reset hidden translate="no"
       class="notranslate mt-2 w-full flex items-center justify-center gap-2 border border-[#ece8e1] bg-white text-dark font-semibold text-[12px] tracking-[0.04em] px-5 py-3 rounded-[3px]">
      <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M3 12a9 9 0 1 0 3-6.7"/><path d="M3 4v5h5"/></svg>
      Kembali ke Bahasa Indonesia
    </button>
  </div>
</header>

<!-- Spacer untuk fixed header -->
<div class="h-[81px]"></div>

<script>
  // Google Translate: bahasa asli situs = Indonesia
  function googleTranslateElementInit() {
    new google.translate.TranslateElement({
      pageLanguage: 'id',
      includedLanguages: 'id,en,zh-CN,ja,ko,ar,ms,es,fr,de',
      layout: google.translate.TranslateElement.InlineLayout.VERTICAL,
      autoDisplay: false
    }, 'google_translate_element');
    var ph = document.querySelector('#google_translate_element .gt-ph');
    if (ph) ph.remove();
    var combo = document.querySelector('#google_translate_element .goog-te-combo');
    if (combo) {
      combo.addEventListener('change', function () { setTimeout(gtSyncReset, 300); });
      if (gtWantsPicker) { combo.focus(); try { combo.showPicker(); } catch (e) {} }
    }
  }

  // Muat script Google Translate hanya bila perlu: langsung bila bahasa lain sedang aktif,
  // selain itu saat pengunjung pertama kali berinteraksi (scroll, sentuh, klik, keyboard).
  var gtLoaded = false, gtWantsPicker = false;
  function gtLoad() {
    if (gtLoaded) return;
    gtLoaded = true;
    var s = document.createElement('script');
    s.src = 'https://translate.google.com/translate_a/element.js?cb=googleTranslateElementInit';
    s.async = true;
    document.head.appendChild(s);
  }

  // Tombol reset bahasa: tampil hanya bila cookie "googtrans" berisi bahasa selain Indonesia
  function gtCurrentLang() {
    var m = document.cookie.match(/(?:^|;\s*)googtrans=([^;]*)/);
    var lang = m ? decodeURIComponent(m[1]).split('/').pop() : '';
    return lang && lang !== 'id' ? lang : '';
  }
  function gtSyncReset() {
    var on = !!gtCurrentLang();
    document.querySelectorAll('[data-gt-reset]').forEach(function (b) { b.hidden = !on; });
  }
  function gtReset() {
    // Google menyimpan cookie di host & domain induknya (mis. .hostingersite.com) — hapus semuanya
    var parts = location.hostname.split('.'), domains = ['', location.hostname];
    for (var i = 0; i < parts.length - 1; i++) domains.push('.' + parts.slice(i).join('.'));
    domains.forEach(function (d) {
      document.cookie = 'googtrans=; expires=Thu, 01 Jan 1970 00:00:00 GMT; path=/' + (d ? '; domain=' + d : '');
    });
    location.reload();
  }
  document.querySelectorAll('[data-gt-reset]').forEach(function (b) { b.addEventListener('click', gtReset); });
  gtSyncReset();
</script>
<script>
  if (gtCurrentLang()) gtLoad();
  else ['pointerdown', 'touchstart', 'keydown', 'scroll', 'mousemove'].forEach(function (ev) {
    addEventListener(ev, gtLoad, { once: true, passive: true });
  });
  document.querySelector('[data-gt-load]').addEventListener('click', function () { gtWantsPicker = true; gtLoad(); });
</script>

<script>
  // Mobile menu toggle
  (function () {
    const btn = document.getElementById('nav-toggle');
    const menu = document.getElementById('mobile-menu');
    if (!btn || !menu) return;
    btn.addEventListener('click', function () {
      const open = menu.classList.toggle('hidden') === false;
      btn.setAttribute('aria-expanded', open ? 'true' : 'false');
    });
    // Tutup menu setelah memilih link / membuka modal (mis. #contact di halaman yang sama)
    menu.addEventListener('click', function (e) {
      if (!e.target.closest('a, button')) return;
      menu.classList.add('hidden');
      btn.setAttribute('aria-expanded', 'false');
    });
  })();

  // Sticky nav scroll effect
  window.addEventListener('scroll', () => {
    const nav = document.getElementById('main-nav');
    nav.classList.toggle('scrolled', window.scrollY > 20);
  });
</script>
