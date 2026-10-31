<?php
// includes/inquiry-form.php — Form inquiry "Get a Quote" (dipakai inline di homepage & di modal global).
// Perilaku submit (validasi, fetch ke inquiry.php, redirect WhatsApp) ada di includes/inquiry-modal.php.
require_once __DIR__ . '/auth.php';

/**
 * @param string $prefix     Prefix id unik per instance form (mis. 'home', 'modal')
 * @param array  $categories Baris kategori [id, name]
 * @param bool   $compact    true = tanpa judul (judul disediakan oleh modal)
 */
function render_inquiry_form(string $prefix, array $categories, bool $compact = false): void
{
    $id = fn(string $f) => htmlspecialchars($prefix . '-' . $f);
    $input = 'w-full border border-border rounded-[3px] bg-cream px-4 py-3 font-jakarta text-[16px] sm:text-[14px] text-dark focus:outline-none focus:border-brand transition-colors';
    $input_sm = str_replace('px-4', 'px-3', $input);
    $label = 'block font-jakarta font-semibold text-muted text-[10px] tracking-[1.5px] uppercase mb-2';
    $opt   = '<span class="normal-case tracking-normal font-normal">(opsional)</span>';
    $error = fn(string $f) => '<p class="field-error hidden mt-1.5 font-jakarta text-[12px] text-red-600" data-for="' . $f . '"></p>';
    ?>
    <form action="<?= BASE_URL ?>/inquiry.php" method="POST" novalidate data-inquiry-form
          class="relative <?= $compact ? '' : 'p-5 sm:p-7 bg-white border border-border rounded-[6px] shadow-sm' ?>">
      <?php if (!$compact): ?>
      <h3 class="font-playfair font-normal text-dark text-[22px] sm:text-[24px] leading-[1.25] mb-1">Dapatkan Penawaran Gratis</h3>
      <p class="font-jakarta text-muted text-[13px] leading-[20px] mb-5">Isi detail singkat di bawah. Data Anda kami simpan, lalu Anda langsung terhubung dengan tim kami di WhatsApp.</p>
      <?php endif; ?>

      <?= csrf_field() ?>
      <!-- Honeypot anti-spam: biarkan kosong -->
      <div class="absolute -left-[9999px]" aria-hidden="true">
        <label>Website <input type="text" name="website" tabindex="-1" autocomplete="off"></label>
      </div>

      <div class="grid grid-cols-1 sm:grid-cols-2 gap-x-4 gap-y-4">
        <div>
          <label for="<?= $id('name') ?>" class="<?= $label ?>">Nama Lengkap <span class="text-brand">*</span></label>
          <input id="<?= $id('name') ?>" name="full_name" type="text" required maxlength="120" autocomplete="name"
                 class="<?= $input ?>" placeholder="Contoh: Budi Santoso">
          <?= $error('full_name') ?>
        </div>

        <div>
          <label for="<?= $id('wa') ?>" class="<?= $label ?>">Nomor WhatsApp <span class="text-brand">*</span></label>
          <input id="<?= $id('wa') ?>" name="whatsapp" type="tel" inputmode="tel" required maxlength="25" autocomplete="tel"
                 class="<?= $input ?>" placeholder="Contoh: 0812 3456 7890">
          <?= $error('whatsapp') ?>
        </div>

        <div class="sm:col-span-2">
          <label for="<?= $id('type') ?>" class="<?= $label ?>">Jenis Produk <span class="text-brand">*</span></label>
          <select id="<?= $id('type') ?>" name="category_id" required class="<?= $input ?>">
            <option value="">— Pilih jenis produk —</option>
            <?php foreach ($categories as $c): ?>
              <option value="<?= (int)$c['id'] ?>"><?= htmlspecialchars($c['name']) ?></option>
            <?php endforeach; ?>
          </select>
          <?= $error('category_id') ?>
        </div>

        <fieldset class="sm:col-span-2 min-w-0">
          <legend class="<?= $label ?>">Ukuran <?= $opt ?></legend>
          <div class="grid grid-cols-2 sm:grid-cols-3 gap-3">
            <div class="min-w-0">
              <label for="<?= $id('w') ?>" class="block font-jakarta text-muted text-[11px] mb-1">Lebar (cm)</label>
              <input id="<?= $id('w') ?>" name="width_cm" type="number" inputmode="decimal" min="1" max="10000" step="0.1"
                     class="<?= $input_sm ?>" placeholder="Contoh: 150">
            </div>
            <div class="min-w-0">
              <label for="<?= $id('h') ?>" class="block font-jakarta text-muted text-[11px] mb-1">Tinggi (cm)</label>
              <input id="<?= $id('h') ?>" name="height_cm" type="number" inputmode="decimal" min="1" max="10000" step="0.1"
                     class="<?= $input_sm ?>" placeholder="Contoh: 200">
            </div>
            <div class="min-w-0 col-span-2 sm:col-span-1">
              <label for="<?= $id('q') ?>" class="block font-jakarta text-muted text-[11px] mb-1">Jumlah / Set</label>
              <input id="<?= $id('q') ?>" name="quantity" type="number" inputmode="numeric" min="1" max="999" step="1" value="1"
                     class="<?= $input_sm ?>">
            </div>
          </div>
          <?= $error('width_cm') ?>
          <?= $error('height_cm') ?>
          <?= $error('quantity') ?>
        </fieldset>

        <div class="sm:col-span-2">
          <label for="<?= $id('notes') ?>" class="<?= $label ?>">Catatan Tambahan <?= $opt ?></label>
          <textarea id="<?= $id('notes') ?>" name="notes" rows="3" maxlength="1000"
                    class="<?= $input ?> resize-y"
                    placeholder="Contoh: kamar tidur utama, warna abu-abu, perlu blackout atau motorized"></textarea>
          <?= $error('notes') ?>
        </div>
      </div>

      <div data-inquiry-alert role="alert" class="hidden mt-5 rounded-[3px] border px-4 py-3 font-jakarta text-[13px]"></div>

      <button type="submit" data-inquiry-submit
              class="mt-6 w-full inline-flex items-center justify-center gap-2 bg-brand text-white font-semibold text-[12px] tracking-[1.8px] uppercase px-7 py-[15px] rounded-[2px] hover:bg-[#6a041c] transition-colors duration-200 disabled:opacity-70 disabled:cursor-not-allowed">
        <svg data-inquiry-spinner class="hidden size-4 animate-spin" viewBox="0 0 24 24" fill="none" aria-hidden="true"><circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="3" class="opacity-25"/><path d="M22 12a10 10 0 0 0-10-10" stroke="currentColor" stroke-width="3" stroke-linecap="round"/></svg>
        <span data-inquiry-label>KIRIM &amp; LANJUT KE WHATSAPP</span>
      </button>
      <p class="mt-3 text-center font-jakarta text-muted text-[11px]">Dengan mengirim, Anda setuju dihubungi tim Wintom lewat WhatsApp.</p>
    </form>
    <?php
}
