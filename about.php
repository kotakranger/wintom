<?php
// about.php — Tentang Kami & Kontak Wintom Curtain
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/db.php';

$page_title = 'Tentang Kami & Kontak';
$active_nav  = 'about';
if (is_legacy_url()) redirect_clean('/tentang');
require_once __DIR__ . '/includes/seo.php';
$meta_description = 'Kenali Wintom Curtain, showroom gorden dan blind di Ruko Soho Ebony, PIK, Jakarta Utara. Jam buka Senin–Sabtu 09.00–18.00. Hubungi kami via WhatsApp untuk survei gratis.';
$canonical_route  = '/tentang';
$schema           = [seo_local_business(), seo_breadcrumbs(['Beranda' => '/', 'Tentang Kami' => null])];

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
      <?php if (copy_v2()): ?>
      <p class="wt-in font-jakarta text-muted text-[16px] md:text-[17px] leading-[1.6] mb-5" style="--d:280ms">
        Dengan pengalaman lebih dari 20 tahun di industri gorden dan roller blind, WINTOM Intelligent Solusi hadir sebagai penyedia solusi tirai untuk hunian, apartemen, perkantoran, hotel, hingga berbagai ruang komersial. Kami menghadirkan beragam pilihan produk berkualitas yang mengutamakan fungsi, kenyamanan, serta keselarasan dengan desain interior.
      </p>
      <p class="wt-in font-jakarta text-muted text-[16px] md:text-[17px] leading-[1.6] mb-5" style="--d:360ms">
        Didukung fasilitas produksi sendiri di Tangerang, kami menyediakan berbagai koleksi gorden, vitrase, roller blind, zebra blind, roman blind, vertical blind, hingga sistem tirai motorized. Tersedia pilihan material blackout, semi blackout, sunscreen, dimout, linen, serta beragam bahan lainnya yang dapat disesuaikan dengan kebutuhan dan karakter setiap ruangan.
      </p>
      <p class="wt-in font-jakarta text-muted text-[16px] md:text-[17px] leading-[1.6]" style="--d:440ms">
        Melalui showroom di PIK 2, kami memberikan layanan menyeluruh mulai dari konsultasi, pemilihan bahan, pengukuran, produksi custom, hingga pemasangan profesional. Dengan mengutamakan kualitas material, ketelitian pengerjaan, dan kepuasan pelanggan, layanan kami dilengkapi garansi sesuai ketentuan serta dukungan purna jual.
      </p>
      <?php else: ?>
      <p class="wt-in font-jakarta text-muted text-[16px] md:text-[17px] leading-[1.6] mb-5" style="--d:280ms">
        WINTOM Intelligent Solusi menghadirkan curtain dan blinds beserta sistem motorized, rel, serta aksesori pendukung. Koleksinya mencakup roller, zebra, roman, dan vertical blinds, serta motorized curtain track.
      </p>
      <p class="wt-in font-jakarta text-muted text-[16px] md:text-[17px] leading-[1.6] mb-5" style="--d:360ms">
        Kami melihat tampilan, komponen, dan kebutuhan pemasangan sebagai bagian dari satu keputusan. Karena itu, pemilihan produk dimulai dengan memahami ruang, lalu membahas seri, ukuran, serta sistem pengoperasian yang sesuai.
      </p>
      <p class="wt-in font-jakarta text-muted text-[16px] md:text-[17px] leading-[1.6]" style="--d:440ms">
        Dari sentuhan kain hingga detail rel, WINTOM membantu Anda melengkapi jendela agar ruang terasa lebih nyaman digunakan.
      </p>
      <?php endif; ?>
    </div>
    <!-- Foto perusahaan: satu card, 3 foto bergantian otomatis (fade) -->
    <?php
    // DUMMY — ganti dengan foto perusahaan asli: simpan di assets/images/company/ lalu isi 'src' dengan path-nya (mis. '/assets/images/company/showroom.jpg').
    $company_photos = [
        ['src' => 'https://images.unsplash.com/photo-1618221195710-dd6b41faaea6', 'alt' => 'Wintom Curtain — suasana showroom'],
        ['src' => 'https://images.unsplash.com/photo-1505691938895-1758d7feb511', 'alt' => 'Wintom Curtain — koleksi gorden dan blinds'],
        ['src' => 'https://images.unsplash.com/photo-1513694203232-719a280e022f', 'alt' => 'Wintom Curtain — hasil pemasangan'],
    ];
    ?>
    <div id="company-photos" class="wt-in relative aspect-[4/3] rounded-[6px] overflow-hidden bg-border" style="--d:300ms"
         role="img" aria-label="Foto perusahaan Wintom Curtain">
      <div class="wt-zoom absolute inset-0">
        <?php foreach ($company_photos as $i => $ph): ?>
        <img <?= img_attrs($ph['src'], '(min-width:1024px) 50vw, 100vw') ?>
             alt="<?= htmlspecialchars($ph['alt']) ?>" <?= $i === 0 ? '' : 'loading="lazy"' ?>
             class="absolute inset-0 w-full h-full object-cover transition-opacity duration-1000 <?= $i === 0 ? 'opacity-100' : 'opacity-0' ?>" data-cp-slide />
        <?php endforeach; ?>
      </div>
      <div class="absolute inset-0 bg-gradient-to-br from-transparent to-[rgba(34,34,32,0.2)] pointer-events-none"></div>
    </div>
    <script>
    (function () {
      const box = document.getElementById('company-photos');
      const slides = Array.from(box.querySelectorAll('[data-cp-slide]'));
      if (slides.length < 2 || (window.matchMedia && matchMedia('(prefers-reduced-motion: reduce)').matches)) return;
      let cur = 0, visible = true;
      if ('IntersectionObserver' in window) new IntersectionObserver(es => { visible = es[0].isIntersecting; }, { threshold: 0.2 }).observe(box);
      setInterval(function () {
        if (!visible || document.hidden) return;
        slides[cur].classList.replace('opacity-100', 'opacity-0');
        cur = (cur + 1) % slides.length;
        slides[cur].classList.replace('opacity-0', 'opacity-100');
      }, 4500);   // ganti foto tiap 4,5 detik, fade 1 detik
    })();
    </script>
  </div>
</section>

<!-- ===== VISI & MISI ===== -->
<section class="bg-cream py-14 md:py-[80px] border-t border-border">
  <div class="max-w-[1100px] mx-auto px-4 sm:px-6 lg:px-12 grid grid-cols-1 md:grid-cols-2 gap-10 md:gap-12">
    <div data-reveal>
      <p class="font-jakarta font-semibold text-brand text-[12px] tracking-[2.64px] uppercase mb-3">VISI</p>
      <p class="font-playfair font-normal text-dark text-[22px] sm:text-[26px] leading-[1.35] m-0"><?= copy_v2() ? 'Menjadi perusahaan gorden dan blinds terkemuka dan terpercaya di Indonesia dengan mengutamakan kualitas, inovasi, serta pelayanan profesional untuk menghadirkan kenyamanan dan nilai estetika pada setiap ruangan.' : 'Menjadi pilihan yang dipercaya untuk solusi tirai dan sistem jendela yang selaras dengan kebutuhan ruang.' ?></p>
    </div>
    <div data-reveal style="--d:120ms">
      <p class="font-jakarta font-semibold text-brand text-[12px] tracking-[2.64px] uppercase mb-3">MISI</p>
      <ul class="list-none p-0 m-0 space-y-4">
        <?php foreach (copy_v2() ? [
          'Menyediakan beragam pilihan gorden dan blinds berkualitas dengan material, desain, serta ukuran yang sesuai kebutuhan hunian maupun ruang komersial.',
          'Menjaga kualitas produk melalui proses produksi yang terkontrol, pemilihan material berkualitas, dan ketelitian dalam setiap tahap pengerjaan.',
          'Memberikan layanan profesional dan menyeluruh, mulai dari konsultasi, pengukuran, produksi custom, hingga pemasangan serta layanan purna jual.',
          'Mengembangkan inovasi produk dan teknologi tirai modern yang mengutamakan kenyamanan, fungsi, dan estetika.',
          'Membangun kepercayaan dan kepuasan pelanggan melalui pelayanan yang responsif, hasil pengerjaan berkualitas, serta komitmen jangka panjang.',
        ] : [
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

<!-- ===== MENGAPA MEMILIH WINTOM (komponen bersama dengan Beranda) ===== -->
<?php include __DIR__ . '/includes/why-choose.php'; ?>

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
        <h3 class="relative font-playfair font-normal text-[28px] md:text-[36px] leading-[1.2] tracking-[-0.5px] mb-4"><?= copy_v2() ? 'Ceritakan Kebutuhan Gorden &amp; Blinds Anda' : 'Ceritakan kebutuhan jendela Anda' ?></h3>
        <p class="relative font-jakarta text-white/80 text-[15px] leading-[1.6] mb-8 max-w-[440px]"><?= copy_v2() ? 'Isi formulir singkat sesuai kebutuhan, mulai dari jenis produk, perkiraan ukuran, hingga jumlah pesanan. Tim kami akan membantu menyiapkan penawaran harga dan menghubungi Anda melalui WhatsApp untuk konsultasi lebih lanjut.' : 'Isi form singkat — jenis tirai, ukuran lebar &amp; tinggi, dan jumlahnya. Tim kami menyiapkan penawaran lalu melanjutkan obrolan lewat WhatsApp.' ?></p>
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
