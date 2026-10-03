<?php
// If PHP built-in web server and requesting a real static file, serve directly
if (php_sapi_name() === 'cli-server') {
    $requestedFile = __DIR__ . parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
    if (is_file($requestedFile)) {
        return false;
    }
}

// ─── SafeHer Front Controller / Router ───────────────────────────────────────
require_once __DIR__ . '/config/app.php';

// Load all controllers
require_once BASE_PATH . '/controllers/AuthController.php';
require_once BASE_PATH . '/controllers/AdminController.php';
require_once BASE_PATH . '/controllers/ComplainantController.php';
require_once BASE_PATH . '/controllers/BarangayController.php';
require_once BASE_PATH . '/controllers/CaseController.php';
require_once BASE_PATH . '/controllers/AnalyticsController.php';
require_once BASE_PATH . '/controllers/ResidentController.php';

// ─── Parse URI ────────────────────────────────────────────────────────────────
$rawUri = $_SERVER['REQUEST_URI'] ?? '/';
$rawUri = strtok($rawUri, '?');          // Strip query string
if (APP_BASE !== '' && str_starts_with($rawUri, APP_BASE)) {
    $rawUri = substr($rawUri, strlen(APP_BASE));
}
$rawUri = '/' . trim($rawUri, '/');
if ($rawUri === '//') $rawUri = '/';

$method = strtoupper($_SERVER['REQUEST_METHOD']);

// ─── Route Table ──────────────────────────────────────────────────────────────
$routes = [
    // Public
    'GET'  => [
        '/'                             => [AuthController::class,       'landing'],
        '/login'                        => [AuthController::class,       'login'],
        '/register'                     => [AuthController::class,       'register'],
        '/logout'                       => [AuthController::class,       'logout'],

        // Admin
        '/admin/dashboard'              => [AdminController::class,      'dashboard'],
        '/admin/users'                  => [AdminController::class,      'users'],
        '/admin/barangays'              => [AdminController::class,      'barangays'],
        '/admin/notifications'          => [AdminController::class,      'notifications'],
        '/admin/cases'                  => [CaseController::class,       'adminList'],
        '/admin/cases/view'             => [CaseController::class,       'viewCase'],
        '/admin/analytics'              => [AnalyticsController::class,  'index'],
        '/admin/analytics/export-csv'   => [AnalyticsController::class,  'exportCsv'],

        // Complainant
        '/complainant/dashboard'        => [ComplainantController::class,'dashboard'],
        '/complainant/my-cases'         => [ComplainantController::class,'myCases'],
        '/complainant/report'           => [CaseController::class,       'submitReport'],
        '/complainant/case-view'        => [CaseController::class,       'viewCase'],

        // Barangay
        '/barangay/dashboard'           => [BarangayController::class,   'dashboard'],
        '/barangay/cases'               => [BarangayController::class,   'cases'],
        '/barangay/case-view'           => [CaseController::class,       'viewCase'],

        // Resident Profiling (Admin & Barangay Staff)
        '/residents'                    => [ResidentController::class,   'index'],
        '/residents/view'               => [ResidentController::class,   'view'],
        '/residents/export-csv'         => [ResidentController::class,   'exportCsv'],
    ],
    'POST' => [
        '/login'                        => [AuthController::class,       'login'],
        '/register'                     => [AuthController::class,       'register'],

        // Admin actions
        '/admin/users/create'           => [AdminController::class,      'createUser'],
        '/admin/users/toggle-status'    => [AdminController::class,      'toggleUserStatus'],
        '/admin/users/delete'           => [AdminController::class,      'deleteUser'],
        '/admin/barangays/create'       => [AdminController::class,      'createBarangay'],
        '/admin/barangays/update'       => [AdminController::class,      'updateBarangay'],
        '/admin/barangays/delete'       => [AdminController::class,      'deleteBarangay'],
        '/admin/notifications/send-blast'=> [AdminController::class,     'sendBlast'],
        '/admin/cases/delete'           => [CaseController::class,       'deleteCase'],

        // Case actions
        '/complainant/report'           => [CaseController::class,       'submitReport'],
        '/case/update-status'           => [CaseController::class,       'updateCaseStatus'],

        // Resident actions
        '/residents/create'             => [ResidentController::class,   'create'],
        '/residents/update'             => [ResidentController::class,   'update'],
        '/residents/delete'             => [ResidentController::class,   'delete'],
    ],
];

// ─── Dispatch ─────────────────────────────────────────────────────────────────
if (isset($routes[$method][$rawUri])) {
    [$class, $action] = $routes[$method][$rawUri];
    $controller = new $class();
    $controller->$action();
} else {
    // 404
    http_response_code(404);
    include BASE_PATH . '/views/public/404.php';
}
