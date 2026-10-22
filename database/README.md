# Database Wintom (MySQL)

`wintom.sql` berisi skema + konten (kategori, produk, varian, galeri). Tidak termasuk
tabel `admins` dan `inquiries`: keduanya dibuat otomatis saat aplikasi pertama kali
dijalankan, dan akun admin default (`admin` / `wintom2024`) dibuat ulang. Ganti password setelah login.

Import ke MAMP (phpMyAdmin atau terminal):

    /Applications/MAMP/Library/bin/mysql -uroot -proot -P8889 -h127.0.0.1 -e "CREATE DATABASE IF NOT EXISTS wintom CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci"
    /Applications/MAMP/Library/bin/mysql -uroot -proot -P8889 -h127.0.0.1 wintom < database/wintom.sql

Lalu salin `includes/config.local.example.php` menjadi `includes/config.local.php`.
