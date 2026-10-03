<?php
/**
 * views/layouts/admin_nav.php
 * Sidebar navigation for the Admin role.
 */
$current = $_SERVER['REQUEST_URI'] ?? '';
function isActive(string $path): string {
    global $current;
    return str_contains($current, $path) ? 'active' : '';
}
$initials = strtoupper(substr($_SESSION['full_name'] ?? 'A', 0, 1));
?>
<div class="layout">

<!-- Sidebar Overlay (mobile) -->
<div class="sidebar-overlay" id="sidebarOverlay" onclick="toggleSidebar()"></div>

<!-- Sidebar -->
<aside class="sidebar" id="sidebar">
  <!-- Brand -->
  <div class="sidebar-brand">
    <a href="<?= APP_URL ?>/admin/dashboard" class="brand-logo">
      <div class="brand-icon"><i class="fas fa-shield-heart"></i></div>
      <div>
        <div class="brand-name">SafeHer</div>
        <div class="brand-sub">Gogon Centro, Virac</div>
      </div>
    </a>
  </div>

  <!-- User -->
  <div class="sidebar-user">
    <div class="user-avatar"><?= $initials ?></div>
    <div>
      <div class="user-name"><?= sanitize($_SESSION['full_name'] ?? '') ?></div>
      <div class="user-role"><i class="fas fa-crown" style="color:#f59e0b;font-size:0.6rem"></i> Barangay Captain (Admin)</div>
    </div>
  </div>

  <!-- Nav -->
  <nav class="sidebar-nav">
    <div class="nav-section-label">Overview</div>
    <a href="<?= APP_URL ?>/admin/dashboard" class="nav-item <?= isActive('/admin/dashboard') ?>">
      <span class="nav-icon"><i class="fas fa-chart-pie"></i></span> Dashboard
    </a>

    <div class="nav-section-label">Case Management</div>
    <a href="<?= APP_URL ?>/admin/cases" class="nav-item <?= isActive('/admin/cases') ?>">
      <span class="nav-icon"><i class="fas fa-folder-open"></i></span> All Cases
    </a>
    <a href="<?= APP_URL ?>/admin/analytics" class="nav-item <?= isActive('/admin/analytics') ?>">
      <span class="nav-icon"><i class="fas fa-chart-line"></i></span> Analytics
    </a>

    <div class="nav-section-label">Management</div>
    <a href="<?= APP_URL ?>/residents" class="nav-item <?= isActive('/residents') ?>">
      <span class="nav-icon"><i class="fas fa-address-book"></i></span> Resident Profiling
    </a>
    <a href="<?= APP_URL ?>/admin/users" class="nav-item <?= isActive('/admin/users') ?>">
      <span class="nav-icon"><i class="fas fa-users"></i></span> Users
    </a>
    <a href="<?= APP_URL ?>/admin/barangays" class="nav-item <?= isActive('/admin/barangays') ?>">
      <span class="nav-icon"><i class="fas fa-map-marker-alt"></i></span> Barangays
    </a>
    <a href="<?= APP_URL ?>/admin/notifications" class="nav-item <?= isActive('/admin/notifications') ?>">
      <span class="nav-icon"><i class="fas fa-comment-sms"></i></span> SMS / Notifications
    </a>
  </nav>

  <div class="sidebar-footer">
    <a href="<?= APP_URL ?>/logout" class="nav-item" onclick="return confirm('Log out of SafeHer?')">
      <span class="nav-icon" style="background:rgba(239,68,68,0.1)"><i class="fas fa-sign-out-alt"></i></span> Logout
    </a>
  </div>
</aside>

<!-- Main Content Wrapper -->
<div class="main-content">
  <!-- Topbar -->
  <header class="topbar">
    <div class="d-flex align-center gap-2">
      <button class="sidebar-toggle" onclick="toggleSidebar()"><i class="fas fa-bars"></i></button>
      <span class="page-title"><?= $pageTitle ?? 'Dashboard' ?></span>
    </div>
    <div class="topbar-actions">
      <a href="<?= APP_URL ?>/admin/notifications" class="btn btn-outline btn-sm">
        <i class="fas fa-comment-sms"></i> SMS Blast
      </a>
    </div>
  </header>
