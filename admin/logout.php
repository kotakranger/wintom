<?php
// admin/logout.php
require_once __DIR__ . '/../includes/auth.php';
logout_admin();
header('Location: ' . BASE_URL . '/admin/login.php');
exit;
