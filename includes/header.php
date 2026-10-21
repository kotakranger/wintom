<?php
// includes/header.php — Global header + navbar
// Usage: include_once __DIR__ . '/includes/header.php';
// Expects: $page_title (string), $active_nav (string)

require_once __DIR__ . '/config.php';
$page_title = $page_title ?? SITE_NAME;
$active_nav = $active_nav ?? '';
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

  <!-- Google Fonts -->

  <!-- Tailwind CSS via CDN -->
  <script src="https://cdn.tailwindcss.com"></script>
  <script>
    tailwind.config = {
      theme: {
        extend: {
          fontFamily: {
            'playfair': ['Georgia','"Times New Roman"','serif'],
            'jakarta': ['Arial','Helvetica','sans-serif'],
          },
          colors: {
            'brand': {
              DEFAULT: '#860524',
              dark: '#6a041c',
            },
            'dark': '#242424',
            'muted': '#686561',
            'cream': '#faf8f5',
            'cream-dark': '#f4f1ea',
            'border': '#ece8e1',
          },
          letterSpacing: {
            'widest-2': '1.65px',
            'widest-3': '1.8px',
            'widest-4': '2.64px',
          }
        }
      }
    }
  </script>
  <style>
    * { box-sizing: border-box; }
    body { background-color: #faf8f5; }
    /* Smooth scroll */
    html { scroll-behavior: smooth; }
    /* Sticky nav transition */
    #main-nav { transition: box-shadow 0.3s ease; }
    #main-nav.scrolled { box-shadow: 0 2px 20px rgba(0,0,0,0.08); }

    /* ===== Google Translate widget (header, kanan atas) ===== */
    #gt-wrap { display: flex; align-items: center; margin-left: 16px; flex-shrink: 0; }
    #google_translate_element { line-height: 0; }
    #google_translate_element .goog-te-gadget { font-size: 0; color: transparent; line-height: 0; }
    #google_translate_element .goog-te-gadget > span,
    #google_translate_element .goog-te-gadget > div:not(:first-child),
    #google_translate_element .goog-logo-link { display: none !important; }
    #google_translate_element .goog-te-combo {
      appearance: none; -webkit-appearance: none;
      margin: 0; width: 132px; max-width: 100%;
      font: 600 11px/1 Arial, Helvetica, sans-serif; letter-spacing: 0.06em; text-transform: uppercase;
      color: #242424; background: #fff url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='10' height='6' viewBox='0 0 10 6'%3E%3Cpath d='M1 1l4 4 4-4' fill='none' stroke='%23741122' stroke-width='1.6' stroke-linecap='round'/%3E%3C/svg%3E") no-repeat right 10px center;
      border: 1px solid #ece8e1; border-radius: 3px; padding: 10px 28px 10px 12px; cursor: pointer;
      transition: border-color .2s;
    }
    #google_translate_element .goog-te-combo:hover,
    #google_translate_element .goog-te-combo:focus { border-color: #860524; outline: none; }
    /* Sembunyikan banner/tooltip bawaan Google & cegah layout bergeser */
    body { top: 0 !important; }
    .skiptranslate > iframe, iframe.goog-te-banner-frame, #goog-gt-tt, .goog-tooltip { display: none !important; }
    .goog-text-highlight { background: none !important; box-shadow: none !important; }
    @media (max-width: 1023px) {
      #gt-wrap { margin-left: 8px; }
      #google_translate_element .goog-te-combo { width: 116px; padding: 9px 22px 9px 9px; font-size: 10px; background-position: right 7px center; }
    }
  </style>
</head>
<body class="font-jakarta text-dark antialiased">

<!-- ===== STICKY HEADER / NAVIGATION ===== -->
<header id="main-nav" class="fixed top-0 left-0 right-0 z-50 backdrop-blur-[6px] bg-[rgba(250,248,245,0.9)] border-b border-[rgba(236,232,225,0.7)]">
  <div class="h-[80px] max-w-[1280px] mx-auto w-full px-4 sm:px-6 lg:px-12 flex items-center justify-between gap-3">

    <!-- Brand Logo -->
    <a href="<?= BASE_URL ?>/" translate="no" class="notranslate flex flex-col items-start shrink-0 no-underline">
      <img src="<?= BASE_URL ?>/assets/images/wintom-logo.png" alt="Wintom Curtain" width="1286" height="465" class="h-[44px] w-auto block">
    </a>

    <!-- Primary Navigation -->
    <nav class="hidden lg:flex items-center gap-0">
      <?php
$nav_links = [
    'HOME' => BASE_URL . '/',
    'PRODUCTS' => BASE_URL . '/products.php',
    'GALLERY' => BASE_URL . '/gallery.php',
    'ABOUT' => BASE_URL . '/about.php',
    'CONTACT' => BASE_URL . '/about.php#contact',
];
foreach ($nav_links as $label => $href):
    $is_active = ($active_nav === strtolower($label));
    ?>
        <a href="<?= $href ?>"
           class="font-semibold text-[11px] tracking-[1.65px] uppercase whitespace-nowrap pl-10 <?= $is_active ? 'text-brand' : 'text-dark hover:text-brand' ?> transition-colors duration-200">
            <?= $label ?>
        </a>
        <?php endforeach; ?>
    </nav>

    <!-- CTA Button + Google Translate (kanan atas) -->
    <div class="flex items-center shrink-0">
    <a href="<?= WA_URL ?>?text=Halo+Wintom%2C+saya+ingin+konsultasi+gorden" target="_blank" rel="noopener"
       class="hidden md:inline-block bg-brand text-white font-semibold text-[12px] tracking-[1.8px] uppercase px-5 py-[10px] rounded-[3px] shadow-sm hover:bg-[#6a041c] transition-colors duration-200 whitespace-nowrap">
      GET A QUOTE
    </a>

    <div id="gt-wrap" class="notranslate" translate="no">
      <div id="google_translate_element" aria-label="Pilih bahasa"></div>
    </div>

    <!-- Hamburger (mobile / tablet) -->
    <button id="nav-toggle" type="button" aria-label="Buka menu" aria-expanded="false" aria-controls="mobile-menu"
            class="lg:hidden ml-2 size-10 flex items-center justify-center rounded-[3px] border border-[#ece8e1] bg-white text-dark">
      <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"><path d="M4 7h16M4 12h16M4 17h16"/></svg>
    </button>
    </div>
  </div>

  <!-- Mobile menu -->
  <div id="mobile-menu" class="hidden lg:hidden border-t border-[rgba(236,232,225,0.9)] bg-[rgba(250,248,245,0.98)] px-4 sm:px-6 py-3">
    <?php foreach ($nav_links as $label => $href):
        $is_active = ($active_nav === strtolower($label)); ?>
    <a href="<?= $href ?>" class="block py-3 font-semibold text-[12px] tracking-[1.65px] uppercase border-b border-[#ece8e1] <?= $is_active ? 'text-brand' : 'text-dark' ?>"><?= $label ?></a>
    <?php endforeach; ?>
    <a href="<?= WA_URL ?>?text=Halo+Wintom%2C+saya+ingin+konsultasi+gorden" target="_blank" rel="noopener"
       class="md:hidden mt-3 block text-center bg-brand text-white font-semibold text-[12px] tracking-[1.8px] uppercase px-5 py-3 rounded-[3px]">GET A QUOTE</a>
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
  }
</script>
<script src="https://translate.google.com/translate_a/element.js?cb=googleTranslateElementInit" async defer></script>

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
  })();

  // Sticky nav scroll effect
  window.addEventListener('scroll', () => {
    const nav = document.getElementById('main-nav');
    nav.classList.toggle('scrolled', window.scrollY > 20);
  });
</script>
