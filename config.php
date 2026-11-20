<?php
error_reporting(0);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

/**
 * Load SMTP settings from a private file on shared hosting.
 * Keep private/smtp_config.php out of Git and never expose its contents.
 */
if (!function_exists('library_smtp_settings')) {
    function library_smtp_settings(): array {
        static $settings = null;
        if ($settings !== null) return $settings;

        $settings = [
            'host' => 'smtp.gmail.com',
            'username' => '',
            'password' => '',
            'port' => 587,
            'encryption' => 'tls'
        ];

        $privateFile = __DIR__ . '/private/smtp_config.php';
        if (is_file($privateFile) && is_readable($privateFile)) {
            $privateSettings = require $privateFile;
            if (is_array($privateSettings)) {
                $settings = array_merge($settings, array_intersect_key(
                    $privateSettings,
                    array_flip(['host', 'username', 'password', 'port', 'encryption'])
                ));
            }
        }

        // Retain environment-variable support on hosts that provide it.
        $settings['username'] = (string)($settings['username'] ?: (getenv('LIBRARY_SMTP_USERNAME') ?: ''));
        $settings['password'] = (string)($settings['password'] ?: (getenv('LIBRARY_SMTP_PASSWORD') ?: ''));
        $settings['host'] = (string)($settings['host'] ?: 'smtp.gmail.com');
        $settings['port'] = (int)($settings['port'] ?: 587);
        $settings['encryption'] = (string)($settings['encryption'] ?: 'tls');

        return $settings;
    }
}

$host = getenv('LIBRARY_DB_HOST') ?: 'sql103.byetcluster.com';
$user = getenv('LIBRARY_DB_USER') ?: 'if0_42113689';
$password = getenv('LIBRARY_DB_PASSWORD');
$database = getenv('LIBRARY_DB_NAME') ?: 'if0_42113689_library_db';
if ($password === false || $password === '') {
    error_log('LIBRARY_DB_PASSWORD is not configured.');
    http_response_code(500);
    exit('Database configuration is incomplete. Please contact the administrator.');
}
$conn = mysqli_connect($host, $user, $password, $database);
if (!$conn) {
    error_log('Database connection failed: ' . mysqli_connect_error());
    http_response_code(500);
    exit('Sorry, we are experiencing technical difficulties. Please try again later.');
}
mysqli_set_charset($conn, 'utf8mb4');
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: SAMEORIGIN');
header('Referrer-Policy: strict-origin-when-cross-origin');
if (session_status() === PHP_SESSION_NONE) session_start();
date_default_timezone_set('Asia/Karachi');
$site_url = 'https://' . ($_SERVER['HTTP_HOST'] ?? 'localhost');
?>