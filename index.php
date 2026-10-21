<?php
// Wintom Curtain — Homepage (index.php)
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/db.php';

$page_title = 'Beautiful Spaces Better Living';
$active_nav = 'home';

// Ambil produk unggulan dari DB (optional, fallback ke hardcode jika kosong)
$db = get_db();
require_once __DIR__ . '/includes/youtube.php';
$home_videos = $db->query('SELECT * FROM content_videos WHERE is_active = 1 ORDER BY sort_order ASC, id ASC')->fetchAll();
$featured = $db->query("SELECT p.*, c.name AS category FROM products p JOIN categories c ON c.id = p.category_id WHERE p.is_active = 1 ORDER BY p.created_at DESC LIMIT 4")->fetchAll();
?>
<?php include __DIR__ . '/includes/header.php'; ?>

<!-- ===================================================================
     HERO SECTION
===================================================================== -->
<section class="relative bg-[#242424] min-h-[720px] flex items-center justify-center overflow-hidden py-[55.5px]">

  <!-- Background video -->
  <video class="absolute inset-0 w-full h-full object-cover" autoplay muted loop playsinline preload="metadata"
         poster="https://images.unsplash.com/photo-1558618666-fcd25c85cd64?w=1920&q=85&fit=crop" aria-hidden="true">
    <source src="<?= BASE_URL ?>/assets/video/hero.mp4" type="video/mp4">
  </video>

  <!-- Dual scrim overlay -->
  <div class="absolute inset-0 bg-gradient-to-r from-[rgba(23,22,21,0.85)] via-[rgba(27,26,24,0.6)] to-[rgba(0,0,0,0.25)]"></div>
  <div class="absolute inset-0 bg-[rgba(255,255,255,0.2)]"></div>

  <!-- Content -->
  <div class="relative flex-1 max-w-[1280px] min-w-0 px-12 py-[112px]">
    <div class="grid grid-cols-12 gap-8" style="grid-template-rows: 385px;">

      <!-- Left: Editorial Content (8 cols) -->
      <div class="col-span-8 flex flex-col items-start justify-center self-center">

        <!-- Eyebrow label -->
        <div class="flex items-center pb-4 w-full">
          <div class="bg-[rgba(255,255,255,0.6)] h-px w-6 shrink-0"></div>
          <span class="font-jakarta font-semibold text-[12px] text-[rgba(255,255,255,0.8)] tracking-[2.64px] uppercase leading-[16px] pl-3 whitespace-nowrap">
            WINDOW DRESSING &amp; MOTORIZED BLIND
          </span>
        </div>

        <!-- Main heading -->
        <div class="drop-shadow-[0px_1px_0.5px_rgba(0,0,0,0.05)]">
          <h1 class="font-playfair font-normal text-white text-[40px] md:text-[54px] tracking-[-1px] leading-[1.12] max-w-[760px] m-0">
            Gorden &amp; Roller Blind — Ready Stock atau Custom Sesuai Ukuran
          </h1>
        </div>

        <!-- Red divider -->
        <div class="bg-brand h-[2px] w-14 my-6"></div>

        <!-- Sub-headline -->
        <div class="max-w-[560px]">
          <p class="font-jakarta text-[16px] md:text-[18px] text-[rgba(255,255,255,0.9)] leading-[1.6] m-0">
            Datang ke showroom PIK, lihat bahannya langsung — atau chat WhatsApp untuk konsultasi &amp; pengukuran.
          </p>
        </div>

        <!-- CTA Buttons -->
        <div class="flex flex-wrap items-center gap-4 pt-8">
          <a href="<?= WA_URL ?>?text=Halo+Wintom%2C+saya+ingin+konsultasi+gorden"
             target="_blank" rel="noopener"
             class="relative bg-brand text-white font-jakarta font-semibold text-[14px] leading-[16px] px-8 py-[16px] rounded-[3px] hover:bg-[#6a041c] transition-colors duration-200 shadow-lg">
            Konsultasi Gratis via WhatsApp
          </a>
          <a href="<?= BASE_URL ?>/products.php"
             class="backdrop-blur-[2px] bg-[rgba(255,255,255,0.1)] border border-[rgba(255,255,255,0.6)] text-white font-jakarta font-medium text-[14px] leading-[16px] px-[33px] py-[15px] rounded-[3px] shadow-sm hover:bg-[rgba(255,255,255,0.2)] transition-colors duration-200 whitespace-nowrap">
            Lihat Koleksi
          </a>
        </div>
        <p class="font-jakarta text-[13px] text-[rgba(255,255,255,0.75)] leading-[1.5] pt-3 m-0">Respon cepat di jam operasional — tanpa komitmen.</p>
      </div>

      <!-- Right: Floating Manifesto Badge (4 cols) -->
      <div class="col-span-4 flex items-center justify-end self-center">
        <div class="backdrop-blur-[6px] bg-[rgba(255,255,255,0.85)] border border-[rgba(255,255,255,0.7)] rounded-[2px] shadow-[0px_25px_50px_-12px_rgba(0,0,0,0.25)] px-[25px] py-[29px] w-[210px]">
          <div class="flex flex-col items-end gap-[6px]">
            <span class="font-jakarta font-semibold text-dark text-[9px] tracking-[1.98px] uppercase leading-[11.25px]">LIGHT</span>
            <span class="font-jakarta font-semibold text-dark text-[9px] tracking-[1.98px] uppercase leading-[11.25px]">PRIVACY</span>
            <span class="font-jakarta font-semibold text-dark text-[9px] tracking-[1.98px] uppercase leading-[11.25px]">COMFORT</span>
            <div class="bg-brand h-[1.5px] w-7 self-end mt-1"></div>
            <div class="text-right">
              <span class="font-jakarta font-bold text-brand text-[9px] tracking-[1.98px] uppercase leading-[11.25px] block">A BRIGHTER</span>
              <span class="font-jakarta font-bold text-brand text-[9px] tracking-[1.98px] uppercase leading-[11.25px] block">TOMORROW</span>
            </div>
          </div>
        </div>
      </div>

    </div>
  </div>
</section>


<!-- ===================================================================
     DUAL CATEGORY SPLIT CARDS (Curtain & Roller Blind)
===================================================================== -->
<section class="bg-[rgba(244,241,234,0.6)] border-t border-[#ece8e1] pt-[81px] pb-[80px] px-12">
  <div class="max-w-[1280px] mx-auto flex gap-10">

    <!-- Card 1: Curtain -->
    <div class="bg-white border border-[rgba(236,232,225,0.8)] rounded-[2px] overflow-hidden flex-1 flex flex-col p-px">
      <!-- Image -->
      <div class="bg-[#ece8e1] h-[420px] relative overflow-hidden z-[2]">
        <img
          src="https://images.unsplash.com/photo-1586023492125-27b2c045efd7?w=800&q=80&fit=crop"
          alt="Living room with elegant full height drape curtains"
          class="absolute top-0 h-full max-w-none object-cover"
          style="left:-17.61%; width:135.22%;"
        />
      </div>
      <!-- Card footer -->
      <div class="bg-white z-[1] relative">
        <div class="flex items-center justify-between p-8">
          <div class="flex flex-col gap-[3px]">
            <h2 class="font-playfair font-normal text-dark text-[30px] uppercase leading-[36px] m-0">CURTAIN</h2>
            <span class="font-jakarta font-medium text-muted text-[11px] tracking-[1.65px] uppercase leading-[16.5px]">ELEVATE YOUR EVERY SPACE</span>
          </div>
          <a href="<?= BASE_URL ?>/products.php?category=curtain"
             class="border border-[rgba(34,34,32,0.2)] rounded-full flex items-center justify-center size-[44px] hover:border-brand hover:bg-brand group transition-all duration-200">
            <svg class="size-4 group-hover:stroke-white transition-colors duration-200" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
              <path d="M3.333 8h9.334M9 4.667 12.333 8 9 11.333" stroke="#242424" stroke-width="1.25" stroke-linecap="round" stroke-linejoin="round" class="group-hover:stroke-white transition-colors duration-200"/>
            </svg>
          </a>
        </div>
      </div>
    </div>

    <!-- Card 2: Roller Blind -->
    <div class="bg-white border border-[rgba(236,232,225,0.8)] rounded-[2px] overflow-hidden flex-1 flex flex-col p-px">
      <!-- Image -->
      <div class="bg-[#ece8e1] h-[420px] relative overflow-hidden z-[2]">
        <img
          src="https://images.unsplash.com/photo-1616486338812-3dadae4b4ace?w=800&q=80&fit=crop"
          alt="Contemporary room with minimalist modern roller blinds"
          class="absolute top-0 h-full max-w-none object-cover"
          style="left:-17.61%; width:135.22%;"
        />
      </div>
      <!-- Card footer -->
      <div class="bg-white z-[1] relative">
        <div class="flex items-center justify-between p-8">
          <div class="flex flex-col gap-[3px]">
            <h2 class="font-playfair font-normal text-dark text-[30px] uppercase leading-[36px] m-0">ROLLER BLIND</h2>
            <span class="font-jakarta font-medium text-muted text-[11px] tracking-[1.65px] uppercase leading-[16.5px]">SIMPLE CONTROL MAXIMUM COMFORT</span>
          </div>
          <a href="<?= BASE_URL ?>/products.php?category=roller-blind"
             class="border border-[rgba(34,34,32,0.2)] rounded-full flex items-center justify-center size-[44px] hover:border-brand hover:bg-brand group transition-all duration-200">
            <svg class="size-4 group-hover:stroke-white transition-colors duration-200" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
              <path d="M3.333 8h9.334M9 4.667 12.333 8 9 11.333" stroke="#242424" stroke-width="1.25" stroke-linecap="round" stroke-linejoin="round" class="group-hover:stroke-white transition-colors duration-200"/>
            </svg>
          </a>
        </div>
      </div>
    </div>

  </div>
</section>


<!-- ===================================================================
     QUALITY IN EVERY DETAIL — Macro Photography + Value Pillars
===================================================================== -->
<section class="bg-[#faf8f5] border-b border-[rgba(236,232,225,0.6)] pt-[80px] pb-[81px]">
  <div class="max-w-[1280px] mx-auto px-12 flex flex-col gap-16">

    <!-- Top: Heading + 4 Macro Tiles (12-col grid) -->
    <div class="grid grid-cols-12 gap-8" style="grid-template-rows: 208px;">

      <!-- Left: Heading (3 cols) -->
      <div class="col-span-3 flex flex-col gap-4 items-start self-center">
        <div>
          <h2 class="font-playfair font-normal text-dark text-[30px] uppercase leading-[36px] m-0">
            QUALITY<br>IN EVERY DETAIL
          </h2>
        </div>
        <div class="bg-brand h-[2px] w-10"></div>
      </div>

      <!-- Right: 4 Macro Tiles (9 cols) -->
      <div class="col-span-9 flex gap-4 h-[208px] items-center">

        <?php
        $macro_tiles = [
          ['url' => 'https://images.unsplash.com/photo-1555041469-a586c61ea9bc?w=400&q=80&fit=crop', 'alt' => 'Macro sheer fabric texture'],
          ['url' => 'https://images.unsplash.com/photo-1578898887932-dce23a595ad4?w=400&q=80&fit=crop', 'alt' => 'Close-up textured blackout drapery weave'],
          ['url' => 'https://images.unsplash.com/photo-1540932239986-30128078f3c5?w=400&q=80&fit=crop', 'alt' => 'Folded luxury curtain textiles'],
          ['url' => 'https://images.unsplash.com/photo-1558618666-fcd25c85cd64?w=400&q=80&fit=crop', 'alt' => 'High precision curtain hardware'],
        ];
        foreach ($macro_tiles as $tile): ?>
        <div class="bg-[#ece8e1] flex-1 h-[208px] overflow-hidden rounded-[2px] relative min-w-0">
          <img
            src="<?= $tile['url'] ?>"
            alt="<?= htmlspecialchars($tile['alt']) ?>"
            class="absolute top-0 h-full max-w-none object-cover"
            style="left:-41.76%; width:183.51%;"
          />
          <div class="absolute inset-0 bg-[rgba(0,0,0,0.1)]"></div>
        </div>
        <?php endforeach; ?>
      </div>

    </div>

    <!-- Bottom: Ringkasan fitur -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-8 border-t border-[#ece8e1] pt-[40px]">
      <?php
      $features = [
        ['title' => 'Dua pilihan: ready atau custom', 'desc' => 'Butuh cepat? Ambil yang sudah jadi. Mau pas sempurna? Custom mengikuti ukuran jendela Anda.',
         'icon' => '<path d="M3.333 3.333h13.334v13.334H3.333z" stroke="#242424" stroke-width="1.25" stroke-linejoin="round"/><path d="M6.667 3.333v13.334M13.333 3.333v13.334M3.333 6.667h13.334M3.333 13.333h13.334" stroke="#242424" stroke-width="1.25"/>'],
        ['title' => 'Bahan bisa dicek langsung', 'desc' => 'Di showroom PIK, pegang dan bandingkan material sebelum memutuskan.',
         'icon' => '<path d="M10 2.5A7.5 7.5 0 0 1 17.5 10 7.5 7.5 0 0 1 10 17.5 7.5 7.5 0 0 1 2.5 10 7.5 7.5 0 0 1 10 2.5Zm0 2.5a5 5 0 1 0 0 10A5 5 0 0 0 10 5Zm0 2.5a2.5 2.5 0 1 1 0 5 2.5 2.5 0 0 1 0-5Z" fill="#242424"/>'],
        ['title' => 'Portofolio proyek', 'desc' => 'Proyek hunian &amp; komersial yang sudah terpasang. Lihat hasilnya di halaman Galeri.',
         'icon' => '<path d="M2.5 15.833h15M5 15.833V5.833a2.5 2.5 0 0 1 2.5-2.5h5A2.5 2.5 0 0 1 15 5.833v10M8.333 8.333h3.334" stroke="#242424" stroke-width="1.25" stroke-linecap="round" stroke-linejoin="round"/>'],
      ];
      foreach ($features as $f): ?>
      <div class="flex flex-col items-center text-center px-2">
        <svg class="size-8 mb-4" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg"><?= $f['icon'] ?></svg>
        <h3 class="font-playfair font-normal text-dark text-[20px] leading-[1.3] m-0 mb-2"><?= $f['title'] ?></h3>
        <p class="font-jakarta text-muted text-[15px] leading-[1.6] m-0 max-w-[340px]"><?= $f['desc'] ?></p>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>


<!-- ===================================================================
     USP — Mengapa Memilih Wintom
===================================================================== -->
<section class="bg-[#f4f1ea] border-b border-[#ece8e1] py-24">
  <div class="max-w-[1280px] mx-auto px-6 md:px-12">
    <div class="max-w-[820px] mx-auto text-center mb-14">
      <p class="font-jakarta font-semibold text-brand text-[12px] tracking-[2.64px] uppercase mb-4">MENGAPA MEMILIH WINTOM?</p>
      <h2 class="font-playfair font-normal text-dark text-[32px] md:text-[38px] leading-[1.2] m-0 mb-5">Gorden Custom Premium: Praktis Secara Online, Presisi Secara Offline.</h2>
      <p class="font-jakarta text-muted text-[17px] leading-[1.6] m-0">Kemudahan konsultasi dan pilih model gorden dari genggaman, dipadukan dengan jaminan kualitas bahan grade A, survei bawakan sampel ke lokasi, hingga instalasi rapi setara hotel bintang lima.</p>
    </div>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
      <?php
      $usp = [
        ['Kualitas Bahan & Jahitan Terbaik', 'Menggunakan material pilihan yang tebal, awet, dan jatuh bergelombang rapi sempurna.'],
        ['Fleksibel Online & Offline', 'Konsultasi praktis via WhatsApp/Website, atau jadwalkan tim kami datang membawa buku sampel bahan langsung ke tempat Anda.'],
        ['Garansi Ukuran Presisi 100%', 'Bebas risiko salah ukuran dengan panduan ukur online dari tim ahli atau layanan survei pengukuran langsung ke lokasi.'],
        ['Terima Beres & Bergaransi', 'Mulai dari pemilihan desain, produksi, hingga pemasangan bersih menggunakan komponen rel yang kokoh dan tidak macet.'],
      ];
      foreach ($usp as $i => [$t, $d]): ?>
      <div class="bg-white border border-[#ece8e1] rounded-[2px] p-8 flex gap-5">
        <span class="font-playfair text-brand text-[32px] leading-none shrink-0"><?= sprintf('%02d', $i + 1) ?></span>
        <div>
          <h3 class="font-playfair font-normal text-dark text-[22px] leading-[1.3] m-0 mb-2"><?= htmlspecialchars($t) ?></h3>
          <p class="font-jakarta text-muted text-[16px] leading-[1.6] m-0"><?= htmlspecialchars($d) ?></p>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>


<!-- ===================================================================
     HOW IT WORKS — 4-Step Process
===================================================================== -->
<section class="bg-[#faf8f5] py-24">
  <div class="max-w-[1280px] mx-auto px-12 flex flex-col gap-14">

    <!-- Section Heading -->
    <div class="flex flex-col gap-3 items-center w-full">
      <h2 class="font-playfair font-normal text-dark text-[36px] tracking-[1.8px] uppercase text-center leading-[40px] m-0">
        CARA PESAN
      </h2>
      <div class="bg-brand h-[2px] w-12"></div>
    </div>

    <!-- 4 Steps -->
    <div class="flex gap-6 items-start">

      <?php
      $steps = [
        ['num' => '01', 'title' => 'KONSULTASI', 'desc' => 'Chat WhatsApp, ceritakan kebutuhan dan ukuran jendela Anda.',
         'img' => 'https://images.unsplash.com/photo-1556742049-0cfed4f6a45d?w=600&q=80&fit=crop', 'alt' => 'Konsultasi pilihan bahan gorden'],
        ['num' => '02', 'title' => 'UKUR & PILIH BAHAN', 'desc' => 'Untuk custom: ukur jendela dan pilih bahan bersama tim kami, atau lihat langsung di showroom PIK.',
         'img' => 'https://images.unsplash.com/photo-1581578731548-c64695cc6952?w=600&q=80&fit=crop', 'alt' => 'Pengukuran jendela'],
        ['num' => '03', 'title' => 'PENAWARAN', 'desc' => 'Anda menerima penawaran yang jelas sebelum produksi dimulai.',
         'img' => 'https://images.unsplash.com/photo-1554224155-6726b3ff858f?w=600&q=80&fit=crop', 'alt' => 'Penawaran harga'],
        ['num' => '04', 'title' => 'PRODUKSI & PEMASANGAN', 'desc' => 'Produksi atau pengiriman, lalu pemasangan di lokasi Anda.',
         'img' => 'https://images.unsplash.com/photo-1586953208448-b95a79798f07?w=600&q=80&fit=crop', 'alt' => 'Pemasangan gorden'],
      ];
      foreach ($steps as $step): ?>

      <div class="flex-1 min-w-0 relative h-[390px]">
        <!-- Step image -->
        <div class="absolute top-0 left-0 right-0 pb-4 h-[256px]">
          <div class="bg-[#ece8e1] h-[240px] overflow-hidden rounded-[2px] w-full relative">
            <img
              src="<?= $step['img'] ?>"
              alt="<?= htmlspecialchars($step['alt']) ?>"
              class="absolute top-0 h-full max-w-none object-cover"
              style="left:-29.21%; width:158.43%;"
            />
          </div>
        </div>
        <!-- Step number -->
        <div class="absolute left-0 right-0 top-[256px] flex items-baseline">
          <span class="font-playfair font-semibold text-dark text-[24px] leading-[32px]"><?= $step['num'] ?></span>
        </div>
        <!-- Step title -->
        <div class="absolute left-0 right-0 top-[288px] pt-1">
          <h3 class="font-jakarta font-bold text-dark text-[12px] tracking-[1.8px] uppercase leading-[16px] m-0"><?= $step['title'] ?></h3>
        </div>
        <!-- Step description -->
        <div class="absolute left-0 right-0 top-[308px] pt-[3.44px]">
          <p class="font-jakarta font-normal text-muted text-[14px] leading-[1.5] m-0"><?= $step['desc'] ?></p>
        </div>
      </div>

      <?php endforeach; ?>
    </div>

  </div>
</section>


<!-- ===================================================================
     FAST QUOTE & WHATSAPP CALLOUT BANNER
===================================================================== -->
<section class="bg-[#ece8df] border-t border-b border-[#ece8e1] px-12 py-[49px]">
  <div class="max-w-[1280px] mx-auto">
    <div class="grid grid-cols-12 gap-8" style="grid-template-rows: 96px;">

      <!-- Left: 3-column features (8 cols) -->
      <div class="col-span-8 flex gap-6 items-center self-center">

        <!-- SIZE -->
        <div class="flex flex-col flex-1 min-w-0">
          <div class="h-12 flex flex-col pb-2 w-10">
            <div class="flex items-center justify-center size-10">
              <svg class="size-7" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path d="M2.5 5h15v10h-15V5Z" stroke="#242424" stroke-width="1.25" stroke-linejoin="round"/>
                <path d="M6.667 5v10M13.333 5v10M2.5 10h15" stroke="#242424" stroke-width="1.25"/>
              </svg>
            </div>
          </div>
          <span class="font-jakarta font-bold text-dark text-[12px] tracking-[1.8px] uppercase leading-[16px]">SIZE</span>
          <span class="font-jakarta font-normal text-muted text-[11px] leading-[15.13px] mt-1 whitespace-nowrap">Custom size to fit your window perfectly</span>
        </div>

        <!-- PRICE -->
        <div class="border-l border-[rgba(34,34,32,0.15)] flex flex-col flex-1 min-w-0 pl-[25px] pr-6">
          <div class="h-12 flex flex-col pb-2 w-10">
            <div class="flex items-center justify-center size-10">
              <svg class="size-7" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path d="M10 1.667v16.666M6.667 5h5a1.667 1.667 0 0 1 0 3.333H8.333A1.667 1.667 0 0 0 8.333 11.667h6.25" stroke="#242424" stroke-width="1.25" stroke-linecap="round" stroke-linejoin="round"/>
              </svg>
            </div>
          </div>
          <span class="font-jakarta font-bold text-dark text-[12px] tracking-[1.8px] uppercase leading-[16px]">PRICE</span>
          <span class="font-jakarta font-normal text-muted text-[11px] leading-[15.13px] mt-1 whitespace-nowrap">Get the best price for your project</span>
        </div>

        <!-- QUOTE -->
        <div class="border-l border-[rgba(34,34,32,0.15)] flex flex-col flex-1 min-w-0 pl-[25px]">
          <div class="h-12 flex flex-col pb-2 w-10">
            <div class="flex items-center justify-center size-10">
              <svg class="size-7" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path d="M4.167 2.5h11.666a.833.833 0 0 1 .834.833v13.334a.833.833 0 0 1-.834.833H4.167a.833.833 0 0 1-.834-.833V3.333a.833.833 0 0 1 .834-.833Z" stroke="#242424" stroke-width="1.25" stroke-linejoin="round"/>
                <path d="M6.667 6.667h6.666M6.667 10h6.666M6.667 13.333h4.166" stroke="#242424" stroke-width="1.25" stroke-linecap="round"/>
              </svg>
            </div>
          </div>
          <span class="font-jakarta font-bold text-dark text-[12px] tracking-[1.8px] uppercase leading-[16px]">QUOTE</span>
          <span class="font-jakarta font-normal text-muted text-[11px] leading-[15.13px] mt-1 whitespace-nowrap">Fast and professional quotation</span>
        </div>

      </div>

      <!-- Right: Buttons (4 cols) -->
      <div class="col-span-4 flex flex-col items-end justify-center self-center">
        <!-- Get a Quote button -->
        <a href="<?= WA_URL ?>?text=Halo+Wintom%2C+saya+ingin+mendapatkan+penawaran+harga+gorden"
           target="_blank" rel="noopener"
           class="flex items-center justify-center gap-2 bg-brand text-white font-jakarta font-semibold text-[12px] tracking-[1.8px] uppercase leading-[16px] px-6 py-[14px] rounded-[2px] shadow-sm w-64 hover:bg-[#6a041c] transition-colors duration-200">
          GET A QUOTE <span>→</span>
        </a>
        <!-- WhatsApp link -->
        <a href="<?= WA_URL ?>?text=Halo+Wintom%2C+saya+ingin+konsultasi"
           target="_blank" rel="noopener"
           class="flex items-center justify-center gap-2 w-64 px-4 py-[10px] mt-3 group">
          <svg class="size-5 text-[#25d366]" viewBox="0 0 20 20" fill="#25D366" xmlns="http://www.w3.org/2000/svg">
            <path d="M10 1.667A8.333 8.333 0 0 0 2.92 13.742L1.667 18.333l4.725-1.237A8.333 8.333 0 1 0 10 1.667Z"/>
            <path d="M7.5 6.667c.167-.417.625-.834 1.25-.834.5 0 .833.25 1.042.5l1.041 1.667c.208.417.042.833-.208 1.083l-.417.417c.417.75 1.125 1.458 1.875 1.875l.417-.417c.25-.25.667-.417 1.083-.208l1.667 1.041c.25.209.5.542.5 1.042 0 .625-.417 1.083-.833 1.25C13.75 14.167 11.25 14.583 8.333 11.667 5.417 8.75 5.833 6.25 6.25 5.417Z" fill="white"/>
          </svg>
          <span class="font-jakarta font-medium text-dark text-[12px] leading-[16px] group-hover:text-brand transition-colors duration-200">Consult Now via WhatsApp</span>
        </a>
      </div>

    </div>
  </div>
</section>


<!-- ===================================================================
     KONTEN WINTOM — YouTube video / shorts (dikelola dari admin)
===================================================================== -->
<?php if ($home_videos): ?>
<section id="konten" class="bg-[#faf8f5] border-t border-[#ece8e1] py-24">
  <div class="max-w-[1280px] mx-auto px-6 md:px-12 flex flex-col gap-12">
    <div class="flex flex-col gap-3 items-center w-full">
      <h2 class="font-playfair font-normal text-dark text-[36px] tracking-[1.8px] uppercase text-center leading-[40px] m-0">KONTEN WINTOM</h2>
      <div class="bg-brand h-[2px] w-12"></div>
    </div>
    <div class="flex flex-wrap justify-center gap-6">
      <?php foreach ($home_videos as $v):
          $vid = htmlspecialchars($v['youtube_id']);
          $ttl = $v['title'] !== null && $v['title'] !== '' ? $v['title'] : 'Video Wintom';
          $is_short = (bool)$v['is_short'];
      ?>
      <figure class="m-0 <?= $is_short ? 'w-[calc(50%-12px)] md:w-[calc(25%-18px)]' : 'w-full md:w-[calc(33.333%-16px)]' ?>">
        <div class="yt-card relative w-full overflow-hidden rounded-[2px] bg-[#242424] <?= $is_short ? 'aspect-[9/16]' : 'aspect-video' ?>">
          <button type="button" data-yt="<?= $vid ?>" data-title="<?= htmlspecialchars($ttl) ?>"
                  class="yt-play group absolute inset-0 block w-full h-full"
                  aria-label="Putar video: <?= htmlspecialchars($ttl) ?>">
            <img src="https://i.ytimg.com/vi/<?= $vid ?>/hqdefault.jpg" alt="" loading="lazy" class="absolute inset-0 w-full h-full object-cover transition-transform duration-300 group-hover:scale-[1.03]">
            <span class="absolute inset-0 bg-black/20 group-hover:bg-black/10 transition-colors"></span>
            <span class="absolute inset-0 flex items-center justify-center">
              <span class="size-14 rounded-full bg-brand text-white flex items-center justify-center shadow-lg">
                <svg class="size-6 ml-0.5" viewBox="0 0 24 24" fill="currentColor"><path d="M8 5v14l11-7z"/></svg>
              </span>
            </span>
          </button>
        </div>
        <?php if ($v['title']): ?>
        <figcaption class="font-jakarta text-dark text-[15px] leading-[1.5] pt-3"><?= htmlspecialchars($v['title']) ?></figcaption>
        <?php endif; ?>
      </figure>
      <?php endforeach; ?>
    </div>
  </div>
</section>
<script>
  // Muat player YouTube hanya setelah diklik (halaman tetap ringan)
  document.querySelectorAll('.yt-play').forEach(function (btn) {
    btn.addEventListener('click', function () {
      var f = document.createElement('iframe');
      f.src = 'https://www.youtube-nocookie.com/embed/' + btn.dataset.yt + '?autoplay=1&rel=0&playsinline=1';
      f.title = btn.dataset.title;
      f.allow = 'accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture';
      f.allowFullscreen = true;
      f.className = 'absolute inset-0 w-full h-full border-0';
      btn.parentNode.replaceChildren(f);
    });
  });
</script>
<?php endif; ?>


<!-- ===================================================================
     WHAT OUR CUSTOMERS SAY — Testimonials
===================================================================== -->
<section class="bg-[#faf8f5] py-24">
  <div class="max-w-[1280px] mx-auto px-12 flex flex-col gap-16">

    <!-- Section Heading -->
    <div class="flex flex-col gap-3 items-center w-full">
      <h2 class="font-playfair font-normal text-dark text-[36px] tracking-[1.8px] uppercase text-center leading-[40px] m-0">
        WHAT OUR CUSTOMERS SAY
      </h2>
      <div class="bg-brand h-[2px] w-12"></div>
    </div>

    <!-- 3 Testimonial Cards -->
    <div class="flex gap-8 items-start">

      <?php
      $testimonials = [
        [
          'img'      => 'https://images.unsplash.com/photo-1493809842364-78817add7ffb?w=600&q=80&fit=crop',
          'alt'      => 'Modern residence with Wintom sheer curtains',
          'quote'    => 'Hasilnya sangat rapi dan sesuai ekspektasi. Timnya profesional, mulai dari pengukuran hingga pemasangan tepat waktu.',
          'location' => '— RUMAH TINGGAL, JAKARTA',
        ],
        [
          'img'      => 'https://images.unsplash.com/photo-1497366216548-37526070297c?w=600&q=80&fit=crop',
          'alt'      => 'Modern executive office with automated roller blinds',
          'quote'    => 'Kualitas bagus, pemasangan cepat, layanan sangat baik. Roller blind motorized-nya bekerja sangat smooth dan hening.',
          'location' => '— OFFICE, TANGERANG',
        ],
        [
          'img'      => 'https://images.unsplash.com/photo-1631049307264-da0ec9d70304?w=600&q=80&fit=crop',
          'alt'      => 'Luxury master bedroom with blackout curtains',
          'quote'    => 'Bahannya premium dan terlihat sangat elegan. Benar-benar 100% blackout untuk kamar tidur kami. Sangat puas!',
          'location' => '— APARTEMEN, JAKARTA',
        ],
      ];
      foreach ($testimonials as $t): ?>

      <div class="bg-white border border-[#ece8e1] rounded-[2px] shadow-[0px_1px_1px_rgba(0,0,0,0.05)] flex flex-col items-start justify-between p-[25px] flex-1 min-w-0">
        <!-- Top content -->
        <div class="flex flex-col gap-[7.3px] w-full">
          <!-- Photo -->
          <div class="bg-[#ece8e1] h-[176px] overflow-hidden rounded-[2px] w-full relative">
            <img
              src="<?= $t['img'] ?>"
              alt="<?= htmlspecialchars($t['alt']) ?>"
              class="absolute top-[-0.05%] left-0 w-full max-w-none"
              style="height:100.11%;"
            />
          </div>
          <!-- Opening quote mark -->
          <div class="pt-[16.7px]">
            <span class="font-playfair font-normal text-brand text-[36px] leading-[36px]">"</span>
          </div>
          <!-- Quote text -->
          <p class="font-jakarta font-normal text-dark text-[14px] leading-[22.75px] m-0"><?= htmlspecialchars($t['quote']) ?></p>
        </div>
        <!-- Bottom: stars + location -->
        <div class="pt-6 w-full">
          <div class="border-t border-[#ece8e1] pt-[17px] flex flex-col gap-[7px]">
            <span class="text-[#f59e0b] text-[12px] leading-[16px]">★★★★★</span>
            <span class="font-jakarta font-semibold text-muted text-[11px] tracking-[1.65px] uppercase leading-[16.5px]"><?= htmlspecialchars($t['location']) ?></span>
          </div>
        </div>
      </div>

      <?php endforeach; ?>

    </div>
  </div>
</section>


<!-- ===================================================================
     FAQ
===================================================================== -->
<section id="faq" class="bg-[#f4f1ea] border-t border-[#ece8e1] py-24">
  <div class="max-w-[860px] mx-auto px-6 md:px-12 flex flex-col gap-12">
    <div class="flex flex-col gap-3 items-center w-full">
      <h2 class="font-playfair font-normal text-dark text-[36px] tracking-[1.8px] uppercase text-center leading-[40px] m-0">PERTANYAAN UMUM</h2>
      <div class="bg-brand h-[2px] w-12"></div>
    </div>
    <div class="flex flex-col gap-3">
      <?php
      $faqs = [
        ['Apa bedanya ready stock dan custom?', 'Ready stock: langsung bawa/pasang. Custom: ukuran &amp; bahan mengikuti jendela dan interior Anda.'],
        ['Bagaimana cara pesannya?', 'Chat WhatsApp → (custom: ukur &amp; pilih bahan) → penawaran → produksi/pengiriman → pemasangan.'],
        ['Apakah melayani proyek?', 'Ya — lihat portofolio kami di halaman Galeri.'],
        ['Bisa lihat barang langsung?', 'Bisa — showroom Ruko Soho Ebony, PIK, Jakarta Utara.'],
      ];
      foreach ($faqs as $i => [$q, $a]): ?>
      <details class="group bg-white border border-[#ece8e1] rounded-[2px]" <?= $i === 0 ? 'open' : '' ?>>
        <summary class="flex items-center justify-between gap-4 cursor-pointer list-none px-6 py-5 font-jakarta font-semibold text-dark text-[17px] leading-[1.4]">
          <?= $q ?>
          <svg class="size-5 shrink-0 text-brand transition-transform duration-200 group-open:rotate-45" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M12 5v14M5 12h14"/></svg>
        </summary>
        <p class="px-6 pb-6 m-0 font-jakarta text-muted text-[16px] leading-[1.6]"><?= $a ?></p>
      </details>
      <?php endforeach; ?>
    </div>
    <div class="text-center">
      <a href="<?= WA_URL ?>?text=Halo+Wintom%2C+saya+ada+pertanyaan"
         target="_blank" rel="noopener"
         class="inline-flex bg-brand text-white font-jakarta font-semibold text-[14px] px-8 py-[14px] rounded-[3px] hover:bg-[#6a041c] transition-colors duration-200">
        Tanya via WhatsApp
      </a>
    </div>
  </div>
</section>


<?php include __DIR__ . '/includes/footer.php'; ?>
