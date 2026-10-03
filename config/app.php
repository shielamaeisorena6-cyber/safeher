<?php
// Start session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// ─── App Constants ────────────────────────────────────────────────────────────
define('APP_NAME',          'SafeHer');
define('APP_VERSION',       '1.0.0');
define('BARANGAY_NAME',     'Barangay Gogon Centro');
define('MUNICIPALITY_NAME', 'Virac');
define('PROVINCE_NAME',     'Catanduanes');
define('LGU_FULL_NAME',     'Barangay Gogon Centro, Virac, Catanduanes');
define('BARANGAY_PUROKS',   ['Purok 1', 'Purok 2', 'Purok 3', 'Purok 4', 'Purok 5', 'Riverside', 'Centro Proper']);

// Dynamic Base URL detection (works seamlessly on php -S localhost:8000 and XAMPP/WAMP subdirectories)
$scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || (isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == 443) ? 'https://' : 'http://';
$host   = $_SERVER['HTTP_HOST'] ?? 'localhost';
$script = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? ''));
$appBase = ($script === '/' || $script === '.' || $script === '') ? '' : rtrim($script, '/');

define('APP_BASE',    $appBase);
define('APP_URL',     $scheme . $host . APP_BASE);

// Filesystem root of the project
define('BASE_PATH',   dirname(__DIR__));

// Uploads
define('UPLOAD_DIR',  BASE_PATH  . '/uploads/evidence/');
define('UPLOAD_URL',  APP_URL    . '/uploads/evidence/');
define('MAX_UPLOAD_MB', 10);

// ─── Error Reporting (set to 0 in production) ─────────────────────────────────
error_reporting(E_ALL);
ini_set('display_errors', 1);

// ─── Timezone ─────────────────────────────────────────────────────────────────
date_default_timezone_set('Asia/Manila');

// ─── Auto-load core files ─────────────────────────────────────────────────────
require_once BASE_PATH . '/config/database.php';
require_once BASE_PATH . '/config/sms.php';
require_once BASE_PATH . '/helpers/auth_helper.php';
require_once BASE_PATH . '/helpers/sms_helper.php';
require_once BASE_PATH . '/helpers/case_helper.php';
require_once BASE_PATH . '/models/User.php';
require_once BASE_PATH . '/models/CaseModel.php';
require_once BASE_PATH . '/models/Barangay.php';
require_once BASE_PATH . '/models/Notification.php';
require_once BASE_PATH . '/models/Resident.php';
