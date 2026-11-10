<?php
// admin/reset-password.php — buat password baru dari tautan email (sekali pakai, 60 menit).
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';

$db = get_db();
$token = (string)($_GET['token'] ?? $_POST['token'] ?? '');
$who = find_password_reset($db, $token);
$error = '';
$done = false;

if ($who && $_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $pw = (string)($_POST['password'] ?? '');
    if ($pw !== (string)($_POST['confirm'] ?? '')) {
        $error = 'Konfirmasi password tidak sama.';
    } elseif ($e = validate_new_password($pw)) {
        $error = $e;
    } else {
        set_admin_password($db, (int)$who['id'], $pw);   // semua sesi lama akun ini keluar
        $db->prepare('DELETE FROM password_resets WHERE admin_id = ?')->execute([$who['id']]);
        $done = true;
    }
}
?>
<!DOCTYPE html>
<html lang="id">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Password Baru — Wintom Curtain</title>
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
      <?php if ($done): ?>
        <h1 class="font-playfair font-semibold text-dark text-[22px] mb-1">Password Diganti</h1>
        <p class="font-jakarta text-muted text-[13px] mb-6">Password baru sudah aktif. Silakan masuk.</p>
        <a href="login.php" class="w-full bg-brand text-white font-jakarta font-semibold text-[12px] tracking-[1.8px] uppercase py-3 rounded-[3px] hover:bg-[#6a041c] transition-colors duration-200 mt-2" style="display:block;text-align:center">MASUK</a>
      <?php elseif (!$who): ?>
        <h1 class="font-playfair font-semibold text-dark text-[22px] mb-1">Tautan Tidak Berlaku</h1>
        <p class="font-jakarta text-muted text-[13px] mb-6">Tautan ini sudah kedaluwarsa atau sudah dipakai. Minta tautan baru.</p>
        <a href="forgot-password.php" class="w-full bg-brand text-white font-jakarta font-semibold text-[12px] tracking-[1.8px] uppercase py-3 rounded-[3px] hover:bg-[#6a041c] transition-colors duration-200 mt-2" style="display:block;text-align:center">MINTA TAUTAN BARU</a>
      <?php else: ?>
        <h1 class="font-playfair font-semibold text-dark text-[22px] mb-1">Password Baru</h1>
        <p class="font-jakarta text-muted text-[13px] mb-6">Akun: <b><?= htmlspecialchars($who['username']) ?></b></p>
        <?php if ($error): ?>
          <div class="bg-red-50 border border-red-200 text-red-700 font-jakarta text-[13px] px-4 py-3 rounded-[4px] mb-5"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        <form method="POST" autocomplete="off" class="flex flex-col gap-4">
          <?= csrf_field() ?>
          <input type="hidden" name="token" value="<?= htmlspecialchars($token) ?>"/>
          <div>
            <label class="font-jakarta font-medium text-dark text-[12px] tracking-[0.6px] uppercase block mb-1.5">Password baru</label>
            <input type="password" name="password" required minlength="<?= PASSWORD_MIN_LENGTH ?>" maxlength="72" autocomplete="new-password" autofocus class="w-full border border-border rounded-[4px] px-3 py-2.5 font-jakarta text-dark text-[14px] focus:outline-none focus:border-brand transition-colors" />
            <p class="font-jakarta text-muted text-[11px] mt-1">Minimal <?= PASSWORD_MIN_LENGTH ?> karakter.</p>
          </div>
          <div>
            <label class="font-jakarta font-medium text-dark text-[12px] tracking-[0.6px] uppercase block mb-1.5">Ulangi password</label>
            <input type="password" name="confirm" required autocomplete="new-password" class="w-full border border-border rounded-[4px] px-3 py-2.5 font-jakarta text-dark text-[14px] focus:outline-none focus:border-brand transition-colors" />
          </div>
          <button type="submit" class="w-full bg-brand text-white font-jakarta font-semibold text-[12px] tracking-[1.8px] uppercase py-3 rounded-[3px] hover:bg-[#6a041c] transition-colors duration-200 mt-2">SIMPAN PASSWORD</button>
        </form>
      <?php endif; ?>
    </div>
    <p class="text-center font-jakarta text-muted text-[11px] mt-5">← <a href="login.php" class="hover:text-brand transition-colors">Kembali ke Login</a></p>
  </div>
</body>
</html>
