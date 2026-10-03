<?php
$pageTitle = 'My Dashboard';
$extraCss  = ['/assets/css/dashboard.css'];
include BASE_PATH . '/views/layouts/header.php';
include BASE_PATH . '/views/layouts/complainant_nav.php';
?>

<div class="page-content">
  <div class="page-header">
    <h2>My Dashboard</h2>
    <p>Welcome back, <?= sanitize($_SESSION['full_name'] ?? '') ?>. Track your reports here.</p>
  </div>

  <?php if ($msg = getFlash('success')): ?><div class="alert alert-success"><i class="fas fa-check-circle"></i><span><?= $msg ?></span></div><?php endif; ?>

  <!-- My Stats -->
  <div class="grid grid-4 mb-3">
    <div class="stat-card">
      <div class="stat-icon"><i class="fas fa-folder"></i></div>
      <div><div class="stat-value"><?= $myCounts['total'] ?></div><div class="stat-label">Total Reports</div></div>
    </div>
    <div class="stat-card">
      <div class="stat-icon" style="background:linear-gradient(135deg,#f59e0b,#ef4444)"><i class="fas fa-clock"></i></div>
      <div><div class="stat-value"><?= $myCounts['pending'] ?></div><div class="stat-label">Pending</div></div>
    </div>
    <div class="stat-card">
      <div class="stat-icon" style="background:linear-gradient(135deg,#3b82f6,#8b5cf6)"><i class="fas fa-search"></i></div>
      <div><div class="stat-value"><?= $myCounts['under_investigation'] ?></div><div class="stat-label">Under Investigation</div></div>
    </div>
    <div class="stat-card">
      <div class="stat-icon" style="background:linear-gradient(135deg,#10b981,#3b82f6)"><i class="fas fa-check"></i></div>
      <div><div class="stat-value"><?= $myCounts['resolved'] ?></div><div class="stat-label">Resolved</div></div>
    </div>
  </div>

  <div class="grid grid-2" style="align-items:start">
    <!-- Recent Cases -->
    <div class="card">
      <div class="d-flex justify-between align-center mb-2">
        <h4 style="color:#e0d7ff"><i class="fas fa-folder-open" style="color:#ec4899"></i> My Recent Cases</h4>
        <a href="<?= APP_URL ?>/complainant/my-cases" class="btn btn-outline btn-sm">View All</a>
      </div>
      <?php if (empty($cases['data'])): ?>
      <div style="text-align:center;padding:2.5rem;color:#7c6fa3">
        <div style="font-size:2.5rem;margin-bottom:0.75rem">📋</div>
        <p>No cases filed yet.</p>
        <a href="<?= APP_URL ?>/complainant/report" class="btn btn-primary btn-sm mt-2">
          <i class="fas fa-plus"></i> File Your First Report
        </a>
      </div>
      <?php else: foreach (array_slice($cases['data'], 0, 5) as $c): ?>
      <div style="display:flex;justify-content:space-between;align-items:center;padding:0.85rem 0;border-bottom:1px solid rgba(255,255,255,0.05)">
        <div>
          <div class="fw-600 text-purple fs-sm"><?= sanitize($c['case_number']) ?></div>
          <div class="fs-sm text-muted"><?= violenceLabel($c['type_of_violence']) ?> — <?= sanitize($c['barangay_name'] ?? '') ?></div>
          <div class="fs-sm text-muted"><?= fmtDate($c['created_at']) ?></div>
        </div>
        <div style="display:flex;align-items:center;gap:0.5rem">
          <span class="badge <?= statusBadge($c['status']) ?>"><?= statusLabel($c['status']) ?></span>
          <a href="<?= APP_URL ?>/complainant/case-view?id=<?= $c['id'] ?>" class="btn btn-outline btn-sm">
            <i class="fas fa-eye"></i>
          </a>
        </div>
      </div>
      <?php endforeach; endif; ?>
    </div>

    <!-- Recent Notifications -->
    <div class="card">
      <h4 style="color:#e0d7ff;margin-bottom:1rem"><i class="fas fa-bell" style="color:#f59e0b"></i> Recent SMS Notifications</h4>
      <?php if (empty($recentNotifs)): ?>
      <p class="text-muted fs-sm" style="text-align:center;padding:1.5rem 0">No notifications yet. You'll receive SMS updates as your case progresses.</p>
      <?php else: foreach ($recentNotifs as $n): ?>
      <div style="padding:0.85rem 0;border-bottom:1px solid rgba(255,255,255,0.05)">
        <div class="fs-sm text-muted"><?= fmtDateTime($n['created_at']) ?></div>
        <p class="fs-sm mt-1" style="color:#c4b5fd"><?= sanitize($n['message']) ?></p>
      </div>
      <?php endforeach; endif; ?>
    </div>
  </div>

  <!-- Quick Action -->
  <div class="card card-glow mt-3" style="background:linear-gradient(135deg,rgba(139,92,246,0.1),rgba(236,72,153,0.08));text-align:center;padding:2rem">
    <div style="font-size:2.5rem;margin-bottom:0.75rem">🛡️</div>
    <h4 style="color:#e0d7ff;margin-bottom:0.4rem">Need to Report an Incident?</h4>
    <p class="text-muted fs-sm mb-2">File your report safely and confidentially. Our team will respond promptly.</p>
    <a href="<?= APP_URL ?>/complainant/report" class="btn btn-primary">
      <i class="fas fa-plus-circle"></i> File a New Report
    </a>
  </div>
</div>

</div></div>
<?php include BASE_PATH . '/views/layouts/footer.php'; ?>
