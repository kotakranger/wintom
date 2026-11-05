# Copy v2 (eksperimen) — flag `FEATURE_COPY_V2`

Sumber: dokumen "Daftar Kebutuhan Konten Web Development Client – update" (kolom **DHANI**), 2026-11-05.
Aturan: sel DHANI yang **terisi** menggantikan teks sekarang; sel yang **kosong** = teks sekarang tetap dipakai.

## Cara mematikan / memantau
- **Default: aktif** (`FEATURE_COPY_V2` di `includes/config.php`).
- **Matikan tanpa deploy**: tambahkan di `includes/config.local.php` di server
  `define('FEATURE_COPY_V2', false);` (atau env `WINTOM_COPY_V2=0`). Hapus barisnya untuk menyalakan lagi.
- **Pratinjau per halaman**: tambahkan `?copy=v1` (teks lama) atau `?copy=v2` (teks baru) di URL. Halaman pratinjau diberi `noindex`.
- Teks lama dan baru berdampingan di kode (`copy_v2() ? baru : lama`), jadi rollout/rollback = satu baris.

## Yang berubah saat flag aktif
| Halaman | Bagian |
|---|---|
| Beranda | label hero (… · JAKARTA), judul hero, deskripsi hero (+ "Belum tahu ukuran…"), tombol "Lihat Koleksi Produk", 4 keunggulan, "Mengapa Memilih Kami?" (judul, paragraf, 4 poin), Cara Pesan (hanya beranda), FAQ (5 pertanyaan baru, ikut ke JSON-LD FAQPage) |
| Produk (katalog) | judul & deskripsi hero (tampilan "Semua Koleksi"), banner konsultasi |
| Galeri | judul, deskripsi, banner ajakan proyek |
| Tentang Kami | profil (3 paragraf), visi & misi (visi baru + 5 misi), kartu "Ceritakan Kebutuhan Gorden & Blinds Anda" |

## Tidak diubah (sel DHANI kosong / sama dengan sekarang)
Menu, "Kualitas di Setiap Detail", ringkasan penawaran, video, testimoni, kategori & pencarian, isi produk, daftar proyek
galeri, keunggulan & cara pesan di Tentang (komponen "Cara Pesan" di Tentang dan detail produk tetap lama), formulir kontak,
alamat/jam/kontak, footer, WhatsApp & modal.

## Kategori beranda (baris 5)
Daftar "Curtain | Roller Blind | Vitrase | Zebra Blind | Others | Aksesori" **diatur lewat admin** (nama, urutan, foto kategori),
bukan lewat kode. Beranda menampilkan 6 kategori pertama menurut urutan di admin.

## Catatan konsistensi (perlu keputusan owner)
- FAQ baru: "Biaya [survei/pemasangan] disesuaikan dengan jarak dan kebutuhan", sedangkan masih ada teks lain yang menyebut **survei gratis**:
  tombol "Reservasi Survei Gratis" (halaman Produk), meta description beranda/kategori, dan paragraf pengantar kategori.
- Copy baru menyebut "showroom di **PIK 2**"; alamat di Kontak/footer/meta dan JSON-LD masih "PIK" (Pantai Indah Kapuk, Ruko Soho Ebony).
- FAQ baru memakai "Kamu" sedangkan sisa situs memakai "Anda".
- Label kecil "KATALOG EKSKLUSIF WINTOM" di halaman Produk tidak disebut di PDF, jadi tidak diubah.
