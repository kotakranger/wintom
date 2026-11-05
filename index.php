<?php
// Wintom Curtain — Homepage (index.php)
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/db.php';

$page_title = 'Gorden, Roller Blind & Zebra Blind di PIK, Jakarta';
$active_nav = 'home';
if (is_legacy_url()) redirect_clean('/');   // /index.php -> /
require_once __DIR__ . '/includes/seo.php';
$meta_description = 'Wintom Curtain: gorden custom, roller blind, zebra blind & motorized smart curtain di PIK, Jakarta Utara. Ready stock atau custom, survei gratis dan pasang se-Jabodetabek.';
$canonical_route  = '/';
$schema           = [seo_local_business()];
// Pertanyaan Umum (ditampilkan di bagian FAQ di bawah) — dipakai juga untuk data terstruktur FAQPage
$faqs_v1 = [
    ['Apa bedanya ready stock dan custom?', 'Ready stock bisa langsung dibawa atau dipasang. Custom dibuat mengikuti ukuran jendela dan bahan pilihan Anda, sehingga hasilnya pas sempurna.'],
    ['Bagaimana cara memesan?', 'Hubungi kami lewat WhatsApp atau form penawaran. Untuk custom, tim kami mengukur dan membantu memilih bahan, lalu Anda menerima penawaran rinci. Setelah setuju, kami produksi dan pasang di lokasi Anda.'],
    ['Apakah survei dan pengukuran ke lokasi gratis?', 'Ya. Tim Wintom datang ke hunian atau kantor Anda untuk mengukur jendela dan membantu memilih bahan, tanpa biaya survei untuk area Jabodetabek.'],
    ['Apakah melayani proyek komersial?', 'Ya. Kami menangani hunian, kantor, hingga hotel &amp; villa. Lihat hasilnya di halaman Galeri.'],
    ['Bisakah saya melihat bahannya langsung?', 'Tentu. Kunjungi showroom kami di Ruko Soho Ebony, PIK, Jakarta Utara, setiap Senin–Sabtu pukul 09.00–18.00.'],
];
$faqs_v2 = [
    ['Apakah bisa melihat langsung koleksi gorden dan blinds?', 'Tentu. Kamu bisa mengunjungi showroom kami di PIK 2 untuk melihat berbagai pilihan bahan, warna, motif, dan tekstur secara langsung.'],
    ['Apakah tersedia layanan survei dan pemasangan?', 'Ya, tersedia layanan konsultasi, pengukuran, hingga pemasangan profesional di wilayah Jabodetabek. Biaya disesuaikan dengan jarak dan kebutuhan di lokasi.'],
    ['Apakah gorden dan blinds bisa custom ukuran?', 'Bisa. Diproduksi langsung dari pabrik dengan beragam pilihan bahan, warna, model, dan ukuran custom, mulai dari gorden, vitrase, roller blind, zebra blind, hingga tirai motorized.'],
    ['Apakah melayani pemesanan untuk proyek?', 'Ya, kami melayani kebutuhan rumah, apartemen, kantor, hotel, restoran, hingga proyek komersial, baik untuk pemesanan satuan maupun jumlah besar.'],
    ['Apa perbedaan blackout, semi blackout, dan sunscreen?', 'Blackout: Menghalangi cahaya secara maksimal, cocok untuk kamar tidur dan ruang meeting.<br>Semi blackout: Menyaring sebagian cahaya agar ruangan tetap nyaman, ideal untuk ruang tamu, kamar, dan kantor.<br>Sunscreen: Mengurangi panas dan silau dengan tetap memungkinkan pandangan ke luar, cocok untuk kantor, jendela besar, teras, dan garasi beratap.'],
];
$faqs = copy_v2() ? $faqs_v2 : $faqs_v1;
$schema[] = [
    '@context'   => 'https://schema.org',
    '@type'      => 'FAQPage',
    'mainEntity' => array_map(fn($f) => [
        '@type' => 'Question', 'name' => html_entity_decode($f[0], ENT_QUOTES, 'UTF-8'),
        'acceptedAnswer' => ['@type' => 'Answer', 'text' => trim(html_entity_decode(strip_tags(str_replace('<br>', "\n", $f[1])), ENT_QUOTES, 'UTF-8'))],
    ], $faqs),
];

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

  <!-- Gambar hero = elemen terbesar (LCP): responsif & diprioritaskan. Video (4,9 MB) dimuat saat pengunjung
       mulai berinteraksi, lalu muncul halus di atas gambar ini, jadi skor PageSpeed tidak terganggu. -->
  <img src="<?= BASE_URL ?>/assets/images/hero/hero-1280.webp"
       srcset="<?= BASE_URL ?>/assets/images/hero/hero-640.webp 640w, <?= BASE_URL ?>/assets/images/hero/hero-960.webp 960w, <?= BASE_URL ?>/assets/images/hero/hero-1280.webp 1280w, <?= BASE_URL ?>/assets/images/hero/hero-1920.webp 1920w"
       sizes="100vw" width="1920" height="1080"
       alt="" fetchpriority="high" decoding="async" class="wt-zoom absolute inset-0 w-full h-full object-cover" />
  <video id="hero-video" class="absolute inset-0 w-full h-full object-cover opacity-0 transition-opacity duration-1000"
         muted loop playsinline preload="none" aria-hidden="true" data-src="<?= BASE_URL ?>/assets/video/hero.mp4"></video>
  <script>
    (function () {
      var v = document.getElementById('hero-video'), c = navigator.connection || {};
      // Tetap gambar saja untuk mode hemat data, koneksi 2G, atau pengunjung yang meminta gerakan dikurangi
      if (!v || c.saveData || /(^|-)2g$/.test(c.effectiveType || '') || matchMedia('(prefers-reduced-motion: reduce)').matches) return;
      function start() {
        v.src = v.dataset.src; v.preload = 'auto';
        v.addEventListener('playing', function () { v.classList.remove('opacity-0'); }, { once: true });
        var p = v.play(); if (p && p.catch) p.catch(function () {});
        // Hemat CPU/baterai: jeda saat hero tidak terlihat
        if ('IntersectionObserver' in window) new IntersectionObserver(function (en) {
          if (en[0].isIntersecting) { var q = v.play(); if (q && q.catch) q.catch(function () {}); } else v.pause();
        }).observe(v);
      }
      // Mulai saat pengunjung pertama kali berinteraksi (hampir seketika pada pengunjung nyata).
      // Memutar video lebih awal membuat main thread sibuk dan menurunkan skor PageSpeed/Lighthouse.
      var started = false, evs = ['pointerdown', 'touchstart', 'keydown', 'wheel', 'scroll', 'mousemove'];
      function go() { if (started) return; started = true; evs.forEach(function (e) { removeEventListener(e, go); }); setTimeout(start, 150); }
      evs.forEach(function (e) { addEventListener(e, go, { passive: true }); });
    })();
  </script>

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
            <?= copy_v2() ? 'SPESIALIS GORDEN &amp; BLINDS · JAKARTA' : 'SPESIALIS GORDEN &amp; BLINDS · PIK, JAKARTA' ?>
          </span>
        </div>

        <!-- Main heading -->
        <h1 class="wt-in font-playfair font-normal text-white text-[32px] sm:text-[40px] md:text-[48px] leading-[1.15] tracking-[-0.5px] max-w-[760px] m-0 drop-shadow-[0px_1px_0.5px_rgba(0,0,0,0.05)]" style="--d:220ms">
          <?php if (copy_v2()): ?>Gorden &amp; Blinds Elegan,<br>Sentuhan Sempurna untuk Ruangan Anda<?php else: ?>Gorden &amp; Blinds yang Pas Sempurna di Setiap Jendela<?php endif; ?>
        </h1>

        <!-- Sub-headline -->
        <p class="wt-in font-jakarta text-[16px] md:text-[18px] text-[rgba(255,255,255,0.9)] leading-[1.6] m-0 mt-6 max-w-[560px]" style="--d:420ms">
          <?php if (copy_v2()): ?>Pilih gorden atau blinds custom langsung dari pabrik, sesuai kebutuhan ruangan Anda. Kami mendampingi setiap tahap, mulai dari konsultasi dan pengukuran hingga pemasangan profesional, disertai garansi serta dukungan purna jual.
          <span class="block mt-3">Belum tahu ukuran atau bahan yang cocok? Kami siap membantu.</span>
          <?php else: ?>Pilih koleksi siap pasang atau buat sesuai ukuran. Tim kami datang mengukur, membawa sampel bahan, dan memasang hingga rapi — Anda tinggal menikmati hasilnya.<?php endif; ?>
        </p>

        <!-- CTA Buttons -->
        <div class="wt-in flex flex-col sm:flex-row sm:flex-wrap sm:items-center gap-3 sm:gap-4 pt-8 w-full sm:w-auto" style="--d:540ms">
          <button type="button" data-open-quote
             class="bg-brand text-white font-jakarta font-semibold text-[14px] leading-[16px] px-8 py-[16px] rounded-[3px] hover:bg-[#6a041c] hover:-translate-y-0.5 transition-all duration-200 shadow-lg">
            Konsultasi Gratis
          </button>
          <a href="<?= url_for(route_catalog()) ?>"
             class="text-center backdrop-blur-[2px] bg-[rgba(255,255,255,0.1)] border border-[rgba(255,255,255,0.6)] text-white font-jakarta font-medium text-[14px] leading-[16px] px-[33px] py-[15px] rounded-[3px] shadow-sm hover:bg-[rgba(255,255,255,0.2)] transition-colors duration-200 whitespace-nowrap">
            <?= copy_v2() ? 'Lihat Koleksi Produk' : 'Jelajahi Koleksi' ?>
          </a>
        </div>
        <p class="wt-in font-jakarta text-[13px] text-[rgba(255,255,255,0.75)] leading-[1.5] pt-3 m-0" style="--d:640ms">Respon cepat di jam operasional.</p>
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
    <a href="<?= url_for(route_catalog($cat['name'])) ?>" data-reveal style="--d:<?= ($i % 3) * 100 ?>ms"
       class="group bg-white border border-[rgba(236,232,225,0.8)] rounded-[2px] overflow-hidden flex flex-col p-px hover:shadow-[0_24px_48px_-24px_rgba(34,34,32,0.4)] transition-shadow duration-300">
      <div class="bg-[#ece8e1] aspect-[4/3] relative overflow-hidden">
        <?php if ($img): ?>
        <img <?= img_attrs($img, '(min-width:1024px) 400px, (min-width:640px) 50vw, 100vw', [480, 800, 1200]) ?> alt="<?= htmlspecialchars($cat['name']) ?>" loading="lazy"
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
          <img <?= img_attrs($tile['url'], '(min-width:640px) 25vw, 50vw', [300, 600]) ?> alt="<?= htmlspecialchars($tile['alt']) ?>" loading="lazy"
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
      if (copy_v2()) {
        foreach ([
          ['Produksi Sendiri, Custom Sesuai Kebutuhan', 'Gorden dan blinds diproduksi langsung di pabrik kami dengan pilihan bahan, model, dan ukuran yang dapat disesuaikan. Setiap proses dikerjakan dengan perhatian terhadap kualitas dan hasil akhir.'],
          ['Pilih Material Langsung di Showroom', 'Kunjungi showroom kita yang ada di PIK 2 untuk melihat koleksi gorden dan roller blind, membandingkan warna, serta merasakan tekstur bahan sebelum menentukan pilihan.'],
          ['Pengukuran &amp; Pemasangan Profesional', 'Kami melayani konsultasi, pengukuran, hingga pemasangan gorden dan blinds untuk hunian maupun ruang komersial. Seluruh proses ditangani oleh tim berpengalaman agar hasilnya rapi dan sesuai kebutuhan.'],
          ['Garansi &amp; Layanan Purna Jual', 'Layanan WINTOM berlanjut setelah pemasangan selesai. Kami menyediakan garansi sesuai ketentuan serta dukungan purna jual untuk memastikan produk tetap berfungsi dengan baik.'],
        ] as $k => [$ft, $fd]) { $features[$k]['title'] = $ft; $features[$k]['desc'] = $fd; }
      }
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
      <p class="font-jakarta font-semibold text-brand text-[12px] tracking-[2.64px] uppercase mb-4"><?= copy_v2() ? 'MENGAPA MEMILIH KAMI?' : 'MENGAPA MEMILIH WINTOM?' ?></p>
      <h2 class="font-playfair font-normal text-dark text-[28px] md:text-[36px] leading-[1.2] tracking-[-0.5px] m-0 mb-5"><?= copy_v2() ? '20 Tahun Pengalaman, dari Produksi hingga Pemasangan' : 'Konsultasi Praktis dari Rumah, Pemasangan Presisi di Lokasi' ?></h2>
      <p class="font-jakarta text-muted text-[16px] md:text-[17px] leading-[1.6] m-0"><?php if (copy_v2()): ?>Memilih gorden atau blinds bukan hanya soal desain dan warna. Kualitas material, ketepatan ukuran, serta hasil pemasangan menjadi bagian penting dalam menciptakan ruangan yang nyaman. Dengan pengalaman selama 20 tahun dan fasilitas produksi sendiri, setiap proses ditangani secara menyeluruh, mulai dari konsultasi hingga pemasangan akhir.<?php else: ?>Pilih model dan konsultasi langsung dari ponsel Anda. Saat siap, tim kami datang membawa sampel bahan grade A, mengukur dengan presisi, lalu memasang serapi standar hotel bintang lima.<?php endif; ?></p>
    </div>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 md:gap-6">
      <?php
      $usp = [
        ['Bahan & Jahitan Berkualitas', 'Material pilihan yang tebal dan awet, dijahit rapi agar gorden jatuh bergelombang dengan sempurna.'],
        ['Konsultasi Online atau Kunjungan', 'Diskusikan kebutuhan Anda lewat WhatsApp atau website, atau jadwalkan tim kami datang membawa buku sampel ke tempat Anda.'],
        ['Garansi Ukuran Presisi 100%', 'Tidak perlu khawatir salah ukur. Ikuti panduan dari tim ahli kami, atau biarkan kami yang mengukur langsung di lokasi.'],
        ['Terima Beres & Bergaransi', 'Dari pemilihan desain, produksi, hingga pemasangan yang bersih — dengan rel kokoh yang bergerak mulus tanpa macet.'],
      ];
      if (copy_v2()) $usp = [
        ['Pilihan Gorden & Blinds Lengkap', 'Tersedia beragam pilihan gorden blackout, semi blackout, vitrase, roller blind, zebra blind, vertical blind, hingga berbagai model tirai lainnya dengan pilihan bahan, warna, dan desain sesuai kebutuhan ruangan.'],
        ['Produksi Sendiri & Custom Ukuran', 'Diproduksi langsung dari pabrik dengan material pilihan dan pengerjaan yang memperhatikan setiap detail. Tersedia layanan custom ukuran untuk kebutuhan hunian, perkantoran, hingga proyek komersial.'],
        ['Konsultasi & Pengukuran Profesional', 'Konsultasikan kebutuhan gorden dan blinds secara online maupun langsung di showroom. Tersedia layanan survei dan pengukuran di lokasi untuk membantu menentukan pilihan bahan, model, serta ukuran yang tepat.'],
        ['Pemasangan Profesional & Bergaransi', 'Mulai dari proses produksi hingga pemasangan, setiap tahap ditangani oleh tim berpengalaman dengan mengutamakan kerapian dan ketepatan hasil. Dilengkapi garansi sesuai ketentuan serta layanan purna jual.'],
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
<?php $order_steps_v2 = copy_v2(); include __DIR__ . '/includes/order-steps.php'; ?>


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
            <svg class="size-7" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M2.5 10.2V4.17c0-.92.75-1.67 1.67-1.67h6.03c.44 0 .87.18 1.18.49l6.46 6.46a1.67 1.67 0 0 1 0 2.36l-5.64 5.64a1.67 1.67 0 0 1-2.36 0L2.99 11.38a1.67 1.67 0 0 1-.49-1.18Z" stroke="#242424" stroke-width="1.25" stroke-linejoin="round"/><circle cx="6.67" cy="6.67" r="1.04" stroke="#242424" stroke-width="1.25"/></svg>
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

    <!-- Carousel testimoni: autoplay + panah kiri/kanan + indikator progres -->
    <?php
    // DUMMY — 10 testimoni contoh. Ganti dengan testimoni pelanggan asli sebelum dipromosikan.
    $testimonials = [
      ['img' => 'photo-1493809842364-78817add7ffb', 'alt' => 'Rumah modern dengan gorden sheer Wintom',
       'quote' => 'Hasilnya sangat rapi dan sesuai ekspektasi. Timnya profesional, mulai dari pengukuran hingga pemasangan tepat waktu.',
       'location' => '— RUMAH TINGGAL, JAKARTA'],
      ['img' => 'photo-1497366216548-37526070297c', 'alt' => 'Kantor dengan roller blind otomatis',
       'quote' => 'Kualitas bagus, pemasangan cepat, layanan sangat baik. Roller blind motorized-nya bergerak sangat halus dan senyap.',
       'location' => '— KANTOR, TANGERANG'],
      ['img' => 'photo-1631049307264-da0ec9d70304', 'alt' => 'Kamar tidur utama dengan gorden blackout',
       'quote' => 'Bahannya premium dan terlihat sangat elegan. Benar-benar 100% blackout untuk kamar tidur kami. Sangat puas!',
       'location' => '— APARTEMEN, JAKARTA'],
      ['img' => 'photo-1618221195710-dd6b41faaea6', 'alt' => 'Ruang keluarga dengan gorden ripple fold',
       'quote' => 'Konsultasinya enak lewat WhatsApp, jadi kami tidak bingung memilih bahan. Waktu dipasang, ukurannya pas sekali di semua jendela.',
       'location' => '— RUMAH TINGGAL, PIK'],
      ['img' => 'photo-1600210492486-724fe5c67fb0', 'alt' => 'Ruang tamu dengan vitrase tipis',
       'quote' => 'Vitrase-nya jatuh cantik dan cahaya masuk jadi lembut. Tamu yang datang sering bertanya beli di mana.',
       'location' => '— RUMAH TINGGAL, JAKARTA BARAT'],
      ['img' => 'photo-1583847268964-b28dc8f51f92', 'alt' => 'Kamar tidur dengan gorden tebal',
       'quote' => 'Kamar jadi gelap dan tenang untuk tidur siang. Jahitannya rapi dan warnanya sama persis seperti sampel yang kami lihat di showroom.',
       'location' => '— RUMAH TINGGAL, JAKARTA SELATAN'],
      ['img' => 'photo-1616486338812-3dadae4b4ace', 'alt' => 'Ruang kerja dengan tirai otomatis',
       'quote' => 'Tirai motorized untuk ruang meeting kami berfungsi mulus. Tim pemasang datang sesuai jadwal dan meninggalkan lokasi dalam keadaan bersih.',
       'location' => '— KANTOR, JAKARTA PUSAT'],
      ['img' => 'photo-1600585154340-be6161a56a0c', 'alt' => 'Villa dengan blind kayu',
       'quote' => 'Kami pilih blind untuk jendela besar di villa. Hasilnya hangat dan natural, dan cara kerjanya mudah dipakai setiap hari.',
       'location' => '— VILLA, BOGOR'],
      ['img' => 'photo-1513694203232-719a280e022f', 'alt' => 'Ruang makan dengan gorden dua lapis',
       'quote' => 'Gorden dua lapis di ruang makan terlihat mewah tapi tetap praktis. Pelayanannya ramah dan responsif dari awal sampai selesai.',
       'location' => '— RUMAH TINGGAL, TANGERANG SELATAN'],
      ['img' => 'photo-1507652313519-d4e9174996dd', 'alt' => 'Apartemen dengan gorden S-fold',
       'quote' => 'Untuk apartemen kami butuh solusi cepat dan rapi. Dari ukur sampai pasang selesai dalam waktu singkat, dan hasilnya memuaskan.',
       'location' => '— APARTEMEN, JAKARTA UTARA'],
    ];
    $t_total = count($testimonials);
    ?>
    <div id="testi" role="region" aria-roledescription="carousel" aria-label="Kata pelanggan kami" class="flex flex-col gap-8">

      <div data-t-track tabindex="0" aria-live="off"
           class="flex gap-6 lg:gap-8 overflow-x-auto snap-x snap-mandatory no-scrollbar scroll-smooth py-3 -my-3 focus:outline-none">
        <?php foreach ($testimonials as $i => $t): ?>
        <div role="group" aria-roledescription="slide" aria-label="<?= $i + 1 ?> dari <?= $t_total ?>"
             class="snap-start shrink-0 basis-full md:basis-[calc((100%-1.5rem*2)/3)] lg:basis-[calc((100%-2rem*2)/3)] min-w-0 bg-white border border-[#ece8e1] rounded-[2px] shadow-[0px_1px_1px_rgba(0,0,0,0.05)] flex flex-col items-start justify-between p-5 sm:p-[25px]">
          <div class="flex flex-col gap-[7.3px] w-full">
            <div class="bg-[#ece8e1] h-[200px] md:h-[176px] overflow-hidden rounded-[2px] w-full relative">
              <img <?= img_attrs('https://images.unsplash.com/' . $t['img'], '(min-width:768px) 33vw, 100vw', [480, 800]) ?>
                   alt="<?= htmlspecialchars($t['alt']) ?>" loading="lazy" draggable="false"
                   class="absolute inset-0 w-full h-full object-cover" />
            </div>
            <div class="pt-[16.7px]">
              <span class="font-playfair font-normal text-brand text-[36px] leading-[36px]">"</span>
            </div>
            <p class="font-jakarta font-normal text-dark text-[14px] leading-[22.75px] m-0"><?= htmlspecialchars($t['quote']) ?></p>
          </div>
          <div class="pt-6 w-full">
            <div class="border-t border-[#ece8e1] pt-[17px] flex flex-col gap-[7px]">
              <span class="text-[#f59e0b] text-[12px] leading-[16px]" aria-label="5 dari 5 bintang">★★★★★</span>
              <span class="font-jakarta font-semibold text-muted text-[11px] tracking-[1.65px] uppercase leading-[16.5px]"><?= htmlspecialchars($t['location']) ?></span>
            </div>
          </div>
        </div>
        <?php endforeach; ?>
      </div>

      <!-- Kontrol: panah kiri/kanan + titik indikator posisi (dibuat oleh JS, satu titik per posisi geser) -->
      <div class="flex items-center justify-center gap-4">
        <button type="button" data-t-prev aria-label="Testimoni sebelumnya"
                class="size-11 shrink-0 flex items-center justify-center rounded-full border border-[#ece8e1] bg-white text-dark hover:border-brand hover:text-brand transition-colors">
          <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="m15 18-6-6 6-6"/></svg>
        </button>
        <div data-t-dots class="flex items-center justify-center gap-2 flex-wrap max-w-[260px]" role="group" aria-label="Pilih testimoni"></div>
        <button type="button" data-t-next aria-label="Testimoni berikutnya"
                class="size-11 shrink-0 flex items-center justify-center rounded-full border border-[#ece8e1] bg-white text-dark hover:border-brand hover:text-brand transition-colors">
          <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="m9 18 6-6-6-6"/></svg>
        </button>
      </div>
    </div>

    <script>
    (function () {
      const root = document.getElementById('testi');
      const track = root.querySelector('[data-t-track]');
      const dotsBox = root.querySelector('[data-t-dots]');
      const INTERVAL = 5000;   // jeda antar geser otomatis (ms)
      const reduced = window.matchMedia && matchMedia('(prefers-reduced-motion: reduce)').matches;
      let elapsed = 0, hover = false, focus = false, touching = false, visible = false, dots = [];

      const maxScroll = () => track.scrollWidth - track.clientWidth;
      const stepPx = () => {
        const c = track.children[0], gap = parseFloat(getComputedStyle(track).columnGap) || 0;
        return c.getBoundingClientRect().width + gap;
      };
      const positions = () => Math.max(1, Math.round(maxScroll() / stepPx()) + 1);   // 8 di desktop (3 kartu), 10 di HP
      const current = () => Math.min(positions() - 1, Math.round(track.scrollLeft / stepPx()));

      function buildDots() {
        const n = positions();
        dotsBox.replaceChildren();
        dots = [];
        for (let i = 0; i < n; i++) {
          const d = document.createElement('button');
          d.type = 'button';
          d.setAttribute('aria-label', 'Ke testimoni ' + (i + 1));
          d.className = 'size-2.5 rounded-full transition-all duration-300 bg-[#d9d2c5] hover:bg-[#b9ad9b]';
          d.addEventListener('click', () => scrollToIndex(i));
          dotsBox.appendChild(d);
          dots.push(d);
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
      function scrollToIndex(i) {
        track.scrollTo({ left: Math.min(maxScroll(), i * stepPx()), behavior: reduced ? 'auto' : 'smooth' });
        elapsed = 0;
      }
      function go(dir) {
        const at = track.scrollLeft, max = maxScroll();
        let target = at + dir * stepPx();
        if (dir > 0 && at >= max - 2) target = 0;            // ujung kanan -> kembali ke awal
        else if (dir < 0 && at <= 2) target = max;           // ujung kiri -> loncat ke akhir
        track.scrollTo({ left: Math.max(0, Math.min(max, target)), behavior: reduced ? 'auto' : 'smooth' });
        elapsed = 0;
      }

      root.querySelector('[data-t-prev]').addEventListener('click', () => go(-1));
      root.querySelector('[data-t-next]').addEventListener('click', () => go(1));
      track.addEventListener('keydown', e => {
        if (e.key === 'ArrowLeft') { e.preventDefault(); go(-1); }
        else if (e.key === 'ArrowRight') { e.preventDefault(); go(1); }
      });
      // titik mengikuti posisi; geser manual (jari / trackpad) menyetel ulang hitungan autoplay
      let settle = 0;
      track.addEventListener('scroll', () => {
        paint();
        clearTimeout(settle); settle = setTimeout(() => { elapsed = 0; }, 150);
      }, { passive: true });
      window.addEventListener('resize', buildDots);
      // jeda saat disentuh, diarahkan kursor, atau difokuskan
      root.addEventListener('mouseenter', () => { hover = true; });
      root.addEventListener('mouseleave', () => { hover = false; });
      // hanya fokus keyboard yang menjeda (fokus bekas klik mouse pada tombol tidak boleh menghentikan autoplay)
      root.addEventListener('focusin', e => { focus = !!(e.target.matches && e.target.matches(':focus-visible')); });
      root.addEventListener('focusout', () => { focus = false; });
      root.addEventListener('touchstart', () => { touching = true; }, { passive: true });
      root.addEventListener('touchend', () => { setTimeout(() => { touching = false; }, 1500); }, { passive: true });
      // autoplay hanya jalan saat section terlihat
      if ('IntersectionObserver' in window) {
        new IntersectionObserver(es => { visible = es[0].isIntersecting; }, { threshold: 0.3 }).observe(root);
      } else { visible = true; }
      if (!reduced) {
        setInterval(() => {
          if (!visible || hover || focus || touching || document.hidden) return;
          elapsed += 100;
          if (elapsed >= INTERVAL) go(1);
        }, 100);
      }
      buildDots();
    })();
    </script>
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
      <?php foreach ($faqs as $i => [$q, $a]): ?>
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
