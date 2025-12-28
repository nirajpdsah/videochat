<?php
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();

// Load local environment files when present (handy for shared hosts like InfinityFree)
function loadEnvFile($path)
{
    if (!is_readable($path)) {
        return;
    }

    $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        $trimmed = trim($line);
        if ($trimmed === '' || $trimmed[0] === '#') {
            continue;
        }
        if (strpos($trimmed, '=') === false) {
            continue;
        }
        list($key, $value) = explode('=', $trimmed, 2);
        $key = trim($key);
        $value = trim($value);
        if ($key === '') {
            continue;
        }
        // Do not override environment variables that already exist
        if (getenv($key) === false) {
            putenv("{$key}={$value}");
            $_ENV[$key] = $value;
            $_SERVER[$key] = $value;
        }
    }
}

// Load PHP array-based configuration (works when dotfiles are blocked by host)
function loadPhpConfig($path)
{
    if (!is_readable($path)) {
        return;
    }

    $data = include $path;
    if (!is_array($data)) {
        return;
    }

    foreach ($data as $key => $value) {
        if (!is_string($key) || $key === '') {
            continue;
        }
        if (getenv($key) === false) {
            putenv("{$key}={$value}");
            $_ENV[$key] = $value;
            $_SERVER[$key] = $value;
        }
    }
}

loadEnvFile(__DIR__ . '/.env.local');
loadEnvFile(__DIR__ . '/.env');
loadPhpConfig(__DIR__ . '/config.production.php');

function envOrDefault($key, $default = null)
{
    $value = getenv($key);
    if ($value === false && isset($_ENV[$key])) {
        $value = $_ENV[$key];
    }
    if ($value === false && isset($_SERVER[$key])) {
        $value = $_SERVER[$key];
    }
    return ($value === false || $value === null || $value === '') ? $default : $value;
}

// Prefer InfinityFree-friendly keys, then generic, then Railway fallbacks, then local defaults
define('DB_HOST', envOrDefault('DB_HOST', envOrDefault('IF_DB_HOST', envOrDefault('INFINITYFREE_DB_HOST', envOrDefault('MYSQLHOST', 'localhost')))));
define('DB_USER', envOrDefault('DB_USER', envOrDefault('IF_DB_USER', envOrDefault('INFINITYFREE_DB_USER', envOrDefault('MYSQLUSER', 'root')))));
define('DB_PASS', envOrDefault('DB_PASS', envOrDefault('IF_DB_PASS', envOrDefault('INFINITYFREE_DB_PASS', envOrDefault('MYSQLPASSWORD', '')))));
define('DB_NAME', envOrDefault('DB_NAME', envOrDefault('IF_DB_NAME', envOrDefault('INFINITYFREE_DB_NAME', envOrDefault('MYSQLDATABASE', 'videochat_db')))));
define('DB_PORT', envOrDefault('DB_PORT', envOrDefault('IF_DB_PORT', envOrDefault('INFINITYFREE_DB_PORT', envOrDefault('MYSQLPORT', '3306')))));

$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME, DB_PORT);

if ($conn->connect_error) {
    // Show detailed error in production for debugging
    error_log("Database connection failed: " . $conn->connect_error);
    die("Database connection failed. Please check your configuration. Error: " . $conn->connect_error);
}

$conn->set_charset("utf8mb4");

// Rest of your config functions...
function isLoggedIn() {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

function getCurrentUser() {
    global $conn;
    if (!isLoggedIn()) {
        return null;
    }
    
    $user_id = $_SESSION['user_id'];
    $stmt = $conn->prepare("SELECT id, username, email, profile_picture, status FROM users WHERE id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        return $result->fetch_assoc();
    }
    return null;
}

function cleanInput($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}
?>