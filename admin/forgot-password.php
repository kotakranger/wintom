<?php
// admin/forgot-password.php — minta tautan reset password lewat email akun admin.
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';

if (is_logged_in()) { header('Location: ' . BASE_URL . '/admin/index.php'); exit; }

$sent = false;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    request_password_reset(get_db(), (string)($_POST['email'] ?? ''));
    usleep(400000);
    $sent = true;   // jawaban selalu sama, agar email yang terdaftar tidak bisa ditebak
}
?>
<!DOCTYPE html>
<html lang="id">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Lupa Password — Wintom Curtain</title>
<link rel="icon" type="image/png" href="../assets/images/favicon.png"/>
  <script src="https://cdn.tailwindcss.com"></script>
  <script>
    tailwind.config = {
      theme: {
        extend: {
          fontFamily: {
            playfair: ['Georgia','"Times New Roman"','serif'],
            jakarta: ['Arial','Helvetica','sans-serif']
          },
          colors: {
            brand: {
              DEFAULT: '#860524',
              dark: '#6a041c'
            },
            dark: '#242424',
            muted: '#686561',
            cream: '#faf8f5',
            'cream-dark': '#f4f1ea',
            border: '#ece8e1'
          }
        }
      }
    }
  </script>
  <style>
    * {
      box-sizing: border-box;
    }

    body {
      background-color: #f5f3f0;
    }
  </style>
</head>

<body class="font-jakarta text-dark antialiased min-h-screen flex items-center justify-center px-4">
  <div class="w-full max-w-[400px]">
    <div class="text-center mb-8">
      <a href="../" class="inline-flex flex-col items-center">
        <span class="font-playfair font-bold text-brand text-[32px] leading-[38px] tracking-[-0.8px]">wintom</span>
        <span class="font-jakarta font-semibold text-dark text-[9px] tracking-[3.15px] uppercase">CURTAIN — ADMIN</span>
      </a>
    </div>
    <div class="bg-white border border-border rounded-[6px] shadow-sm p-8">
      <h1 class="font-playfair font-semibold text-dark text-[22px] mb-1">Lupa Password</h1>
      <?php if ($sent): ?>
        <p class="font-jakarta text-muted text-[13px] mb-4">Jika email tersebut terdaftar sebagai akun admin, tautan untuk mengatur ulang password sudah kami kirim. Tautan berlaku 60 menit dan hanya bisa dipakai sekali.</p>
        <p class="font-jakarta text-muted text-[12px]">Belum masuk? Periksa folder spam, atau minta Super Admin mengatur ulang password Anda lewat menu <b>Pengguna</b>.</p>
      <?php else: ?>
        <p class="font-jakarta text-muted text-[13px] mb-6">Masukkan email akun admin Anda. Kami kirimkan tautan untuk membuat password baru.</p>
        <form method="POST" autocomplete="off" class="flex flex-col gap-4">
          <?= csrf_field() ?>
          <div>
            <label class="font-jakarta font-medium text-dark text-[12px] tracking-[0.6px] uppercase block mb-1.5">Email</label>
            <input type="email" name="email" required autofocus maxlength="190" autocapitalize="none" spellcheck="false"
              class="w-full border border-border rounded-[4px] px-3 py-2.5 font-jakarta text-dark text-[14px] focus:outline-none focus:border-brand transition-colors" placeholder="nama@wintom.co.id" />
          </div>
          <button type="submit" class="w-full bg-brand text-white font-jakarta font-semibold text-[12px] tracking-[1.8px] uppercase py-3 rounded-[3px] hover:bg-[#6a041c] transition-colors duration-200 mt-2">KIRIM TAUTAN</button>
        </form>
      <?php endif; ?>
    </div>
    <p class="text-center font-jakarta text-muted text-[11px] mt-5">← <a href="login.php" class="hover:text-brand transition-colors">Kembali ke Login</a></p>
  </div>
</body>
</html>
