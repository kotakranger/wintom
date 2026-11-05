<?php
// includes/order-steps.php — Section "Cara Pesan" (sumber tunggal: homepage, detail produk, about).
// Opsional sebelum include: $order_steps_bg (kelas background section).
$order_steps = [
    ['num' => '01', 'title' => 'KONSULTASI', 'desc' => 'Ceritakan kebutuhan dan ukuran jendela Anda lewat WhatsApp atau form penawaran.',
     'img' => 'https://images.unsplash.com/photo-1556742049-0cfed4f6a45d?w=600&q=80&fit=crop', 'alt' => 'Konsultasi pilihan bahan gorden'],
    ['num' => '02', 'title' => 'UKUR & PILIH BAHAN', 'desc' => 'Tim kami mengukur dan membantu memilih bahan — di lokasi Anda atau di showroom PIK.',
     'img' => 'https://images.unsplash.com/photo-1581578731548-c64695cc6952?w=600&q=80&fit=crop', 'alt' => 'Pengukuran jendela'],
    ['num' => '03', 'title' => 'PENAWARAN', 'desc' => 'Terima penawaran harga yang jelas dan rinci sebelum produksi dimulai.',
     'img' => 'https://images.unsplash.com/photo-1554224155-6726b3ff858f?w=600&q=80&fit=crop', 'alt' => 'Penawaran harga'],
    ['num' => '04', 'title' => 'PRODUKSI & PEMASANGAN', 'desc' => 'Gorden diproduksi, lalu dipasang rapi oleh tim kami di lokasi Anda.',
     'img' => 'https://images.unsplash.com/photo-1586953208448-b95a79798f07?w=600&q=80&fit=crop', 'alt' => 'Pemasangan gorden'],
];

// Copy baru (flag FEATURE_COPY_V2) hanya untuk beranda: halaman yang meng-include harus menyetel $order_steps_v2 = copy_v2().
// Halaman lain (detail produk, tentang) tetap memakai teks di atas.
if (!empty($order_steps_v2)) {
    foreach ([
        ['Konsultasi Kebutuhan', 'Sampaikan kebutuhan gorden atau blinds melalui WhatsApp, website, atau kunjungan langsung ke showroom. Kami siap membantu menentukan pilihan yang sesuai dengan ruangan Anda.'],
        ['Survei, Ukur &amp; Pilih Bahan', 'Pilih material, warna, dan model sesuai kebutuhan. Tersedia layanan survei dan pengukuran langsung di lokasi, serta beragam koleksi bahan yang dapat dilihat di showroom.'],
        ['Penawaran &amp; Konfirmasi', 'Dapatkan penawaran harga berdasarkan pilihan produk, material, dan ukuran yang telah disepakati. Seluruh rincian pesanan dikonfirmasi sebelum masuk proses produksi.'],
        ['Produksi &amp; Pemasangan', 'Pesanan diproduksi sesuai spesifikasi yang telah disetujui, kemudian dipasang oleh tim berpengalaman dengan memperhatikan ketepatan ukuran, kerapian, dan fungsi produk.'],
    ] as $k => [$st, $sd]) { $order_steps[$k]['title'] = $st; $order_steps[$k]['desc'] = $sd; }
}
$order_steps_v2 = false;   // jangan bocor ke include berikutnya di halaman yang sama
?>
<section class="<?= $order_steps_bg ?? 'bg-[#faf8f5]' ?> py-16 md:py-24">
  <div class="max-w-[1280px] mx-auto px-4 sm:px-6 lg:px-12 flex flex-col gap-10 md:gap-14">

    <div class="flex flex-col gap-3 items-center w-full" data-reveal>
      <h2 class="font-playfair font-normal text-dark text-[28px] md:text-[36px] tracking-[-0.5px] text-center leading-[1.2] m-0">
        Cara Pesan
      </h2>
      <div class="bg-brand h-[2px] w-12"></div>
    </div>

    <ol class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-x-6 gap-y-10 list-none p-0 m-0">
      <?php foreach ($order_steps as $i => $step): ?>
      <li class="min-w-0 group" data-reveal style="--d:<?= $i * 100 ?>ms">
        <div class="bg-[#ece8e1] aspect-[4/3] overflow-hidden rounded-[2px] w-full mb-4">
          <img <?= img_attrs($step['img'], '(min-width:1024px) 25vw, (min-width:640px) 50vw, 100vw', [480, 800]) ?> alt="<?= htmlspecialchars($step['alt']) ?>" loading="lazy"
               class="w-full h-full object-cover transition-transform duration-700 ease-out group-hover:scale-[1.06]" />
        </div>
        <span class="block font-playfair font-normal text-dark text-[24px] leading-[32px]"><?= $step['num'] ?></span>
        <h3 class="font-jakarta font-bold text-dark text-[12px] tracking-[1.8px] uppercase leading-[16px] mt-1 mb-2"><?= $step['title'] ?></h3>
        <p class="font-jakarta font-normal text-muted text-[14px] leading-[1.5] m-0"><?= $step['desc'] ?></p>
      </li>
      <?php endforeach; ?>
    </ol>

  </div>
</section>
