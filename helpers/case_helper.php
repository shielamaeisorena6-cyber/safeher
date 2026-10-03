<?php
// ─── Case Helper Functions ────────────────────────────────────────────────────

/**
 * Generate a unique case number in the format: SH-YYYYMM-NNNN
 * e.g. SH-202601-0042
 */
function generateCaseNumber(): string {
    $db     = getDB();
    $prefix = 'SH-' . date('Ym') . '-';

    $stmt = $db->prepare(
        "SELECT COUNT(*) FROM cases WHERE case_number LIKE :prefix"
    );
    $stmt->execute([':prefix' => $prefix . '%']);
    $count = (int) $stmt->fetchColumn();

    return $prefix . str_pad($count + 1, 4, '0', STR_PAD_LEFT);
}

/**
 * Return the CSS badge class for a case status.
 */
function statusBadge(string $status): string {
    return match ($status) {
        'pending'               => 'badge-warning',
        'under_investigation'   => 'badge-info',
        'resolved'              => 'badge-success',
        'dismissed'             => 'badge-secondary',
        default                 => 'badge-secondary',
    };
}

/**
 * Human-readable label for a case status.
 */
function statusLabel(string $status): string {
    return match ($status) {
        'pending'               => 'Pending',
        'under_investigation'   => 'Under Investigation',
        'resolved'              => 'Resolved',
        'dismissed'             => 'Dismissed',
        default                 => ucfirst($status),
    };
}

/**
 * Human-readable label for violence type.
 */
function violenceLabel(string $type): string {
    return match ($type) {
        'physical'      => 'Physical Violence',
        'sexual'        => 'Sexual Violence',
        'psychological' => 'Psychological Violence',
        'economic'      => 'Economic Abuse',
        'others'        => 'Others',
        default         => ucfirst($type),
    };
}

/**
 * Human-readable label for user roles.
 */
function roleLabel(string $role): string {
    return match ($role) {
        'admin'           => 'Barangay Captain (Admin)',
        'barangay_staff'  => 'Barangay Staff / VAW Officer',
        'complainant'     => 'Complainant',
        default           => ucfirst(str_replace('_', ' ', $role)),
    };
}

/**
 * Handle file upload for case evidence.
 * Returns the stored filename or false on failure.
 */
function uploadEvidence(array $file): string|false {
    $allowedTypes = ['image/jpeg','image/png','image/gif','application/pdf','video/mp4'];
    $maxBytes     = MAX_UPLOAD_MB * 1024 * 1024;

    if ($file['error'] !== UPLOAD_ERR_OK) return false;
    if ($file['size'] > $maxBytes) return false;
    if (!in_array($file['type'], $allowedTypes, true)) return false;

    $ext      = pathinfo($file['name'], PATHINFO_EXTENSION);
    $filename = bin2hex(random_bytes(16)) . '.' . strtolower($ext);
    $dest     = UPLOAD_DIR . $filename;

    if (!is_dir(UPLOAD_DIR)) {
        mkdir(UPLOAD_DIR, 0755, true);
    }

    return move_uploaded_file($file['tmp_name'], $dest) ? $filename : false;
}

/**
 * Format date-time for display.
 */
function fmtDate(string $datetime, string $format = 'M d, Y'): string {
    return date($format, strtotime($datetime));
}

/**
 * Format date-time for display with time.
 */
function fmtDateTime(string $datetime): string {
    return date('M d, Y h:i A', strtotime($datetime));
}

/**
 * Paginate query results.
 * Returns ['data'=>[], 'total'=>int, 'pages'=>int, 'current'=>int]
 */
function paginate(string $sql, array $params, int $page = 1, int $perPage = 15): array {
    $db = getDB();

    // Count total
    $countSql  = "SELECT COUNT(*) FROM ($sql) AS _count";
    $countStmt = $db->prepare($countSql);
    $countStmt->execute($params);
    $total = (int) $countStmt->fetchColumn();

    // Fetch page
    $offset = ($page - 1) * $perPage;
    $stmt   = $db->prepare("$sql LIMIT $perPage OFFSET $offset");
    $stmt->execute($params);
    $data = $stmt->fetchAll();

    return [
        'data'    => $data,
        'total'   => $total,
        'pages'   => (int) ceil($total / $perPage),
        'current' => $page,
    ];
}
