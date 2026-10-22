<?php
// Wintom Curtain — Homepage (index.php)
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/db.php';

$page_title = 'Gorden & Blinds Custom di PIK, Jakarta';
$active_nav = 'home';

$db = get_db();
require_once __DIR__ . '/includes/youtube.php';
require_once __DIR__ . '/includes/inquiry-form.php';
// Kartu kategori landing page: 6 kategori teratas (urutan diatur di admin > Kategori).
// Foto dari master kategori; bila kosong pakai cover produk aktif pertama di kategori itu.
$home_categories = $db->query("
    SELECT c.name, c.tagline, c.image_url,
           (SELECT p.cover_image FROM products p WHERE p.category_id = c.id AND p.is_active = 1 AND p.cover_image IS NOT NULL
             ORDER BY p.created_at DESC LIMIT 1) AS fallback_image
    FROM categories c ORDER BY c.sort_order ASC, c.name ASC LIMIT 6")->fetchAll();
$home_videos = $db->query('SELECT * FROM content_videos WHERE is_active = 1 ORDER BY sort_order ASC, id ASC')->fetchAll();
?>
<?php include __DIR__ . '/includes/header.php'; ?>

<!-- ===================================================================
     HERO SECTION
===================================================================== -->
<section class="relative bg-[#242424] min-h-[calc(100svh-81px)] md:min-h-[640px] lg:min-h-[720px] flex items-center overflow-hidden">

  <!-- Background video -->
  <video class="wt-zoom absolute inset-0 w-full h-full object-cover" autoplay muted loop playsinline preload="metadata"
         poster="https://images.unsplash.com/photo-1558618666-fcd25c85cd64?w=1920&q=85&fit=crop" aria-hidden="true">
    <source src="<?= BASE_URL ?>/assets/video/hero.mp4" type="video/mp4">
  </video>

  <!-- Dual scrim overlay -->
  <div class="absolute inset-0 bg-gradient-to-t md:bg-gradient-to-r from-[rgba(23,22,21,0.88)] via-[rgba(27,26,24,0.62)] to-[rgba(0,0,0,0.25)]"></div>
  <div class="absolute inset-0 bg-[rgba(255,255,255,0.12)]"></div>

  <!-- Content -->
  <div class="relative w-full max-w-[1280px] mx-auto px-4 sm:px-6 lg:px-12 py-16 md:py-24 lg:py-[112px]">
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-center">

      <!-- Left: Editorial Content -->
      <div class="lg:col-span-8 flex flex-col items-start min-w-0">

        <!-- Eyebrow label -->
        <div class="wt-in flex items-center pb-4" style="--d:100ms">
          <span class="font-jakarta font-semibold text-[11px] sm:text-[12px] text-[rgba(255,255,255,0.8)] tracking-[2px] sm:tracking-[2.64px] uppercase leading-[16px]">
            SPESIALIS GORDEN &amp; BLINDS · PIK, JAKARTA
          </span>
        </div>

        <!-- Main heading -->
        <h1 class="wt-in font-playfair font-normal text-white text-[32px] sm:text-[40px] md:text-[48px] leading-[1.15] tracking-[-0.5px] max-w-[760px] m-0 drop-shadow-[0px_1px_0.5px_rgba(0,0,0,0.05)]" style="--d:220ms">
          Gorden &amp; Blinds yang Pas Sempurna di Setiap Jendela
        </h1>

        <!-- Sub-headline -->
        <p class="wt-in font-jakarta text-[16px] md:text-[18px] text-[rgba(255,255,255,0.9)] leading-[1.6] m-0 mt-6 max-w-[560px]" style="--d:420ms">
          Pilih koleksi siap pasang atau buat sesuai ukuran. Tim kami datang mengukur, membawa sampel bahan, dan memasang hingga rapi — Anda tinggal menikmati hasilnya.
        </p>

        <!-- CTA Buttons -->
        <div class="wt-in flex flex-col sm:flex-row sm:flex-wrap sm:items-center gap-3 sm:gap-4 pt-8 w-full sm:w-auto" style="--d:540ms">
          <button type="button" data-open-quote
             class="bg-brand text-white font-jakarta font-semibold text-[14px] leading-[16px] px-8 py-[16px] rounded-[3px] hover:bg-[#6a041c] hover:-translate-y-0.5 transition-all duration-200 shadow-lg">
            Konsultasi Gratis
          </button>
          <a href="<?= BASE_URL ?>/products.php"
             class="text-center backdrop-blur-[2px] bg-[rgba(255,255,255,0.1)] border border-[rgba(255,255,255,0.6)] text-white font-jakarta font-medium text-[14px] leading-[16px] px-[33px] py-[15px] rounded-[3px] shadow-sm hover:bg-[rgba(255,255,255,0.2)] transition-colors duration-200 whitespace-nowrap">
            Jelajahi Koleksi
          </a>
        </div>
        <p class="wt-in font-jakarta text-[13px] text-[rgba(255,255,255,0.75)] leading-[1.5] pt-3 m-0" style="--d:640ms">Respon cepat di jam operasional.</p>
      </div>

      <!-- Right: Floating Manifesto Badge (desktop) -->
      <div class="hidden lg:flex lg:col-span-4 items-center justify-end">
        <div class="wt-in backdrop-blur-[6px] bg-[rgba(255,255,255,0.85)] border border-[rgba(255,255,255,0.7)] rounded-[2px] shadow-[0px_25px_50px_-12px_rgba(0,0,0,0.25)] px-[25px] py-[29px] w-[210px]" style="--d:760ms">
          <div class="flex flex-col items-end gap-[6px]">
            <span class="font-jakarta font-semibold text-dark text-[9px] tracking-[1.98px] uppercase leading-[11.25px]">CAHAYA</span>
            <span class="font-jakarta font-semibold text-dark text-[9px] tracking-[1.98px] uppercase leading-[11.25px]">PRIVASI</span>
            <span class="font-jakarta font-semibold text-dark text-[9px] tracking-[1.98px] uppercase leading-[11.25px]">KENYAMANAN</span>
            <div class="bg-brand h-[1.5px] w-7 self-end mt-1"></div>
            <div class="text-right">
              <span class="font-jakarta font-bold text-brand text-[9px] tracking-[1.98px] uppercase leading-[11.25px] block">HARI ESOK</span>
              <span class="font-jakarta font-bold text-brand text-[9px] tracking-[1.98px] uppercase leading-[11.25px] block">LEBIH CERAH</span>
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
<section class="bg-[rgba(244,241,234,0.6)] border-t border-[#ece8e1] py-14 md:pt-[81px] md:pb-[80px] px-4 sm:px-6 lg:px-12">
  <div class="max-w-[1280px] mx-auto grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5 lg:gap-8">
    <?php foreach ($home_categories as $i => $cat):
        $img = $cat['image_url'] ?: $cat['fallback_image'];
    ?>
    <a href="<?= BASE_URL ?>/products.php?category=<?= urlencode($cat['name']) ?>" data-reveal style="--d:<?= ($i % 3) * 100 ?>ms"
       class="group bg-white border border-[rgba(236,232,225,0.8)] rounded-[2px] overflow-hidden flex flex-col p-px hover:shadow-[0_24px_48px_-24px_rgba(34,34,32,0.4)] transition-shadow duration-300">
      <div class="bg-[#ece8e1] aspect-[4/3] relative overflow-hidden">
        <?php if ($img): ?>
        <img src="<?= htmlspecialchars(asset_url($img)) ?>" alt="<?= htmlspecialchars($cat['name']) ?>" loading="lazy"
             class="absolute inset-0 w-full h-full object-cover transition-transform duration-[1200ms] ease-out group-hover:scale-[1.06]" />
        <?php else: ?>
        <span class="absolute inset-0 flex items-center justify-center font-playfair font-normal text-brand/25 text-[64px] uppercase"><?= htmlspecialchars(mb_substr($cat['name'], 0, 1)) ?></span>
        <?php endif; ?>
      </div>
      <div class="flex items-center justify-between gap-4 p-5 sm:p-6 flex-1">
        <div class="flex flex-col gap-[3px] min-w-0">
          <h2 class="font-playfair font-normal text-dark text-[20px] sm:text-[22px] leading-[1.3] m-0"><?= htmlspecialchars($cat['name']) ?></h2>
          <?php if ($cat['tagline']): ?>
          <span class="font-jakarta font-medium text-muted text-[11px] tracking-[1.65px] uppercase leading-[16.5px]"><?= htmlspecialchars($cat['tagline']) ?></span>
          <?php endif; ?>
        </div>
        <span class="shrink-0 border border-[rgba(34,34,32,0.2)] rounded-full flex items-center justify-center size-[44px] text-dark group-hover:border-brand group-hover:bg-brand group-hover:text-white transition-all duration-300">
          <svg class="size-4 transition-transform duration-300 group-hover:translate-x-0.5" viewBox="0 0 16 16" fill="none"><path d="M3.333 8h9.334M9 4.667 12.333 8 9 11.333" stroke="currentColor" stroke-width="1.25" stroke-linecap="round" stroke-linejoin="round"/></svg>
        </span>
      </div>
    </a>
    <?php endforeach; ?>
  </div>
</section>


<!-- ===================================================================
     QUALITY IN EVERY DETAIL — Macro Photography + Value Pillars
===================================================================== -->
<section class="bg-[#faf8f5] border-b border-[rgba(236,232,225,0.6)] py-14 md:pt-[80px] md:pb-[81px]">
  <div class="max-w-[1280px] mx-auto px-4 sm:px-6 lg:px-12 flex flex-col gap-12 md:gap-16">

    <!-- Top: Heading + 4 Macro Tiles -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 lg:gap-8 items-center">

      <div class="lg:col-span-3 flex flex-col gap-4 items-start" data-reveal>
        <h2 class="font-playfair font-normal text-dark text-[28px] md:text-[36px] leading-[1.2] tracking-[-0.5px] m-0">
          Kualitas<br class="hidden lg:block"> di Setiap Detail
        </h2>
        <div class="bg-brand h-[2px] w-10"></div>
      </div>

      <div class="lg:col-span-9 grid grid-cols-2 sm:grid-cols-4 gap-3 sm:gap-4">
        <?php
        $macro_tiles = [
          ['url' => 'https://images.unsplash.com/photo-1555041469-a586c61ea9bc?w=400&q=80&fit=crop', 'alt' => 'Macro sheer fabric texture'],
          ['url' => 'https://images.unsplash.com/photo-1578898887932-dce23a595ad4?w=400&q=80&fit=crop', 'alt' => 'Close-up textured blackout drapery weave'],
          ['url' => 'https://images.unsplash.com/photo-1540932239986-30128078f3c5?w=400&q=80&fit=crop', 'alt' => 'Folded luxury curtain textiles'],
          ['url' => 'https://images.unsplash.com/photo-1558618666-fcd25c85cd64?w=400&q=80&fit=crop', 'alt' => 'High precision curtain hardware'],
        ];
        foreach ($macro_tiles as $i => $tile): ?>
        <div class="group bg-[#ece8e1] aspect-square sm:aspect-auto sm:h-[208px] overflow-hidden rounded-[2px] relative min-w-0" data-reveal style="--d:<?= $i * 90 ?>ms">
          <img src="<?= $tile['url'] ?>" alt="<?= htmlspecialchars($tile['alt']) ?>" loading="lazy"
               class="absolute inset-0 w-full h-full object-cover transition-transform duration-700 group-hover:scale-110" />
          <div class="absolute inset-0 bg-[rgba(0,0,0,0.1)]"></div>
        </div>
        <?php endforeach; ?>
      </div>

    </div>

    <!-- Bottom: Ringkasan fitur -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-x-8 gap-y-10 border-t border-[#ece8e1] pt-10">
      <?php
      $features = [
        ['title' => 'Siap Pasang atau Custom', 'desc' => 'Butuh cepat? Pilih koleksi siap pasang. Ingin pas sempurna? Kami buatkan sesuai ukuran jendela Anda.',
         'icon' => '<path d="M3.333 3.333h13.334v13.334H3.333z" stroke="#242424" stroke-width="1.25" stroke-linejoin="round"/><path d="M6.667 3.333v13.334M13.333 3.333v13.334M3.333 6.667h13.334M3.333 13.333h13.334" stroke="#242424" stroke-width="1.25"/>'],
        ['title' => 'Sentuh Bahannya Langsung', 'desc' => 'Kunjungi showroom kami di PIK untuk meraba dan membandingkan material sebelum memutuskan.',
         'icon' => '<path d="M10 2.5A7.5 7.5 0 0 1 17.5 10 7.5 7.5 0 0 1 10 17.5 7.5 7.5 0 0 1 2.5 10 7.5 7.5 0 0 1 10 2.5Zm0 2.5a5 5 0 1 0 0 10A5 5 0 0 0 10 5Zm0 2.5a2.5 2.5 0 1 1 0 5 2.5 2.5 0 0 1 0-5Z" fill="#242424"/>'],
        ['title' => 'Bukti Nyata di Lapangan', 'desc' => 'Lihat hasil pemasangan kami di hunian, kantor, hingga hotel &amp; villa pada halaman Galeri.',
         'icon' => '<path d="M2.5 15.833h15M5 15.833V5.833a2.5 2.5 0 0 1 2.5-2.5h5A2.5 2.5 0 0 1 15 5.833v10M8.333 8.333h3.334" stroke="#242424" stroke-width="1.25" stroke-linecap="round" stroke-linejoin="round"/>'],
        ['title' => 'Garansi &amp; Layanan Purna Jual', 'desc' => 'Jaminan kualitas material dan pemasangan dengan garansi mekanik resmi serta dukungan perbaikan purna jual responsif demi kenyamanan jangka panjang Anda.',
         'icon' => '<path d="M10 1.875 3.75 4.375v4.583c0 3.95 2.6 7.35 6.25 9.167 3.65-1.817 6.25-5.217 6.25-9.167V4.375L10 1.875Z" stroke="#242424" stroke-width="1.25" stroke-linejoin="round"/><path d="m7.083 10 2.084 2.083 3.75-4.166" stroke="#242424" stroke-width="1.25" stroke-linecap="round" stroke-linejoin="round"/>'],
      ];
      foreach ($features as $i => $f): ?>
      <div class="flex flex-col items-center text-center px-2" data-reveal style="--d:<?= $i * 100 ?>ms">
        <span class="size-14 rounded-full bg-white border border-[#ece8e1] flex items-center justify-center mb-4 shadow-sm">
          <svg class="size-7" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg"><?= $f['icon'] ?></svg>
        </span>
        <h3 class="font-playfair font-normal text-dark text-[20px] sm:text-[22px] leading-[1.3] m-0 mb-2"><?= $f['title'] ?></h3>
        <p class="font-jakarta text-muted text-[15px] leading-[1.6] m-0 max-w-[340px]"><?= $f['desc'] ?></p>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>


<!-- ===================================================================
     USP — Mengapa Memilih Wintom
===================================================================== -->
<section class="bg-[#f4f1ea] border-b border-[#ece8e1] py-16 md:py-24">
  <div class="max-w-[1280px] mx-auto px-4 sm:px-6 lg:px-12">
    <div class="max-w-[820px] mx-auto text-center mb-10 md:mb-14" data-reveal>
      <p class="font-jakarta font-semibold text-brand text-[12px] tracking-[2.64px] uppercase mb-4">MENGAPA MEMILIH WINTOM?</p>
      <h2 class="font-playfair font-normal text-dark text-[28px] md:text-[36px] leading-[1.2] tracking-[-0.5px] m-0 mb-5">Konsultasi Praktis dari Rumah, Pemasangan Presisi di Lokasi</h2>
      <p class="font-jakarta text-muted text-[16px] md:text-[17px] leading-[1.6] m-0">Pilih model dan konsultasi langsung dari ponsel Anda. Saat siap, tim kami datang membawa sampel bahan grade A, mengukur dengan presisi, lalu memasang serapi standar hotel bintang lima.</p>
    </div>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 md:gap-6">
      <?php
      $usp = [
        ['Bahan & Jahitan Berkualitas', 'Material pilihan yang tebal dan awet, dijahit rapi agar gorden jatuh bergelombang dengan sempurna.'],
        ['Konsultasi Online atau Kunjungan', 'Diskusikan kebutuhan Anda lewat WhatsApp atau website, atau jadwalkan tim kami datang membawa buku sampel ke tempat Anda.'],
        ['Garansi Ukuran Presisi 100%', 'Tidak perlu khawatir salah ukur. Ikuti panduan dari tim ahli kami, atau biarkan kami yang mengukur langsung di lokasi.'],
        ['Terima Beres & Bergaransi', 'Dari pemilihan desain, produksi, hingga pemasangan yang bersih — dengan rel kokoh yang bergerak mulus tanpa macet.'],
      ];
      foreach ($usp as $i => [$t, $d]): ?>
      <div class="bg-white border border-[#ece8e1] rounded-[2px] p-6 sm:p-8 flex gap-4 sm:gap-5 hover:-translate-y-1 hover:shadow-[0_18px_40px_-20px_rgba(34,34,32,0.3)] transition-all duration-300" data-reveal style="--d:<?= ($i % 2) * 120 ?>ms">
        <span class="font-playfair font-normal text-brand text-[32px] leading-none shrink-0"><?= sprintf('%02d', $i + 1) ?></span>
        <div>
          <h3 class="font-playfair font-normal text-dark text-[20px] sm:text-[22px] leading-[1.3] m-0 mb-2"><?= htmlspecialchars($t) ?></h3>
          <p class="font-jakarta text-muted text-[15px] sm:text-[16px] leading-[1.6] m-0"><?= htmlspecialchars($d) ?></p>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>


<!-- ===================================================================
     HOW IT WORKS — Cara Pesan (komponen bersama)
===================================================================== -->
<?php include __DIR__ . '/includes/order-steps.php'; ?>


<!-- ===================================================================
     FAST QUOTE & WHATSAPP CALLOUT BANNER
===================================================================== -->
<section class="bg-[#ece8df] border-t border-b border-[#ece8e1] px-4 sm:px-6 lg:px-12 py-12 md:py-[49px]">
  <div class="max-w-[1280px] mx-auto">
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-center">

      <!-- Left: 3 features -->
      <div class="lg:col-span-8 grid grid-cols-1 sm:grid-cols-3 gap-6 sm:gap-0" data-reveal>
        <div class="flex sm:flex-col items-center sm:items-start gap-4 sm:gap-0 min-w-0 sm:pr-6">
          <div class="flex items-center justify-center size-10 shrink-0 sm:mb-2">
            <svg class="size-7" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M2.5 5h15v10h-15V5Z" stroke="#242424" stroke-width="1.25" stroke-linejoin="round"/><path d="M6.667 5v10M13.333 5v10M2.5 10h15" stroke="#242424" stroke-width="1.25"/></svg>
          </div>
          <div class="min-w-0">
            <span class="block font-jakarta font-bold text-dark text-[12px] tracking-[1.8px] uppercase leading-[16px]">UKURAN</span>
            <span class="block font-jakarta font-normal text-muted text-[12px] leading-[1.4] mt-1">Dibuat pas sesuai ukuran jendela Anda</span>
          </div>
        </div>
        <div class="flex sm:flex-col items-center sm:items-start gap-4 sm:gap-0 min-w-0 sm:pr-6 sm:border-l sm:border-[rgba(34,34,32,0.15)] sm:pl-6">
          <div class="flex items-center justify-center size-10 shrink-0 sm:mb-2">
            <svg class="size-7" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M10 1.667v16.666M6.667 5h5a1.667 1.667 0 0 1 0 3.333H8.333A1.667 1.667 0 0 0 8.333 11.667h6.25" stroke="#242424" stroke-width="1.25" stroke-linecap="round" stroke-linejoin="round"/></svg>
          </div>
          <div class="min-w-0">
            <span class="block font-jakarta font-bold text-dark text-[12px] tracking-[1.8px] uppercase leading-[16px]">HARGA</span>
            <span class="block font-jakarta font-normal text-muted text-[12px] leading-[1.4] mt-1">Harga transparan sesuai kebutuhan proyek</span>
          </div>
        </div>
        <div class="flex sm:flex-col items-center sm:items-start gap-4 sm:gap-0 min-w-0 sm:pr-6 sm:border-l sm:border-[rgba(34,34,32,0.15)] sm:pl-6">
          <div class="flex items-center justify-center size-10 shrink-0 sm:mb-2">
            <svg class="size-7" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M4.167 2.5h11.666a.833.833 0 0 1 .834.833v13.334a.833.833 0 0 1-.834.833H4.167a.833.833 0 0 1-.834-.833V3.333a.833.833 0 0 1 .834-.833Z" stroke="#242424" stroke-width="1.25" stroke-linejoin="round"/><path d="M6.667 6.667h6.666M6.667 10h6.666M6.667 13.333h4.166" stroke="#242424" stroke-width="1.25" stroke-linecap="round"/></svg>
          </div>
          <div class="min-w-0">
            <span class="block font-jakarta font-bold text-dark text-[12px] tracking-[1.8px] uppercase leading-[16px]">PENAWARAN</span>
            <span class="block font-jakarta font-normal text-muted text-[12px] leading-[1.4] mt-1">Penawaran cepat, rinci, dan profesional</span>
          </div>
        </div>
      </div>

      <!-- Right: Buttons -->
      <div class="lg:col-span-4 flex flex-col items-stretch sm:items-center lg:items-end" data-reveal style="--d:120ms">
        <button type="button" data-open-quote
           class="flex items-center justify-center gap-2 bg-brand text-white font-jakarta font-semibold text-[12px] tracking-[1.8px] uppercase leading-[16px] px-6 py-[14px] rounded-[2px] shadow-sm w-full sm:w-64 hover:bg-[#6a041c] transition-colors duration-200 group">
          MINTA PENAWARAN <span class="transition-transform duration-300 group-hover:translate-x-1" aria-hidden="true">→</span>
        </button>
        <a href="<?= WA_URL ?>?text=Halo+Wintom%2C+saya+ingin+konsultasi"
           target="_blank" rel="noopener"
           class="flex items-center justify-center gap-2 w-full sm:w-64 px-4 py-[10px] mt-3 group">
          <svg class="size-5 text-[#25d366]" viewBox="0 0 20 20" fill="#25D366" xmlns="http://www.w3.org/2000/svg">
            <path d="M10 1.667A8.333 8.333 0 0 0 2.92 13.742L1.667 18.333l4.725-1.237A8.333 8.333 0 1 0 10 1.667Z"/>
            <path d="M7.5 6.667c.167-.417.625-.834 1.25-.834.5 0 .833.25 1.042.5l1.041 1.667c.208.417.042.833-.208 1.083l-.417.417c.417.75 1.125 1.458 1.875 1.875l.417-.417c.25-.25.667-.417 1.083-.208l1.667 1.041c.25.209.5.542.5 1.042 0 .625-.417 1.083-.833 1.25C13.750 14.167 11.250 14.583 8.333 11.667 5.417 8.750 5.833 6.250 6.250 5.417Z" fill="white"/>
          </svg>
          <span class="font-jakarta font-medium text-dark text-[12px] leading-[16px] group-hover:text-brand transition-colors duration-200">Konsultasi via WhatsApp</span>
        </a>
      </div>

    </div>
  </div>
</section>


<!-- ===================================================================
     KONTEN WINTOM — YouTube video / shorts (dikelola dari admin)
===================================================================== -->
<?php if ($home_videos): ?>
<section id="konten" class="bg-[#faf8f5] border-t border-[#ece8e1] py-16 md:py-24">
  <div class="max-w-[1280px] mx-auto px-4 sm:px-6 lg:px-12 flex flex-col gap-10 md:gap-12">
    <div class="flex flex-col gap-3 items-center w-full" data-reveal>
      <h2 class="font-playfair font-normal text-dark text-[28px] md:text-[36px] tracking-[-0.5px] text-center leading-[1.2] m-0">Konten Wintom</h2>
      <div class="bg-brand h-[2px] w-12"></div>
    </div>
    <div class="flex flex-wrap justify-center gap-4 md:gap-6">
      <?php foreach ($home_videos as $v):
          $vid = htmlspecialchars($v['youtube_id']);
          $ttl = $v['title'] !== null && $v['title'] !== '' ? $v['title'] : 'Video Wintom';
          $is_short = (bool)$v['is_short'];
      ?>
      <figure class="m-0 <?= $is_short ? 'w-[calc(50%-8px)] md:w-[calc(25%-18px)]' : 'w-full md:w-[calc(33.333%-16px)]' ?>" data-reveal>
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
     KATA PELANGGAN — Testimoni
===================================================================== -->
<section class="bg-[#faf8f5] py-16 md:py-24">
  <div class="max-w-[1280px] mx-auto px-4 sm:px-6 lg:px-12 flex flex-col gap-10 md:gap-16">

    <!-- Section Heading -->
    <div class="flex flex-col gap-3 items-center w-full" data-reveal>
      <h2 class="font-playfair font-normal text-dark text-[28px] md:text-[36px] tracking-[-0.5px] text-center leading-[1.2] m-0">
        Kata Pelanggan Kami
      </h2>
      <div class="bg-brand h-[2px] w-12"></div>
    </div>

    <!-- 3 Testimonial Cards -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 lg:gap-8">

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
          'quote'    => 'Kualitas bagus, pemasangan cepat, layanan sangat baik. Roller blind motorized-nya bergerak sangat halus dan senyap.',
          'location' => '— KANTOR, TANGERANG',
        ],
        [
          'img'      => 'https://images.unsplash.com/photo-1631049307264-da0ec9d70304?w=600&q=80&fit=crop',
          'alt'      => 'Luxury master bedroom with blackout curtains',
          'quote'    => 'Bahannya premium dan terlihat sangat elegan. Benar-benar 100% blackout untuk kamar tidur kami. Sangat puas!',
          'location' => '— APARTEMEN, JAKARTA',
        ],
      ];
      foreach ($testimonials as $i => $t): ?>

      <div class="bg-white border border-[#ece8e1] rounded-[2px] shadow-[0px_1px_1px_rgba(0,0,0,0.05)] flex flex-col items-start justify-between p-5 sm:p-[25px] min-w-0 hover:-translate-y-1 hover:shadow-[0_18px_40px_-20px_rgba(34,34,32,0.3)] transition-all duration-300" data-reveal style="--d:<?= $i * 120 ?>ms">
        <!-- Top content -->
        <div class="flex flex-col gap-[7.3px] w-full">
          <!-- Photo -->
          <div class="bg-[#ece8e1] h-[200px] md:h-[176px] overflow-hidden rounded-[2px] w-full relative">
            <img
              src="<?= $t['img'] ?>"
              alt="<?= htmlspecialchars($t['alt']) ?>"
              loading="lazy"
              class="absolute inset-0 w-full h-full object-cover"
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
<section id="faq" class="bg-[#f4f1ea] border-t border-[#ece8e1] py-16 md:py-24">
  <div class="max-w-[860px] mx-auto px-4 sm:px-6 lg:px-12 flex flex-col gap-10 md:gap-12">
    <div class="flex flex-col gap-3 items-center w-full" data-reveal>
      <h2 class="font-playfair font-normal text-dark text-[28px] md:text-[36px] tracking-[-0.5px] text-center leading-[1.2] m-0">Pertanyaan Umum</h2>
      <div class="bg-brand h-[2px] w-12"></div>
    </div>
    <div class="flex flex-col gap-3">
      <?php
      $faqs = [
        ['Apa bedanya ready stock dan custom?', 'Ready stock bisa langsung dibawa atau dipasang. Custom dibuat mengikuti ukuran jendela dan bahan pilihan Anda, sehingga hasilnya pas sempurna.'],
        ['Bagaimana cara memesan?', 'Hubungi kami lewat WhatsApp atau form penawaran. Untuk custom, tim kami mengukur dan membantu memilih bahan, lalu Anda menerima penawaran rinci. Setelah setuju, kami produksi dan pasang di lokasi Anda.'],
        ['Apakah melayani proyek komersial?', 'Ya. Kami menangani hunian, kantor, hingga hotel &amp; villa. Lihat hasilnya di halaman Galeri.'],
        ['Bisakah saya melihat bahannya langsung?', 'Tentu. Kunjungi showroom kami di Ruko Soho Ebony, PIK, Jakarta Utara, setiap Senin–Sabtu pukul 09.00–18.00.'],
      ];
      foreach ($faqs as $i => [$q, $a]): ?>
      <details class="group bg-white border border-[#ece8e1] rounded-[2px]" <?= $i === 0 ? 'open' : '' ?>>
        <summary class="flex items-center justify-between gap-4 cursor-pointer list-none px-5 sm:px-6 py-5 font-jakarta font-semibold text-dark text-[16px] sm:text-[17px] leading-[1.4] [&::-webkit-details-marker]:hidden">
          <?= $q ?>
          <svg class="size-5 shrink-0 text-brand transition-transform duration-200 group-open:rotate-45" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M12 5v14M5 12h14"/></svg>
        </summary>
        <p class="px-5 sm:px-6 pb-6 m-0 font-jakarta text-muted text-[15px] sm:text-[16px] leading-[1.6]"><?= $a ?></p>
      </details>
      <?php endforeach; ?>
    </div>
    <div class="text-center">
      <a href="<?= WA_URL ?>?text=Halo+Wintom%2C+saya+ada+pertanyaan"
         target="_blank" rel="noopener"
         class="inline-flex bg-brand text-white font-jakarta font-semibold text-[14px] px-8 py-[14px] rounded-[3px] hover:bg-[#6a041c] transition-colors duration-200">
        Tanya Langsung via WhatsApp
      </a>
    </div>
  </div>
</section>


<!-- ===================================================================
     CONTACT US — form inline (modal global tetap bisa dipanggil dari CTA)
===================================================================== -->
<section id="contact" class="bg-cream py-16 md:py-24 scroll-mt-[81px]">
  <div class="max-w-[1280px] mx-auto px-4 sm:px-6 lg:px-12">
    <div class="text-center mb-10 md:mb-14" data-reveal>
      <p class="font-jakarta font-semibold text-brand text-[11px] tracking-[2.64px] uppercase mb-3">HUBUNGI KAMI</p>
      <h2 class="font-playfair font-normal text-dark text-[28px] md:text-[36px] leading-[1.2] tracking-[-0.5px] m-0">Ceritakan Kebutuhan Jendela Anda</h2>
    </div>
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 lg:gap-12 items-stretch">
      <div class="min-w-0" data-reveal><?php render_inquiry_form('home', $quote_categories); ?></div>
      <div class="min-w-0" data-reveal style="--d:120ms"><?php include __DIR__ . '/includes/contact-info.php'; ?></div>
    </div>
  </div>
</section>


<?php include __DIR__ . '/includes/footer.php'; ?>
