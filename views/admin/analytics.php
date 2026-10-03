<?php
$pageTitle = 'Case Analytics';
$extraCss  = ['/assets/css/dashboard.css'];
$extraJs   = ['https://cdn.jsdelivr.net/npm/chart.js', '/assets/js/charts.js'];
include BASE_PATH . '/views/layouts/header.php';
include BASE_PATH . '/views/layouts/admin_nav.php';
?>

<div class="page-content">
  <div class="page-header d-flex justify-between align-center">
    <div><h2>Case Analytics</h2><p>Data-driven insights to improve VAWC response times.</p></div>
    <a href="<?= APP_URL ?>/admin/analytics/export-csv" class="btn btn-outline btn-sm">
      <i class="fas fa-file-csv"></i> Export CSV
    </a>
  </div>

  <!-- Summary Cards -->
  <div class="grid grid-4 mb-3">
    <div class="stat-card">
      <div class="stat-icon"><i class="fas fa-folder"></i></div>
      <div><div class="stat-value"><?= $statusSummary['total'] ?></div><div class="stat-label">Total Cases</div></div>
    </div>
    <div class="stat-card">
      <div class="stat-icon" style="background:linear-gradient(135deg,#10b981,#3b82f6)"><i class="fas fa-check"></i></div>
      <div><div class="stat-value"><?= $resolvedRate ?>%</div><div class="stat-label">Resolution Rate</div></div>
    </div>
    <div class="stat-card">
      <div class="stat-icon" style="background:linear-gradient(135deg,#f59e0b,#ef4444)"><i class="fas fa-exclamation-circle"></i></div>
      <div><div class="stat-value"><?= $statusSummary['pending'] ?></div><div class="stat-label">Pending Action</div></div>
    </div>
    <div class="stat-card">
      <div class="stat-icon" style="background:linear-gradient(135deg,#3b82f6,#6366f1)"><i class="fas fa-search"></i></div>
      <div><div class="stat-value"><?= $statusSummary['under_investigation'] ?></div><div class="stat-label">Under Investigation</div></div>
    </div>
  </div>

  <!-- Charts Row 1 -->
  <div class="grid grid-2 mb-3">
    <div class="chart-card">
      <div class="chart-title"><i class="fas fa-chart-line"></i> Monthly Case Trend (12 Months)</div>
      <div class="chart-container"><canvas id="trendChart"></canvas></div>
    </div>
    <div class="chart-card">
      <div class="chart-title"><i class="fas fa-chart-pie"></i> Type of Violence</div>
      <div class="chart-container"><canvas id="violenceChart"></canvas></div>
    </div>
  </div>

  <!-- Charts Row 2 -->
  <div class="grid grid-2 mb-3">
    <div class="chart-card">
      <div class="chart-title"><i class="fas fa-map-marker-alt" style="color:#ec4899"></i> Cases per Purok / Zone (Gogon Centro)</div>
      <div class="chart-container"><canvas id="barangayChart"></canvas></div>
    </div>
    <div class="chart-card">
      <div class="chart-title"><i class="fas fa-chart-pie"></i> Case Status Distribution</div>
      <div class="chart-container"><canvas id="statusChart"></canvas></div>
    </div>
  </div>

  <!-- Purok Ranking Table -->
  <div class="card mb-3">
    <h4 style="color:#e0d7ff;margin-bottom:1rem"><i class="fas fa-trophy" style="color:#f59e0b"></i> Purok / Zone Case Ranking (Barangay Gogon Centro)</h4>
    <div class="table-wrapper">
      <table class="table">
        <thead><tr><th>Rank</th><th>Purok / Zone</th><th>Total Cases</th><th>Share</th></tr></thead>
        <tbody>
          <?php $rank = 1; $total = $statusSummary['total'] ?: 1; foreach ($byPurok as $row): ?>
          <tr>
            <td><span class="badge badge-purple">#<?= $rank++ ?></span></td>
            <td class="fw-600"><?= sanitize($row['purok']) ?></td>
            <td><?= (int)$row['total'] ?></td>
            <td>
              <div style="display:flex;align-items:center;gap:0.5rem">
                <div style="flex:1;height:6px;background:rgba(255,255,255,0.1);border-radius:3px">
                  <div style="width:<?= round(($row['total']/$total)*100) ?>%;height:100%;background:linear-gradient(135deg,#8b5cf6,#ec4899);border-radius:3px"></div>
                </div>
                <span class="fs-sm text-muted"><?= round(($row['total']/$total)*100, 1) ?>%</span>
              </div>
            </td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>

  <!-- Violence Type Table -->
  <div class="card">
    <h4 style="color:#e0d7ff;margin-bottom:1rem"><i class="fas fa-list-ul" style="color:#8b5cf6"></i> Violence Type Breakdown</h4>
    <div class="table-wrapper">
      <table class="table">
        <thead><tr><th>Type of Violence</th><th>Cases</th><th>Percentage</th></tr></thead>
        <tbody>
          <?php foreach ($byViolenceType as $row): ?>
          <tr>
            <td class="fw-600"><?= violenceLabel($row['type_of_violence']) ?></td>
            <td><?= (int)$row['total'] ?></td>
            <td>
              <div style="display:flex;align-items:center;gap:0.5rem">
                <div style="flex:1;height:6px;background:rgba(255,255,255,0.1);border-radius:3px">
                  <div style="width:<?= round(($row['total']/$total)*100) ?>%;height:100%;background:linear-gradient(135deg,#ec4899,#f97316);border-radius:3px"></div>
                </div>
                <span class="fs-sm text-muted"><?= round(($row['total']/$total)*100, 1) ?>%</span>
              </div>
            </td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

</div></div>

<script>
const trendLabels  = <?= $trendLabels ?>;
const trendValues  = <?= $trendData  ?>;
const vtLabels     = <?= $vtLabels   ?>;
const vtValues     = <?= $vtData     ?>;
const brLabels     = <?= $purokLabels ?>;
const brValues     = <?= $purokData   ?>;
const statusLabels = ['Pending','Under Investigation','Resolved','Dismissed'];
const statusValues = [<?= $statusSummary['pending'] ?>,<?= $statusSummary['under_investigation'] ?>,<?= $statusSummary['resolved'] ?>,<?= $statusSummary['dismissed'] ?>];
</script>

<?php include BASE_PATH . '/views/layouts/footer.php'; ?>
