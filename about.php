<?php
// about.php — Tentang Kami & Kontak Wintom Curtain
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/db.php';

$page_title = 'Tentang Kami & Kontak';
$active_nav  = 'about';

include_once __DIR__ . '/includes/header.php';
?>

<!-- ===== BRAND STORY HERO ===== -->
<section class="bg-cream-dark border-b border-border">
  <div class="max-w-[1280px] mx-auto px-4 sm:px-6 lg:px-12 py-14 md:py-[80px] grid grid-cols-1 lg:grid-cols-2 gap-10 lg:gap-16 items-center">
    <!-- Text -->
    <div class="min-w-0">
      <p class="wt-in font-jakarta font-semibold text-brand text-[12px] tracking-[2.64px] uppercase leading-[16.5px] mb-5" style="--d:80ms">TENTANG KAMI</p>
      <h1 class="wt-in font-playfair font-normal text-dark text-[32px] sm:text-[40px] md:text-[48px] leading-[1.15] tracking-[-0.5px] mb-6" style="--d:180ms">
        WINTOM Intelligent Solusi
      </h1>
      <p class="wt-in font-jakarta text-muted text-[16px] md:text-[17px] leading-[1.6] mb-5" style="--d:280ms">
        WINTOM Intelligent Solusi menghadirkan curtain dan blinds beserta sistem motorized, rel, serta aksesori pendukung. Koleksinya mencakup roller, zebra, roman, dan vertical blinds, serta motorized curtain track.
      </p>
      <p class="wt-in font-jakarta text-muted text-[16px] md:text-[17px] leading-[1.6] mb-5" style="--d:360ms">
        Kami melihat tampilan, komponen, dan kebutuhan pemasangan sebagai bagian dari satu keputusan. Karena itu, pemilihan produk dimulai dengan memahami ruang, lalu membahas seri, ukuran, serta sistem pengoperasian yang sesuai.
      </p>
      <p class="wt-in font-jakarta text-muted text-[16px] md:text-[17px] leading-[1.6]" style="--d:440ms">
        Dari sentuhan kain hingga detail rel, WINTOM membantu Anda melengkapi jendela agar ruang terasa lebih nyaman digunakan.
      </p>
    </div>
    <!-- Image -->
    <div class="wt-in relative aspect-[4/3] rounded-[6px] overflow-hidden bg-border" style="--d:300ms">
      <img <?= img_attrs('https://images.unsplash.com/photo-1618221195710-dd6b41faaea6', '(min-width:1024px) 50vw, 100vw') ?>
           alt="Wintom Curtain Atelier"
           class="wt-zoom w-full h-full object-cover" />
      <div class="absolute inset-0 bg-gradient-to-br from-transparent to-[rgba(34,34,32,0.2)]"></div>
    </div>
  </div>
</section>

<!-- ===== VISI & MISI ===== -->
<section class="bg-cream py-14 md:py-[80px] border-t border-border">
  <div class="max-w-[1100px] mx-auto px-4 sm:px-6 lg:px-12 grid grid-cols-1 md:grid-cols-2 gap-10 md:gap-12">
    <div data-reveal>
      <p class="font-jakarta font-semibold text-brand text-[12px] tracking-[2.64px] uppercase mb-3">VISI</p>
      <p class="font-playfair font-normal text-dark text-[22px] sm:text-[26px] leading-[1.35] m-0">Menjadi pilihan yang dipercaya untuk solusi tirai dan sistem jendela yang selaras dengan kebutuhan ruang.</p>
    </div>
    <div data-reveal style="--d:120ms">
      <p class="font-jakarta font-semibold text-brand text-[12px] tracking-[2.64px] uppercase mb-3">MISI</p>
      <ul class="list-none p-0 m-0 space-y-4">
        <?php foreach ([
          'Menghadirkan pilihan curtain, blinds, sistem motorized, serta komponen pendukung dalam satu portofolio.',
          'Membantu pemilihan produk melalui pembahasan fungsi, tampilan, ukuran, dan kesiapan pemasangan.',
          'Menyampaikan spesifikasi, penawaran, serta cakupan dukungan produk dengan jelas.',
          'Mengutamakan detail produk dan kesesuaian sistem untuk penggunaan sehari-hari.',
        ] as $m): ?>
        <li class="flex gap-3 font-jakarta text-muted text-[16px] leading-[1.6]">
          <span class="mt-[9px] size-1.5 rounded-full bg-brand shrink-0"></span><span><?= htmlspecialchars($m) ?></span>
        </li>
        <?php endforeach; ?>
      </ul>
    </div>
  </div>
</section>

<!-- ===== TIM INTI ===== -->
<?php
// Jajaran direksi / tim inti. PLACEHOLDER — ganti nama, jabatan & foto (isi 'photo' dengan URL/path foto, mis. /assets/images/team/nama.jpg).
$team = [
    ['name' => 'Nama Anggota Tim', 'role' => 'Direktur Utama',            'photo' => ''],
    ['name' => 'Nama Anggota Tim', 'role' => 'Direktur Operasional',      'photo' => ''],
    ['name' => 'Nama Anggota Tim', 'role' => 'Kepala Desain & Konsultasi', 'photo' => ''],
    ['name' => 'Nama Anggota Tim', 'role' => 'Kepala Produksi',           'photo' => ''],
    ['name' => 'Nama Anggota Tim', 'role' => 'Kepala Instalasi',          'photo' => ''],
    ['name' => 'Nama Anggota Tim', 'role' => 'Manajer Layanan Pelanggan', 'photo' => ''],
];
?>
<section class="bg-[#f4f1ea] border-t border-[#ece8e1] py-16 md:py-24">
  <div class="max-w-[1280px] mx-auto px-4 sm:px-6 lg:px-12 flex flex-col gap-10 md:gap-14">

    <!-- Judul: gaya sama dengan section di landing page -->
    <div class="flex flex-col gap-3 items-center w-full" data-reveal>
      <h2 class="font-playfair font-normal text-dark text-[28px] md:text-[36px] tracking-[-0.5px] text-center leading-[1.2] m-0">Tim Kami</h2>
      <div class="bg-brand h-[2px] w-12"></div>
    </div>

    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-4 lg:gap-6">
      <?php foreach ($team as $i => $m): ?>
      <figure class="m-0 group bg-white border border-[#ece8e1] rounded-[2px] shadow-[0px_1px_1px_rgba(0,0,0,0.05)] p-3 flex flex-col"
              data-reveal style="--d:<?= ($i % 6) * 70 ?>ms">
        <div class="relative aspect-square overflow-hidden rounded-[2px] bg-[#ece8e1]">
          <?php if ($m['photo']): ?>
          <img <?= img_attrs($m['photo'], '(min-width:1024px) 200px, (min-width:640px) 33vw, 50vw', [240, 480]) ?> alt="<?= htmlspecialchars($m['name'] . ', ' . $m['role']) ?>" loading="lazy"
               class="absolute inset-0 w-full h-full object-cover transition-transform duration-700 group-hover:scale-[1.05]"/>
          <?php else: ?>
          <!-- Placeholder siluet sampai foto asli tersedia -->
          <svg class="absolute inset-0 w-full h-full text-[#d9d2c5]" viewBox="0 0 100 100" preserveAspectRatio="xMidYMax meet" aria-hidden="true">
            <circle cx="50" cy="42" r="15" fill="currentColor"/>
            <path d="M22 100v-4c0-14 12.5-24 28-24s28 10 28 24v4z" fill="currentColor"/>
          </svg>
          <?php endif; ?>
        </div>
        <figcaption class="pt-3 px-1 pb-1">
          <p class="font-playfair font-normal text-dark text-[15px] sm:text-[16px] leading-[1.3] m-0"><?= htmlspecialchars($m['name']) ?></p>
          <p class="font-jakarta font-semibold text-muted text-[10px] tracking-[1.2px] uppercase leading-[1.4] mt-1 m-0"><?= htmlspecialchars($m['role']) ?></p>
        </figcaption>
      </figure>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- ===== BRAND VALUES ===== -->
<section class="bg-cream py-14 md:py-[80px]">
  <div class="max-w-[1280px] mx-auto px-4 sm:px-6 lg:px-12">
    <div class="text-center mb-10 md:mb-14" data-reveal>
      <p class="font-jakarta font-semibold text-brand text-[12px] tracking-[2.64px] uppercase mb-3">MENGAPA MEMILIH WINTOM?</p>
      <h2 class="font-playfair font-normal text-dark text-[28px] md:text-[36px] leading-[1.2] tracking-[-0.5px] max-w-[760px] mx-auto mb-4">Gorden Custom Premium: Praktis Secara Online, Presisi Secara Offline.</h2>
      <p class="font-jakarta text-muted text-[16px] md:text-[17px] leading-[1.6] max-w-[760px] mx-auto">Kemudahan konsultasi dan pilih model gorden dari genggaman, dipadukan dengan jaminan kualitas bahan grade A, survei bawakan sampel ke lokasi, hingga instalasi rapi setara hotel bintang lima.</p>
    </div>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-px bg-border rounded-[6px] overflow-hidden">
      <?php
      $values = [
        ['icon' => '<path stroke-linecap="round" stroke-linejoin="round" d="M9.813 15.904 9 18.75l-.813-2.846a4.5 4.5 0 0 0-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 0 0 3.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 0 0 3.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 0 0-3.09 3.09Z"/>',
         'title' => 'Kualitas Bahan & Jahitan Terbaik',
         'desc'  => 'Menggunakan material pilihan yang tebal, awet, dan jatuh bergelombang rapi sempurna.'],
        ['icon' => '<path stroke-linecap="round" stroke-linejoin="round" d="m3.75 13.5 10.5-11.25L12 10.5h8.25L9.75 21.75 12 13.5H3.75Z"/>',
         'title' => 'Fleksibel Online & Offline',
         'desc'  => 'Konsultasi praktis via WhatsApp/Website, atau jadwalkan tim kami datang membawa buku sampel bahan langsung ke tempat Anda.'],
        ['icon' => '<path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/>',
         'title' => 'Garansi Ukuran Presisi 100%',
         'desc'  => 'Bebas risiko salah ukuran dengan panduan ukur online dari tim ahli atau layanan survei pengukuran langsung ke lokasi.'],
        ['icon' => '<path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/>',
         'title' => 'Terima Beres & Bergaransi',
         'desc'  => 'Mulai dari pemilihan desain, produksi, hingga pemasangan bersih menggunakan komponen rel yang kokoh dan tidak macet.'],
      ];
      foreach ($values as $i => $v): ?>
      <div class="bg-cream p-6 sm:p-8 hover:bg-cream-dark transition-colors duration-200" data-reveal style="--d:<?= ($i % 2) * 120 ?>ms">
        <div class="size-10 rounded-full bg-brand/10 flex items-center justify-center mb-5">
          <svg class="size-5 text-brand" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
            <?= $v['icon'] ?>
          </svg>
        </div>
        <h3 class="font-playfair font-normal text-dark text-[20px] sm:text-[22px] leading-[1.3] mb-3"><?= $v['title'] ?></h3>
        <p class="font-jakarta text-muted text-[16px] leading-[1.6]"><?= $v['desc'] ?></p>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- ===== CARA PESAN (komponen bersama) ===== -->
<?php $order_steps_bg = 'bg-cream-dark border-t border-border'; include __DIR__ . '/includes/order-steps.php'; ?>

<!-- ===== CONTACT SECTION ===== -->
<section id="contact" class="bg-cream border-t border-border py-14 md:py-[80px] scroll-mt-[81px]">
  <div class="max-w-[1280px] mx-auto px-4 sm:px-6 lg:px-12">
    <div class="text-center mb-10 md:mb-14" data-reveal>
      <p class="font-jakarta font-semibold text-brand text-[11px] tracking-[2.64px] uppercase mb-3">HUBUNGI KAMI</p>
      <h2 class="font-playfair font-normal text-dark text-[28px] md:text-[36px] leading-[1.2] tracking-[-0.5px] m-0">Siap Membantu Proyek Anda</h2>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 lg:gap-12 items-stretch">
      <!-- Kiri: CTA ke modal Get a Quote -->
      <div class="relative overflow-hidden rounded-[6px] bg-brand text-white p-7 sm:p-10 flex flex-col justify-center min-w-0" data-reveal>
        <div class="absolute -right-16 -top-16 size-56 rounded-full bg-white/5"></div>
        <div class="absolute -right-4 bottom-[-72px] size-44 rounded-full bg-white/5"></div>
        <p class="relative font-jakarta font-semibold text-white/70 text-[11px] tracking-[2.4px] uppercase mb-3">KONSULTASI GRATIS</p>
        <h3 class="relative font-playfair font-normal text-[28px] md:text-[36px] leading-[1.2] tracking-[-0.5px] mb-4">Ceritakan kebutuhan jendela Anda</h3>
        <p class="relative font-jakarta text-white/80 text-[15px] leading-[1.6] mb-8 max-w-[440px]">Isi form singkat — jenis tirai, ukuran lebar &amp; tinggi, dan jumlahnya. Tim kami menyiapkan penawaran lalu melanjutkan obrolan lewat WhatsApp.</p>
        <div class="relative flex flex-col sm:flex-row gap-3">
          <button type="button" data-open-quote
                  class="inline-flex items-center justify-center gap-2 bg-white text-brand font-jakarta font-semibold text-[12px] tracking-[1.8px] uppercase px-7 py-[15px] rounded-[2px] hover:bg-cream transition-colors group">
            MINTA PENAWARAN <span class="transition-transform duration-300 group-hover:translate-x-1" aria-hidden="true">→</span>
          </button>
          <a href="<?= WA_URL ?>?text=Halo+Wintom%2C+saya+ingin+konsultasi" target="_blank" rel="noopener"
             class="inline-flex items-center justify-center border border-white/40 hover:border-white text-white font-jakarta font-semibold text-[12px] tracking-[1.8px] uppercase px-7 py-[14px] rounded-[2px] transition-colors">
            CHAT VIA WHATSAPP
          </a>
        </div>
      </div>

      <!-- Kanan: peta + detail kontak -->
      <div class="min-w-0" data-reveal style="--d:120ms"><?php include __DIR__ . '/includes/contact-info.php'; ?></div>
    </div>
  </div>
</section>

<?php include_once __DIR__ . '/includes/footer.php'; ?>
