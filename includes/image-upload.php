<?php
// includes/image-upload.php — Upload gambar standar untuk seluruh admin.
//   • Tampilan: dropzone seragam (klik atau seret & lepas) + pratinjau  -> image_dropzone()
//   • Browser : validasi tipe & maks 5 MB, lalu kompres dengan plugin browser-image-compression -> image_upload_script()
//   • Server  : validasi ulang + kompres/resize dengan GD sebelum disimpan          -> store_uploaded_image()

const IMAGE_MAX_BYTES     = 5 * 1024 * 1024;   // 5 MB per foto
const IMAGE_MAX_DIMENSION = 2000;              // sisi terpanjang setelah kompres (px)
const IMAGE_ALLOWED_MIME  = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];

/** $_FILES['x'] (single atau multiple) -> daftar file tunggal */
function uploaded_files(array $f): array {
    if (!is_array($f['name'])) return [$f];
    $out = [];
    foreach ($f['tmp_name'] as $i => $tmp) {
        $out[] = ['name' => $f['name'][$i], 'type' => $f['type'][$i], 'tmp_name' => $tmp, 'error' => $f['error'][$i], 'size' => $f['size'][$i]];
    }
    return $out;
}

/** True jika field upload berisi minimal satu file. */
function has_uploaded_file(?array $f): bool {
    if (!$f) return false;
    foreach (uploaded_files($f) as $one) {
        if ($one['error'] !== UPLOAD_ERR_NO_FILE) return true;
    }
    return false;
}

/**
 * Validasi, kompres, dan simpan satu gambar ke /uploads/<subdir>/.
 * @return string|false URL relatif (/uploads/...) atau false; $error berisi alasan.
 */
function store_uploaded_image(array $file, string $subdir, ?string &$error = null, string $prefix = 'img_'): string|false {
    $error = null;
    if ($file['error'] !== UPLOAD_ERR_OK) {
        $error = in_array($file['error'], [UPLOAD_ERR_INI_SIZE, UPLOAD_ERR_FORM_SIZE], true)
            ? 'Ukuran foto melebihi 5 MB.' : 'Upload foto gagal, coba lagi.';
        return false;
    }
    if ($file['size'] > IMAGE_MAX_BYTES) { $error = 'Ukuran foto maksimal 5 MB.'; return false; }
    $mime = (new finfo(FILEINFO_MIME_TYPE))->file($file['tmp_name']);
    if (!isset(IMAGE_ALLOWED_MIME[$mime])) { $error = 'Format foto harus JPG, PNG, atau WEBP.'; return false; }
    if (!@getimagesize($file['tmp_name'])) { $error = 'File bukan gambar yang valid.'; return false; }

    $dir = __DIR__ . '/../uploads/' . $subdir;
    if (!is_dir($dir)) @mkdir($dir, 0755, true);
    $ext  = IMAGE_ALLOWED_MIME[$mime];
    $name = uniqid($prefix, true) . '.' . $ext;
    $dest = $dir . '/' . $name;

    if (!compress_image($file['tmp_name'], $dest, $mime)
        && !(is_uploaded_file($file['tmp_name']) ? move_uploaded_file($file['tmp_name'], $dest) : copy($file['tmp_name'], $dest))) {
        $error = 'Gagal menyimpan foto.';
        return false;
    }
    image_make_variants($dest);   // versi WebP 480/960/1600 untuk srcset
    return '/uploads/' . $subdir . '/' . $name;
}

/**
 * Resize ke maks IMAGE_MAX_DIMENSION & encode ulang (JPEG/WEBP q82, PNG level 9).
 * Mengembalikan false bila GD tidak tersedia atau gagal (pemanggil lalu menyimpan file asli).
 */
function compress_image(string $src, string $dest, string $mime): bool {
    if (!extension_loaded('gd')) return false;
    $img = match ($mime) {
        'image/jpeg' => @imagecreatefromjpeg($src),
        'image/png'  => @imagecreatefrompng($src),
        'image/webp' => function_exists('imagecreatefromwebp') ? @imagecreatefromwebp($src) : false,
        default      => false,
    };
    if (!$img) return false;

    // Foto HP: putar sesuai orientasi EXIF
    if ($mime === 'image/jpeg' && function_exists('exif_read_data')) {
        $o = (int)(@exif_read_data($src)['Orientation'] ?? 1);
        $deg = [3 => 180, 6 => -90, 8 => 90][$o] ?? 0;
        if ($deg) { $r = imagerotate($img, $deg, 0); if ($r) { imagedestroy($img); $img = $r; } }
    }

    $w = imagesx($img); $h = imagesy($img);
    $scale = min(1, IMAGE_MAX_DIMENSION / max($w, $h));
    if ($scale < 1) {
        $nw = (int)round($w * $scale); $nh = (int)round($h * $scale);
        $res = imagecreatetruecolor($nw, $nh);
        if ($mime !== 'image/jpeg') { imagealphablending($res, false); imagesavealpha($res, true); }
        imagecopyresampled($res, $img, 0, 0, 0, 0, $nw, $nh, $w, $h);
        imagedestroy($img);
        $img = $res;
    } elseif ($mime !== 'image/jpeg') {
        imagealphablending($img, false); imagesavealpha($img, true);
    }

    $ok = match ($mime) {
        'image/jpeg' => imagejpeg($img, $dest, 82),
        'image/png'  => imagepng($img, $dest, 9),
        'image/webp' => imagewebp($img, $dest, 82),
    };
    imagedestroy($img);

    // Jangan sampai hasil "kompres" justru lebih besar dari aslinya
    if ($ok && $scale >= 1 && filesize($dest) > filesize($src)) {
        @unlink($dest);
        return false;
    }
    return $ok;
}

/**
 * Dropzone upload gambar (tampilan seragam di semua halaman admin).
 * Opsi: name, multiple (bool), required (bool), max_files (int), auto_submit (bool),
 *       compact (bool, versi ringkas untuk "tambah foto"), title (teks utama).
 */
function image_dropzone(array $o): string {
    $name     = $o['name'] ?? 'photo';
    $multiple = !empty($o['multiple']);
    $max      = (int)($o['max_files'] ?? ($multiple ? 12 : 1));
    $compact  = !empty($o['compact']);
    $title    = $o['title'] ?? ($multiple ? 'Seret & lepas foto, atau klik untuk memilih' : 'Seret & lepas foto, atau klik untuk memilih');
    $hint     = 'JPG, PNG, WEBP · maks 5 MB/foto' . ($multiple ? ' · maks ' . $max . ' foto' : '') . ' · otomatis dikompres';
    $field    = htmlspecialchars($name . ($multiple ? '[]' : ''));
    ob_start(); ?>
    <div class="img-drop" data-img-drop data-max-files="<?= $max ?>" <?= !empty($o['auto_submit']) ? 'data-auto-submit' : '' ?>>
      <label class="img-drop-zone block border-2 border-dashed border-border rounded-[6px] bg-cream/60 text-center cursor-pointer transition-colors hover:border-brand/50 hover:bg-cream <?= $compact ? 'px-4 py-3' : 'px-5 py-6' ?>">
        <span class="<?= $compact ? 'inline-flex items-center gap-2' : 'block' ?>">
          <svg class="<?= $compact ? 'size-5' : 'size-8 mx-auto mb-2' ?> text-muted" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5m-13.5-9L12 3m0 0 4.5 4.5M12 3v13.5"/></svg>
          <span class="font-jakarta text-dark text-[12px] font-medium"><?= htmlspecialchars($title) ?></span>
        </span>
        <span class="block font-jakarta text-muted text-[10.5px] mt-1"><?= htmlspecialchars($hint) ?></span>
        <input type="file" name="<?= $field ?>" accept="image/jpeg,image/png,image/webp" class="sr-only"
               <?= $multiple ? 'multiple' : '' ?> <?= !empty($o['required']) ? 'required' : '' ?>/>
      </label>
      <p class="img-drop-status hidden font-jakarta text-[11px] mt-1.5"></p>
      <div class="img-drop-previews grid grid-cols-4 gap-2 mt-2 empty:hidden"></div>
    </div>
    <?php
    return ob_get_clean();
}

/** Script dropzone + kompres di browser. Panggil sekali per halaman (sebelum </body>). */
function image_upload_script(): void {
    static $done = false;
    if ($done) return;
    $done = true;
    ?>
    <style>
      .img-drop-zone.is-dragover { border-color: #860524; background: rgba(134,5,36,.04); }
      .img-drop.is-busy .img-drop-zone { opacity: .6; pointer-events: none; }
    </style>
    <!-- Plugin kompres gambar di browser (jika gagal dimuat, server tetap mengompres) -->
    <script src="https://cdn.jsdelivr.net/npm/browser-image-compression@2.0.2/dist/browser-image-compression.js"></script>
    <script>
    (function () {
      const MAX_BYTES = <?= IMAGE_MAX_BYTES ?>;
      const TYPES = ['image/jpeg', 'image/png', 'image/webp'];
      const mb = b => (b / 1048576).toLocaleString('id-ID', { maximumFractionDigits: 1 }) + ' MB';

      document.querySelectorAll('[data-img-drop]').forEach(function (box) {
        const zone = box.querySelector('.img-drop-zone');
        const input = box.querySelector('input[type=file]');
        const status = box.querySelector('.img-drop-status');
        const previews = box.querySelector('.img-drop-previews');
        const form = box.closest('form');
        const max = input.multiple ? parseInt(box.dataset.maxFiles, 10) || 12 : 1;

        function say(msg, isError) {
          status.textContent = msg || '';
          status.classList.toggle('hidden', !msg);
          status.className = 'img-drop-status font-jakarta text-[11px] mt-1.5 ' + (msg ? '' : 'hidden ') + (isError ? 'text-red-600' : 'text-muted');
        }
        function lockForm(on) {
          box.classList.toggle('is-busy', on);
          if (form) form.querySelectorAll('[type=submit]').forEach(b => { b.disabled = on; });
        }

        async function handle(fileList) {
          const errors = [];
          let files = Array.from(fileList).filter(function (f) {
            if (!TYPES.includes(f.type)) { errors.push(f.name + ': format harus JPG, PNG, atau WEBP'); return false; }
            if (f.size > MAX_BYTES) { errors.push(f.name + ': ' + mb(f.size) + ' — maksimal 5 MB'); return false; }
            return true;
          });
          if (files.length > max) { errors.push('Maksimal ' + max + ' foto, sisanya diabaikan.'); files = files.slice(0, max); }

          previews.replaceChildren();
          const dt = new DataTransfer();
          if (!files.length) { input.files = dt.files; say(errors.join(' · '), true); return; }

          lockForm(true);
          say('Mengompres ' + files.length + ' foto…');
          let before = 0, after = 0;
          for (const f of files) {
            let out = f;
            if (window.imageCompression) {
              try {
                const c = await imageCompression(f, { maxSizeMB: 1, maxWidthOrHeight: <?= IMAGE_MAX_DIMENSION ?>, initialQuality: 0.82, useWebWorker: true });
                if (c.size < f.size) out = new File([c], f.name, { type: c.type, lastModified: Date.now() });
              } catch (e) { /* pakai file asli; server tetap mengompres */ }
            }
            before += f.size; after += out.size;
            dt.items.add(out);

            const cell = document.createElement('div');
            cell.className = 'relative aspect-square rounded-[3px] overflow-hidden border ' + (dt.files.length === 1 && input.multiple ? 'border-2 border-brand' : 'border-border');
            const img = document.createElement('img');
            img.className = 'w-full h-full object-cover';
            img.alt = '';
            img.src = URL.createObjectURL(out);
            cell.appendChild(img);
            if (dt.files.length === 1 && input.multiple && box.dataset.coverHint !== 'off') {
              const tag = document.createElement('span');
              tag.className = 'absolute top-1 left-1 bg-brand text-white font-jakarta text-[9px] uppercase tracking-[1px] px-1.5 py-0.5 rounded-[2px]';
              tag.textContent = 'Pertama';
              cell.appendChild(tag);
            }
            previews.appendChild(cell);
          }
          input.files = dt.files;
          lockForm(false);
          const summary = files.length + ' foto siap · ' + mb(before) + (after < before ? ' → ' + mb(after) : '');
          say(errors.length ? summary + ' · ' + errors.join(' · ') : summary, errors.length > 0);

          if (box.hasAttribute('data-auto-submit') && form) {
            say('Mengunggah ' + files.length + ' foto…');
            lockForm(true);
            HTMLFormElement.prototype.submit.call(form);
          }
        }

        input.addEventListener('change', function () { if (input.files.length) handle(input.files); });
        ['dragenter', 'dragover'].forEach(ev => zone.addEventListener(ev, function (e) { e.preventDefault(); zone.classList.add('is-dragover'); }));
        ['dragleave', 'dragend', 'drop'].forEach(ev => zone.addEventListener(ev, function () { zone.classList.remove('is-dragover'); }));
        zone.addEventListener('drop', function (e) { e.preventDefault(); if (e.dataTransfer.files.length) handle(e.dataTransfer.files); });
      });
    })();
    </script>
    <?php
}
