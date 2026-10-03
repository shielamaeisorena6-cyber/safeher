 <?php
$pageTitle = 'Barangay Captain Dashboard';
$extraCss  = ['/assets/css/dashboard.css'];
$extraJs   = ['https://cdn.jsdelivr.net/npm/chart.js', '/assets/js/charts.js'];
include BASE_PATH . '/views/layouts/header.php';
include BASE_PATH . '/views/layouts/admin_nav.php';

// Prepare chart data for inline scripts
$caseModel  = new CaseModel();
$trend      = $caseModel->monthlyTrend(6);
$vtData     = $caseModel->countByViolenceType();
?>

<div class="page-content">
  <div class="page-header">
    <h2>Barangay Captain Dashboard</h2>
    <p>Welcome back, Captain <?= sanitize($_SESSION['full_name'] ?? 'Admin') ?>. Here is the latest executive summary and case analytics.</p>
  </div>

  <!-- Flash Messages -->
  <?php if ($msg = getFlash('success')): ?><div class="alert alert-success"><i class="fas fa-check-circle"></i><span><?= $msg ?></span></div><?php endif; ?>
  <?php if ($msg = getFlash('error')):   ?><div class="alert alert-error"  ><i class="fas fa-times-circle"></i><span><?= $msg ?></span></div><?php endif; ?>

  <!-- ── Stat Cards ── -->
  <div class="grid grid-4 mb-3">
    <div class="stat-card">
      <div class="stat-icon"><i class="fas fa-folder-open"></i></div>
      <div>
        <div class="stat-value"><?= $stats['total'] ?></div>
        <div class="stat-label">Total Cases</div>
      </div>
    </div>
    <div class="stat-card">
      <div class="stat-icon" style="background:linear-gradient(135deg,#f59e0b,#ef4444)"><i class="fas fa-clock"></i></div>
      <div>
        <div class="stat-value"><?= $stats['pending'] ?></div>
        <div class="stat-label">Pending</div>
      </div>
    </div>
    <div class="stat-card">
      <div class="stat-icon" style="background:linear-gradient(135deg,#10b981,#3b82f6)"><i class="fas fa-check-circle"></i></div>
      <div>
        <div class="stat-value"><?= $stats['resolved'] ?></div>
        <div class="stat-label">Resolved</div>
      </div>
    </div>
    <div class="stat-card">
      <div class="stat-icon" style="background:linear-gradient(135deg,#3b82f6,#8b5cf6)"><i class="fas fa-users"></i></div>
      <div>
        <div class="stat-value"><?= $totalUsers ?></div>
        <div class="stat-label">Total Users</div>
      </div>
    </div>
    <div class="stat-card">
      <div class="stat-icon" style="background:linear-gradient(135deg,#6366f1,#a855f7)"><i class="fas fa-search"></i></div>
      <div>
        <div class="stat-value"><?= $stats['under_investigation'] ?></div>
        <div class="stat-label">Under Investigation</div>
      </div>
    </div>
    <div class="stat-card">
      <div class="stat-icon" style="background:linear-gradient(135deg,#14b8a6,#0ea5e9)"><i class="fas fa-address-book"></i></div>
      <div>
        <div class="stat-value"><?= $residentCnt ?? 0 ?></div>
        <div class="stat-label">Residents Profiled</div>
      </div>
    </div>
    <div class="stat-card">
      <div class="stat-icon" style="background:linear-gradient(135deg,#ec4899,#8b5cf6)"><i class="fas fa-comment-sms"></i></div>
      <div>
        <div class="stat-value"><?= $smsSent ?></div>
        <div class="stat-label">SMS Sent</div>
      </div>
    </div>
    <div class="stat-card">
      <div class="stat-icon" style="background:linear-gradient(135deg,#f97316,#ef4444)"><i class="fas fa-ban"></i></div>
      <div>
        <div class="stat-value"><?= $stats['dismissed'] ?></div>
        <div class="stat-label">Dismissed</div>
      </div>
    </div>
  </div>

  <!-- ── Charts Row ── -->
  <div class="grid grid-2 mb-3">
    <div class="chart-card">
      <div class="chart-title"><i class="fas fa-chart-line"></i> Cases Trend (6 Months)</div>
      <div class="chart-container">
        <canvas id="trendChart"></canvas>
      </div>
    </div>
    <div class="chart-card">
      <div class="chart-title"><i class="fas fa-chart-pie"></i> Violence Type Distribution</div>
      <div class="chart-container">
        <canvas id="violenceChart"></canvas>
      </div>
    </div>
  </div>

  <!-- ── Recent Cases Table ── -->
  <div class="card">
    <div class="d-flex justify-between align-center mb-2">
      <h4 style="color:#e0d7ff"><i class="fas fa-list" style="color:#8b5cf6"></i> Recent Cases</h4>
      <a href="<?= APP_URL ?>/admin/cases" class="btn btn-outline btn-sm">View All</a>
    </div>
    <div class="table-wrapper">
      <table class="table">
        <thead>
          <tr>
            <th>Case No.</th>
            <th>Complainant</th>
            <th>Barangay</th>
            <th>Type</th>
            <th>Filed On</th>
            <th>Status</th>
            <th>Action</th>
          </tr>
        </thead>
        <tbody>
          <?php if (empty($recentCases)): ?>
          <tr><td colspan="7" class="text-center text-muted" style="padding:2rem">No cases filed yet.</td></tr>
          <?php else: foreach ($recentCases as $c): ?>
          <tr>
            <td><span class="fw-600 text-purple"><?= sanitize($c['case_number']) ?></span></td>
            <td><?= sanitize($c['complainant_name'] ?? '—') ?></td>
            <td><?= sanitize($c['barangay_name'] ?? '—') ?></td>
            <td><?= violenceLabel($c['type_of_violence']) ?></td>
            <td><?= fmtDate($c['created_at']) ?></td>
            <td><span class="badge <?= statusBadge($c['status']) ?>"><?= statusLabel($c['status']) ?></span></td>
            <td>
              <a href="<?= APP_URL ?>/admin/cases/view?id=<?= $c['id'] ?>" class="btn btn-outline btn-sm">
                <i class="fas fa-eye"></i>
              </a>
            </td>
          </tr>
          <?php endforeach; endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div><!-- page-content -->

</div><!-- main-content -->
</div><!-- layout -->

<script>
const trendLabels = <?= json_encode(array_column($trend, 'month')) ?>;
const trendValues = <?= json_encode(array_map('intval', array_column($trend, 'total'))) ?>;
const vtLabels = <?= json_encode(array_map(fn($r) => violenceLabel($r['type_of_violence']), $vtData)) ?>;
const vtValues = <?= json_encode(array_map('intval', array_column($vtData, 'total'))) ?>;
</script>

<?php include BASE_PATH . '/views/layouts/footer.php'; ?>