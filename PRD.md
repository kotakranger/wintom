# PRD — Wintom Curtain: Company Profile, Catalog & Portfolio Gallery
> **Single Source of Truth (SSoT)**
> Dibuat: 2026-09-19 | Terakhir diperbarui: 2026-09-19
> Status: 🟡 **In Planning**

---

## Daftar Isi
1. [Ringkasan Proyek & Tujuan](#1-ringkasan-proyek--tujuan)
2. [Prinsip Arsitektur & Tooling](#2-prinsip-arsitektur--tooling)
3. [Struktur Halaman Publik](#3-struktur-halaman-publik)
4. [Admin Panel](#4-admin-panel)
5. [Skema Database SQLite](#5-skema-database-sqlite)
6. [Konvensi & Standar Kode](#6-konvensi--standar-kode)
7. [Keputusan Desain](#7-keputusan-desain)
8. [Status & Roadmap](#8-status--roadmap)
9. [Log Perubahan](#9-log-perubahan)

---

## 1. Ringkasan Proyek & Tujuan

| Field | Detail |
|---|---|
| **Nama Proyek** | Wintom Curtain — Company Profile & Dynamic Catalog (MVP) |
| **Target Audience** | Calon pembeli gorden premium/arsitektural, area Jabodetabek |
| **Platform Target** | Shared hosting cPanel (PHP + SQLite) |

### Tujuan Utama
- Menampilkan profil bisnis gorden arsitektural/premium, standar layanan survei, dan garansi instalasi.
- Menyediakan **katalog produk interaktif** (daftar & detail) serta **arsip galeri portofolio visual**.
- **Konversi ke WhatsApp** dengan pesan dinamis terformat otomatis sesuai produk yang dilihat.
- **Panel Admin mandiri** di `/admin` untuk mengelola katalog produk (multi-foto) dan galeri portofolio.

---

## 2. Prinsip Arsitektur & Tooling

### Stack Teknologi
| Layer | Teknologi |
|---|---|
| **Backend** | PHP Native 8.x |
| **Database** | SQLite (`database/curtains.sqlite`) |
| **Frontend Styling** | Tailwind CSS via CDN |
| **Icons** | Inline SVG |
| **Aset Gambar Statis** | `/assets/images/` |
| **Aset Gambar Upload** | `/uploads/` |

### Aturan Ketat (Strict Rules)
- DILARANG membuat scaffolding berbasis Vite, Node.js, npm, Webpack, Parcel, React, atau framework JS frontend apapun.
- DILARANG menghasilkan file JSX/TSX atau folder `dist/`.
- Output wajib berupa **Pure Semantic HTML5** + **Tailwind CSS utility class**.
- Tailwind CSS dimuat via CDN: `<script src="https://cdn.tailwindcss.com"></script>`
- Semua icon sebagai **inline SVG**.
- **Zero build-step** — kode langsung berjalan di server.

### Template Prompt Figma MCP (Slicing)
```
Slicing frame Figma berikut: [MASUKKAN_URL_FIGMA_DI_SINI].
Jangan buat proyek Vite, Node.js, atau React.
Hasilkan kode berupa potongan HTML5 semantik murni dengan utility class Tailwind CSS
yang presisi sesuai token Figma (warna, font family, rasio gambar, padding, dan gap).
Kode harus siap langsung ditempel ke dalam file template PHP (.php) tanpa build step apa pun.
```

---

## 3. Struktur Halaman Publik

### A. Homepage (`index.php`)
| Seksi | Deskripsi |
|---|---|
| **Hero Section** | Value proposition, tagline kemewahan arsitektural, tombol CTA konsultasi |
| **Keunggulan Layanan** | Survei gratis Jabodetabek, custom dimensi, swatch kain fisik ke lokasi |
| **Koleksi Unggulan** | Cuplikan beberapa produk terbaik dari database |
| **3 Langkah Pemesanan** | Pilih Koleksi & Konsultasi → Jadwalkan Survei Gratis → Instalasi Presisi & Garansi |
| **Footer** | Navigasi, jam operasional, media sosial, kontak, alamat showroom |

### B. Katalog Produk (`products.php`)
| Seksi | Deskripsi |
|---|---|
| **Header Koleksi** | Judul seksi kurasi produk & ringkasan layanan |
| **Filter Kategori** | Tab dinamis: Semua Produk, Curtain, Vitrase & Sheer, Roller Blind, Motorized Smart System, Wood & Bamboo Blind |
| **Grid Kartu Produk** | Foto cover (`aspect-[3/4]`), badge jenis kain, nama seri, badge diskon, harga/meter, tombol "Lihat Detail" & "Via WhatsApp" |

### C. Detail Produk (`product-detail.php`)
| Seksi | Deskripsi |
|---|---|
| **Galeri Multi-Foto** | Foto utama HD + baris thumbnail dari `product_images` |
| **Info Produk** | Judul seri, kode REF arsitektural, deskripsi material, harga/meter, variasi (warna, dimensi, model jahitan) |
| **CTA Utama** | Tombol "Konsultasi & Cek Estimasi via WhatsApp" → pesan dinamis otomatis |
| **Protokol Pemesanan** | 4 tahap: Konsultasi Awal → Survei & Swatch → Penjahitan Atelier → Pemasangan Rapi |
| **Rekomendasi** | Grid produk alternatif/pelengkap |

Model jahitan: Ripple Fold / S-Fold Wave (dapat ditambah)

### D. Galeri Portofolio (`gallery.php`)
| Seksi | Deskripsi |
|---|---|
| **Header** | Judul galeri & kurasi hasil pengerjaan |
| **Filter Kategori** | Semua Proyek, Living Room, Penthouse Sanctuary, Dining & Atelier, Office & Commercial, Hotel & Private Villa |
| **Grid Portofolio** | Foto dokumentasi pemasangan dari tabel `galleries` |
| **Seksi Reservasi** | Banner ajakan survei & pengukuran ke lokasi |

### E. Tentang Kami & Kontak (`about.php`)
| Seksi | Deskripsi |
|---|---|
| **Cerita Brand** | Profil atelier gorden, standar penjahitan, kontrol kualitas kain |
| **Layanan Lengkap** | Survei onsite hingga layanan purnajual |
| **Kontak & Lokasi** | Peta/alamat showroom, nomor telepon, tautan chat |

---

## 4. Admin Panel

**Base URL:** `/admin`
**Autentikasi:** PHP Native Session (`session_start()`) + `password_hash()`

### Modul Admin
| Modul | File | Fitur |
|---|---|---|
| **Login** | `admin/login.php` | Form login, validasi session |
| **Dashboard** | `admin/index.php` | Ringkasan statistik (opsional) |
| **Produk** | `admin/products.php` | CRUD produk: nama, slug, kategori, badge, ref code, harga asli, harga diskon, satuan harga, deskripsi, spesifikasi, cover image, multi-image upload |
| **Galeri** | `admin/gallery.php` | Upload foto portofolio, input judul & kategori, hapus foto |

### Catatan Upload
- File produk → `/uploads/products/`
- File galeri → `/uploads/gallery/`
- Hapus produk → otomatis hapus DB record + file fisik

---

## 5. Skema Database SQLite

**Path:** `database/curtains.sqlite`

```sql
-- Tabel Admin
CREATE TABLE IF NOT EXISTS admins (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    username TEXT UNIQUE NOT NULL,
    password_hash TEXT NOT NULL
);

-- Tabel Produk Utama
CREATE TABLE IF NOT EXISTS products (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    slug TEXT UNIQUE NOT NULL,
    name TEXT NOT NULL,
    category TEXT NOT NULL,
    badge TEXT,
    ref_code TEXT,
    price INTEGER NOT NULL,
    original_price INTEGER,
    price_unit TEXT DEFAULT '/ meter',
    description TEXT,
    features TEXT,
    options_json TEXT,
    cover_image TEXT NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- Tabel Galeri Multi-Foto Detail Produk
CREATE TABLE IF NOT EXISTS product_images (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    product_id INTEGER NOT NULL,
    image_url TEXT NOT NULL,
    sort_order INTEGER DEFAULT 0,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
);

-- Tabel Portofolio Galeri
CREATE TABLE IF NOT EXISTS galleries (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    title TEXT NOT NULL,
    category TEXT NOT NULL,
    image_url TEXT NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);
```

---

## 6. Konvensi & Standar Kode

### Struktur Folder
```
wintom/
├── index.php               # Homepage
├── products.php            # Katalog produk
├── product-detail.php      # Detail produk
├── gallery.php             # Galeri portofolio
├── about.php               # Tentang & kontak
├── admin/
│   ├── index.php           # Dashboard admin
│   ├── login.php           # Login admin
│   ├── products.php        # Manajemen produk
│   └── gallery.php         # Manajemen galeri
├── includes/
│   ├── db.php              # Koneksi database SQLite
│   ├── auth.php            # Helper session/autentikasi
│   ├── header.php          # Header HTML global
│   └── footer.php          # Footer HTML global
├── assets/
│   └── images/             # Gambar statis (hardcoded)
├── uploads/
│   ├── products/           # Upload gambar produk
│   └── gallery/            # Upload gambar galeri
└── database/
    └── curtains.sqlite     # File database SQLite
```

### Konvensi Penamaan
- File PHP: `kebab-case.php`
- Slug produk: `kebab-case` (unik, URL-friendly)
- Kode referensi: `PREFIX-NNN` (contoh: `BEL-102`)
- Kategori produk: string tetap sesuai daftar di seksi 3B & 3D

### Nomor WhatsApp
- Simpan di variabel konstanta di `includes/config.php`
- Format: `628xxxxxxxxxx`
- Ganti sebelum deploy production

---

## 7. Keputusan Desain

| Tanggal | Keputusan | Alasan |
|---|---|---|
| 2026-09-19 | Gunakan PHP Native + SQLite (bukan Laravel/MySQL) | Zero-build-step, hemat token AI, kompatibel shared hosting cPanel |
| 2026-09-19 | Tailwind CSS via CDN (bukan install) | Sesuai prinsip no build-step |
| 2026-09-19 | Desain dari Figma via MCP | Presisi pixel-perfect tanpa interpretasi manual |

---

## 8. Status & Roadmap

### MVP Scope
| # | Fitur | Status |
|---|---|---|
| 1 | Setup folder & database init | ✅ Selesai |
| 2 | `includes/` (db, auth, header, footer) | ✅ Selesai |
| 3 | Homepage (`index.php`) | ✅ Selesai |
| 4 | Katalog Produk (`products.php`) | ✅ Selesai |
| 5 | Detail Produk (`product-detail.php`) | ✅ Selesai |
| 6 | Galeri Portofolio (`gallery.php`) | 🔲 Belum |
| 7 | Tentang & Kontak (`about.php`) | 🔲 Belum |
| 8 | Admin Login (`admin/login.php`) | 🔲 Belum |
| 9 | Admin Produk CRUD (`admin/products.php`) | 🔲 Belum |
| 10 | Admin Galeri CRUD (`admin/gallery.php`) | 🔲 Belum |
| 11 | WhatsApp CTA dinamis (semua halaman) | ✅ Selesai |
| 12 | Filter kategori dinamis (produk) | ✅ Selesai |

### Legend Status
`🔲 Belum` | `🔄 In Progress` | `✅ Selesai` | `⏸ Hold` | `❌ Batal`

---

## 9. Log Perubahan

| Tanggal | Versi | Perubahan | Oleh |
|---|---|---|---|
| 2026-09-19 | v0.1 | Inisialisasi PRD, konversi ke format SSoT | AI + Tim |
| 2026-09-19 | v0.2 | Selesai Phase 1 Foundation, Homepage, Katalog Produk & Detail Produk | AI + Tim |


---

*Dokumen ini adalah SSoT proyek Wintom Curtain. Setiap perubahan requirement, arsitektur, atau scope WAJIB diupdate di sini sebelum diimplementasikan.*
