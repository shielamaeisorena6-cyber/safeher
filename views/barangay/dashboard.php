<?php
$pageTitle = 'Barangay Dashboard';
$extraCss  = ['/assets/css/dashboard.css'];
include BASE_PATH . '/views/layouts/header.php';
include BASE_PATH . '/views/layouts/barangay_nav.php';
?>

<div class="page-content">
  <div class="page-header">
    <h2>Barangay Dashboard</h2>
    <p>Cases assigned to <strong style="color:#93c5fd"><?= sanitize($barangay['name'] ?? '') ?></strong>, <?= sanitize($barangay['municipality'] ?? '') ?></p>
  </div>

  <?php if ($msg = getFlash('success')): ?><div class="alert alert-success"><i class="fas fa-check-circle"></i><span><?= $msg ?></span></div><?php endif; ?>

  <!-- Stats -->
  <div class="grid grid-4 mb-3">
    <div class="stat-card">
      <div class="stat-icon"><i class="fas fa-folder"></i></div>
      <div><div class="stat-value"><?= $stats['total'] ?></div><div class="stat-label">Total Cases</div></div>
    </div>
    <div class="stat-card">
      <div class="stat-icon" style="background:linear-gradient(135deg,#f59e0b,#ef4444)"><i class="fas fa-clock"></i></div>
      <div><div class="stat-value"><?= $stats['pending'] ?></div><div class="stat-label">Pending</div></div>
    </div>
    <div class="stat-card">
      <div class="stat-icon" style="background:linear-gradient(135deg,#3b82f6,#8b5cf6)"><i class="fas fa-search"></i></div>
      <div><div class="stat-value"><?= $stats['under_investigation'] ?></div><div class="stat-label">Under Investigation</div></div>
    </div>
    <div class="stat-card">
      <div class="stat-icon" style="background:linear-gradient(135deg,#10b981,#3b82f6)"><i class="fas fa-check-circle"></i></div>
      <div><div class="stat-value"><?= $stats['resolved'] ?></div><div class="stat-label">Resolved</div></div>
    </div>
  </div>

  <!-- Recent Cases -->
  <div class="card">
    <div class="d-flex justify-between align-center mb-2">
      <h4 style="color:#e0d7ff"><i class="fas fa-list" style="color:#3b82f6"></i> Recent Cases in Your Barangay</h4>
      <a href="<?= APP_URL ?>/barangay/cases" class="btn btn-outline btn-sm">View All</a>
    </div>
    <?php if (empty($recentCases)): ?>
    <div style="text-align:center;padding:2.5rem;color:#7c6fa3">
      <div style="font-size:2rem;margin-bottom:0.5rem">✅</div>
      <p>No cases assigned to your barangay yet.</p>
    </div>
    <?php else: ?>
    <div class="table-wrapper">
      <table class="table">
        <thead><tr><th>Case No.</th><th>Complainant</th><th>Type</th><th>Incident Date</th><th>Status</th><th>Action</th></tr></thead>
        <tbody>
          <?php foreach ($recentCases as $c): ?>
          <tr>
            <td class="fw-600 text-purple"><?= sanitize($c['case_number']) ?></td>
            <td><?= sanitize($c['complainant_name'] ?? '—') ?></td>
            <td><?= violenceLabel($c['type_of_violence']) ?></td>
            <td class="text-muted"><?= fmtDate($c['incident_date']) ?></td>
            <td><span class="badge <?= statusBadge($c['status']) ?>"><?= statusLabel($c['status']) ?></span></td>
            <td>
              <a href="<?= APP_URL ?>/barangay/case-view?id=<?= $c['id'] ?>" class="btn btn-outline btn-sm">
                <i class="fas fa-eye"></i> View
              </a>
            </td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
    <?php endif; ?>
  </div>
</div>

</div></div>
<?php include BASE_PATH . '/views/layouts/footer.php'; ?>
