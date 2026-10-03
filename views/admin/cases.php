<?php
$pageTitle = 'Case Management';
$extraCss  = ['/assets/css/dashboard.css'];
include BASE_PATH . '/views/layouts/header.php';
include BASE_PATH . '/views/layouts/admin_nav.php';
?>

<div class="page-content">
  <div class="page-header d-flex justify-between align-center">
    <div>
      <h2>Case Management</h2>
      <p>View, filter, and manage all VAWC cases across barangays.</p>
    </div>
    <a href="<?= APP_URL ?>/admin/analytics/export-csv" class="btn btn-outline btn-sm">
      <i class="fas fa-download"></i> Export CSV
    </a>
  </div>

  <?php if ($msg = getFlash('success')): ?><div class="alert alert-success"><i class="fas fa-check-circle"></i><span><?= $msg ?></span></div><?php endif; ?>
  <?php if ($msg = getFlash('error')):   ?><div class="alert alert-error"><i class="fas fa-times-circle"></i><span><?= $msg ?></span></div><?php endif; ?>

  <!-- Filters -->
  <div class="card mb-3">
    <form method="GET" action="<?= APP_URL ?>/admin/cases" class="filter-bar" id="filterForm">
      <input type="text" name="search" class="form-control" placeholder="Search case no. or complainant..."
        value="<?= sanitize($filters['search'] ?? '') ?>" style="min-width:220px">
      <select name="status" class="form-select">
        <option value="">All Statuses</option>
        <?php foreach (['pending','under_investigation','resolved','dismissed'] as $s): ?>
        <option value="<?= $s ?>" <?= ($filters['status'] ?? '') === $s ? 'selected' : '' ?>><?= statusLabel($s) ?></option>
        <?php endforeach; ?>
      </select>
      <select name="type" class="form-select">
        <option value="">All Types</option>
        <?php foreach (['physical','sexual','psychological','economic','others'] as $t): ?>
        <option value="<?= $t ?>" <?= ($filters['type_of_violence'] ?? '') === $t ? 'selected' : '' ?>><?= violenceLabel($t) ?></option>
        <?php endforeach; ?>
      </select>
      <select name="barangay_id" class="form-select">
        <option value="">All Barangays</option>
        <?php foreach ($barangays as $b): ?>
        <option value="<?= $b['id'] ?>" <?= ($filters['barangay_id'] ?? '') == $b['id'] ? 'selected' : '' ?>><?= sanitize($b['name']) ?></option>
        <?php endforeach; ?>
      </select>
      <button type="submit" class="btn btn-primary btn-sm"><i class="fas fa-search"></i> Filter</button>
      <a href="<?= APP_URL ?>/admin/cases" class="btn btn-outline btn-sm"><i class="fas fa-times"></i> Clear</a>
    </form>
  </div>

  <!-- Table -->
  <div class="card">
    <div class="d-flex justify-between align-center mb-2">
      <span class="text-muted fs-sm">Showing <?= count($cases['data']) ?> of <?= $cases['total'] ?> cases</span>
    </div>
    <div class="table-wrapper">
      <table class="table">
        <thead>
          <tr>
            <th>Case No.</th>
            <th>Complainant</th>
            <th>Barangay</th>
            <th>Type of Violence</th>
            <th>Incident Date</th>
            <th>Filed On</th>
            <th>Status</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php if (empty($cases['data'])): ?>
          <tr><td colspan="8" class="text-center text-muted" style="padding:3rem">No cases found matching your filters.</td></tr>
          <?php else: foreach ($cases['data'] as $c): ?>
          <tr>
            <td><span class="fw-600 text-purple"><?= sanitize($c['case_number']) ?></span></td>
            <td><?= sanitize($c['complainant_name'] ?? '—') ?></td>
            <td><?= sanitize($c['barangay_name'] ?? '—') ?></td>
            <td><?= violenceLabel($c['type_of_violence']) ?></td>
            <td><?= fmtDate($c['incident_date']) ?></td>
            <td><?= fmtDate($c['created_at']) ?></td>
            <td><span class="badge <?= statusBadge($c['status']) ?>"><?= statusLabel($c['status']) ?></span></td>
            <td>
              <div class="d-flex gap-1">
                <a href="<?= APP_URL ?>/admin/cases/view?id=<?= $c['id'] ?>" class="btn btn-outline btn-sm" title="View">
                  <i class="fas fa-eye"></i>
                </a>
                <form method="POST" action="<?= APP_URL ?>/admin/cases/delete"
                  onsubmit="return confirm('Delete case <?= sanitize($c['case_number']) ?>? This cannot be undone.')">
                  <?= csrfField() ?>
                  <input type="hidden" name="id" value="<?= $c['id'] ?>">
                  <button type="submit" class="btn btn-danger btn-sm" title="Delete"><i class="fas fa-trash"></i></button>
                </form>
              </div>
            </td>
          </tr>
          <?php endforeach; endif; ?>
        </tbody>
      </table>
    </div>

    <!-- Pagination -->
    <?php if ($cases['pages'] > 1): ?>
    <div class="pagination">
      <?php for ($p = 1; $p <= $cases['pages']; $p++): ?>
      <a href="?page=<?= $p ?>&status=<?= urlencode($filters['status'] ?? '') ?>&search=<?= urlencode($filters['search'] ?? '') ?>"
        class="page-item <?= $p === $cases['current'] ? 'active' : '' ?>"><?= $p ?></a>
      <?php endfor; ?>
    </div>
    <?php endif; ?>
  </div>
</div>

</div></div>
<?php include BASE_PATH . '/views/layouts/footer.php'; ?>
