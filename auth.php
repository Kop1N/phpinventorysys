<?php
// auth.php - Authorization helper functions

function isAdmin() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
}

function currentUserRole() {
    return $_SESSION['role'] ?? null;
}

function currentUserName() {
    return $_SESSION['name'] ?? 'Guest';
}

function requireAdmin() {
    if (!isAdmin()) {
        $_SESSION['error'] = "Access denied. Administrator privileges required.";
        header("Location: index.php");
        exit();
    }
}

function requireLogin() {
    if (!isset($_SESSION['user_id'])) {
        $_SESSION['redirect_url'] = $_SERVER['REQUEST_URI'];
        header("Location: login.php");
        exit();
    }
}
?>