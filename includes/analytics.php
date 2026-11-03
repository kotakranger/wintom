<?php
// includes/analytics.php — Google Analytics 4 (gtag.js) untuk halaman publik.
// Aktif hanya bila GA_MEASUREMENT_ID terisi & bukan di localhost (trafik development tidak ikut terhitung).
//
// Event yang dikirim (tandai sebagai "Key event" di GA › Admin › Events):
//   • generate_lead     — form "Minta Penawaran" berhasil terkirim (kategori produk)
//   • contact_whatsapp  — klik link WhatsApp mana pun (lokasi: header/fab/produk/halaman)
//   • contact_phone     — klik nomor telepon (tel:)
//   • quote_open        — modal "Minta Penawaran" dibuka
// Panggil dari JS lain dengan: wtTrack('nama_event', { param: nilai })

$ga_host = strtolower(explode(':', $_SERVER['HTTP_HOST'] ?? '')[0]);
$ga_on   = GA_MEASUREMENT_ID !== '' && !in_array($ga_host, ['localhost', '127.0.0.1', '::1'], true) && !str_ends_with($ga_host, '.test');
?>
<script>
  window.dataLayer = window.dataLayer || [];
  function gtag() { dataLayer.push(arguments); }
  // Aman dipanggil walau GA nonaktif: event cukup masuk antrean dataLayer.
  function wtTrack(name, params) { gtag('event', name, params || {}); }
<?php if ($ga_on): ?>
  gtag('js', new Date());
  gtag('config', <?= json_encode(GA_MEASUREMENT_ID) ?>);
  // gtag.js (±150 KB) dimuat setelah halaman selesai tampil agar skor PageSpeed tidak turun;
  // event sebelum itu tetap tersimpan di dataLayer dan terkirim saat script siap.
  addEventListener('load', function () {
    setTimeout(function () {
      var s = document.createElement('script');
      s.async = true;
      s.src = 'https://www.googletagmanager.com/gtag/js?id=' + <?= json_encode(GA_MEASUREMENT_ID) ?>;
      document.head.appendChild(s);
    }, 1);
  });
<?php endif; ?>

  // Klik kontak: WhatsApp & telepon di seluruh situs (satu listener, tidak perlu tandai tiap link)
  document.addEventListener('click', function (e) {
    var a = e.target.closest && e.target.closest('a[href]');
    if (a) {
      var href = a.getAttribute('href');
      var loc = a.closest('#wa-fab') ? 'fab' : a.closest('header') ? 'header' : a.closest('footer') ? 'footer'
              : a.id === 'wa-cta-btn' ? 'product' : 'page';
      if (/wa\.me\//.test(href)) wtTrack('contact_whatsapp', { link_location: loc });
      else if (/^tel:/.test(href)) wtTrack('contact_phone', { link_location: loc });
    }
    if (e.target.closest && e.target.closest('[data-open-quote]')) wtTrack('quote_open');
  }, true);
</script>
