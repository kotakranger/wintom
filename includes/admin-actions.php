<?php
// includes/admin-actions.php — Tombol aksi baris tabel admin (Edit / Lihat / Hapus) + dialog konfirmasi hapus.
//
// Pemakaian di sel aksi (admin_btn_* dipanggil sebagai fungsi PHP biasa):
//   admin_btn_edit(['href' => 'products.php?edit=1']);      // atau ['modal' => 'edit-cat-1']
//   admin_btn_view($url);
//   admin_btn_delete('Hapus "Nama"?', 'Penjelasan akibatnya.', ['_action' => 'delete', 'product_id' => 1]);
// Sekali per halaman, sebelum penutup body: admin_actions_assets();
// Dialog konfirmasi bekerja untuk semua form dengan atribut data-confirm-title (dan data-confirm-text).

const ADMIN_ICONS = [
    'edit'  => '<path d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0115.75 21H5.25A2.25 2.25 0 013 18.75V8.25A2.25 2.25 0 015.25 6H10"/>',
    'view'  => '<path d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z"/><path d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>',
    'trash' => '<path d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0"/>',
];

function admin_icon(string $name, string $class = 'size-[17px]'): string {
    return '<svg class="' . $class . '" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">'
         . ADMIN_ICONS[$name] . '</svg>';
}

/** Edit: teks + ikon (aksi utama). $o = ['href' => url] atau ['modal' => id modal]. */
function admin_btn_edit(array $o, string $label = 'Edit'): void {
    $cls = 'adm-act adm-act-edit';
    $inner = admin_icon('edit') . '<span>' . htmlspecialchars($label) . '</span>';
    if (isset($o['href'])) {
        echo '<a href="' . htmlspecialchars($o['href']) . '" class="' . $cls . '" data-tip="Ubah data" aria-label="' . htmlspecialchars($label) . '">' . $inner . '</a>';
    } else {
        echo '<button type="button" data-modal-open="' . htmlspecialchars($o['modal']) . '" class="' . $cls . '" data-tip="Ubah data" aria-label="' . htmlspecialchars($label) . '">' . $inner . '</button>';
    }
}

/** Lihat di situs (tab baru): hanya ikon + tooltip. */
function admin_btn_view(string $url, string $tip = 'Lihat di situs'): void {
    echo '<a href="' . htmlspecialchars($url) . '" target="_blank" rel="noopener" class="adm-act adm-act-icon" data-tip="' . htmlspecialchars($tip) . '" aria-label="' . htmlspecialchars($tip) . '">'
       . admin_icon('view') . '</a>';
}

/** Hapus: form POST + dialog konfirmasi. $hidden = field tersembunyi (mis. _action, product_id). */
function admin_btn_delete(string $title, string $text, array $hidden, string $tip = 'Hapus'): void {
    echo '<form method="POST" class="m-0 inline-flex" data-confirm-title="' . htmlspecialchars($title) . '" data-confirm-text="' . htmlspecialchars($text) . '">';
    echo csrf_field();
    foreach ($hidden as $k => $v) echo '<input type="hidden" name="' . htmlspecialchars((string)$k) . '" value="' . htmlspecialchars((string)$v) . '"/>';
    echo '<button type="submit" class="adm-act adm-act-icon adm-act-danger" data-tip="' . htmlspecialchars($tip) . '" aria-label="' . htmlspecialchars($tip) . '">'
       . admin_icon('trash') . '</button></form>';
}

/** CSS tombol + dialog konfirmasi + skripnya. Panggil sekali per halaman. */
function admin_actions_assets(): void {
    ?>
<style>
  .adm-act { position: relative; display: inline-flex; align-items: center; justify-content: center; gap: 6px; height: 34px; min-width: 34px;
    border: 1px solid #ece8e1; border-radius: 6px; background: #fff; color: #686561; font: 600 12px/1 Arial, Helvetica, sans-serif; cursor: pointer;
    transition: background-color .15s, color .15s, border-color .15s, box-shadow .15s; text-decoration: none; white-space: nowrap; }
  .adm-act-edit { padding: 0 12px; color: #860524; border-color: rgba(134,5,36,.28); }
  .adm-act-edit:hover { background: #860524; color: #fff; border-color: #860524; }
  .adm-act-icon:hover { color: #242424; border-color: rgba(36,36,36,.35); background: #faf8f5; }
  .adm-act-danger { color: #d4574f; border-color: #f4cfcc; }
  .adm-act-danger:hover { color: #fff; background: #d4574f; border-color: #d4574f; }
  .adm-act:focus-visible { outline: 2px solid #860524; outline-offset: 2px; }
  .adm-act:active { transform: translateY(1px); }
  /* Tooltip singkat saat hover/fokus */
  .adm-act[data-tip]::after { content: attr(data-tip); position: absolute; bottom: calc(100% + 6px); left: 50%; transform: translate(-50%, 3px);
    background: #242424; color: #fff; font: 500 11px/1 Arial, Helvetica, sans-serif; padding: 6px 8px; border-radius: 4px; white-space: nowrap;
    opacity: 0; pointer-events: none; transition: opacity .12s, transform .12s; z-index: 30; }
  .adm-act[data-tip]:hover::after, .adm-act[data-tip]:focus-visible::after { opacity: 1; transform: translate(-50%, 0); transition-delay: .25s; }
  @media (hover: none) { .adm-act[data-tip]::after { display: none; } }
  #adm-confirm [data-confirm-panel] { animation: adm-pop .18s cubic-bezier(.2,.7,.2,1) both; }
  @keyframes adm-pop { from { opacity: 0; transform: translateY(8px) scale(.97); } to { opacity: 1; transform: none; } }
</style>

<div id="adm-confirm" class="fixed inset-0 z-[70] hidden items-center justify-center p-4" role="alertdialog" aria-modal="true" aria-labelledby="adm-confirm-title" aria-describedby="adm-confirm-text">
  <div data-confirm-cancel class="absolute inset-0 bg-[rgba(24,22,20,0.55)] backdrop-blur-[2px]"></div>
  <div data-confirm-panel class="relative w-full max-w-[400px] bg-white rounded-[8px] shadow-2xl p-6 text-center">
    <div class="mx-auto mb-4 flex size-12 items-center justify-center rounded-full bg-red-50 text-red-500"><?= admin_icon('trash', 'size-6') ?></div>
    <h2 id="adm-confirm-title" class="font-jakarta font-semibold text-dark text-[16px] leading-[22px]"></h2>
    <p id="adm-confirm-text" class="font-jakarta text-muted text-[13px] leading-[20px] mt-1.5"></p>
    <div class="mt-6 flex gap-3">
      <button type="button" data-confirm-cancel class="flex-1 h-10 rounded-[6px] border border-border font-jakarta font-semibold text-[13px] text-dark hover:bg-cream transition-colors">Batal</button>
      <button type="button" data-confirm-ok class="flex-1 h-10 rounded-[6px] bg-red-500 font-jakarta font-semibold text-[13px] text-white hover:bg-red-600 transition-colors">Ya, hapus</button>
    </div>
  </div>
</div>
<script>
(function () {
  var box = document.getElementById('adm-confirm'), pending = null, lastFocus = null;
  function close() { box.classList.add('hidden'); box.classList.remove('flex'); document.documentElement.style.overflow = ''; pending = null; if (lastFocus && lastFocus.focus) lastFocus.focus(); }
  document.addEventListener('submit', function (e) {
    var f = e.target;
    if (!f.matches || !f.matches('form[data-confirm-title]') || f.dataset.confirmed) return;
    e.preventDefault();
    pending = f; lastFocus = document.activeElement;
    document.getElementById('adm-confirm-title').textContent = f.dataset.confirmTitle;
    var t = document.getElementById('adm-confirm-text'); t.textContent = f.dataset.confirmText || ''; t.hidden = !f.dataset.confirmText;
    box.classList.remove('hidden'); box.classList.add('flex'); document.documentElement.style.overflow = 'hidden';
    box.querySelector('[data-confirm-cancel].flex-1').focus();
  });
  box.addEventListener('click', function (e) {
    if (e.target.closest('[data-confirm-cancel]')) close();
    else if (e.target.closest('[data-confirm-ok]') && pending) {
      var f = pending; f.dataset.confirmed = '1'; box.querySelector('[data-confirm-ok]').disabled = true;
      if (f.requestSubmit) f.requestSubmit(); else f.submit();
    }
  });
  document.addEventListener('keydown', function (e) { if (e.key === 'Escape' && pending) close(); });
})();
</script>
    <?php
}
