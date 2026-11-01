<?php
// admin/account.php — Akun Saya: setiap pengguna (Admin & Super Admin) mengganti password sendiri.
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/admin-sidebar.php';
require_login();
if ($_SERVER['REQUEST_METHOD'] === 'POST') csrf_check();

$db  = get_db();
$me  = current_admin();
$msg = $err = '';

if (($_POST['_action'] ?? '') === 'change_password') {
    $current = (string)($_POST['current_password'] ?? '');
    $new     = (string)($_POST['new_password'] ?? '');
    $confirm = (string)($_POST['confirm_password'] ?? '');

    $st = $db->prepare('SELECT password_hash FROM admins WHERE id = ?');
    $st->execute([$me['id']]);
    if (!password_verify($current, (string)$st->fetchColumn())) {
        $err = 'Password saat ini salah.';
    } elseif ($new !== $confirm) {
        $err = 'Konfirmasi password baru tidak sama.';
    } elseif ($new === $current) {
        $err = 'Password baru harus berbeda dari yang lama.';
    } elseif ($e = validate_new_password($new)) {
        $err = $e;
    } else {
        set_admin_password($db, (int)$me['id'], $new);   // perangkat lain otomatis keluar
        $sv = $db->prepare('SELECT session_version FROM admins WHERE id = ?');
        $sv->execute([$me['id']]);
        session_regenerate_id(true);
        $_SESSION['admin_sv'] = (int)$sv->fetchColumn();   // sesi ini tetap masuk
        unset($_SESSION['weak_pw']);
        current_admin(true);
        $msg = 'Password berhasil diganti. Perangkat lain yang sedang masuk dengan akun ini sudah dikeluarkan.';
    }
}

admin_page_start('Akun Saya', 'account', 'max-w-[560px]');
?>
  <div class="mb-6">
    <h1 class="font-playfair font-semibold text-dark text-[26px]">Akun Saya</h1>
    <p class="font-jakarta text-muted text-[13px] mt-0.5">Kelola password untuk akun yang sedang masuk.</p>
  </div>
  <?php admin_flash($msg, $err); ?>

  <div class="bg-white border border-border rounded-[6px] p-6 mb-5 flex items-center gap-4">
    <span class="flex size-12 shrink-0 items-center justify-center rounded-full bg-brand text-white font-jakarta font-semibold text-[18px] uppercase"><?= htmlspecialchars(mb_substr($me['username'], 0, 1)) ?></span>
    <div>
      <p class="font-jakarta font-semibold text-dark text-[15px]"><?= htmlspecialchars($me['username']) ?></p>
      <p class="font-jakarta text-muted text-[12px]"><?= admin_role_label($me['role']) ?></p>
    </div>
  </div>

  <form method="POST" class="bg-white border border-border rounded-[6px] p-6 flex flex-col gap-4" autocomplete="off">
    <?= csrf_field() ?>
    <input type="hidden" name="_action" value="change_password"/>
    <h2 class="font-jakarta font-semibold text-dark text-[14px] border-b border-border pb-3">Ganti Password</h2>
    <div>
      <label class="label" for="cur">Password saat ini</label>
      <input id="cur" type="password" name="current_password" class="input pw" required autocomplete="current-password"/>
    </div>
    <div>
      <label class="label" for="new">Password baru</label>
      <input id="new" type="password" name="new_password" class="input pw" required minlength="<?= PASSWORD_MIN_LENGTH ?>" maxlength="72" autocomplete="new-password"/>
      <p class="font-jakarta text-muted text-[11px] mt-1">Minimal <?= PASSWORD_MIN_LENGTH ?> karakter.</p>
    </div>
    <div>
      <label class="label" for="conf">Ulangi password baru</label>
      <input id="conf" type="password" name="confirm_password" class="input pw" required autocomplete="new-password"/>
    </div>
    <label class="flex items-center gap-2 font-jakarta text-[12px] text-muted cursor-pointer select-none">
      <input type="checkbox" class="accent-[#860524]" onchange="document.querySelectorAll('.pw').forEach(function(i){i.type=this.checked?'text':'password'}.bind(this))"/> Tampilkan password
    </label>
    <button type="submit" class="self-start bg-brand text-white font-jakarta font-semibold text-[12px] tracking-[1.5px] uppercase px-5 py-3 rounded-[4px] hover:bg-[#6a041c] transition-colors">Simpan Password</button>
  </form>

  <?php if (!is_super_admin()): ?>
  <p class="font-jakarta text-muted text-[12px] mt-4 leading-[18px]">Lupa password? Minta Super Admin untuk mengatur ulang lewat menu <b>Pengguna</b>.</p>
  <?php endif; ?>
<?php admin_page_end(); ?>
