<?php
// Wintom Curtain — Homepage (index.php)
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/db.php';

$page_title = 'Beautiful Spaces Better Living';
$active_nav = 'home';

// Ambil produk unggulan dari DB (optional, fallback ke hardcode jika kosong)
$db = get_db();
$featured = $db->query("SELECT * FROM products ORDER BY created_at DESC LIMIT 4")->fetchAll();
?>
<?php include __DIR__ . '/includes/header.php'; ?>

<!-- ===================================================================
     HERO SECTION
===================================================================== -->
<section class="relative bg-[#222220] min-h-[720px] flex items-center justify-center overflow-hidden py-[55.5px]">

  <!-- Background image -->
  <div class="absolute inset-[-18px_-32px] flex items-center justify-center" style="container-type:size;">
    <div class="w-[100cqw] h-[100cqh] flex-none">
      <div class="relative size-full overflow-hidden">
        <img
          src="https://images.unsplash.com/photo-1558618666-fcd25c85cd64?w=1920&q=85&fit=crop"
          alt="Sunlit minimalist luxury living room with sheer curtains"
          class="absolute top-0 h-full max-w-none object-cover"
          style="left:-1.61%; width:103.23%;"
        />
      </div>
    </div>
  </div>

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
          <h1 class="font-playfair font-normal text-white text-[62px] tracking-[-1.55px] leading-[62px] whitespace-nowrap m-0">
            BEAUTIFUL<br>SPACES<br>BETTER LIVING
          </h1>
        </div>

        <!-- Red divider -->
        <div class="bg-brand h-[2px] w-14 my-6"></div>

        <!-- Subtitle -->
        <div class="max-w-[512px]">
          <p class="font-jakarta font-semibold text-[12px] text-[rgba(255,255,255,0.8)] tracking-[1.8px] uppercase leading-[19.5px] m-0">
            CURTAIN · ROLLER BLIND<br>TAILORED FOR YOUR LIFESTYLE
          </p>
        </div>

        <!-- CTA Buttons -->
        <div class="flex items-center gap-4 pt-8">
          <a href="<?= WA_URL ?>?text=Halo+Wintom%2C+saya+ingin+konsultasi+gorden"
             target="_blank" rel="noopener"
             class="relative bg-brand text-white font-jakarta font-medium text-[12px] tracking-[1.8px] uppercase leading-[16px] px-8 py-[14px] rounded-[3px] hover:bg-[#5a0d1a] transition-colors duration-200 shadow-lg whitespace-nowrap">
            CONSULTATION
          </a>
          <a href="<?= BASE_URL ?>/products.php"
             class="backdrop-blur-[2px] bg-[rgba(255,255,255,0.1)] border border-[rgba(255,255,255,0.6)] text-white font-jakarta font-medium text-[12px] tracking-[1.8px] uppercase leading-[16px] px-[33px] py-[15px] rounded-[3px] shadow-sm hover:bg-[rgba(255,255,255,0.2)] transition-colors duration-200 whitespace-nowrap">
            VIEW COLLECTION
          </a>
        </div>
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
              <path d="M3.333 8h9.334M9 4.667 12.333 8 9 11.333" stroke="#222220" stroke-width="1.25" stroke-linecap="round" stroke-linejoin="round" class="group-hover:stroke-white transition-colors duration-200"/>
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
              <path d="M3.333 8h9.334M9 4.667 12.333 8 9 11.333" stroke="#222220" stroke-width="1.25" stroke-linecap="round" stroke-linejoin="round" class="group-hover:stroke-white transition-colors duration-200"/>
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

    <!-- Bottom: 4 Value Pillars -->
    <div class="flex gap-8 items-start border-t border-[#ece8e1] pt-[33px]">

      <?php
      $pillars = [
        ['icon' => '<path d="M10 2.5A7.5 7.5 0 0 1 17.5 10 7.5 7.5 0 0 1 10 17.5 7.5 7.5 0 0 1 2.5 10 7.5 7.5 0 0 1 10 2.5Zm0 2.5a5 5 0 1 0 0 10A5 5 0 0 0 10 5Zm0 2.5a2.5 2.5 0 1 1 0 5 2.5 2.5 0 0 1 0-5Z" fill="#222220"/>', 'label' => 'PREMIUM', 'label2' => 'MATERIAL'],
        ['icon' => '<path d="M3.333 3.333h13.334v13.334H3.333z" stroke="#222220" stroke-width="1.25" stroke-linejoin="round"/><path d="M6.667 3.333v13.334M13.333 3.333v13.334M3.333 6.667h13.334M3.333 13.333h13.334" stroke="#222220" stroke-width="1.25"/>', 'label' => 'CUSTOM', 'label2' => 'MADE'],
        ['icon' => '<path d="M10 1.667 12.575 6.883l5.758.837-4.167 4.062.984 5.735L10 14.917l-5.15 2.6.984-5.735L1.667 7.72l5.758-.837L10 1.667Z" stroke="#222220" stroke-width="1.25" stroke-linejoin="round"/>', 'label' => 'MODERN', 'label2' => 'DESIGN'],
        ['icon' => '<path d="M2.5 15.833h15M5 15.833V5.833a2.5 2.5 0 0 1 2.5-2.5h5A2.5 2.5 0 0 1 15 5.833v10M8.333 8.333h3.334" stroke="#222220" stroke-width="1.25" stroke-linecap="round" stroke-linejoin="round"/>', 'label' => 'SUITABLE FOR', 'label2' => 'HOME &amp; COMMERCIAL'],
      ];
      foreach ($pillars as $p): ?>
      <div class="flex-1 min-w-0">
        <div class="flex flex-col items-center p-2">
          <!-- Icon -->
          <div class="flex flex-col h-16 items-start pb-4 w-12">
            <div class="flex items-center justify-center size-12">
              <svg class="size-7" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                <?= $p['icon'] ?>
              </svg>
            </div>
          </div>
          <!-- Label -->
          <div class="text-center">
            <span class="font-jakarta font-bold text-dark text-[12px] tracking-[2.64px] uppercase leading-[16px] block"><?= $p['label'] ?></span>
            <span class="font-jakarta font-bold text-dark text-[12px] tracking-[2.64px] uppercase leading-[16px] block"><?= $p['label2'] ?></span>
          </div>
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
        HOW IT WORKS
      </h2>
      <div class="bg-brand h-[2px] w-12"></div>
    </div>

    <!-- 4 Steps -->
    <div class="flex gap-6 items-start">

      <?php
      $steps = [
        [
          'num'   => '01',
          'title' => 'CONSULTATION',
          'desc'  => 'Explore fabric collections, textures, and shading needs with our design advisor.',
          'img'   => 'https://images.unsplash.com/photo-1556742049-0cfed4f6a45d?w=600&q=80&fit=crop',
          'alt'   => 'Client selecting curtain fabric swatches',
        ],
        [
          'num'   => '02',
          'title' => 'MEASUREMENT',
          'desc'  => 'On-site professional window surveying to guarantee millimetric precision.',
          'img'   => 'https://images.unsplash.com/photo-1581578731548-c64695cc6952?w=600&q=80&fit=crop',
          'alt'   => 'Technician measuring window dimensions',
        ],
        [
          'num'   => '03',
          'title' => 'QUOTATION',
          'desc'  => 'Transparent, itemized pricing breakdown with zero hidden fees.',
          'img'   => 'https://images.unsplash.com/photo-1554224155-6726b3ff858f?w=600&q=80&fit=crop',
          'alt'   => 'Transparent quotation document',
        ],
        [
          'num'   => '04',
          'title' => 'INSTALLATION',
          'desc'  => 'Clean, quiet setup by certified technicians followed by complete steaming.',
          'img'   => 'https://images.unsplash.com/photo-1586953208448-b95a79798f07?w=600&q=80&fit=crop',
          'alt'   => 'Professional technician installing curtain tracks',
        ],
      ];
      foreach ($steps as $step): ?>

      <div class="flex-1 min-w-0 relative h-[347.75px]">
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
          <p class="font-jakarta font-normal text-muted text-[11px] leading-[17.88px] m-0"><?= $step['desc'] ?></p>
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
                <path d="M2.5 5h15v10h-15V5Z" stroke="#222220" stroke-width="1.25" stroke-linejoin="round"/>
                <path d="M6.667 5v10M13.333 5v10M2.5 10h15" stroke="#222220" stroke-width="1.25"/>
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
                <path d="M10 1.667v16.666M6.667 5h5a1.667 1.667 0 0 1 0 3.333H8.333A1.667 1.667 0 0 0 8.333 11.667h6.25" stroke="#222220" stroke-width="1.25" stroke-linecap="round" stroke-linejoin="round"/>
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
                <path d="M4.167 2.5h11.666a.833.833 0 0 1 .834.833v13.334a.833.833 0 0 1-.834.833H4.167a.833.833 0 0 1-.834-.833V3.333a.833.833 0 0 1 .834-.833Z" stroke="#222220" stroke-width="1.25" stroke-linejoin="round"/>
                <path d="M6.667 6.667h6.666M6.667 10h6.666M6.667 13.333h4.166" stroke="#222220" stroke-width="1.25" stroke-linecap="round"/>
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
           class="flex items-center justify-center gap-2 bg-brand text-white font-jakarta font-semibold text-[12px] tracking-[1.8px] uppercase leading-[16px] px-6 py-[14px] rounded-[2px] shadow-sm w-64 hover:bg-[#5a0d1a] transition-colors duration-200">
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


<?php include __DIR__ . '/includes/footer.php'; ?>
