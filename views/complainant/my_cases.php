<?php
$pageTitle = 'My Cases';
$extraCss  = ['/assets/css/dashboard.css'];
include BASE_PATH . '/views/layouts/header.php';
include BASE_PATH . '/views/layouts/complainant_nav.php';
?>

<div class="page-content">
  <div class="page-header d-flex justify-between align-center">
    <div><h2>My Cases</h2><p>All reports you have filed through SafeHer.</p></div>
    <a href="<?= APP_URL ?>/complainant/report" class="btn btn-primary btn-sm">
      <i class="fas fa-plus"></i> New Report
    </a>
  </div>

  <?php if ($msg = getFlash('success')): ?><div class="alert alert-success"><i class="fas fa-check-circle"></i><span><?= $msg ?></span></div><?php endif; ?>

  <div class="card">
    <?php if (empty($cases['data'])): ?>
    <div style="text-align:center;padding:4rem;color:#7c6fa3">
      <div style="font-size:3rem;margin-bottom:1rem">📂</div>
      <h4 style="color:#c4b5fd;margin-bottom:0.5rem">No Cases Filed Yet</h4>
      <p class="fs-sm">Your filed reports will appear here.</p>
      <a href="<?= APP_URL ?>/complainant/report" class="btn btn-primary mt-2">
        <i class="fas fa-plus-circle"></i> File Your First Report
      </a>
    </div>
    <?php else: ?>
    <div class="table-wrapper">
      <table class="table">
        <thead>
          <tr><th>Case No.</th><th>Type</th><th>Barangay</th><th>Incident Date</th><th>Filed On</th><th>Status</th><th>View</th></tr>
        </thead>
        <tbody>
          <?php foreach ($cases['data'] as $c): ?>
          <tr>
            <td class="fw-600 text-purple"><?= sanitize($c['case_number']) ?></td>
            <td><?= violenceLabel($c['type_of_violence']) ?></td>
            <td class="text-muted"><?= sanitize($c['barangay_name'] ?? '—') ?></td>
            <td class="text-muted"><?= fmtDate($c['incident_date']) ?></td>
            <td class="text-muted"><?= fmtDate($c['created_at']) ?></td>
            <td><span class="badge <?= statusBadge($c['status']) ?>"><?= statusLabel($c['status']) ?></span></td>
            <td>
              <a href="<?= APP_URL ?>/complainant/case-view?id=<?= $c['id'] ?>" class="btn btn-outline btn-sm">
                <i class="fas fa-eye"></i> View
              </a>
            </td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
    <?php if ($cases['pages'] > 1): ?>
    <div class="pagination">
      <?php for ($p=1; $p<=$cases['pages']; $p++): ?>
      <a href="?page=<?= $p ?>" class="page-item <?= $p===$cases['current']?'active':'' ?>"><?= $p ?></a>
      <?php endfor; ?>
    </div>
    <?php endif; ?>
    <?php endif; ?>
  </div>
</div>

</div></div>
<?php include BASE_PATH . '/views/layouts/footer.php'; ?>
