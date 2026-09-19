<?php
// admin/login.php — Admin Login Wintom Curtain
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';

start_session();
if (is_logged_in()) { header('Location: ' . BASE_URL . '/admin/index.php'); exit; }

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    if ($username && $password) {
        $db = get_db();
        $stmt = $db->prepare("SELECT id, password_hash FROM admins WHERE username = ?");
        $stmt->execute([$username]);
        $admin = $stmt->fetch();
        if ($admin && password_verify($password, $admin['password_hash'])) {
            login_admin((int)$admin['id']);
            header('Location: ' . BASE_URL . '/admin/index.php');
            exit;
        }
    }
    $error = 'Username atau password salah.';
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8" /><meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Admin Login — Wintom Curtain</title>
  <link rel="preconnect" href="https://fonts.googleapis.com" /><link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;600;700&family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet" />
  <script src="https://cdn.tailwindcss.com"></script>
  <script>tailwind.config={theme:{extend:{fontFamily:{playfair:['"Playfair Display"','serif'],jakarta:['"Plus Jakarta Sans"','sans-serif']},colors:{brand:{DEFAULT:'#741122',dark:'#5a0d1a'},dark:'#222220',muted:'#686561',cream:'#faf8f5','cream-dark':'#f4f1ea',border:'#ece8e1'}}}}</script>
  <style>*{box-sizing:border-box;}body{background-color:#f5f3f0;}</style>
</head>
<body class="font-jakarta text-dark antialiased min-h-screen flex items-center justify-center px-4">
  <div class="w-full max-w-[400px]">
    <!-- Logo -->
    <div class="text-center mb-8">
      <a href="../" class="inline-flex flex-col items-center">
        <span class="font-playfair font-bold text-brand text-[32px] leading-[38px] tracking-[-0.8px]">wintom</span>
        <span class="font-jakarta font-semibold text-dark text-[9px] tracking-[3.15px] uppercase">CURTAIN — ADMIN</span>
      </a>
    </div>
    <!-- Login Card -->
    <div class="bg-white border border-border rounded-[6px] shadow-sm p-8">
      <h1 class="font-playfair font-semibold text-dark text-[22px] mb-1">Admin Login</h1>
      <p class="font-jakarta text-muted text-[13px] mb-6">Masuk ke panel manajemen Wintom Curtain.</p>
      <?php if ($error): ?>
      <div class="bg-red-50 border border-red-200 text-red-700 font-jakarta text-[13px] px-4 py-3 rounded-[4px] mb-5">
        <?= htmlspecialchars($error) ?>
      </div>
      <?php endif; ?>
      <form method="POST" autocomplete="off" class="flex flex-col gap-4">
        <div>
          <label class="font-jakarta font-medium text-dark text-[12px] tracking-[0.6px] uppercase block mb-1.5">Username</label>
          <input type="text" name="username" id="username" required autofocus
                 class="w-full border border-border rounded-[4px] px-3 py-2.5 font-jakarta text-dark text-[14px] focus:outline-none focus:border-brand transition-colors"
                 placeholder="admin" />
        </div>
        <div>
          <label class="font-jakarta font-medium text-dark text-[12px] tracking-[0.6px] uppercase block mb-1.5">Password</label>
          <input type="password" name="password" id="password" required
                 class="w-full border border-border rounded-[4px] px-3 py-2.5 font-jakarta text-dark text-[14px] focus:outline-none focus:border-brand transition-colors"
                 placeholder="••••••••" />
        </div>
        <button type="submit"
                class="w-full bg-brand text-white font-jakarta font-semibold text-[12px] tracking-[1.8px] uppercase py-3 rounded-[3px] hover:bg-[#5a0d1a] transition-colors duration-200 mt-2">
          MASUK
        </button>
      </form>
      <p class="font-jakarta text-muted text-[11px] text-center mt-5">Default: <strong>admin</strong> / <strong>wintom2024</strong></p>
    </div>
    <p class="text-center font-jakarta text-muted text-[11px] mt-5">← <a href="../" class="hover:text-brand transition-colors">Kembali ke Website</a></p>
  </div>
</body>
</html>
