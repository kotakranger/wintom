<?php

// Wintom Curtain — Auth Helper

require_once __DIR__ . '/config.php';

// Halaman admin tidak boleh muncul di hasil pencarian Google.
if (strpos($_SERVER['SCRIPT_NAME'] ?? '', '/admin/') !== false && !headers_sent()) {
    header('X-Robots-Tag: noindex, nofollow');
}

function start_session(): void
{
    if (session_status() === PHP_SESSION_NONE) {
        ini_set('session.use_strict_mode', '1');
        session_set_cookie_params([
            'httponly' => true,
            'samesite' => 'Lax',
            'secure' => !empty($_SERVER['HTTPS']),
            'path' => '/',
        ]);
        session_start();
    }
}

const ROLE_SUPER = 'superadmin';
const ROLE_ADMIN = 'admin';
const ADMIN_DEFAULT_PASSWORD = 'wintom2024';   // password bawaan instalasi awal — dipakai hanya untuk memberi peringatan
const PASSWORD_MIN_LENGTH = 8;

/**
 * Admin yang sedang login (baris dari tabel admins) atau null.
 * Dicek ke database di setiap request: akun yang dihapus, atau yang passwordnya diganti/direset
 * (session_version naik), langsung keluar dari semua perangkat.
 */
function current_admin(bool $refresh = false): ?array
{
    static $row = false;
    if ($refresh) {
        $row = false;
    }
    if ($row !== false) {
        return $row;
    }
    start_session();
    $row = null;
    $id = (int)($_SESSION['admin_id'] ?? 0);
    if ($id > 0) {
        require_once __DIR__ . '/db.php';
        $st = get_db()->prepare('SELECT id, username, email, role, session_version, created_at FROM admins WHERE id = ?');
        $st->execute([$id]);
        $a = $st->fetch() ?: null;
        if ($a && (int)($_SESSION['admin_sv'] ?? 0) === (int)$a['session_version']) {
            $row = $a;
        } else {
            unset($_SESSION['admin_id'], $_SESSION['admin_sv'], $_SESSION['weak_pw']);
        }
    }
    return $row;
}

function is_logged_in(): bool
{
    return current_admin() !== null;
}

function is_super_admin(): bool
{
    $a = current_admin();
    return $a !== null && $a['role'] === ROLE_SUPER;
}

function require_login(): void
{
    if (!is_logged_in()) {
        header('Location: ' . BASE_URL . '/admin/login.php');
        exit;
    }
}

/** Halaman khusus Super Admin (mis. kelola pengguna). */
function require_super_admin(): void
{
    require_login();
    if (!is_super_admin()) {
        http_response_code(403);
        echo '<!DOCTYPE html><meta charset="utf-8"><title>Akses ditolak</title>'
           . '<p style="font:16px sans-serif;margin:3rem auto;max-width:480px">Halaman ini hanya untuk Super Admin.</p>'
           . '<p style="font:16px sans-serif;margin:0 auto;max-width:480px"><a href="' . BASE_URL . '/admin/index.php">&larr; Kembali ke Dashboard</a></p>';
        exit;
    }
}

function login_admin(int $id): void
{
    start_session();
    require_once __DIR__ . '/db.php';
    $sv = get_db()->prepare('SELECT session_version FROM admins WHERE id = ?');
    $sv->execute([$id]);
    session_regenerate_id(true);
    $_SESSION['admin_id'] = $id;
    $_SESSION['admin_sv'] = (int)$sv->fetchColumn();
}

/** Aturan password sederhana: 8–72 karakter, bukan password bawaan. Mengembalikan pesan error atau null. */
function validate_new_password(string $pw): ?string
{
    if (strlen($pw) < PASSWORD_MIN_LENGTH) {
        return 'Password minimal ' . PASSWORD_MIN_LENGTH . ' karakter.';
    }
    if (strlen($pw) > 72) {
        return 'Password maksimal 72 karakter.';
    }
    if ($pw === ADMIN_DEFAULT_PASSWORD) {
        return 'Pilih password lain, bukan password bawaan.';
    }
    return null;
}

/** Simpan password baru dan keluarkan semua sesi lama akun itu (session_version naik). */
function set_admin_password(PDO $db, int $id, string $password): void
{
    $db->prepare('UPDATE admins SET password_hash = ?, session_version = session_version + 1 WHERE id = ?')
       ->execute([password_hash($password, PASSWORD_DEFAULT), $id]);
}

/**
 * Lupa password: buat tautan sekali pakai (berlaku 60 menit) dan kirim ke email akun.
 * Selalu aman dipanggil: tidak membocorkan apakah email terdaftar. Maksimal 1 permintaan per 2 menit per akun.
 * Token asli hanya ada di email; database hanya menyimpan hash SHA-256-nya.
 */
function request_password_reset(PDO $db, string $identity): void
{
    $identity = strtolower(trim($identity));
    if ($identity === '') return;
    $st = $db->prepare('SELECT id, username, email FROM admins WHERE email = ? LIMIT 1');
    $st->execute([$identity]);
    $a = $st->fetch();
    if (!$a || empty($a['email'])) return;
    $recent = $db->prepare('SELECT 1 FROM password_resets WHERE admin_id = ? AND created_at > (NOW() - INTERVAL 2 MINUTE) LIMIT 1');
    $recent->execute([$a['id']]);
    if ($recent->fetchColumn()) return;

    $db->prepare('DELETE FROM password_resets WHERE admin_id = ? OR expires_at < NOW()')->execute([$a['id']]);
    $token = bin2hex(random_bytes(32));
    $db->prepare('INSERT INTO password_resets (admin_id, token_hash, expires_at) VALUES (?, ?, NOW() + INTERVAL 60 MINUTE)')
       ->execute([$a['id'], hash('sha256', $token)]);

    $link = abs_url('/admin/reset-password.php?token=' . $token);
    $body = "Halo {$a['username']},\r\n\r\n"
          . "Kami menerima permintaan untuk mengatur ulang password dashboard admin " . SITE_NAME . ".\r\n"
          . "Buka tautan berikut (berlaku 60 menit, hanya bisa dipakai sekali):\r\n\r\n{$link}\r\n\r\n"
          . "Jika Anda tidak merasa memintanya, abaikan email ini — password Anda tidak berubah.\r\n";
    $headers = 'From: ' . SITE_NAME . ' <' . MAIL_FROM . ">\r\n"
             . 'Reply-To: ' . SITE_EMAIL . "\r\n"
             . "Content-Type: text/plain; charset=UTF-8\r\n";
    $ok = @mail($a['email'], '=?UTF-8?B?' . base64_encode('Atur ulang password admin ' . SITE_NAME) . '?=', $body, $headers, '-f' . MAIL_FROM);
    if (!$ok) error_log('[wintom] gagal mengirim email reset password ke akun #' . $a['id']);
}

/** Baris admin untuk token reset yang masih berlaku dan belum dipakai, atau null. */
function find_password_reset(PDO $db, string $token): ?array
{
    if (!preg_match('/^[a-f0-9]{64}$/', $token)) return null;
    $st = $db->prepare('SELECT r.id AS rid, a.id, a.username FROM password_resets r JOIN admins a ON a.id = r.admin_id
                        WHERE r.token_hash = ? AND r.used_at IS NULL AND r.expires_at > NOW()');
    $st->execute([hash('sha256', $token)]);
    return $st->fetch() ?: null;
}

function logout_admin(): void
{
    start_session();
    $_SESSION = [];
    $params = session_get_cookie_params();
    setcookie(
        session_name(),
        '',
        time() - 3600,
        $params['path'],
        $params['domain'],
        $params['secure'],
        $params['httponly']
    );
    session_destroy();
}

function csrf_token(): string
{
    start_session();
    if (empty($_SESSION['csrf'])) {
        $_SESSION['csrf'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf'];
}

function csrf_field(): string
{
    return '<input type="hidden" name="csrf" value="' . htmlspecialchars(csrf_token()) . '">';
}

function csrf_check(): void
{
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (!hash_equals(csrf_token(), (string)($_POST['csrf'] ?? ''))) {
            http_response_code(400);
            exit('Invalid CSRF token');
        }
    }
}
