<?php
// includes/why-choose.php — Section "Mengapa Memilih Wintom" (dipakai di Beranda & Tentang Kami agar konsisten).
// Teks mengikuti flag copy_v2().
?>
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
