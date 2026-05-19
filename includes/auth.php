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
 * Get or create the per-session CSRF token.
 */
function csrf_token(){
    if(session_status() === PHP_SESSION_NONE) session_start();
    if(empty($_SESSION['csrf_token'])){
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Render a hidden CSRF field for forms.
 */
function csrf_field(){
    return '<input type="hidden" name="csrf_token" value="' .
        htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8') . '">';
}

/**
 * Validate a submitted CSRF token.
 */
function validate_csrf_token($token){
    if(session_status() === PHP_SESSION_NONE) session_start();
    return is_string($token)
        && isset($_SESSION['csrf_token'])
        && hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * Stop unsafe state-changing requests before they reach business logic.
 */
function require_csrf(){
    $token = $_POST['csrf_token'] ?? $_GET['csrf_token'] ?? '';
    if(!validate_csrf_token($token)){
        http_response_code(403);
        exit('Invalid security token.');
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
