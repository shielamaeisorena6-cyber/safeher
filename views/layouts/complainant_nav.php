<?php
/**
 * views/layouts/complainant_nav.php
 */
$current = $_SERVER['REQUEST_URI'] ?? '';
function isActive(string $path): string {
    global $current;
    return str_contains($current, $path) ? 'active' : '';
}
$initials = strtoupper(substr($_SESSION['full_name'] ?? 'C', 0, 1));
?>
<div class="layout">
<div class="sidebar-overlay" id="sidebarOverlay" onclick="toggleSidebar()"></div>
<aside class="sidebar" id="sidebar">
  <div class="sidebar-brand">
    <a href="<?= APP_URL ?>/complainant/dashboard" class="brand-logo">
      <div class="brand-icon"><i class="fas fa-shield-heart"></i></div>
      <div>
        <div class="brand-name">SafeHer</div>
        <div class="brand-sub">Gogon Centro, Virac</div>
      </div>
    </a>
  </div>
  <div class="sidebar-user">
    <div class="user-avatar" style="background:linear-gradient(135deg,#ec4899,#8b5cf6)"><?= $initials ?></div>
    <div>
      <div class="user-name"><?= sanitize($_SESSION['full_name'] ?? '') ?></div>
      <div class="user-role" style="color:#f472b6"><i class="fas fa-circle" style="color:#f472b6;font-size:0.5rem"></i> Complainant</div>
    </div>
  </div>
  <nav class="sidebar-nav">
    <div class="nav-section-label">My Space</div>
    <a href="<?= APP_URL ?>/complainant/dashboard" class="nav-item <?= isActive('/complainant/dashboard') ?>">
      <span class="nav-icon"><i class="fas fa-home"></i></span> Dashboard
    </a>
    <a href="<?= APP_URL ?>/complainant/report" class="nav-item <?= isActive('/complainant/report') ?>">
      <span class="nav-icon"><i class="fas fa-plus-circle"></i></span> File a Report
    </a>
    <a href="<?= APP_URL ?>/complainant/my-cases" class="nav-item <?= isActive('/complainant/my-cases') ?>">
      <span class="nav-icon"><i class="fas fa-folder"></i></span> My Cases
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
    <div class="topbar-actions">
      <a href="<?= APP_URL ?>/complainant/report" class="btn btn-primary btn-sm">
        <i class="fas fa-plus"></i> New Report
      </a>
    </div>
  </header>
