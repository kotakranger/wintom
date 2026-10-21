<?php
// inquiry.php — Terima form "Kontak Kami": validasi, simpan ke MySQL, kembalikan URL WhatsApp.
// Dipanggil via fetch() (JSON). Tanpa JavaScript, request biasa akan langsung di-redirect ke WhatsApp.
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/auth.php';

const INQUIRY_MAX_PER_WINDOW = 5;   // maks submit per IP
const INQUIRY_WINDOW_MINUTES = 10;

$wants_json = stripos($_SERVER['HTTP_ACCEPT'] ?? '', 'application/json') !== false;

function inquiry_respond(bool $json, int $status, array $payload): never
{
    http_response_code($status);
    if ($json) {
        header('Content-Type: application/json; charset=utf-8');
        header('Cache-Control: no-store');
        echo json_encode($payload, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        exit;
    }
    if (!empty($payload['ok']) && !empty($payload['wa_url'])) {
        header('Location: ' . $payload['wa_url']);
        exit;
    }
    header('Content-Type: text/html; charset=utf-8');
    echo '<!DOCTYPE html><meta charset="utf-8"><title>Form belum lengkap</title>'
       . '<p style="font:16px sans-serif;margin:3rem auto;max-width:480px">'
       . htmlspecialchars($payload['message'] ?? 'Terjadi kesalahan.')
       . '</p><p style="font:16px sans-serif;margin:0 auto;max-width:480px"><a href="javascript:history.back()">&larr; Kembali</a></p>';
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Allow: POST');
    inquiry_respond($wants_json, 405, ['ok' => false, 'message' => 'Metode tidak diizinkan.']);
}

// CSRF (token disisipkan form lewat csrf_field())
if (!hash_equals(csrf_token(), (string)($_POST['csrf'] ?? ''))) {
    inquiry_respond($wants_json, 400, ['ok' => false, 'message' => 'Sesi form kedaluwarsa. Muat ulang halaman lalu coba lagi.']);
}

// Honeypot: bot mengisi field tersembunyi. Pura-pura sukses tanpa menyimpan apa pun.
if (trim((string)($_POST['website'] ?? '')) !== '') {
    inquiry_respond($wants_json, 200, ['ok' => true, 'wa_url' => WA_URL]);
}

$full_name   = trim(preg_replace('/\s+/u', ' ', (string)($_POST['full_name'] ?? '')));
$whatsapp    = trim((string)($_POST['whatsapp'] ?? ''));
$category_id = (int)($_POST['category_id'] ?? 0);
$size        = trim((string)($_POST['estimated_size'] ?? ''));
$notes       = trim((string)($_POST['notes'] ?? ''));

$errors = [];

$len = fn(string $s): int => mb_strlen($s, 'UTF-8');

if ($full_name === '') {
    $errors['full_name'] = 'Nama lengkap wajib diisi.';
} elseif ($len($full_name) < 2 || $len($full_name) > 120) {
    $errors['full_name'] = 'Nama harus 2–120 karakter.';
}

// Normalisasi nomor: +62 812-3456-7890 / 0812... / 62812... -> 628123456789(0)
if ($whatsapp === '') {
    $errors['whatsapp'] = 'Nomor WhatsApp wajib diisi.';
} elseif (!preg_match('/^[+\d][\d\s().-]*$/', $whatsapp)) {
    $errors['whatsapp'] = 'Nomor WhatsApp hanya boleh berisi angka.';
} else {
    $digits = preg_replace('/\D/', '', $whatsapp);
    if (str_starts_with($digits, '0')) {
        $digits = '62' . substr($digits, 1);
    } elseif (str_starts_with($digits, '8')) {
        $digits = '62' . $digits;
    }
    if (!preg_match('/^62\d{8,13}$/', $digits)) {
        $errors['whatsapp'] = 'Nomor WhatsApp tidak valid. Contoh: 0812 3456 7890.';
    } else {
        $whatsapp = $digits;
    }
}

$db = get_db();

$category = null;
if ($category_id <= 0) {
    $errors['category_id'] = 'Pilih jenis curtain.';
} else {
    $st = $db->prepare('SELECT id, name FROM categories WHERE id = ?');
    $st->execute([$category_id]);
    $category = $st->fetch();
    if (!$category) {
        $errors['category_id'] = 'Jenis curtain tidak ditemukan.';
    }
}

if ($len($size) > 100) {
    $errors['estimated_size'] = 'Estimasi ukuran maksimal 100 karakter.';
}
if ($len($notes) > 1000) {
    $errors['notes'] = 'Detail kebutuhan maksimal 1000 karakter.';
}

if ($errors) {
    inquiry_respond($wants_json, 422, [
        'ok'      => false,
        'message' => 'Mohon periksa kembali isian form.',
        'errors'  => $errors,
    ]);
}

// Rate limit per IP
$ip = substr((string)($_SERVER['REMOTE_ADDR'] ?? ''), 0, 45);
$rl = $db->prepare('SELECT COUNT(*) FROM inquiries WHERE ip_address = ? AND created_at > (NOW() - INTERVAL ' . INQUIRY_WINDOW_MINUTES . ' MINUTE)');
$rl->execute([$ip]);
if ((int)$rl->fetchColumn() >= INQUIRY_MAX_PER_WINDOW) {
    inquiry_respond($wants_json, 429, ['ok' => false, 'message' => 'Terlalu banyak pengiriman. Silakan coba lagi beberapa menit lagi.']);
}

try {
    $ins = $db->prepare(
        'INSERT INTO inquiries (full_name, whatsapp, category_id, curtain_type, estimated_size, notes, ip_address)
         VALUES (?, ?, ?, ?, ?, ?, ?)'
    );
    $ins->execute([
        $full_name,
        $whatsapp,
        (int)$category['id'],
        $category['name'],
        $size !== '' ? $size : null,
        $notes !== '' ? $notes : null,
        $ip !== '' ? $ip : null,
    ]);
    $inquiry_id = (int)$db->lastInsertId();
} catch (Throwable $e) {
    error_log('inquiry insert failed: ' . $e->getMessage());
    inquiry_respond($wants_json, 500, ['ok' => false, 'message' => 'Maaf, data belum tersimpan. Silakan coba lagi.']);
}

// Pesan WhatsApp berisi ringkasan seluruh isian
$lines = [
    'Halo Wintom, saya ingin konsultasi dan meminta penawaran.',
    '',
    '*Nama:* ' . $full_name,
    '*No. WhatsApp:* ' . $whatsapp,
    '*Jenis Curtain:* ' . $category['name'],
    '*Estimasi Ukuran:* ' . ($size !== '' ? $size : '-'),
    '*Kebutuhan / Detail:* ' . ($notes !== '' ? $notes : '-'),
    '',
    '(Ref. inquiry #' . $inquiry_id . ')',
];
$wa_url = WA_URL . '?text=' . rawurlencode(implode("\n", $lines));

inquiry_respond($wants_json, 200, ['ok' => true, 'id' => $inquiry_id, 'wa_url' => $wa_url]);
