<?php
// ─── Auth Helpers ─────────────────────────────────────────────────────────────

/**
 * Check if user is logged in.
 */
function isLoggedIn(): bool {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

/**
 * Get the current logged-in user's role.
 */
function userRole(): ?string {
    return $_SESSION['role'] ?? null;
}

/**
 * Require user to be logged in. Redirect to login if not.
 */
function requireLogin(): void {
    if (!isLoggedIn()) {
        redirect('/login');
    }
}

/**
 * Require a specific role. Redirect to appropriate dashboard or login if mismatch.
 */
function requireRole(string ...$roles): void {
    requireLogin();
    if (!in_array(userRole(), $roles, true)) {
        redirect(dashboardUrl());
    }
}

/**
 * Return the dashboard URL for the current role.
 */
function dashboardUrl(): string {
    return match (userRole()) {
        'admin'           => '/admin/dashboard',
        'complainant'     => '/complainant/dashboard',
        'barangay_staff'  => '/barangay/dashboard',
        default           => '/login',
    };
}

/**
 * Redirect to a path (relative to APP_BASE).
 */
function redirect(string $path): never {
    header('Location: ' . APP_URL . $path);
    exit;
}

/**
 * Flash a message to the session.
 */
function flash(string $key, string $msg): void {
    $_SESSION['flash'][$key] = $msg;
}

/**
 * Retrieve and clear a flash message.
 */
function getFlash(string $key): ?string {
    $msg = $_SESSION['flash'][$key] ?? null;
    unset($_SESSION['flash'][$key]);
    return $msg;
}

/**
 * Sanitize string input.
 */
function sanitize(string $input): string {
    return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
}

/**
 * Generate a CSRF token.
 */
function csrfToken(): string {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Validate CSRF token from POST.
 */
function validateCsrf(): bool {
    $token = $_POST['csrf_token'] ?? '';
    return hash_equals($_SESSION['csrf_token'] ?? '', $token);
}

/**
 * Output a hidden CSRF field.
 */
function csrfField(): string {
    return '<input type="hidden" name="csrf_token" value="' . csrfToken() . '">';
}
