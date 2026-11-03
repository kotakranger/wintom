# Wintom — Rencana Google Analytics & SEO

Terakhir diperbarui: 2026-11-03 · Kode GA4 & SEO fase 1 sudah di `main` dan live di https://wintom.co.id

Legenda: `[x]` selesai · `[ ]` belum · **(Kamu)** perlu tindakan pemilik situs · **(Claude)** dikerjakan di kode

---

## Status & langkah berikutnya (2026-11-03)

**Sudah selesai & live:** SEO fase 1 (URL bersih + redirect 301, meta/canonical/Open Graph, JSON-LD, `sitemap.xml`, `robots.txt`, noindex admin/pencarian). Kode GA4 sudah ter-merge tapi **nonaktif** sampai `GA_MEASUREMENT_ID` diisi.

**Berikutnya, berurutan:**
1. **(Kamu)** Buat Property GA4 → Web stream `https://wintom.co.id` → kirim Measurement ID `G-XXXXXXXXXX`. Tidak perlu verifikasi bisnis; cukup akun Google biasa. _Status: menunggu akun GA siap._
2. **(Claude)** Isi `GA_MEASUREMENT_ID` (di `includes/config.php`), push ke `main`, purge CDN, cek GA › Realtime bahwa kunjungan dan event (`generate_lead`, `contact_whatsapp`, `contact_phone`, `quote_open`) masuk.
3. **(Kamu)** GA › Admin › Events: tandai `generate_lead` dan `contact_whatsapp` sebagai Key event.
4. **(Kamu, opsional, bisa kapan saja)** Google Search Console tipe **Domain** (`wintom.co.id`): kirim kode TXT verifikasi ke Claude untuk ditambah ke DNS, lalu kirim `sitemap.xml`. Tidak diperlukan untuk GA.
5. **(Claude)** Cek ulang Lighthouse SEO ≥ 95 dan Rich Results Test (belum dijalankan; kuota API PageSpeed Google habis pada 2026-11-03, ulangi lewat pagespeed.web.dev).
6. **(Ditunda)** Google Business Profile (butuh verifikasi kartu pos/telepon) dan ulasan Google: hanya untuk Maps/pencarian lokal, bukan untuk analytics.
7. **(Claude, setelah GA live)** Fase 3 konten: teks pengantar kategori + FAQ (schema FAQ), halaman per proyek portofolio.

**Catatan operasional**
- Setelah deploy yang menambah rute baru, purge CDN (Hostinger CDN sempat menyimpan 404 lama): `hosting_cache_clear` scope `cdn`.
- `mod_rewrite` mati di MAMP lokal. Tes URL bersih dengan salinan `httpd.conf` MAMP di port lain (rewrite aktif), bukan dengan mengubah MAMP.
- Auto-deploy GitHub sekarang ke wintom.co.id (branch `main`). Situs dummy `lightslategrey-…hostingersite.com` dibekukan sebagai cadangan.
- Load situs terukur naik-turun (0,2–3,5 dtk) pada 2026-11-03, termasuk untuk file statis CDN dan situs Uvora di server yang sama, jadi bukan murni masalah kode Wintom. Kalau berulang: catat halaman, jam, dan jaringan, lalu cek hPanel › Statistik/penggunaan resource.

---

## 0. Kondisi saat ini

- Situs live di **https://wintom.co.id** (sejak 2026-11-03). Situs dummy `lightslategrey-wombat-883743.hostingersite.com` masih online sebagai cadangan; Hostinger memblokir Googlebot di sana. Google belum mengindeks apa pun sampai sitemap dikirim di Search Console.
- Kecepatan mobile (Lighthouse) 97–99. Pertahankan: jangan menambah script yang memblokir render.
- Hasil audit awal:
  - Semua halaman memakai meta description yang sama.
  - Belum ada: `sitemap.xml`, canonical, Open Graph, data terstruktur (schema).
  - `index.html` dan `products.html` (versi lama) masih bisa dibuka.
  - URL produk masih teknis (`product-detail.php?slug=…`).
  - Sudah bagus: judul unik per halaman, 1 H1 per halaman, semua gambar punya alt, HTTP → HTTPS, 404 benar.

---

## 1. Google Analytics 4

### Kode (sudah selesai di branch)
- [x] **(Claude)** `includes/analytics.php`: gtag dimuat setelah halaman tampil (tidak menurunkan PageSpeed); nonaktif di localhost dan bila ID kosong.
- [x] **(Claude)** Konstanta `GA_MEASUREMENT_ID` di `includes/config.php` (bisa di-override di `config.local.php`).
- [x] **(Claude)** Event: `generate_lead`, `contact_whatsapp`, `contact_phone`, `quote_open`.
- [x] **(Claude)** Diuji di lokal: event masuk antrean `dataLayer`.

### Yang perlu dilakukan
- [ ] **(Kamu)** Buat Property GA4 di analytics.google.com (zona waktu Jakarta, mata uang IDR) → aliran data **Web**.
- [ ] **(Kamu)** Kirim **Measurement ID** (`G-XXXXXXXXXX`) ke Claude.
- [ ] **(Claude)** Isi `GA_MEASUREMENT_ID`, merge ke `main`, deploy, lalu cek di GA › Reports › **Realtime** bahwa kunjungan dan event masuk.
- [ ] **(Kamu)** GA › Admin › Events: tandai `generate_lead` dan `contact_whatsapp` sebagai **Key event**.
- [ ] **(Kamu)** Saat domain baru aktif: ubah URL aliran data di GA (ID tetap sama, data lama tidak hilang).
- [ ] **(Kamu, opsional)** GA › Admin › Data retention: ubah ke 14 bulan.

| Event | Dikirim saat | Parameter |
|---|---|---|
| `generate_lead` | Form "Minta Penawaran" berhasil terkirim | `form_location`, `product_category` |
| `contact_whatsapp` | Klik link `wa.me` mana pun | `link_location` (header / fab / footer / product / page) |
| `contact_phone` | Klik link `tel:` | `link_location` |
| `quote_open` | Modal penawaran dibuka | — |

> Catatan privasi: GA4 memakai cookie. Bila nanti ada target pengunjung Uni Eropa atau kebijakan privasi diperlukan, tambahkan banner persetujuan dan halaman Kebijakan Privasi.

---

## 2. SEO

### Fase 1 — Teknis (selesai 2026-11-03)
- [x] **(Claude)** Satu konstanta `SITE_URL` untuk alamat utama situs (ganti satu baris saat pindah domain).
- [x] **(Claude)** Meta description unik per halaman; produk dan kategori diambil otomatis dari deskripsinya (maks. ±155 karakter).
- [x] **(Claude)** Tag `canonical` di semua halaman publik.
- [x] **(Claude)** Open Graph + Twitter Card (judul, deskripsi, foto produk sebagai gambar pratinjau di WhatsApp / Instagram / Facebook).
- [x] **(Claude)** Data terstruktur JSON-LD:
  - `LocalBusiness` (nama, alamat PIK, telepon, jam buka, peta, media sosial)
  - `Product` (nama, foto, harga per meter) di halaman produk
  - `BreadcrumbList`
- [x] **(Claude)** `sitemap.xml` dinamis (halaman utama, kategori, produk, portofolio).
- [x] **(Claude)** `robots.txt` sendiri: blok `/admin`, `/includes`, `/database`; cantumkan alamat sitemap.
- [x] **(Claude)** URL bersih dengan redirect 301 dari alamat lama:
  - `/produk/<slug>`, `/katalog/<kategori>`, `/galeri`, `/tentang`
- [x] **(Claude)** Hapus `index.html` dan `products.html`.
- [x] **(Claude)** `noindex` untuk halaman admin dan halaman yang tidak perlu muncul di Google.
- [ ] **(Claude)** Cek ulang: Lighthouse SEO ≥ 95 dan tes **Rich Results** Google untuk schema (setelah deploy; Lighthouse belum dijalankan).

### Fase 2 — Saat domain baru aktif
- [x] **(Kamu)** Beli dan sambungkan domain **wintom.co.id** di Hostinger; SSL aktif dan HTTP → HTTPS (2026-11-03).
- [x] **(Claude)** `SITE_URL` = `https://wintom.co.id` (otomatis; di localhost mengikuti alamat yang dibuka) dan `robots.txt` sendiri mengizinkan Googlebot.
- [ ] **(Kamu)** Alihkan permanen (301) alamat `hostingersite.com` ke domain baru. _Ditunda: situs dummy sengaja dibiarkan online sebagai cadangan (auto-deploy sudah dimatikan)._
- [ ] **(Kamu)** Daftar **Google Search Console** (verifikasi domain via DNS), kirim `sitemap.xml`, hubungkan ke GA4.
- [ ] **(Kamu)** Buat dan verifikasi **Google Business Profile** untuk Wintom di PIK. Nama, alamat, dan nomor telepon harus **persis sama** dengan di website. Isi kategori bisnis, jam buka, foto, dan tautan website.
- [ ] **(Kamu)** Minta pelanggan lama memberi ulasan Google (target awal: 10+ ulasan).
- [ ] **(Kamu)** Cantumkan alamat website di profil Instagram, TikTok, dan Facebook.
- [ ] Setelah ±1–2 minggu: cek di Search Console bahwa halaman terindeks dan tidak ada error.

### Fase 3 — Konten (berkelanjutan)
- [ ] Teks pengantar 150–300 kata di tiap halaman kategori dengan kata kunci lokal (mis. "roller blind Jakarta Utara", "gorden motorized PIK").
- [ ] Bagian FAQ (+ schema FAQ): lama pengerjaan, survei gratis, area layanan, cara mengukur, garansi.
- [ ] Satu halaman per proyek portofolio, dengan lokasi dan jenis produk.
- [ ] Seragamkan judul dan alt text portofolio ke Bahasa Indonesia.
- [ ] Opsional: artikel panduan ("Cara mengukur jendela untuk gorden", "Roller blind vs zebra blind").
- [ ] Setiap kali ada produk atau proyek baru: isi nama, deskripsi, dan alt text yang jelas (bukan "IMG_1234").

---

## 3. Pemantauan rutin

- Mingguan (bulan pertama): GA Realtime/Reports untuk melihat sumber kunjungan dan jumlah `generate_lead`.
- Bulanan: Search Console (kata kunci, halaman terindeks, error), grafik "Resource lainnya" di hPanel, Lighthouse mobile (target ≥ 90).
- Setelah mengubah class Tailwind di halaman publik: jalankan `npm run build:css` sebelum push agar CSS ikut diperbarui.
