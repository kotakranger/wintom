<?php

// Wintom Curtain — Auth Helper

require_once __DIR__ . '/config.php';

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
        $st = get_db()->prepare('SELECT id, username, role, session_version, created_at FROM admins WHERE id = ?');
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
