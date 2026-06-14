<?php
require_once __DIR__ . '/security.php';
initSecureSession();

function isAdminLoggedIn() {
    return isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true;
}

function requireAdmin() {
    if (!isAdminLoggedIn()) {
        header("Location: ../admin/index.php");
        exit();
    }
}
