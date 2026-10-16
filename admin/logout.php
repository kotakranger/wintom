<?php
// admin/logout.php
require_once __DIR__ . '/../includes/auth.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    logout_admin();
    header('Location: ' . BASE_URL . '/admin/login.php');
    exit;
}

// GET request — redirect to index
header('Location: ' . BASE_URL . '/admin/index.php');
exit;
