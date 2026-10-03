<?php
$pageTitle = 'Assigned Cases';
$extraCss  = ['/assets/css/dashboard.css'];
include BASE_PATH . '/views/layouts/header.php';
include BASE_PATH . '/views/layouts/barangay_nav.php';
?>

<div class="page-content">
  <div class="page-header"><h2>Assigned Cases</h2><p>All cases assigned to your barangay.</p></div>

  <?php if ($msg = getFlash('success')): ?><div class="alert alert-success"><i class="fas fa-check-circle"></i><span><?= $msg ?></span></div><?php endif; ?>

  <!-- Status Filter Tabs -->
  <div class="d-flex gap-1 mb-3 flex-wrap">
    <?php
    $statuses = ['' => 'All','pending'=>'Pending','under_investigation'=>'Under Investigation','resolved'=>'Resolved','dismissed'=>'Dismissed'];
    foreach ($statuses as $s => $l): ?>
    <a href="?status=<?= $s ?>" class="btn <?= $statusFilter === $s ? 'btn-primary' : 'btn-outline' ?> btn-sm"><?= $l ?></a>
    <?php endforeach; ?>
  </div>

  <div class="card">
    <div class="d-flex justify-between align-center mb-2">
      <span class="text-muted fs-sm"><?= $cases['total'] ?> cases found</span>
    </div>
    <div class="table-wrapper">
      <table class="table">
        <thead><tr><th>Case No.</th><th>Complainant</th><th>Type of Violence</th><th>Incident Date</th><th>Filed On</th><th>Status</th><th>Action</th></tr></thead>
        <tbody>
          <?php if (empty($cases['data'])): ?>
          <tr><td colspan="7" class="text-center text-muted" style="padding:2.5rem">No cases found.</td></tr>
          <?php else: foreach ($cases['data'] as $c): ?>
          <tr>
            <td class="fw-600 text-purple"><?= sanitize($c['case_number']) ?></td>
            <td><?= sanitize($c['complainant_name'] ?? '—') ?></td>
            <td><?= violenceLabel($c['type_of_violence']) ?></td>
            <td class="text-muted"><?= fmtDate($c['incident_date']) ?></td>
            <td class="text-muted"><?= fmtDate($c['created_at']) ?></td>
            <td><span class="badge <?= statusBadge($c['status']) ?>"><?= statusLabel($c['status']) ?></span></td>
            <td>
              <a href="<?= APP_URL ?>/barangay/case-view?id=<?= $c['id'] ?>" class="btn btn-outline btn-sm">
                <i class="fas fa-eye"></i> View
              </a>
            </td>
          </tr>
          <?php endforeach; endif; ?>
        </tbody>
      </table>
    </div>
    <?php if ($cases['pages'] > 1): ?>
    <div class="pagination">
      <?php for ($p=1; $p<=$cases['pages']; $p++): ?>
      <a href="?page=<?= $p ?>&status=<?= urlencode($statusFilter) ?>" class="page-item <?= $p===$cases['current']?'active':'' ?>"><?= $p ?></a>
      <?php endfor; ?>
    </div>
    <?php endif; ?>
  </div>
</div>

</div></div>
<?php include BASE_PATH . '/views/layouts/footer.php'; ?>
