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
  <meta name="description" content="Wintom Curtain — Spesialis gorden premium, roller blind, dan motorized smart system untuk hunian dan komersial di Jabodetabek." />

  <!-- Google Fonts -->
  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;600;700&family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet" />

  <!-- Tailwind CSS via CDN -->
  <script src="https://cdn.tailwindcss.com"></script>
  <script>
    tailwind.config = {
      theme: {
        extend: {
          fontFamily: {
            'playfair': ['"Playfair Display"', 'serif'],
            'jakarta': ['"Plus Jakarta Sans"', 'sans-serif'],
          },
          colors: {
            'brand': {
              DEFAULT: '#741122',
              dark: '#5a0d1a',
            },
            'dark': '#222220',
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
  </style>
</head>
<body class="font-jakarta text-dark antialiased">

<!-- ===== STICKY HEADER / NAVIGATION ===== -->
<header id="main-nav" class="fixed top-0 left-0 right-0 z-50 backdrop-blur-[6px] bg-[rgba(250,248,245,0.9)] border-b border-[rgba(236,232,225,0.7)]">
  <div class="h-[80px] max-w-[1280px] mx-auto w-full px-12 flex items-center justify-between">

    <!-- Brand Logo -->
    <a href="<?= BASE_URL ?>/" class="flex flex-col items-start shrink-0 no-underline">
      <span class="font-playfair font-bold text-brand text-[24px] leading-[32px] tracking-[-0.6px] whitespace-nowrap">wintom</span>
      <span class="font-jakarta font-semibold text-dark text-[9px] tracking-[3.15px] uppercase leading-[13.5px] whitespace-nowrap">CURTAIN</span>
    </a>

    <!-- Primary Navigation -->
    <nav class="flex items-center gap-0">
      <?php
      $nav_links = [
        'HOME'     => BASE_URL . '/',
        'PRODUCTS' => BASE_URL . '/products.php',
        'GALLERY'  => BASE_URL . '/gallery.php',
        'ABOUT'    => BASE_URL . '/about.php',
        'CONTACT'  => BASE_URL . '/about.php#contact',
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

    <!-- CTA Button -->
    <a href="<?= WA_URL ?>?text=Halo+Wintom%2C+saya+ingin+konsultasi+gorden" target="_blank" rel="noopener"
       class="bg-brand text-white font-semibold text-[12px] tracking-[1.8px] uppercase px-5 py-[10px] rounded-[3px] shadow-sm hover:bg-[#5a0d1a] transition-colors duration-200 whitespace-nowrap">
      GET A QUOTE
    </a>
  </div>
</header>

<!-- Spacer untuk fixed header -->
<div class="h-[81px]"></div>

<script>
  // Sticky nav scroll effect
  window.addEventListener('scroll', () => {
    const nav = document.getElementById('main-nav');
    nav.classList.toggle('scrolled', window.scrollY > 20);
  });
</script>
