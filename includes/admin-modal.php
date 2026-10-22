<?php
// includes/admin-modal.php — Modal overlay sederhana untuk halaman admin.
// Pemakaian:
//   tombol pemicu : <button data-modal-open="add-cat">
//   isi modal     : admin_modal_start('add-cat', 'Tambah Kategori'); …form… admin_modal_end();
//   sekali/halaman: admin_modal_script($id_yang_dibuka_otomatis_atau_null) — mis. saat validasi gagal

function admin_modal_start(string $id, string $title, string $width = 'max-w-[480px]'): void
{
    ?>
    <div id="<?= htmlspecialchars($id) ?>" data-modal class="fixed inset-0 z-50 hidden items-center justify-center p-4" role="dialog" aria-modal="true" aria-labelledby="<?= htmlspecialchars($id) ?>-title">
      <div data-modal-close class="absolute inset-0 bg-[rgba(24,22,20,0.55)] backdrop-blur-[2px]"></div>
      <div class="relative w-full <?= $width ?> max-h-[90vh] overflow-y-auto bg-white rounded-[6px] shadow-2xl">
        <div class="sticky top-0 z-10 bg-white flex items-center justify-between gap-4 px-6 py-4 border-b border-border">
          <h2 id="<?= htmlspecialchars($id) ?>-title" class="font-jakarta font-semibold text-dark text-[15px]"><?= htmlspecialchars($title) ?></h2>
          <button type="button" data-modal-close aria-label="Tutup"
                  class="size-8 -mr-2 flex items-center justify-center rounded-full text-muted hover:text-dark hover:bg-cream transition-colors">
            <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"><path d="M6 6l12 12M18 6 6 18"/></svg>
          </button>
        </div>
        <div class="p-6">
    <?php
}

function admin_modal_end(): void
{
    echo "        </div>\n      </div>\n    </div>\n";
}

function admin_modal_script(?string $auto_open = null): void
{
    ?>
    <script>
    (function () {
      let last = null;
      function open(m) {
        last = document.activeElement;
        m.classList.remove('hidden'); m.classList.add('flex');
        document.documentElement.style.overflow = 'hidden';
        const f = m.querySelector('input:not([type=hidden]), select, textarea');
        if (f) setTimeout(function () { f.focus(); }, 30);
      }
      function close(m) {
        m.classList.add('hidden'); m.classList.remove('flex');
        document.documentElement.style.overflow = '';
        if (last && last.focus) last.focus();
      }
      document.addEventListener('click', function (e) {
        const o = e.target.closest('[data-modal-open]');
        if (o) { const m = document.getElementById(o.dataset.modalOpen); if (m) open(m); return; }
        const c = e.target.closest('[data-modal-close]');
        if (c) close(c.closest('[data-modal]'));
      });
      document.addEventListener('keydown', function (e) {
        if (e.key !== 'Escape') return;
        document.querySelectorAll('[data-modal].flex').forEach(close);
      });
      <?php if ($auto_open): ?>
      const auto = document.getElementById(<?= json_encode($auto_open) ?>);
      if (auto) open(auto);
      <?php endif; ?>
    })();
    </script>
    <?php
}
