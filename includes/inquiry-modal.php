<?php
// includes/inquiry-modal.php — Modal "Get a Quote" global + perilaku semua form inquiry.
// Di-include oleh footer.php. Pemicu: elemen apa pun dengan atribut data-open-quote.
//   data-quote-category="<id kategori>"  pilih jenis curtain otomatis
//   data-quote-notes="..."               isi awal kolom kebutuhan
//   data-quote-width / -height / -qty    isi awal ukuran (dipakai kalkulator di detail produk)
// Bisa juga dibuka lewat URL ber-hash #quote.
require_once __DIR__ . '/inquiry-form.php';
?>
<div id="quote-modal" class="fixed inset-0 z-[200] hidden" role="dialog" aria-modal="true" aria-labelledby="quote-modal-title">
  <div data-quote-close data-quote-backdrop class="absolute inset-0 bg-[rgba(24,22,20,0.6)] backdrop-blur-[2px]"></div>
  <div class="absolute inset-x-0 bottom-0 sm:inset-0 sm:flex sm:items-center sm:justify-center sm:p-6 pointer-events-none">
    <div data-quote-panel class="pointer-events-auto relative w-full sm:max-w-[600px] max-h-[92dvh] overflow-y-auto overscroll-contain bg-white rounded-t-[12px] sm:rounded-[6px] shadow-2xl">
      <div class="sticky top-0 z-10 bg-white border-b border-border px-5 sm:px-7 py-4 flex items-start justify-between gap-4">
        <div>
          <p class="font-jakarta font-semibold text-brand text-[10px] tracking-[2.2px] uppercase mb-1">PENAWARAN GRATIS</p>
          <h2 id="quote-modal-title" class="font-playfair font-bold text-dark text-[20px] sm:text-[22px] leading-[1.25] m-0">Dapatkan Penawaran Gratis</h2>
        </div>
        <button type="button" data-quote-close aria-label="Tutup form"
                class="shrink-0 size-10 -mr-2 flex items-center justify-center rounded-full text-muted hover:text-dark hover:bg-cream transition-colors">
          <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"><path d="M6 6l12 12M18 6 6 18"/></svg>
        </button>
      </div>
      <div class="px-5 sm:px-7 pt-4 pb-6 sm:pb-7">
        <p class="font-jakarta text-muted text-[13px] leading-[20px] mb-5">Isi detail singkat di bawah. Data Anda kami simpan, lalu Anda langsung terhubung dengan tim kami di WhatsApp.</p>
        <?php render_inquiry_form('modal', $quote_categories ?? [], true); ?>
      </div>
    </div>
  </div>
</div>

<script>
(function () {
  // ---------- Modal ----------
  const modal = document.getElementById('quote-modal');
  const modalForm = modal.querySelector('[data-inquiry-form]');
  let lastFocus = null;

  function prefill(d) {
    if (d.quoteCategory) modalForm.category_id.value = d.quoteCategory;
    // Ganti catatan otomatis sebelumnya (mis. produk lain), tapi jangan timpa tulisan pengguna
    const notes = modalForm.notes, cur = notes.value.trim();
    if (d.quoteNotes && (!cur || cur === notes.dataset.auto)) { notes.value = d.quoteNotes; notes.dataset.auto = d.quoteNotes; }
    if (d.quoteWidth) modalForm.width_cm.value = d.quoteWidth;
    if (d.quoteHeight) modalForm.height_cm.value = d.quoteHeight;
    if (d.quoteQty) modalForm.quantity.value = d.quoteQty;
  }
  function open(trigger) {
    lastFocus = trigger || document.activeElement;
    if (trigger) prefill(trigger.dataset);
    modal.classList.remove('hidden');
    document.documentElement.style.overflow = 'hidden';
    const first = modalForm.querySelector('input:not([type=hidden]):not([tabindex="-1"])');
    window.setTimeout(function () { first && first.focus({ preventScroll: true }); }, 30);
  }
  function close() {
    if (modal.classList.contains('hidden')) return;
    modal.classList.add('hidden');
    document.documentElement.style.overflow = '';
    if (location.hash === '#quote') history.replaceState(null, '', location.pathname + location.search);
    if (lastFocus && lastFocus.focus) lastFocus.focus({ preventScroll: true });
  }
  window.WintomQuote = { open: open, close: close };

  document.addEventListener('click', function (e) {
    const t = e.target.closest('[data-open-quote]');
    if (t) { e.preventDefault(); open(t); return; }
    if (e.target.closest('[data-quote-close]')) close();
  });
  document.addEventListener('keydown', function (e) {
    if (modal.classList.contains('hidden')) return;
    if (e.key === 'Escape') { close(); return; }
    if (e.key === 'Tab') {   // jaga fokus tetap di dalam modal
      const f = Array.from(modal.querySelectorAll('button, [href], input:not([type=hidden]):not([tabindex="-1"]), select, textarea'))
        .filter(function (el) { return !el.disabled && el.offsetParent !== null; });
      if (!f.length) return;
      if (e.shiftKey && document.activeElement === f[0]) { e.preventDefault(); f[f.length - 1].focus(); }
      else if (!e.shiftKey && document.activeElement === f[f.length - 1]) { e.preventDefault(); f[0].focus(); }
    }
  });
  if (location.hash === '#quote') open();
  window.addEventListener('hashchange', function () { if (location.hash === '#quote') open(); });

  // ---------- Form inquiry (inline & modal) ----------
  const FIELDS = ['full_name', 'whatsapp', 'category_id', 'width_cm', 'height_cm', 'quantity', 'notes'];
  const MAX_DIM = 10000, MAX_QTY = 999;

  document.querySelectorAll('[data-inquiry-form]').forEach(function (form) {
    const btn = form.querySelector('[data-inquiry-submit]');
    const label = form.querySelector('[data-inquiry-label]');
    const spinner = form.querySelector('[data-inquiry-spinner]');
    const alertBox = form.querySelector('[data-inquiry-alert]');
    const idleLabel = label.textContent;
    let busy = false;

    ['width_cm', 'height_cm', 'quantity'].forEach(function (n) {
      form.elements[n].addEventListener('keydown', function (e) {
        if (['-', '+', 'e', 'E'].includes(e.key) || (n === 'quantity' && (e.key === '.' || e.key === ','))) e.preventDefault();
      });
    });

    function setError(name, msg) {
      const el = form.querySelector('.field-error[data-for="' + name + '"]');
      const input = form.elements[name];
      if (el) { el.textContent = msg || ''; el.classList.toggle('hidden', !msg); }
      if (input) { input.classList.toggle('border-red-500', !!msg); input.setAttribute('aria-invalid', msg ? 'true' : 'false'); }
    }
    function clearErrors() {
      FIELDS.forEach(function (n) { setError(n, ''); });
      alertBox.classList.add('hidden');
    }
    function showAlert(msg, ok) {
      alertBox.textContent = msg;
      alertBox.className = 'mt-5 rounded-[3px] border px-4 py-3 font-jakarta text-[13px] ' +
        (ok ? 'bg-green-50 border-green-200 text-green-800' : 'bg-red-50 border-red-200 text-red-700');
    }
    function setLoading(on, text) {
      busy = on;
      btn.disabled = on;
      spinner.classList.toggle('hidden', !on);
      label.textContent = on ? text : idleLabel;
    }

    // Validasi klien meniru inquiry.php (server tetap sumber kebenaran).
    function validate() {
      const errs = {};
      const name = form.full_name.value.trim().replace(/\s+/g, ' ');
      if (!name) errs.full_name = 'Nama lengkap wajib diisi.';
      else if (name.length < 2) errs.full_name = 'Nama minimal 2 karakter.';

      const wa = form.whatsapp.value.trim();
      let digits = wa.replace(/\D/g, '');
      if (!wa) errs.whatsapp = 'Nomor WhatsApp wajib diisi.';
      else if (!/^[+\d][\d\s().-]*$/.test(wa)) errs.whatsapp = 'Nomor WhatsApp hanya boleh berisi angka.';
      else {
        if (digits.startsWith('0')) digits = '62' + digits.slice(1);
        else if (digits.startsWith('8')) digits = '62' + digits;
        if (!/^62\d{8,13}$/.test(digits)) errs.whatsapp = 'Nomor WhatsApp tidak valid. Contoh: 0812 3456 7890.';
      }
      if (!form.category_id.value) errs.category_id = 'Pilih jenis produk.';

      const dim = function (field, lbl) {
        const raw = form.elements[field].value.trim().replace(',', '.');
        if (raw === '') return '';
        const v = Number(raw);
        if (!/^\d{1,5}(\.\d)?$/.test(raw) || !(v > 0) || v > MAX_DIM) errs[field] = lbl + ' harus angka 1–10.000 cm.';
        return raw;
      };
      const w = dim('width_cm', 'Lebar'), h = dim('height_cm', 'Tinggi');
      if (w && !h && !errs.width_cm) errs.height_cm = 'Isi juga tingginya.';
      else if (h && !w && !errs.height_cm) errs.width_cm = 'Isi juga lebarnya.';

      const q = form.quantity.value.trim();
      if (q !== '' && (!/^\d+$/.test(q) || +q < 1 || +q > MAX_QTY)) errs.quantity = 'Jumlah harus bilangan bulat 1–' + MAX_QTY + '.';
      return errs;
    }

    form.addEventListener('input', function (e) { if (e.target.name) setError(e.target.name, ''); });
    form.addEventListener('change', function (e) { if (e.target.name) setError(e.target.name, ''); });

    form.addEventListener('submit', async function (e) {
      e.preventDefault();
      if (busy) return;
      clearErrors();

      const errs = validate();
      const keys = Object.keys(errs);
      if (keys.length) {
        keys.forEach(function (k) { setError(k, errs[k]); });
        form.elements[keys[0]].focus();
        return;
      }

      setLoading(true, 'MENYIMPAN…');
      try {
        const res = await fetch(form.action, {
          method: 'POST',
          body: new FormData(form),
          headers: { 'Accept': 'application/json' },
          credentials: 'same-origin'
        });
        let data = null;
        try { data = await res.json(); } catch (_) {}

        if (res.ok && data && data.ok && data.wa_url) {
          setLoading(true, 'MEMBUKA WHATSAPP…');
          showAlert('Terima kasih! Data Anda sudah kami terima. Mengalihkan ke WhatsApp…', true);
          window.setTimeout(function () { window.location.href = data.wa_url; }, 900);
          return;
        }
        if (data && data.errors) {
          const first = Object.keys(data.errors)[0];
          Object.keys(data.errors).forEach(function (k) { setError(k, data.errors[k]); });
          if (first && form.elements[first]) form.elements[first].focus();
        }
        showAlert((data && data.message) || 'Maaf, terjadi kesalahan. Silakan coba lagi.', false);
      } catch (err) {
        showAlert('Tidak dapat terhubung ke server. Periksa koneksi internet Anda lalu coba lagi.', false);
      }
      setLoading(false);
    });
  });
})();
</script>
