<?php
// Wintom Curtain — Auth Helper

require_once __DIR__ . '/config.php';

function start_session(): void {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
}

function is_logged_in(): bool {
    start_session();
    return !empty($_SESSION['admin_id']);
}

function require_login(): void {
    if (!is_logged_in()) {
        header('Location: ' . BASE_URL . '/admin/login.php');
        exit;
    }
}

function login_admin(int $id): void {
    start_session();
    session_regenerate_id(true);
    $_SESSION['admin_id'] = $id;
}

function logout_admin(): void {
    start_session();
    session_destroy();
}
