<?php
// about.php — Tentang Kami & Kontak Wintom Curtain
require_once __DIR__ . '/includes/config.php';

$page_title = 'Tentang Kami & Kontak';
$active_nav  = 'about';

include_once __DIR__ . '/includes/header.php';
?>

<!-- ===== BRAND STORY HERO ===== -->
<section class="bg-cream-dark border-b border-border">
  <div class="max-w-[1280px] mx-auto px-12 py-[80px] grid grid-cols-1 lg:grid-cols-2 gap-16 items-center">
    <!-- Text -->
    <div>
      <p class="font-jakarta font-semibold text-brand text-[11px] tracking-[2.64px] uppercase leading-[16.5px] mb-5">TENTANG WINTOM</p>
      <h1 class="font-playfair font-bold text-dark text-[48px] leading-[56px] tracking-[-1px] mb-6">
        Atelier Gorden Arsitektural untuk Hunian &amp; Komersial Premium
      </h1>
      <p class="font-jakarta text-muted text-[16px] leading-[28px] mb-6">
        Wintom Curtain lahir dari keyakinan bahwa sebuah tirai bukan sekadar penutup jendela — ia adalah elemen arsitektural yang mendefinisikan karakter sebuah ruang. Sejak berdiri, kami mengkhususkan diri pada jasa custom curtain, roller blind, dan motorized smart system untuk hunian premium dan properti komersial di area Jabodetabek.
      </p>
      <p class="font-jakarta text-muted text-[16px] leading-[28px]">
        Setiap meter kain yang kami jahit melewati kontrol kualitas ketat — mulai dari seleksi material impor, pola jahitan presisi di atelier kami, hingga pemasangan rapi yang dilakukan langsung oleh tim teknisi berpengalaman kami di lokasi Anda.
      </p>
    </div>
    <!-- Image -->
    <div class="relative aspect-[4/3] rounded-[6px] overflow-hidden bg-border">
      <img src="https://images.unsplash.com/photo-1618221195710-dd6b41faaea6?w=1400&q=85"
           alt="Wintom Curtain Atelier"
           class="w-full h-full object-cover" />
      <div class="absolute inset-0 bg-gradient-to-br from-transparent to-[rgba(34,34,32,0.2)]"></div>
    </div>
  </div>
</section>

<!-- ===== BRAND VALUES ===== -->
<section class="bg-cream py-[80px]">
  <div class="max-w-[1280px] mx-auto px-12">
    <div class="text-center mb-14">
      <p class="font-jakarta font-semibold text-brand text-[11px] tracking-[2.64px] uppercase mb-3">MENGAPA WINTOM</p>
      <h2 class="font-playfair font-bold text-dark text-[36px] leading-[44px] tracking-[-0.8px]">Standar Layanan yang Kami Junjung</h2>
    </div>
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-px bg-border rounded-[6px] overflow-hidden">
      <?php
      $values = [
        ['icon' => '<path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/>',
         'title' => 'Survei Gratis Jabodetabek',
         'desc'  => 'Tim kami datang ke lokasi Anda untuk pengukuran presisi, membawa swatch kain fisik pilihan, tanpa biaya survei apapun.'],
        ['icon' => '<path stroke-linecap="round" stroke-linejoin="round" d="M9.813 15.904 9 18.75l-.813-2.846a4.5 4.5 0 0 0-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 0 0 3.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 0 0 3.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 0 0-3.09 3.09Z"/>',
         'title' => 'Custom Dimensi Presisi',
         'desc'  => 'Setiap produk dibuat custom sesuai dimensi jendela Anda — bukan ukuran standar toko. Dijahit di atelier kami sendiri.'],
        ['icon' => '<path stroke-linecap="round" stroke-linejoin="round" d="M11.42 15.17 17.25 21A2.652 2.652 0 0 0 21 17.25l-5.877-5.877M11.42 15.17l2.496-3.03c.317-.384.74-.626 1.208-.766M11.42 15.17l-4.655 5.653a2.548 2.548 0 1 1-3.586-3.586l6.837-5.63m5.108-.233c.55-.164 1.163-.188 1.743-.14a4.5 4.5 0 0 0 4.486-6.336l-3.276 3.277a3.004 3.004 0 0 1-2.25-2.25l3.276-3.276a4.5 4.5 0 0 0-6.336 4.486c.091 1.076-.071 2.264-.904 2.95l-.102.085m-1.745 1.437L5.909 7.5H4.5L2.25 3.75l1.5-1.5L7.5 4.5v1.409l4.26 4.26m-1.745 1.437 1.745-1.437m6.615 8.206L15.75 15.75M4.867 19.125h.008v.008h-.008v-.008Z"/>',
         'title' => 'Material Impor Premium',
         'desc'  => 'Pilihan kain dari Belgian Linen, Swiss Voile, hingga Sunscreen PVC impor — dikurasi ketat untuk daya tahan dan estetika terbaik.'],
        ['icon' => '<path stroke-linecap="round" stroke-linejoin="round" d="m3.75 13.5 10.5-11.25L12 10.5h8.25L9.75 21.75 12 13.5H3.75Z"/>',
         'title' => 'Motorized Smart System',
         'desc'  => 'Integrasi motorisasi Somfy & Tuya dengan Apple HomeKit dan Google Home untuk kenyamanan hidup modern yang sesungguhnya.'],
        ['icon' => '<path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/>',
         'title' => 'Tepat Waktu & Rapi',
         'desc'  => 'Jadwal pemasangan yang terstruktur. Tim instalasi kami datang tepat waktu, bekerja rapi, dan meninggalkan area bersih.'],
        ['icon' => '<path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75m-3-7.036A11.959 11.959 0 0 1 3.598 6 11.99 11.99 0 0 0 3 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285Z"/>',
         'title' => 'Garansi Instalasi & Kain',
         'desc'  => 'Garansi pemasangan 1 tahun dan jaminan kualitas kain. Kami bertanggung jawab penuh terhadap setiap karya yang kami hasilkan.'],
      ];
      foreach ($values as $v): ?>
      <div class="bg-cream p-8 hover:bg-cream-dark transition-colors duration-200">
        <div class="size-10 rounded-full bg-brand/10 flex items-center justify-center mb-5">
          <svg class="size-5 text-brand" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
            <?= $v['icon'] ?>
          </svg>
        </div>
        <h3 class="font-jakarta font-semibold text-dark text-[16px] leading-[22px] mb-3"><?= $v['title'] ?></h3>
        <p class="font-jakarta text-muted text-[14px] leading-[22px]"><?= $v['desc'] ?></p>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- ===== 4-STEP SERVICE PROTOCOL ===== -->
<section class="bg-cream-dark border-t border-border py-[80px]">
  <div class="max-w-[1280px] mx-auto px-12">
    <div class="text-center mb-14">
      <p class="font-jakarta font-semibold text-brand text-[11px] tracking-[2.64px] uppercase mb-3">PROTOKOL LAYANAN</p>
      <h2 class="font-playfair font-bold text-dark text-[36px] leading-[44px] tracking-[-0.8px]">Dari Konsultasi Hingga Pemasangan</h2>
    </div>
    <div class="grid grid-cols-1 md:grid-cols-4 gap-8 relative">
      <!-- Connector line (desktop) -->
      <div class="hidden md:block absolute top-[28px] left-[calc(12.5%+20px)] right-[calc(12.5%+20px)] h-px bg-border z-0"></div>

      <?php
      $steps = [
        ['num'=>'01','title'=>'Konsultasi Awal','desc'=>'Hubungi kami via WhatsApp atau telepon. Ceritakan kebutuhan, gaya interior, dan estimasi dimensi ruang Anda.'],
        ['num'=>'02','title'=>'Survei & Swatch','desc'=>'Tim kami datang ke lokasi — mengukur jendela secara presisi dan membawa koleksi swatch kain fisik untuk Anda pilih.'],
        ['num'=>'03','title'=>'Penjahitan Atelier','desc'=>'Kain yang Anda pilih dijahit custom di atelier kami oleh penjahit berpengalaman, sesuai spesifikasi dan model jahitan yang disepakati.'],
        ['num'=>'04','title'=>'Pemasangan & Garansi','desc'=>'Instalasi profesional oleh tim teknisi kami, rapi dan tepat waktu. Disertai garansi pemasangan dan layanan purnajual.'],
      ];
      foreach ($steps as $step): ?>
      <div class="flex flex-col items-center text-center relative z-10">
        <div class="size-14 rounded-full bg-brand flex items-center justify-center mb-5 shadow-md">
          <span class="font-playfair font-bold text-white text-[18px]"><?= $step['num'] ?></span>
        </div>
        <h3 class="font-jakarta font-semibold text-dark text-[15px] leading-[20px] mb-3"><?= $step['title'] ?></h3>
        <p class="font-jakarta text-muted text-[13px] leading-[20px]"><?= $step['desc'] ?></p>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- ===== CONTACT SECTION ===== -->
<section id="contact" class="bg-cream py-[80px]">
  <div class="max-w-[1280px] mx-auto px-12">
    <div class="text-center mb-14">
      <p class="font-jakarta font-semibold text-brand text-[11px] tracking-[2.64px] uppercase mb-3">HUBUNGI KAMI</p>
      <h2 class="font-playfair font-bold text-dark text-[36px] leading-[44px] tracking-[-0.8px]">Siap Membantu Proyek Anda</h2>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-12 items-start">
      <!-- Contact Info -->
      <div class="space-y-7">
        <!-- WhatsApp -->
        <a href="<?= WA_URL ?>?text=Halo+Wintom%2C+saya+ingin+konsultasi" target="_blank" rel="noopener"
           class="flex items-start gap-5 p-6 bg-cream-dark border border-border rounded-[6px] hover:border-brand/30 hover:shadow-sm transition-all duration-200 group">
          <div class="size-12 rounded-full bg-[#25d366]/15 flex items-center justify-center shrink-0 mt-0.5">
            <svg class="size-5 text-[#25d366]" viewBox="0 0 20 20" fill="currentColor"><path d="M10 1.667A8.333 8.333 0 0 0 2.92 13.742L1.667 18.333l4.725-1.237A8.333 8.333 0 1 0 10 1.667Z"/><path d="M7.5 6.667c.167-.417.625-.834 1.25-.834.5 0 .833.25 1.042.5l1.041 1.667c.208.417.042.833-.208 1.083l-.417.417c.417.75 1.125 1.458 1.875 1.875l.417-.417c.25-.25.667-.417 1.083-.208l1.667 1.041c.25.209.5.542.5 1.042 0 .625-.417 1.083-.833 1.25C13.75 14.167 11.25 14.583 8.333 11.667 5.417 8.75 5.833 6.25 6.25 5.417Z" fill="white"/></svg>
          </div>
          <div>
            <p class="font-jakarta font-semibold text-muted text-[10px] tracking-[1.5px] uppercase mb-1">WHATSAPP — KONSULTASI LANGSUNG</p>
            <p class="font-jakarta font-semibold text-dark text-[17px] group-hover:text-[#25d366] transition-colors"><?= SITE_PHONE ?></p>
            <p class="font-jakarta text-muted text-[13px] mt-1">Respon cepat hari kerja &amp; Sabtu</p>
          </div>
        </a>

        <!-- Phone -->
        <a href="tel:<?= SITE_PHONE_RAW ?>"
           class="flex items-start gap-5 p-6 bg-cream-dark border border-border rounded-[6px] hover:border-brand/30 hover:shadow-sm transition-all duration-200 group">
          <div class="size-12 rounded-full bg-brand/10 flex items-center justify-center shrink-0 mt-0.5">
            <svg class="size-5 text-brand" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 6.75c0 8.284 6.716 15 15 15h2.25a2.25 2.25 0 0 0 2.25-2.25v-1.372c0-.516-.351-.966-.852-1.091l-4.423-1.106c-.44-.11-.902.055-1.173.417l-.97 1.293c-.282.376-.769.542-1.21.38a12.035 12.035 0 0 1-7.143-7.143c-.162-.441.004-.928.38-1.21l1.293-.97c.363-.271.527-.734.417-1.173L6.963 3.102a1.125 1.125 0 0 0-1.091-.852H4.5A2.25 2.25 0 0 0 2.25 4.5v2.25Z"/></svg>
          </div>
          <div>
            <p class="font-jakarta font-semibold text-muted text-[10px] tracking-[1.5px] uppercase mb-1">TELEPON</p>
            <p class="font-jakarta font-semibold text-dark text-[17px] group-hover:text-brand transition-colors"><?= SITE_PHONE ?></p>
            <p class="font-jakarta text-muted text-[13px] mt-1"><?= SITE_HOURS ?></p>
          </div>
        </a>

        <!-- Email -->
        <a href="mailto:<?= SITE_EMAIL ?>"
           class="flex items-start gap-5 p-6 bg-cream-dark border border-border rounded-[6px] hover:border-brand/30 hover:shadow-sm transition-all duration-200 group">
          <div class="size-12 rounded-full bg-brand/10 flex items-center justify-center shrink-0 mt-0.5">
            <svg class="size-5 text-brand" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75"><path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 0 1-2.25 2.25h-15a2.25 2.25 0 0 1-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25m19.5 0v.243a2.25 2.25 0 0 1-1.07 1.916l-7.5 4.615a2.25 2.25 0 0 1-2.36 0L3.32 8.91a2.25 2.25 0 0 1-1.07-1.916V6.75"/></svg>
          </div>
          <div>
            <p class="font-jakarta font-semibold text-muted text-[10px] tracking-[1.5px] uppercase mb-1">EMAIL</p>
            <p class="font-jakarta font-semibold text-dark text-[17px] group-hover:text-brand transition-colors"><?= SITE_EMAIL ?></p>
            <p class="font-jakarta text-muted text-[13px] mt-1">Balasan dalam 1×24 jam kerja</p>
          </div>
        </a>

        <!-- Showroom -->
        <div class="flex items-start gap-5 p-6 bg-cream-dark border border-border rounded-[6px]">
          <div class="size-12 rounded-full bg-brand/10 flex items-center justify-center shrink-0 mt-0.5">
            <svg class="size-5 text-brand" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75"><path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z"/><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1 1 15 0Z"/></svg>
          </div>
          <div>
            <p class="font-jakarta font-semibold text-muted text-[10px] tracking-[1.5px] uppercase mb-1">SHOWROOM</p>
            <p class="font-jakarta font-semibold text-dark text-[17px]"><?= SITE_ADDRESS ?></p>
            <p class="font-jakarta text-muted text-[13px] mt-1"><?= SITE_HOURS ?></p>
          </div>
        </div>
      </div>

      <!-- Map Embed Placeholder + Direct CTA -->
      <div class="flex flex-col gap-6">
        <div class="aspect-[4/3] rounded-[6px] overflow-hidden bg-border border border-border">
          <iframe
            src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d15862.234!2d106.73!3d-6.11!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x2e6a0!2sPIK+2+Jakarta!5e0!3m2!1sen!2sid!4v1234567890"
            width="100%" height="100%" style="border:0;" allowfullscreen="" loading="lazy"
            referrerpolicy="no-referrer-when-downgrade"
            title="Lokasi Wintom Curtain Showroom">
          </iframe>
        </div>

        <div class="p-7 bg-[#741122] rounded-[6px] text-center">
          <p class="font-playfair font-bold text-white text-[22px] leading-[30px] mb-2">Konsultasi Gratis Sekarang</p>
          <p class="font-jakarta text-white/70 text-[13px] leading-[20px] mb-5">Ceritakan kebutuhan Anda. Tim kami siap memberikan rekomendasi terbaik.</p>
          <a href="<?= WA_URL ?>?text=Halo+Wintom%2C+saya+ingin+konsultasi+gorden+untuk+proyek+saya"
             target="_blank" rel="noopener"
             class="inline-flex items-center gap-2 bg-white text-brand font-semibold text-[12px] tracking-[1.8px] uppercase px-7 py-[13px] rounded-[2px] hover:bg-cream transition-colors duration-200">
            CHAT WHATSAPP SEKARANG
          </a>
        </div>
      </div>
    </div>
  </div>
</section>

<?php include_once __DIR__ . '/includes/footer.php'; ?>
