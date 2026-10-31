<?php
// includes/image.php — Gambar responsif & ringan untuk seluruh situs.
//
// Cara pakai di template:
//   <img [echo img_attrs($url, '(min-width:1024px) 33vw, 100vw')] alt="..." loading="lazy">
//   -> src + srcset + sizes, sehingga HP mengunduh versi kecil dan desktop versi besar.
//
// Sumber gambar yang didukung:
//   • Unsplash (images.unsplash.com): diminta dengan lebar yang pas + auto=format
//     (WebP/AVIF otomatis) + kualitas 70, langsung dari CDN Unsplash.
//   • Upload admin (/uploads/...): varian WebP "nama-480.webp", "-960", "-1600" dibuat
//     saat upload (lihat image_make_variants) atau sekali saat pertama kali ditampilkan.
//   • Lainnya (logo, YouTube, dsb.): dipakai apa adanya.

const IMG_WIDTHS        = [480, 960, 1600];   // varian upload admin
const IMG_UNSPLASH_Q    = 70;
const IMG_WEBP_QUALITY  = 78;

function img_is_unsplash(string $url): bool {
    return str_starts_with($url, 'https://images.unsplash.com/');
}

/** URL Unsplash dengan lebar tertentu + format modern otomatis. */
function img_unsplash(string $url, int $w): string {
    $parts = parse_url($url);
    parse_str($parts['query'] ?? '', $q);
    unset($q['w'], $q['q'], $q['auto'], $q['fm']);
    $q = ['w' => $w, 'q' => IMG_UNSPLASH_Q, 'auto' => 'format'] + $q;
    if (!isset($q['fit'])) $q['fit'] = 'crop';
    return 'https://images.unsplash.com' . ($parts['path'] ?? '') . '?' . http_build_query($q);
}

/** Path disk untuk URL /uploads/... (null bila bukan upload lokal). */
function img_upload_path(string $url): ?string {
    if (!str_starts_with($url, '/uploads/')) return null;
    $path = realpath(__DIR__ . '/..' . $url);
    $base = realpath(__DIR__ . '/../uploads');
    return ($path && $base && str_starts_with($path, $base . DIRECTORY_SEPARATOR)) ? $path : null;
}

/** "/uploads/gallery/x.jpg" + 960 -> "/uploads/gallery/x-960.webp" */
function img_variant_url(string $url, int $w): string {
    return preg_replace('/\.(jpe?g|png|webp)$/i', '', $url) . '-' . $w . '.webp';
}

/**
 * Buat varian WebP (480/960/1600) untuk satu file upload. Varian yang lebih lebar dari
 * aslinya dilewati. Aman dipanggil berulang (yang sudah ada tidak dibuat ulang).
 */
function image_make_variants(string $file): void {
    if (!extension_loaded('gd') || !function_exists('imagewebp') || !is_file($file)) return;
    $info = @getimagesize($file);
    if (!$info) return;
    [$w, $h] = $info;
    $todo = [];
    foreach (IMG_WIDTHS as $vw) {
        $target = preg_replace('/\.(jpe?g|png|webp)$/i', '', $file) . '-' . $vw . '.webp';
        if ($vw < $w && !is_file($target)) $todo[$vw] = $target;
    }
    if (!$todo) return;
    $src = match ($info['mime']) {
        'image/jpeg' => @imagecreatefromjpeg($file),
        'image/png'  => @imagecreatefrompng($file),
        'image/webp' => @imagecreatefromwebp($file),
        default      => false,
    };
    if (!$src) return;
    foreach ($todo as $vw => $target) {
        $vh  = (int)round($h * $vw / $w);
        $dst = imagecreatetruecolor($vw, $vh);
        imagealphablending($dst, false);
        imagesavealpha($dst, true);
        imagecopyresampled($dst, $src, 0, 0, 0, 0, $vw, $vh, $w, $h);
        @imagewebp($dst, $target, IMG_WEBP_QUALITY);
        imagedestroy($dst);
    }
    imagedestroy($src);
}

/** Hapus file upload beserta semua varian WebP-nya. */
function image_delete_with_variants(string $file): void {
    if (is_file($file)) @unlink($file);
    $stem = preg_replace('/\.(jpe?g|png|webp)$/i', '', $file);
    foreach (IMG_WIDTHS as $vw) {
        if (is_file($stem . '-' . $vw . '.webp')) @unlink($stem . '-' . $vw . '.webp');
    }
}

/** Satu URL teroptimasi dengan lebar tertentu (dipakai mis. untuk lightbox/JS). */
function img_src(string $url, int $w = 1600): string {
    if (img_is_unsplash($url)) return img_unsplash($url, $w);
    if ($path = img_upload_path($url)) {
        image_make_variants($path);
        foreach (IMG_WIDTHS as $vw) {
            if ($vw >= $w && is_file(img_upload_path(img_variant_url($url, $vw)) ?? '')) {
                return asset_url(img_variant_url($url, $vw));
            }
        }
    }
    return asset_url($url);
}

/**
 * src + srcset (tanpa escape) untuk satu gambar.
 * @param int[] $widths lebar kandidat untuk Unsplash
 * @return array{src:string, srcset:string}  srcset kosong bila tidak ada varian
 */
function img_parts(string $url, array $widths = [480, 800, 1200, 1600]): array {
    if (img_is_unsplash($url)) {
        $set = array_map(fn($w) => img_unsplash($url, $w) . ' ' . $w . 'w', $widths);
        return ['src' => img_unsplash($url, $widths[(int)floor((count($widths) - 1) / 2)]), 'srcset' => implode(', ', $set)];
    }
    if ($path = img_upload_path($url)) {
        image_make_variants($path);
        $set = [];
        foreach (IMG_WIDTHS as $vw) {
            $v = img_variant_url($url, $vw);
            if (is_file(img_upload_path($v) ?? '')) $set[] = asset_url($v) . ' ' . $vw . 'w';
        }
        $info = @getimagesize($path);
        if ($set && $info) {
            $set[] = asset_url($url) . ' ' . $info[0] . 'w';   // asli sebagai ukuran terbesar
            return ['src' => asset_url($url), 'srcset' => implode(', ', $set)];
        }
    }
    return ['src' => asset_url($url), 'srcset' => ''];
}

/**
 * Atribut src + srcset + sizes untuk <img>.
 * @param string $sizes ukuran tampil, mis. '(min-width:1024px) 33vw, 100vw'
 */
function img_attrs(string $url, string $sizes = '100vw', array $widths = [480, 800, 1200, 1600]): string {
    $p = img_parts($url, $widths);
    $out = 'src="' . htmlspecialchars($p['src'], ENT_QUOTES) . '"';
    if ($p['srcset'] !== '') {
        $out .= ' srcset="' . htmlspecialchars($p['srcset'], ENT_QUOTES) . '" sizes="' . htmlspecialchars($sizes, ENT_QUOTES) . '"';
    }
    return $out;
}
