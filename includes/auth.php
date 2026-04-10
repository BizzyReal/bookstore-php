<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * Redirect ke halaman login jika user belum login
 */
function redirectIfNotLoggedIn() {
    if (!isset($_SESSION['user_id'])) {
        header('Location: login.php');
        exit();
    }
}

/**
 * Cek apakah user sudah login
 * @return bool
 */
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}