<?php
// includes/wa-fab.php — Tombol WhatsApp melayang + panel "pilih topik" ala customer service.
// Topik umum -> nomor utama. "Konsultasi produk" -> pilih kategori -> WhatsApp admin kategori itu.
// Butuh: $quote_categories (id, name, whatsapp) dari header.php. Opsional: $product (halaman detail produk).

$fab_cats    = $quote_categories ?? [];
$fab_product = (isset($product) && is_array($product) && !empty($product['name'])) ? $product['name'] : null;
$fab_prodcat = $fab_product ? ($product['category'] ?? null) : null;
// Di halaman produk: kategori produk tersebut ditaruh paling atas
if ($fab_prodcat) {
    usort($fab_cats, fn($a, $b) => ($b['name'] === $fab_prodcat) <=> ($a['name'] === $fab_prodcat));
}
$fab_wa      = fn(string $num, string $msg) => 'https://wa.me/' . $num . '?text=' . rawurlencode($msg);
$fab_ctx     = $fab_product ? " (saya sedang melihat produk {$fab_product})" : '';

// Ubah daftar topik di sini. 'msg' = isi awal pesan WhatsApp.
$fab_topics = [
    ['key' => 'product',  'label' => 'Konsultasi produk & harga',      'hint' => 'Pilih jenis produk',
     'icon' => 'M3.75 5.25h16.5M3.75 5.25v13.5h16.5V5.25M8.25 5.25v13.5M15.75 5.25v13.5'],
    ['key' => 'survey',   'label' => 'Jadwalkan survei & pengukuran',  'hint' => 'Gratis di Jabodetabek',
     'icon' => 'M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5h18v7.5',
     'msg' => "Halo Wintom, saya ingin menjadwalkan survei & pengukuran ke lokasi saya{$fab_ctx}.\n\nAlamat:\nWaktu yang diinginkan:"],
    ['key' => 'order',    'label' => 'Status pesanan & pemasangan',    'hint' => 'Cek progres pesanan Anda',
     'icon' => 'M8.25 18.75a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m3 0h6m-9 0H3.375a1.125 1.125 0 0 1-1.125-1.125V14.25m17.25 4.5a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m3 0h1.125c.621 0 1.129-.504 1.09-1.124a17.902 17.902 0 0 0-3.213-9.193 2.056 2.056 0 0 0-1.58-.86H14.25M16.5 18.75h-2.25m0-11.177v-.958c0-.568-.422-1.048-.987-1.106a48.554 48.554 0 0 0-10.026 0 1.106 1.106 0 0 0-.987 1.106v7.635m12-6.677v6.677m0 4.5v-4.5m0 0h-12',
     'msg' => "Halo Wintom, saya ingin menanyakan status pesanan/pemasangan saya.\n\nNama pemesan:\nNo. pesanan / tanggal pesan:"],
    ['key' => 'warranty', 'label' => 'Garansi & layanan purna jual',   'hint' => 'Klaim garansi atau perbaikan',
     'icon' => 'M9 12.75 11.25 15 15 9.75m-3-7.036A11.959 11.959 0 0 1 3.598 6 11.99 11.99 0 0 0 3 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285Z',
     'msg' => "Halo Wintom, saya ingin mengajukan klaim garansi / layanan purna jual.\n\nProduk:\nKendala:"],
    ['key' => 'showroom', 'label' => 'Lokasi & jam buka showroom',     'hint' => 'Ruko Soho Ebony, PIK',
     'icon' => 'M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1 1 15 0Z',
     'msg' => "Halo Wintom, saya ingin berkunjung ke showroom. Boleh info lokasi dan jam bukanya?"],
    ['key' => 'other',    'label' => 'Pertanyaan lainnya',             'hint' => 'Chat langsung dengan tim kami',
     'icon' => 'M8.625 12a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm4.125 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm4.125 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0ZM21 12c0 4.556-4.03 8.25-9 8.25a9.764 9.764 0 0 1-2.555-.337A5.972 5.972 0 0 1 5.41 20.97a5.969 5.969 0 0 0 .474-.065 4.48 4.48 0 0 0 .978-2.025c.09-.457-.133-.901-.467-1.226C3.93 16.178 3 14.189 3 12c0-4.556 4.03-8.25 9-8.25s9 3.694 9 8.25Z',
     'msg' => "Halo Wintom, saya ada pertanyaan{$fab_ctx}."],
];
$chev = '<svg class="size-4 shrink-0 text-muted transition-transform group-hover:translate-x-0.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="m9 18 6-6-6-6"/></svg>';
?>
<!-- ===== WHATSAPP: pilih topik ===== -->
<div id="wa-fab" class="fixed bottom-4 right-4 sm:bottom-6 sm:right-6 z-[60] flex flex-col items-end">

  <div id="wa-fab-panel" role="dialog" aria-modal="false" aria-labelledby="wa-fab-title" hidden
       class="mb-3 w-[340px] max-w-[calc(100vw-32px)] max-h-[min(560px,calc(100dvh-110px))] flex flex-col overflow-hidden rounded-[14px] bg-white shadow-[0_24px_60px_-12px_rgba(0,0,0,0.35)] border border-border origin-bottom-right">
    <!-- Header -->
    <div class="bg-brand text-white px-5 pt-4 pb-5 shrink-0">
      <div class="flex items-start justify-between gap-3">
        <div class="flex items-center gap-3 min-w-0">
          <span class="relative size-10 shrink-0 rounded-full bg-white flex items-center justify-center">
            <img src="<?= BASE_URL ?>/assets/images/favicon.png" alt="" class="size-7 object-contain">
            <span class="absolute bottom-0 right-0 size-2.5 rounded-full bg-[#25d366] ring-2 ring-brand"></span>
          </span>
          <div class="min-w-0">
            <p id="wa-fab-title" class="font-jakarta font-semibold text-[14px] leading-tight m-0">Wintom Customer Service</p>
            <p class="font-jakarta text-white/75 text-[11px] m-0 mt-0.5">Respon cepat di jam operasional</p>
          </div>
        </div>
        <button type="button" data-wa-close aria-label="Tutup" class="size-8 -mr-2 -mt-1 shrink-0 flex items-center justify-center rounded-full text-white/80 hover:text-white hover:bg-white/10 transition-colors">
          <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"><path d="M6 6l12 12M18 6 6 18"/></svg>
        </button>
      </div>
    </div>

    <div class="overflow-y-auto overscroll-contain bg-cream">
      <!-- Langkah 1: topik -->
      <div data-wa-step="topics" class="p-4">
        <div class="bg-white border border-border rounded-[10px] rounded-tl-[2px] px-4 py-3 mb-4 shadow-sm max-w-[92%]">
          <p class="font-jakarta text-dark text-[13px] leading-[1.5] m-0">Halo! 👋 Ada yang bisa kami bantu? Pilih topik di bawah, kami lanjutkan obrolannya di WhatsApp.</p>
        </div>
        <ul class="list-none m-0 p-0 flex flex-col gap-2">
          <?php foreach ($fab_topics as $t): ?>
          <li>
            <?php if ($t['key'] === 'product'): ?>
            <button type="button" data-wa-goto="products"
            <?php else: ?>
            <a href="<?= htmlspecialchars($fab_wa(WA_NUMBER, $t['msg'])) ?>" target="_blank" rel="noopener" data-wa-pick
            <?php endif; ?>
               class="group w-full flex items-center gap-3 text-left bg-white border border-border rounded-[10px] px-3.5 py-3 hover:border-brand/40 hover:shadow-sm transition-all">
              <span class="size-9 shrink-0 rounded-full bg-brand/10 text-brand flex items-center justify-center">
                <svg class="size-[18px]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.6"><path stroke-linecap="round" stroke-linejoin="round" d="<?= $t['icon'] ?>"/></svg>
              </span>
              <span class="flex-1 min-w-0">
                <span class="block font-jakarta font-semibold text-dark text-[13px] leading-tight"><?= htmlspecialchars($t['label']) ?></span>
                <span class="block font-jakarta text-muted text-[11px] mt-0.5"><?= htmlspecialchars($t['hint']) ?></span>
              </span>
              <?= $chev ?>
            <?= $t['key'] === 'product' ? '</button>' : '</a>' ?>
          </li>
          <?php endforeach; ?>
        </ul>
      </div>

      <!-- Langkah 2: kategori produk (diteruskan ke admin kategori tsb.) -->
      <div data-wa-step="products" class="p-4" hidden>
        <button type="button" data-wa-goto="topics" class="inline-flex items-center gap-1.5 font-jakarta text-muted text-[12px] hover:text-dark mb-3 transition-colors">
          <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="m15 18-6-6 6-6"/></svg>
          Kembali
        </button>
        <div class="bg-white border border-border rounded-[10px] rounded-tl-[2px] px-4 py-3 mb-4 shadow-sm max-w-[92%]">
          <p class="font-jakarta text-dark text-[13px] leading-[1.5] m-0">Produk apa yang ingin Anda konsultasikan? Anda akan terhubung dengan tim penjualan produk tersebut.</p>
        </div>
        <ul class="list-none m-0 p-0 grid grid-cols-1 gap-2">
          <?php foreach ($fab_cats as $c):
              $is_current = $fab_prodcat !== null && $c['name'] === $fab_prodcat;
              $msg = "Halo Wintom, saya ingin konsultasi produk {$c['name']}" . ($is_current ? " — {$fab_product}" : '') . ". Boleh minta info dan estimasi harganya?";
          ?>
          <li>
            <a href="<?= htmlspecialchars($fab_wa(category_wa_number($c['whatsapp'] ?? null), $msg)) ?>" target="_blank" rel="noopener" data-wa-pick
               class="group w-full flex items-center justify-between gap-3 bg-white border border-border rounded-[10px] px-4 py-3 hover:border-brand/40 hover:shadow-sm transition-all">
              <span class="min-w-0">
                <span class="block font-jakarta font-semibold text-dark text-[13px]"><?= htmlspecialchars($c['name']) ?></span>
                <?php if ($is_current): ?><span class="block font-jakarta text-brand text-[11px] mt-0.5 truncate">Produk ini: <?= htmlspecialchars($fab_product) ?></span><?php endif; ?>
              </span>
              <?= $chev ?>
            </a>
          </li>
          <?php endforeach; ?>
        </ul>
      </div>
    </div>
  </div>

  <!-- Tombol: tanpa JS tetap langsung membuka WhatsApp -->
  <a href="<?= htmlspecialchars($fab_wa(WA_NUMBER, 'Halo Wintom, saya ingin konsultasi gorden')) ?>" target="_blank" rel="noopener"
     id="wa-fab-toggle" aria-controls="wa-fab-panel" aria-expanded="false" aria-label="Chat dengan Wintom via WhatsApp"
     class="wa-float relative bg-[#25d366] rounded-full p-[14px] shadow-lg hover:shadow-xl hover:scale-105 transition-all duration-200">
    <svg data-wa-icon="open" class="size-6" viewBox="0 0 24 24" fill="white" aria-hidden="true">
      <path d="M12 2C6.477 2 2 6.477 2 12c0 1.89.525 3.66 1.438 5.168L2 22l4.832-1.438A9.946 9.946 0 0 0 12 22c5.523 0 10-4.477 10-10S17.523 2 12 2Z"/>
    </svg>
    <svg data-wa-icon="close" class="size-6 hidden" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2.2" stroke-linecap="round" aria-hidden="true"><path d="M6 6l12 12M18 6 6 18"/></svg>
  </a>
</div>

<style>
  /* class "flex" pada panel mengalahkan atribut hidden bawaan browser — paksa sembunyi */
  #wa-fab [hidden] { display: none !important; }
  #wa-fab-panel:not([hidden]) { animation: wa-fab-in .22s cubic-bezier(.2,.7,.2,1) both; }
  @keyframes wa-fab-in { from { opacity: 0; transform: translateY(10px) scale(.96); } to { opacity: 1; transform: none; } }
  #wa-fab [data-wa-step]:not([hidden]) { animation: wa-step-in .2s ease both; }
  @keyframes wa-step-in { from { opacity: 0; transform: translateX(8px); } to { opacity: 1; transform: none; } }
  #wa-fab.is-open .wa-float { animation: none; }
</style>
<script>
(function () {
  const root = document.getElementById('wa-fab');
  const panel = document.getElementById('wa-fab-panel');
  const toggle = document.getElementById('wa-fab-toggle');
  const steps = root.querySelectorAll('[data-wa-step]');

  function show(step) {
    steps.forEach(function (s) { s.hidden = s.dataset.waStep !== step; });
    const first = root.querySelector('[data-wa-step="' + step + '"] a, [data-wa-step="' + step + '"] button');
    if (first) first.focus({ preventScroll: true });
  }
  function setOpen(open) {
    panel.hidden = !open;
    root.classList.toggle('is-open', open);
    toggle.setAttribute('aria-expanded', open ? 'true' : 'false');
    toggle.setAttribute('aria-label', open ? 'Tutup pilihan topik' : 'Chat dengan Wintom via WhatsApp');
    toggle.querySelector('[data-wa-icon="open"]').classList.toggle('hidden', open);
    toggle.querySelector('[data-wa-icon="close"]').classList.toggle('hidden', !open);
    if (open) show('topics');
  }

  toggle.addEventListener('click', function (e) { e.preventDefault(); setOpen(panel.hidden); });
  root.addEventListener('click', function (e) {
    const go = e.target.closest('[data-wa-goto]');
    if (go) { show(go.dataset.waGoto); return; }
    if (e.target.closest('[data-wa-close]')) { setOpen(false); toggle.focus(); return; }
    if (e.target.closest('[data-wa-pick]')) setTimeout(function () { setOpen(false); }, 150);   // link WA tetap terbuka
  });
  document.addEventListener('keydown', function (e) {
    if (e.key === 'Escape' && !panel.hidden) { setOpen(false); toggle.focus(); }
  });
  document.addEventListener('click', function (e) {
    if (!panel.hidden && !root.contains(e.target)) setOpen(false);
  });
})();
</script>
