<?php
// admin/inquiries.php — Inquiry masuk dari form "Kontak Kami"
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/admin-sidebar.php';
require_once __DIR__ . '/../includes/admin-actions.php';
require_once __DIR__ . '/../includes/admin-table.php';
require_login();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
}

$db  = get_db();
$msg = '';
$err = '';

/* ACTION: UPDATE STATUS */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['_action'] ?? '') === 'status') {
    $id     = (int)($_POST['id'] ?? 0);
    $status = (string)($_POST['status'] ?? '');
    if ($id > 0 && isset(INQUIRY_STATUSES[$status])) {
        $db->prepare('UPDATE inquiries SET status = ? WHERE id = ?')->execute([$status, $id]);
        $msg = 'Status inquiry #' . $id . ' diubah menjadi "' . INQUIRY_STATUSES[$status][0] . '".';
    } else {
        $err = 'Status tidak valid.';
    }
}

/* ACTION: DELETE */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['_action'] ?? '') === 'delete') {
    $id = (int)($_POST['id'] ?? 0);
    if ($id > 0) {
        $db->prepare('DELETE FROM inquiries WHERE id = ?')->execute([$id]);
        $msg = 'Inquiry #' . $id . ' dihapus.';
    }
}

$filter = (string)($_GET['status'] ?? '');
if ($filter !== '' && !isset(INQUIRY_STATUSES[$filter])) {
    $filter = '';
}

$counts = array_fill_keys(array_keys(INQUIRY_STATUSES), 0);
foreach ($db->query('SELECT status, COUNT(*) c FROM inquiries GROUP BY status')->fetchAll() as $row) {
    $counts[$row['status']] = (int)$row['c'];
}
$total = array_sum($counts);

$pg     = admin_paginate($filter !== '' ? $counts[$filter] : $total);
$sql    = 'SELECT * FROM inquiries' . ($filter !== '' ? ' WHERE status = ?' : '')
        . ' ORDER BY created_at DESC, id DESC LIMIT ' . (int)$pg['per'] . ' OFFSET ' . (int)$pg['offset'];
$stmt   = $db->prepare($sql);
$stmt->execute($filter !== '' ? [$filter] : []);
$inquiries = $stmt->fetchAll();

admin_page_start('Inquiry', 'inquiries', '');
?>
<style>
  .st-select { appearance: none; -webkit-appearance: none; cursor: pointer; border-width: 1px; border-radius: 9999px; height: 32px; padding: 0 28px 0 26px;
    font: 600 12px/1 Arial, Helvetica, sans-serif; background: no-repeat right 10px center / 10px 6px url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='10' height='6' viewBox='0 0 10 6'%3E%3Cpath d='M1 1l4 4 4-4' fill='none' stroke='%23686561' stroke-width='1.6' stroke-linecap='round'/%3E%3C/svg%3E"); transition: box-shadow .15s; }
  .st-select:hover { box-shadow: 0 0 0 3px rgba(134,5,36,.08); }
  .st-select:focus-visible { outline: 2px solid #860524; outline-offset: 2px; }
  .st-select:disabled { opacity: .6; cursor: progress; }
</style>

  <div class="mb-6">
    <h1 class="font-playfair font-semibold text-dark text-[26px]">Inquiry</h1>
    <p class="font-jakarta text-muted text-[13px] mt-0.5">Dari form “Kontak Kami” — <?= $total ?> total<?php if ($counts['new']): ?>, <b class="text-amber-700"><?= $counts['new'] ?> belum dihubungi</b><?php endif; ?></p>
  </div>
  <?php admin_flash($msg, $err); ?>

  <!-- Filter tabs -->
  <div class="flex flex-wrap gap-2 mb-5">
    <?php
    $tabs = ['' => ['Semua', $total]];
    foreach (INQUIRY_STATUSES as $k => $v) { $tabs[$k] = [$v[0], $counts[$k]]; }
    foreach ($tabs as $k => [$label, $n]):
        $active = ($filter === (string)$k);
    ?>
    <a href="inquiries.php<?= $k !== '' ? '?status=' . $k : '' ?>"
       class="px-3.5 py-1.5 rounded-full border font-jakarta text-[12px] font-medium transition-colors <?= $active ? 'bg-brand text-white border-brand' : 'bg-white text-muted border-border hover:border-brand/40' ?>">
      <?= $label ?> <span class="opacity-70"><?= $n ?></span>
    </a>
    <?php endforeach; ?>
  </div>

  <div class="bg-white border border-border rounded-[6px] overflow-hidden">
   <div class="overflow-x-auto">
    <table class="w-full min-w-[980px]">
      <thead class="bg-cream-dark">
        <tr>
          <th class="px-4 py-3 text-left font-jakarta font-semibold text-muted text-[11px] tracking-[1px] uppercase w-36">Waktu</th>
          <th class="px-4 py-3 text-left font-jakarta font-semibold text-muted text-[11px] tracking-[1px] uppercase w-56">Pelanggan</th>
          <th class="px-4 py-3 text-left font-jakarta font-semibold text-muted text-[11px] tracking-[1px] uppercase w-60">Jenis &amp; Ukuran</th>
          <th class="px-4 py-3 text-left font-jakarta font-semibold text-muted text-[11px] tracking-[1px] uppercase min-w-[240px]">Kebutuhan</th>
          <th class="px-4 py-3 text-left font-jakarta font-semibold text-muted text-[11px] tracking-[1px] uppercase w-44">Status</th>
          <th class="px-4 py-3 text-right font-jakarta font-semibold text-muted text-[11px] tracking-[1px] uppercase w-36">Aksi</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($inquiries as $q):
            $is_new = $q['status'] === 'new';
            $reply = 'https://wa.me/' . $q['whatsapp'] . '?text=' . rawurlencode('Halo ' . $q['full_name'] . ', terima kasih sudah menghubungi Wintom Curtain. ');
            [, $st_class, $st_dot] = INQUIRY_STATUSES[$q['status']] ?? ['', 'bg-gray-50 text-gray-700 border-gray-200', '#9ca3af'];
            $notes = trim((string)$q['notes']);
        ?>
        <tr class="border-t border-border align-top hover:bg-cream/50 transition-colors">
          <td class="px-4 py-4 whitespace-nowrap <?= $is_new ? 'shadow-[inset_3px_0_0_#f59e0b]' : '' ?>">
            <p class="font-jakarta font-semibold text-dark text-[13px]"><?= htmlspecialchars(admin_date($q['created_at'])) ?></p>
            <p class="font-jakarta text-muted text-[12px] mt-0.5"><?= htmlspecialchars(date('H:i', strtotime($q['created_at']))) ?></p>
          </td>
          <td class="px-4 py-4">
            <p class="font-jakarta font-semibold text-dark text-[14px] leading-[18px]"><?= htmlspecialchars($q['full_name']) ?></p>
            <div class="mt-1"><?php admin_phone_copy((string)$q['whatsapp']); ?></div>
          </td>
          <td class="px-4 py-4 font-jakarta text-[12px]">
            <p class="font-medium text-dark text-[13px]"><?= htmlspecialchars($q['curtain_type']) ?></p>
            <p class="text-muted mt-0.5"><?= $q['estimated_size'] ? htmlspecialchars($q['estimated_size']) : '—' ?></p>
            <?php if (!empty($q['routed_whatsapp'])): ?>
            <p class="text-muted mt-1 text-[11px]">Dikirim ke <span class="text-dark">+<?= htmlspecialchars($q['routed_whatsapp']) ?></span><?= $q['routed_whatsapp'] === WA_NUMBER ? ' (nomor utama)' : '' ?></p>
            <?php endif; ?>
          </td>
          <td class="px-4 py-4 font-jakarta text-[13px] leading-[19px] text-dark whitespace-pre-line break-words max-w-[360px]"><?= $notes !== '' ? htmlspecialchars($notes) : '<span class="text-muted">—</span>' ?></td>
          <td class="px-4 py-4">
            <form method="POST" class="relative inline-block">
              <?= csrf_field() ?>
              <input type="hidden" name="_action" value="status">
              <input type="hidden" name="id" value="<?= (int)$q['id'] ?>">
              <span class="pointer-events-none absolute left-2.5 top-1/2 size-2 -translate-y-1/2 rounded-full" style="background:<?= $st_dot ?>"></span>
              <select name="status" aria-label="Status inquiry #<?= (int)$q['id'] ?>" onchange="this.form.submit();this.disabled=true" class="st-select <?= $st_class ?>">
                <?php foreach (INQUIRY_STATUSES as $k => [$lbl]): ?>
                <option value="<?= $k ?>" <?= $q['status'] === $k ? 'selected' : '' ?>><?= $lbl ?></option>
                <?php endforeach; ?>
              </select>
            </form>
          </td>
          <td class="px-4 py-4">
            <div class="flex items-center justify-end gap-1.5">
              <?php admin_btn_whatsapp($reply); ?>
              <?php admin_btn_delete('Hapus inquiry dari "' . $q['full_name'] . '"?', 'Data inquiry #' . (int)$q['id'] . ' akan dihapus permanen dan tidak bisa dikembalikan.', ['_action' => 'delete', 'id' => (int)$q['id']], 'Hapus inquiry'); ?>
            </div>
          </td>
        </tr>
        <?php endforeach; ?>
        <?php if (!$inquiries): ?>
          <?php admin_empty_row(6, 'Belum ada inquiry' . ($filter !== '' ? ' dengan status ini' : ''), $filter !== '' ? 'Coba pilih filter status lain.' : 'Inquiry dari form “Minta Penawaran” akan muncul di sini.'); ?>
        <?php endif; ?>
      </tbody>
    </table>
   </div>
   <?php admin_pagination($pg); ?>
  </div>

<?php admin_actions_assets(); ?>
<?php admin_page_end(); ?>
