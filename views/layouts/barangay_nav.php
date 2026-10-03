<?php
/**
 * views/layouts/barangay_nav.php
 */
$current = $_SERVER['REQUEST_URI'] ?? '';
function isActive(string $path): string {
    global $current;
    return str_contains($current, $path) ? 'active' : '';
}
$initials = strtoupper(substr($_SESSION['full_name'] ?? 'B', 0, 1));
?>
<div class="layout">
<div class="sidebar-overlay" id="sidebarOverlay" onclick="toggleSidebar()"></div>
<aside class="sidebar" id="sidebar">
  <div class="sidebar-brand">
    <a href="<?= APP_URL ?>/barangay/dashboard" class="brand-logo">
      <div class="brand-icon"><i class="fas fa-shield-heart"></i></div>
      <div>
        <div class="brand-name">SafeHer</div>
        <div class="brand-sub">Gogon Centro, Virac</div>
      </div>
    </a>
  </div>
  <div class="sidebar-user">
    <div class="user-avatar" style="background:linear-gradient(135deg,#3b82f6,#8b5cf6)"><?= $initials ?></div>
    <div>
      <div class="user-name"><?= sanitize($_SESSION['full_name'] ?? '') ?></div>
      <div class="user-role" style="color:#93c5fd"><i class="fas fa-circle" style="color:#3b82f6;font-size:0.5rem"></i> Barangay Staff</div>
    </div>
  </div>
  <nav class="sidebar-nav">
    <div class="nav-section-label">Overview</div>
    <a href="<?= APP_URL ?>/barangay/dashboard" class="nav-item <?= isActive('/barangay/dashboard') ?>">
      <span class="nav-icon"><i class="fas fa-chart-bar"></i></span> Dashboard
    </a>
    <a href="<?= APP_URL ?>/barangay/cases" class="nav-item <?= isActive('/barangay/cases') ?>">
      <span class="nav-icon"><i class="fas fa-folder-open"></i></span> Assigned Cases
    </a>

    <div class="nav-section-label">Community Registry</div>
    <a href="<?= APP_URL ?>/residents" class="nav-item <?= isActive('/residents') ?>">
      <span class="nav-icon"><i class="fas fa-address-book"></i></span> Resident Profiling
    </a>
  </nav>
  <div class="sidebar-footer">
    <a href="<?= APP_URL ?>/logout" class="nav-item" onclick="return confirm('Log out?')">
      <span class="nav-icon" style="background:rgba(239,68,68,0.1)"><i class="fas fa-sign-out-alt"></i></span> Logout
    </a>
  </div>
</aside>
<div class="main-content">
  <header class="topbar">
    <div class="d-flex align-center gap-2">
      <button class="sidebar-toggle" onclick="toggleSidebar()"><i class="fas fa-bars"></i></button>
      <span class="page-title"><?= $pageTitle ?? 'Dashboard' ?></span>
    </div>
  </header>
