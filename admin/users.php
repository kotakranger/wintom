<?php
// admin/users.php — Kelola pengguna (khusus Super Admin): tambah admin, atur ulang password admin, hapus admin.
// Super Admin tidak bisa dihapus, diubah perannya, atau di-reset oleh siapa pun; ia mengganti passwordnya sendiri di Akun Saya.
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/admin-modal.php';
require_once __DIR__ . '/../includes/admin-actions.php';
require_once __DIR__ . '/../includes/admin-sidebar.php';
require_once __DIR__ . '/../includes/admin-table.php';
require_super_admin();
if ($_SERVER['REQUEST_METHOD'] === 'POST') csrf_check();

$db  = get_db();
$msg = $err = '';
$reopen = null;
$form_username = '';

/** Admin biasa dengan id tertentu (Super Admin tidak pernah bisa dipilih). */
$find_admin = function (int $id) use ($db): ?array {
    $st = $db->prepare("SELECT id, username FROM admins WHERE id = ? AND role = ?");
    $st->execute([$id, ROLE_ADMIN]);
    return $st->fetch() ?: null;
};

$action = $_POST['_action'] ?? '';

if ($action === 'add') {
    $form_username = strtolower(trim((string)($_POST['username'] ?? '')));
    $pw = (string)($_POST['password'] ?? '');
    $exists = $db->prepare('SELECT 1 FROM admins WHERE username = ?');
    $exists->execute([$form_username]);
    if (!preg_match('/^[a-z0-9._-]{3,30}$/', $form_username)) {
        $err = 'Username 3–30 karakter: huruf kecil, angka, titik, garis bawah, atau strip.';
    } elseif ($exists->fetchColumn()) {
        $err = 'Username "' . $form_username . '" sudah dipakai.';
    } elseif ($e = validate_new_password($pw)) {
        $err = $e;
    } else {
        $db->prepare("INSERT INTO admins (username, password_hash, role) VALUES (?, ?, ?)")
           ->execute([$form_username, password_hash($pw, PASSWORD_DEFAULT), ROLE_ADMIN]);
        $msg = 'Admin "' . $form_username . '" ditambahkan. Berikan username dan password-nya kepada yang bersangkutan.';
        $form_username = '';
    }
    if ($err) $reopen = 'add-user';
}

if ($action === 'set_password') {
    $id = (int)($_POST['user_id'] ?? 0);
    $pw = (string)($_POST['password'] ?? '');
    $target = $find_admin($id);
    if (!$target) {
        $err = 'Pengguna tidak ditemukan, atau Super Admin tidak bisa diubah dari sini.';
    } elseif ($e = validate_new_password($pw)) {
        $err = $e; $reopen = 'pw-' . $id;
    } else {
        set_admin_password($db, $id, $pw);   // sesi lama admin tersebut otomatis keluar
        $msg = 'Password "' . $target['username'] . '" diganti. Berikan password baru kepada yang bersangkutan.';
    }
}

if ($action === 'delete') {
    $target = $find_admin((int)($_POST['user_id'] ?? 0));
    if (!$target) {
        $err = 'Pengguna tidak ditemukan, atau Super Admin tidak bisa dihapus.';
    } else {
        $db->prepare("DELETE FROM admins WHERE id = ? AND role = ?")->execute([$target['id'], ROLE_ADMIN]);
        $msg = 'Admin "' . $target['username'] . '" dihapus.';
    }
}

$pg = admin_paginate((int)$db->query('SELECT COUNT(*) FROM admins')->fetchColumn());
$users = $db->query("SELECT id, username, role, created_at FROM admins ORDER BY (role = '" . ROLE_SUPER . "') DESC, username ASC LIMIT " . (int)$pg['per'] . " OFFSET " . (int)$pg['offset'])->fetchAll();

/** Isian password + tombol "Buat acak" (JS kecil di bawah). */
function user_pw_field(string $id): void { ?>
  <label class="label" for="<?= $id ?>">Password</label>
  <div class="flex gap-2">
    <input id="<?= $id ?>" type="text" name="password" class="input font-mono" required minlength="<?= PASSWORD_MIN_LENGTH ?>" maxlength="72" autocomplete="off" spellcheck="false"/>
    <button type="button" data-gen-pw="<?= $id ?>" class="shrink-0 h-[38px] px-3 rounded-[4px] border border-border bg-white font-jakarta font-semibold text-[12px] text-dark hover:bg-cream transition-colors">Buat acak</button>
  </div>
  <p class="font-jakarta text-muted text-[11px] mt-1">Minimal <?= PASSWORD_MIN_LENGTH ?> karakter. Catat atau kirim password ini ke yang bersangkutan — setelah disimpan tidak bisa dilihat lagi.</p>
<?php }

admin_page_start('Pengguna', 'users');
?>
  <div class="mb-6 flex items-center justify-between gap-4">
    <div>
      <h1 class="font-playfair font-semibold text-dark text-[26px]">Pengguna</h1>
      <p class="font-jakarta text-muted text-[13px] mt-0.5"><?= $pg['total'] ?> akun · hanya Super Admin yang bisa menambah, menghapus, dan mengatur ulang password Admin.</p>
    </div>
    <button type="button" data-modal-open="add-user" class="bg-brand text-white font-jakarta font-semibold text-[12px] tracking-[1.5px] uppercase px-5 py-3 rounded-[4px] hover:bg-[#6a041c] transition-colors">+ Tambah Admin</button>
  </div>
  <?php admin_flash($msg, $err); ?>

  <div class="bg-white border border-border rounded-[6px] overflow-hidden">
    <table class="w-full">
      <thead class="bg-cream-dark"><tr>
        <th class="px-4 py-3 text-left font-jakarta font-semibold text-muted text-[11px] tracking-[1px] uppercase">Username</th>
        <th class="px-4 py-3 text-left font-jakarta font-semibold text-muted text-[11px] tracking-[1px] uppercase">Peran</th>
        <th class="px-4 py-3 text-left font-jakarta font-semibold text-muted text-[11px] tracking-[1px] uppercase hidden md:table-cell">Dibuat</th>
        <th class="px-4 py-3 text-right font-jakarta font-semibold text-muted text-[11px] tracking-[1px] uppercase">Aksi</th>
      </tr></thead>
      <tbody>
      <?php foreach ($users as $u): $is_super = $u['role'] === ROLE_SUPER; ?>
        <tr class="border-t border-border">
          <td class="px-4 py-3 font-jakarta font-semibold text-dark text-[13px]"><?= htmlspecialchars($u['username']) ?></td>
          <td class="px-4 py-3">
            <span class="inline-block font-jakarta text-[11px] px-2.5 py-1 rounded-full border <?= $is_super ? 'bg-brand/10 text-brand border-brand/20' : 'bg-cream-dark text-muted border-border' ?>"><?= admin_role_label($u['role']) ?></span>
          </td>
          <td class="px-4 py-3 font-jakarta text-muted text-[12px] hidden md:table-cell"><?= htmlspecialchars(admin_date($u['created_at'])) ?></td>
          <td class="px-4 py-3">
            <div class="flex items-center justify-end gap-1.5">
              <?php if ($is_super): ?>
                <span class="font-jakarta text-[11px] text-muted italic mr-1">Tidak bisa dihapus · ganti password di Akun Saya</span>
                <span class="adm-act adm-act-icon !cursor-default hover:!bg-white hover:!border-border" data-tip="Super Admin dilindungi" aria-label="Super Admin dilindungi">
                  <svg class="size-[17px]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="5" y="11" width="14" height="9" rx="2"/><path d="M8 11V8a4 4 0 118 0v3"/></svg>
                </span>
              <?php else: ?>
                <button type="button" data-modal-open="pw-<?= (int)$u['id'] ?>" class="adm-act adm-act-edit" data-tip="Atur ulang password" aria-label="Atur ulang password">
                  <svg class="size-[17px]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M15.75 5.25a3 3 0 013 3m3 0a6 6 0 01-7.029 5.912c-.563-.097-1.159.026-1.563.43L10.5 17.25H8.25v2.25H6v2.25H2.25v-2.818c0-.597.237-1.17.659-1.591l6.499-6.499c.404-.404.527-1 .43-1.563A6 6 0 1121.75 8.25z"/></svg>
                  <span>Password</span>
                </button>
                <?php admin_btn_delete('Hapus admin "' . $u['username'] . '"?', 'Akun ini tidak bisa masuk lagi dan langsung dikeluarkan dari semua perangkat. Tindakan ini tidak bisa dibatalkan.', ['_action' => 'delete', 'user_id' => (int)$u['id']], 'Hapus admin'); ?>
              <?php endif; ?>
            </div>
          </td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
    <?php admin_pagination($pg); ?>
  </div>
  <p class="font-jakarta text-muted text-[12px] mt-4 leading-[18px]">Admin dan Super Admin punya akses fitur yang sama. Bedanya, hanya Super Admin yang bisa membuka halaman ini, dan akun Super Admin tidak bisa dihapus atau diubah perannya.</p>

  <?php admin_modal_start('add-user', 'Tambah Admin'); ?>
    <form method="POST" class="flex flex-col gap-4" autocomplete="off">
      <?= csrf_field() ?>
      <input type="hidden" name="_action" value="add"/>
      <div>
        <label class="label" for="new-username">Username</label>
        <input id="new-username" type="text" name="username" class="input" required minlength="3" maxlength="30" pattern="[a-z0-9._\-]{3,30}"
               value="<?= htmlspecialchars($form_username) ?>" autocomplete="off" autocapitalize="none" spellcheck="false"/>
        <p class="font-jakarta text-muted text-[11px] mt-1">Huruf kecil, angka, titik, garis bawah, atau strip. Contoh: <b>rina</b></p>
      </div>
      <div><?php user_pw_field('new-password'); ?></div>
      <button type="submit" class="self-start bg-brand text-white font-jakarta font-semibold text-[12px] tracking-[1.5px] uppercase px-5 py-3 rounded-[4px] hover:bg-[#6a041c] transition-colors">Simpan</button>
    </form>
  <?php admin_modal_end(); ?>

  <?php foreach ($users as $u): if ($u['role'] === ROLE_SUPER) continue; ?>
  <?php admin_modal_start('pw-' . (int)$u['id'], 'Atur Ulang Password — ' . $u['username']); ?>
    <form method="POST" class="flex flex-col gap-4" autocomplete="off">
      <?= csrf_field() ?>
      <input type="hidden" name="_action" value="set_password"/>
      <input type="hidden" name="user_id" value="<?= (int)$u['id'] ?>"/>
      <div><?php user_pw_field('pw-input-' . (int)$u['id']); ?></div>
      <p class="font-jakarta text-muted text-[12px] leading-[18px]">Admin ini akan dikeluarkan dari semua perangkat dan harus masuk lagi dengan password baru.</p>
      <button type="submit" class="self-start bg-brand text-white font-jakarta font-semibold text-[12px] tracking-[1.5px] uppercase px-5 py-3 rounded-[4px] hover:bg-[#6a041c] transition-colors">Simpan Password</button>
    </form>
  <?php admin_modal_end(); ?>
  <?php endforeach; ?>

<?php admin_modal_script($reopen); ?>
<?php admin_actions_assets(); ?>
<script>
  // Password acak 12 karakter (tanpa huruf/angka yang mirip: 0/O, 1/l/I)
  document.addEventListener('click', function (e) {
    var b = e.target.closest('[data-gen-pw]'); if (!b) return;
    var chars = 'abcdefghjkmnpqrstuvwxyzABCDEFGHJKLMNPQRSTUVWXYZ23456789', out = '', r = new Uint32Array(12);
    crypto.getRandomValues(r);
    for (var i = 0; i < 12; i++) out += chars[r[i] % chars.length];
    var f = document.getElementById(b.dataset.genPw); f.value = out; f.focus(); f.select();
  });
</script>
<?php admin_page_end(); ?>
