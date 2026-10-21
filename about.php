<?php
// about.php — Tentang Kami & Kontak Wintom Curtain
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/auth.php';

csrf_token(); // mulai sesi sebelum header.php mengirim output, agar cookie CSRF terkirim
$inquiry_categories = get_db()->query('SELECT id, name FROM categories ORDER BY sort_order ASC, name ASC')->fetchAll();

$page_title = 'Tentang Kami & Kontak';
$active_nav  = 'about';

include_once __DIR__ . '/includes/header.php';
?>

<!-- ===== BRAND STORY HERO ===== -->
<section class="bg-cream-dark border-b border-border">
  <div class="max-w-[1280px] mx-auto px-12 py-[80px] grid grid-cols-1 lg:grid-cols-2 gap-16 items-center">
    <!-- Text -->
    <div>
      <p class="font-jakarta font-semibold text-brand text-[12px] tracking-[2.64px] uppercase leading-[16.5px] mb-5">TENTANG KAMI</p>
      <h1 class="font-playfair font-bold text-dark text-[40px] md:text-[48px] leading-[1.15] tracking-[-1px] mb-6">
        WINTOM Intelligent Solusi
      </h1>
      <p class="font-jakarta text-muted text-[17px] leading-[1.6] mb-5">
        WINTOM Intelligent Solusi menghadirkan curtain dan blinds beserta sistem motorized, rel, serta aksesori pendukung. Koleksinya mencakup roller, zebra, roman, dan vertical blinds, serta motorized curtain track.
      </p>
      <p class="font-jakarta text-muted text-[17px] leading-[1.6] mb-5">
        Kami melihat tampilan, komponen, dan kebutuhan pemasangan sebagai bagian dari satu keputusan. Karena itu, pemilihan produk dimulai dengan memahami ruang, lalu membahas seri, ukuran, serta sistem pengoperasian yang sesuai.
      </p>
      <p class="font-jakarta text-muted text-[17px] leading-[1.6]">
        Dari sentuhan kain hingga detail rel, WINTOM membantu Anda melengkapi jendela agar ruang terasa lebih nyaman digunakan.
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
      <p class="font-jakarta font-semibold text-brand text-[12px] tracking-[2.64px] uppercase mb-3">MENGAPA MEMILIH WINTOM?</p>
      <h2 class="font-playfair font-bold text-dark text-[32px] md:text-[36px] leading-[1.2] tracking-[-0.8px] max-w-[760px] mx-auto mb-4">Gorden Custom Premium: Praktis Secara Online, Presisi Secara Offline.</h2>
      <p class="font-jakarta text-muted text-[17px] leading-[1.6] max-w-[760px] mx-auto">Kemudahan konsultasi dan pilih model gorden dari genggaman, dipadukan dengan jaminan kualitas bahan grade A, survei bawakan sampel ke lokasi, hingga instalasi rapi setara hotel bintang lima.</p>
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
      foreach ($values as $v): ?>
      <div class="bg-cream p-8 hover:bg-cream-dark transition-colors duration-200">
        <div class="size-10 rounded-full bg-brand/10 flex items-center justify-center mb-5">
          <svg class="size-5 text-brand" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
            <?= $v['icon'] ?>
          </svg>
        </div>
        <h3 class="font-jakarta font-semibold text-dark text-[18px] leading-[1.35] mb-3"><?= $v['title'] ?></h3>
        <p class="font-jakarta text-muted text-[16px] leading-[1.6]"><?= $v['desc'] ?></p>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- ===== 4-STEP SERVICE PROTOCOL ===== -->
<section class="bg-cream-dark border-t border-border py-[80px]">
  <div class="max-w-[1280px] mx-auto px-12">
    <div class="text-center mb-14">
      <p class="font-jakarta font-semibold text-brand text-[11px] tracking-[2.64px] uppercase mb-3">CARA PESAN</p>
      <h2 class="font-playfair font-bold text-dark text-[36px] leading-[44px] tracking-[-0.8px]">Dari Konsultasi Hingga Pemasangan</h2>
    </div>
    <div class="grid grid-cols-1 md:grid-cols-4 gap-8 relative">
      <!-- Connector line (desktop) -->
      <div class="hidden md:block absolute top-[28px] left-[calc(12.5%+20px)] right-[calc(12.5%+20px)] h-px bg-border z-0"></div>

      <?php
      $steps = [
        ['num'=>'01','title'=>'Konsultasi','desc'=>'Chat WhatsApp dan ceritakan kebutuhan serta ukuran jendela Anda.'],
        ['num'=>'02','title'=>'Ukur & Pilih Bahan','desc'=>'Untuk custom: ukur jendela dan pilih bahan bersama tim kami, atau lihat langsung di showroom PIK.'],
        ['num'=>'03','title'=>'Penawaran','desc'=>'Anda menerima penawaran yang jelas sebelum produksi dimulai.'],
        ['num'=>'04','title'=>'Produksi & Pemasangan','desc'=>'Produksi atau pengiriman, lalu pemasangan di lokasi Anda.'],
      ];
      foreach ($steps as $step): ?>
      <div class="flex flex-col items-center text-center relative z-10">
        <div class="size-14 rounded-full bg-brand flex items-center justify-center mb-5 shadow-md">
          <span class="font-playfair font-bold text-white text-[18px]"><?= $step['num'] ?></span>
        </div>
        <h3 class="font-jakarta font-semibold text-dark text-[15px] leading-[20px] mb-3"><?= $step['title'] ?></h3>
        <p class="font-jakarta text-muted text-[15px] leading-[1.55]"><?= $step['desc'] ?></p>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- ===== VISI & MISI ===== -->
<section class="bg-cream py-[80px] border-t border-border">
  <div class="max-w-[1100px] mx-auto px-6 md:px-12 grid grid-cols-1 md:grid-cols-2 gap-12">
    <div>
      <p class="font-jakarta font-semibold text-brand text-[12px] tracking-[2.64px] uppercase mb-3">VISI</p>
      <p class="font-playfair text-dark text-[26px] leading-[1.35] m-0">Menjadi pilihan yang dipercaya untuk solusi tirai dan sistem jendela yang selaras dengan kebutuhan ruang.</p>
    </div>
    <div>
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

<!-- ===== CONTACT SECTION ===== -->
<section id="contact" class="bg-cream py-[80px]">
  <div class="max-w-[1280px] mx-auto px-12">
    <div class="text-center mb-14">
      <p class="font-jakarta font-semibold text-brand text-[11px] tracking-[2.64px] uppercase mb-3">HUBUNGI KAMI</p>
      <h2 class="font-playfair font-bold text-dark text-[36px] leading-[44px] tracking-[-0.8px]">Siap Membantu Proyek Anda</h2>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 lg:gap-12 items-start">
      <!-- Kiri: form inquiry -->
      <div>
        <!-- Inquiry form -->
        <form id="inquiry-form" action="<?= BASE_URL ?>/inquiry.php" method="POST" novalidate
              class="relative p-7 bg-white border border-border rounded-[6px] shadow-sm">
          <h3 class="font-playfair font-bold text-dark text-[24px] leading-[30px] mb-1">Konsultasi Gratis Sekarang</h3>
          <p class="font-jakarta text-muted text-[13px] leading-[20px] mb-5">Isi form singkat ini. Data tersimpan di tim kami, lalu Anda langsung diarahkan ke WhatsApp.</p>

          <?= csrf_field() ?>
          <!-- Honeypot anti-spam: biarkan kosong -->
          <div class="absolute -left-[9999px]" aria-hidden="true">
            <label>Website <input type="text" name="website" tabindex="-1" autocomplete="off"></label>
          </div>

          <div class="grid grid-cols-1 sm:grid-cols-2 gap-x-4 gap-y-4">
            <div>
              <label for="f-name" class="block font-jakarta font-semibold text-muted text-[10px] tracking-[1.5px] uppercase mb-2">Nama Lengkap <span class="text-brand">*</span></label>
              <input id="f-name" name="full_name" type="text" required maxlength="120" autocomplete="name"
                     class="w-full border border-border rounded-[3px] bg-cream px-4 py-2.5 font-jakarta text-[14px] text-dark focus:outline-none focus:border-brand transition-colors"
                     placeholder="cth. Budi Santoso">
              <p class="field-error hidden mt-1.5 font-jakarta text-[12px] text-red-600" data-for="full_name"></p>
            </div>

            <div>
              <label for="f-wa" class="block font-jakarta font-semibold text-muted text-[10px] tracking-[1.5px] uppercase mb-2">Nomor WhatsApp <span class="text-brand">*</span></label>
              <input id="f-wa" name="whatsapp" type="tel" inputmode="tel" required maxlength="25" autocomplete="tel"
                     class="w-full border border-border rounded-[3px] bg-cream px-4 py-2.5 font-jakarta text-[14px] text-dark focus:outline-none focus:border-brand transition-colors"
                     placeholder="cth. 0812 3456 7890">
              <p class="field-error hidden mt-1.5 font-jakarta text-[12px] text-red-600" data-for="whatsapp"></p>
            </div>

            <div>
              <label for="f-type" class="block font-jakarta font-semibold text-muted text-[10px] tracking-[1.5px] uppercase mb-2">Jenis Curtain <span class="text-brand">*</span></label>
              <select id="f-type" name="category_id" required
                      class="w-full border border-border rounded-[3px] bg-cream px-4 py-2.5 font-jakarta text-[14px] text-dark focus:outline-none focus:border-brand transition-colors">
                <option value="">— Pilih jenis curtain —</option>
                <?php foreach ($inquiry_categories as $c): ?>
                  <option value="<?= (int) $c['id'] ?>"><?= htmlspecialchars($c['name']) ?></option>
                <?php endforeach; ?>
              </select>
              <p class="field-error hidden mt-1.5 font-jakarta text-[12px] text-red-600" data-for="category_id"></p>
            </div>

            <div>
              <label for="f-size" class="block font-jakarta font-semibold text-muted text-[10px] tracking-[1.5px] uppercase mb-2">Estimasi Ukuran <span class="normal-case tracking-normal font-normal">(opsional)</span></label>
              <input id="f-size" name="estimated_size" type="text" maxlength="100"
                     class="w-full border border-border rounded-[3px] bg-cream px-4 py-2.5 font-jakarta text-[14px] text-dark focus:outline-none focus:border-brand transition-colors"
                     placeholder="cth. 2 jendela, masing-masing 150 × 200 cm">
              <p class="field-error hidden mt-1.5 font-jakarta text-[12px] text-red-600" data-for="estimated_size"></p>
            </div>

            <div class="sm:col-span-2">
              <label for="f-notes" class="block font-jakarta font-semibold text-muted text-[10px] tracking-[1.5px] uppercase mb-2">Kebutuhan / Detail Tambahan <span class="normal-case tracking-normal font-normal">(opsional)</span></label>
              <textarea id="f-notes" name="notes" rows="3" maxlength="1000"
                        class="w-full border border-border rounded-[3px] bg-cream px-4 py-2.5 font-jakarta text-[14px] text-dark focus:outline-none focus:border-brand transition-colors resize-y"
                        placeholder="Ceritakan ruangan, warna favorit, atau kebutuhan khusus (blackout, motorized, dll.)"></textarea>
              <p class="field-error hidden mt-1.5 font-jakarta text-[12px] text-red-600" data-for="notes"></p>
            </div>
          </div>

          <div id="inquiry-alert" role="alert" class="hidden mt-5 rounded-[3px] border px-4 py-3 font-jakarta text-[13px]"></div>

          <button id="inquiry-submit" type="submit"
                  class="mt-6 w-full inline-flex items-center justify-center gap-2 bg-brand text-white font-semibold text-[12px] tracking-[1.8px] uppercase px-7 py-[14px] rounded-[2px] hover:bg-[#6a041c] transition-colors duration-200 disabled:opacity-70 disabled:cursor-not-allowed">
            <svg id="inquiry-spinner" class="hidden size-4 animate-spin" viewBox="0 0 24 24" fill="none" aria-hidden="true"><circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="3" class="opacity-25"/><path d="M22 12a10 10 0 0 0-10-10" stroke="currentColor" stroke-width="3" stroke-linecap="round"/></svg>
            <span id="inquiry-submit-label">KIRIM &amp; LANJUT KE WHATSAPP</span>
          </button>
          <p class="mt-3 text-center font-jakarta text-muted text-[11px]">Dengan mengirim, Anda setuju dihubungi tim Wintom lewat WhatsApp.</p>
        </form>
      </div>

      <!-- Kanan: peta + detail kontak -->
      <?php $maps_q = urlencode('Ruko Soho Ebony, Pantai Indah Kapuk, Jakarta Utara'); ?>
      <div class="bg-white border border-border rounded-[24px] shadow-sm overflow-hidden">
        <div class="relative aspect-[16/10] bg-border">
          <iframe
            src="https://maps.google.com/maps?q=<?= $maps_q ?>&z=15&output=embed&hl=id"
            class="absolute inset-0 w-full h-full" style="border:0;" allowfullscreen="" loading="lazy"
            referrerpolicy="no-referrer-when-downgrade"
            title="Lokasi Wintom Curtain Showroom"></iframe>
          <a href="https://www.google.com/maps/search/?api=1&query=<?= $maps_q ?>" target="_blank" rel="noopener"
             class="absolute top-4 right-4 inline-flex items-center gap-2 bg-white text-dark font-jakarta font-medium text-[13px] px-4 py-2.5 rounded-full shadow-md hover:shadow-lg transition-shadow">
            <span class="text-brand"><svg class="size-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75"><path stroke-linecap="round" stroke-linejoin="round" d="M12 21s-7-5.686-7-11a7 7 0 1 1 14 0c0 5.314-7 11-7 11Z"/><circle cx="12" cy="10" r="2.5"/></svg></span> Buka di Google Maps
          </a>
        </div>
        <ul class="px-7 py-6 space-y-3.5 font-jakarta text-[14px] leading-[22px] text-dark">
          <li class="flex items-start gap-3"><svg class="size-[18px] shrink-0 mt-0.5 text-brand" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75"><path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z"/><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1 1 15 0Z"/></svg><span><?= SITE_ADDRESS ?></span></li>
          <li class="flex items-center gap-3"><svg class="size-[18px] shrink-0 text-brand" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75"><circle cx="12" cy="12" r="9"/><path stroke-linecap="round" stroke-linejoin="round" d="M12 7v5l3 2"/></svg><span><?= SITE_HOURS ?></span></li>
          <li class="flex items-center gap-3"><svg class="size-[18px] shrink-0 text-[#25d366]" viewBox="0 0 20 20" fill="currentColor"><path d="M10 1.667A8.333 8.333 0 0 0 2.92 13.742L1.667 18.333l4.725-1.237A8.333 8.333 0 1 0 10 1.667Z"/></svg><span><a href="<?= WA_URL ?>?text=Halo+Wintom%2C+saya+ingin+konsultasi" target="_blank" rel="noopener" class="hover:text-brand transition-colors"><?= SITE_PHONE ?></a> &middot; <a href="<?= WA_URL_2 ?>?text=Halo+Wintom%2C+saya+ingin+konsultasi" target="_blank" rel="noopener" class="hover:text-brand transition-colors"><?= SITE_PHONE_2 ?></a> <span class="text-muted">(WhatsApp)</span></span></li>
          <li class="flex items-center gap-3"><svg class="size-[18px] shrink-0 text-brand" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75"><path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 0 1-2.25 2.25h-15a2.25 2.25 0 0 1-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25m19.5 0v.243a2.25 2.25 0 0 1-1.07 1.916l-7.5 4.615a2.25 2.25 0 0 1-2.36 0L3.32 8.91a2.25 2.25 0 0 1-1.07-1.916V6.75"/></svg><a href="mailto:<?= SITE_EMAIL ?>" class="hover:text-brand transition-colors"><?= SITE_EMAIL ?></a></li>
        </ul>
      </div>
    </div>
  </div>
</section>

<script>
(function () {
  const form = document.getElementById('inquiry-form');
  if (!form) return;
  const btn = document.getElementById('inquiry-submit');
  const label = document.getElementById('inquiry-submit-label');
  const spinner = document.getElementById('inquiry-spinner');
  const alertBox = document.getElementById('inquiry-alert');
  const idleLabel = label.textContent;
  const FIELDS = ['full_name', 'whatsapp', 'category_id', 'estimated_size', 'notes'];
  let busy = false;

  function setError(name, msg) {
    const el = form.querySelector('.field-error[data-for="' + name + '"]');
    const input = form.elements[name];
    if (el) { el.textContent = msg || ''; el.classList.toggle('hidden', !msg); }
    if (input) { input.classList.toggle('border-red-500', !!msg); input.setAttribute('aria-invalid', msg ? 'true' : 'false'); }
  }
  function clearErrors() {
    FIELDS.forEach(function (n) { setError(n, ''); });
    alertBox.classList.add('hidden');
  }
  function showAlert(msg, ok) {
    alertBox.textContent = msg;
    alertBox.className = 'mt-5 rounded-[3px] border px-4 py-3 font-jakarta text-[13px] ' +
      (ok ? 'bg-green-50 border-green-200 text-green-800' : 'bg-red-50 border-red-200 text-red-700');
  }
  function setLoading(on, text) {
    busy = on;
    btn.disabled = on;
    spinner.classList.toggle('hidden', !on);
    label.textContent = on ? text : idleLabel;
  }

  // Client-side validation mirrors inquiry.php (server tetap sumber kebenaran).
  function validate() {
    const errs = {};
    const name = form.full_name.value.trim().replace(/\s+/g, ' ');
    if (!name) errs.full_name = 'Nama lengkap wajib diisi.';
    else if (name.length < 2) errs.full_name = 'Nama minimal 2 karakter.';

    const wa = form.whatsapp.value.trim();
    let digits = wa.replace(/\D/g, '');
    if (!wa) errs.whatsapp = 'Nomor WhatsApp wajib diisi.';
    else if (!/^[+\d][\d\s().-]*$/.test(wa)) errs.whatsapp = 'Nomor WhatsApp hanya boleh berisi angka.';
    else {
      if (digits.startsWith('0')) digits = '62' + digits.slice(1);
      else if (digits.startsWith('8')) digits = '62' + digits;
      if (!/^62\d{8,13}$/.test(digits)) errs.whatsapp = 'Nomor WhatsApp tidak valid. Contoh: 0812 3456 7890.';
    }
    if (!form.category_id.value) errs.category_id = 'Pilih jenis curtain.';
    return errs;
  }

  form.addEventListener('input', function (e) { if (e.target.name) setError(e.target.name, ''); });
  form.addEventListener('change', function (e) { if (e.target.name) setError(e.target.name, ''); });

  form.addEventListener('submit', async function (e) {
    e.preventDefault();
    if (busy) return;
    clearErrors();

    const errs = validate();
    const keys = Object.keys(errs);
    if (keys.length) {
      keys.forEach(function (k) { setError(k, errs[k]); });
      form.elements[keys[0]].focus();
      return;
    }

    setLoading(true, 'MENYIMPAN…');
    try {
      const res = await fetch(form.action, {
        method: 'POST',
        body: new FormData(form),
        headers: { 'Accept': 'application/json' },
        credentials: 'same-origin'
      });
      let data = null;
      try { data = await res.json(); } catch (_) {}

      if (res.ok && data && data.ok && data.wa_url) {
        setLoading(true, 'MEMBUKA WHATSAPP…');
        showAlert('Terima kasih! Data Anda sudah kami terima. Mengalihkan ke WhatsApp…', true);
        window.setTimeout(function () { window.location.href = data.wa_url; }, 900);
        return;
      }
      if (data && data.errors) {
        const first = Object.keys(data.errors)[0];
        Object.keys(data.errors).forEach(function (k) { setError(k, data.errors[k]); });
        if (first && form.elements[first]) form.elements[first].focus();
      }
      showAlert((data && data.message) || 'Maaf, terjadi kesalahan. Silakan coba lagi.', false);
    } catch (err) {
      showAlert('Tidak dapat terhubung ke server. Periksa koneksi internet Anda lalu coba lagi.', false);
    }
    setLoading(false);
  });
})();
</script>

<?php include_once __DIR__ . '/includes/footer.php'; ?>
