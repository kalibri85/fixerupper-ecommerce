<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
define('APP_INIT', true);
// ================= SECURITY HEADERS =================
header("X-Frame-Options: SAMEORIGIN");
header("X-Content-Type-Options: nosniff");

header("Strict-Transport-Security: max-age=31536000; includeSubDomains; preload");

header("Referrer-Policy: no-referrer-when-downgrade");

header("X-XSS-Protection: 0");
//header("Content-Security-Policy: default-src 'self'; script-src 'self' https://cdn.jsdelivr.net https://code.jquery.com https://cdn.tiny.cloud; style-src 'self' https://cdn.jsdelivr.net https://cdnjs.cloudflare.com https://fonts.googleapis.com; font-src 'self' https://fonts.gstatic.com https://cdnjs.cloudflare.com https://cdn.jsdelivr.net; img-src 'self' data: https://sp.tinymce.com; connect-src 'self'; object-src 'none'; frame-ancestors 'self'; form-action 'self'; base-uri 'self';");

header("Content-Security-Policy: default-src 'self'; script-src 'self' https://cdn.jsdelivr.net https://code.jquery.com; style-src 'self' https://cdn.jsdelivr.net https://cdnjs.cloudflare.com https://fonts.googleapis.com; font-src 'self' https://fonts.gstatic.com https://cdnjs.cloudflare.com; img-src 'self' data:; connect-src 'self'; frame-ancestors 'self'; form-action 'self'; base-uri 'self';");
// ================= SESSION =================
session_set_cookie_params([
    'httponly' => true,
    'secure' => false,   // true when HTTPS
    'samesite' => 'Strict'
]);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// ================= DATABASE =================
require_once dirname(__DIR__, 2) . '/includes/connection.php';

// ================= FUNCTIONS =================
require_once __DIR__ . '/functions.php';

// ================= CSRF =================
 // CSRF
function csrf()
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function checkCSRF()
{
    if (
        empty($_POST['csrf_token']) ||
        empty($_SESSION['csrf_token']) ||
        !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])
    ) {
        die("CSRF attack detected");
    }
}

// ================= GLOBAL HELPERS =================
$currentPage = basename($_SERVER['PHP_SELF']);

function redirect($url) {
    header("Location: $url");
    exit;
}
?>