<?php
// ── AUTH HELPERS ──

/**
 * Require admin login — redirect to login.php if not authenticated
 */
function require_admin(){
    if(session_status() === PHP_SESSION_NONE) session_start();
    if(!isset($_SESSION['admin_id'])){
        header("Location: login.php");
        exit;
    }
}

/**
 * Check if user is logged in (returns true/false)
 */
function is_logged_in(){
    if(session_status() === PHP_SESSION_NONE) session_start();
    return isset($_SESSION['admin_id']);
}

/**
 * Get current admin name
 */
function admin_name(){
    return $_SESSION['admin_name'] ?? $_SESSION['admin_user'] ?? 'Admin';
}

/**
 * Check if request comes from localhost (host PC)
 */
function is_local(){
    $ip = $_SERVER['REMOTE_ADDR'];
    return in_array($ip, ['127.0.0.1', '::1', $_SERVER['SERVER_ADDR']]);
}
?>