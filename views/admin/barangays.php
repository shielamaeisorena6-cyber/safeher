<?php
$pageTitle = 'Barangay Management';
$extraCss  = ['/assets/css/dashboard.css'];
include BASE_PATH . '/views/layouts/header.php';
include BASE_PATH . '/views/layouts/admin_nav.php';
?>

<div class="page-content">
  <div class="page-header d-flex justify-between align-center">
    <div>
      <h2>Barangay Jurisdiction Profile</h2>
      <p>Official jurisdiction and VAW Desk administration for <?= sanitize(LGU_FULL_NAME) ?>.</p>
    </div>
    <button class="btn btn-primary btn-sm" onclick="openModal('createBarangayModal')">
      <i class="fas fa-plus"></i> Add Sub-Office / Barangay
    </button>
  </div>

  <?php if ($msg = getFlash('success')): ?><div class="alert alert-success"><i class="fas fa-check-circle"></i><span><?= $msg ?></span></div><?php endif; ?>
  <?php if ($msg = getFlash('error')):   ?><div class="alert alert-error"><i class="fas fa-times-circle"></i><span><?= $msg ?></span></div><?php endif; ?>

  <!-- Official Barangay Summary Card -->
  <div class="card mb-3" style="background: linear-gradient(135deg, rgba(139, 92, 246, 0.15), rgba(236, 72, 153, 0.1)); border: 1px solid rgba(139, 92, 246, 0.3);">
    <div class="d-flex justify-between align-center flex-wrap gap-2">
      <div>
        <div class="d-flex align-center gap-2 mb-1">
          <i class="fas fa-shield-alt fa-2x" style="color:#ec4899"></i>
          <div>
            <h3 style="color:#f8fafc;margin:0"><?= sanitize(BARANGAY_NAME) ?></h3>
            <span class="text-muted fs-sm"><?= sanitize(MUNICIPALITY_NAME) ?>, <?= sanitize(PROVINCE_NAME) ?> &bull; Single Barangay System Jurisdiction</span>
          </div>
        </div>
      </div>
      <div class="d-flex gap-2">
        <span class="badge badge-purple" style="font-size:0.9rem;padding:0.4rem 0.8rem">
          <i class="fas fa-map-marked-alt"></i> <?= count(BARANGAY_PUROKS) ?> Puroks / Zones
        </span>
        <span class="badge badge-success" style="font-size:0.9rem;padding:0.4rem 0.8rem">
          <i class="fas fa-user-shield"></i> Active VAW Desk
        </span>
      </div>
    </div>

    <!-- Puroks Grid -->
    <div style="margin-top:1.25rem;padding-top:1.25rem;border-top:1px solid rgba(255,255,255,0.08)">
      <h5 style="color:#e0d7ff;margin-bottom:0.75rem"><i class="fas fa-map-signs" style="color:#8b5cf6"></i> Registered Puroks / Zones in <?= sanitize(BARANGAY_NAME) ?>:</h5>
      <div class="d-flex flex-wrap gap-2">
        <?php foreach (BARANGAY_PUROKS as $purok): ?>
        <span class="badge" style="background:rgba(255,255,255,0.06);border:1px solid rgba(139,92,246,0.25);color:#cbd5e1;padding:0.35rem 0.75rem">
          <i class="fas fa-map-pin" style="color:#ec4899;font-size:0.75rem;margin-right:4px"></i> <?= sanitize($purok) ?>
        </span>
        <?php endforeach; ?>
      </div>
    </div>
  </div>

  <div class="card">
    <div class="card-header d-flex justify-between align-center mb-2">
      <h4 style="color:#e0d7ff;margin:0"><i class="fas fa-building" style="color:#8b5cf6"></i> Barangay Registry & Desk Assignments</h4>
    </div>
    <div class="table-wrapper">
      <table class="table">
        <thead>
          <tr><th>#</th><th>Barangay Name</th><th>Municipality</th><th>Province</th><th>Assigned Staff</th><th>Total Cases</th><th>Actions</th></tr>
        </thead>
        <tbody>
          <?php if (empty($barangays['data'])): ?>
          <tr><td colspan="7" class="text-center text-muted" style="padding:2rem">No barangays added yet.</td></tr>
          <?php else: $i = 1; foreach ($barangays['data'] as $b): ?>
          <tr>
            <td class="text-muted"><?= $i++ ?></td>
            <td class="fw-600 text-primary"><?= sanitize($b['name']) ?></td>
            <td class="text-muted"><?= sanitize($b['municipality']) ?></td>
            <td class="text-muted"><?= sanitize($b['province']) ?></td>
            <td><?= sanitize($b['staff_name'] ?? '<span class="text-muted">Unassigned</span>') ?></td>
            <td><span class="badge badge-purple"><?= (int)$b['case_count'] ?></span></td>
            <td>
              <div class="d-flex gap-1">
                <button class="btn btn-outline btn-sm" onclick='openEditBarangay(<?= json_encode($b) ?>)'>
                  <i class="fas fa-edit"></i>
                </button>
                <form method="POST" action="<?= APP_URL ?>/admin/barangays/delete"
                  onsubmit="return confirm('Delete <?= sanitize($b['name']) ?>?')">
                  <?= csrfField() ?>
                  <input type="hidden" name="id" value="<?= $b['id'] ?>">
                  <button class="btn btn-danger btn-sm"><i class="fas fa-trash"></i></button>
                </form>
              </div>
            </td>
          </tr>
          <?php endforeach; endif; ?>
        </tbody>
      </table>
    </div>
    <?php if ($barangays['pages'] > 1): ?>
    <div class="pagination">
      <?php for ($p = 1; $p <= $barangays['pages']; $p++): ?>
      <a href="?page=<?= $p ?>" class="page-item <?= $p === $barangays['current'] ? 'active' : '' ?>"><?= $p ?></a>
      <?php endfor; ?>
    </div>
    <?php endif; ?>
  </div>
</div>

<!-- Create Modal -->
<div class="modal-overlay" id="createBarangayModal">
  <div class="modal">
    <div class="modal-header">
      <h3 style="color:#e0d7ff">Add Barangay</h3>
      <button class="modal-close" onclick="closeModal('createBarangayModal')">&times;</button>
    </div>
    <form method="POST" action="<?= APP_URL ?>/admin/barangays/create">
      <?= csrfField() ?>
      <div class="form-group"><label class="form-label">Barangay Name</label>
        <input type="text" name="name" class="form-control" required></div>
      <div class="form-group"><label class="form-label">Municipality</label>
        <input type="text" name="municipality" class="form-control" required></div>
      <div class="form-group"><label class="form-label">Province</label>
        <input type="text" name="province" class="form-control" required></div>
      <div class="form-group"><label class="form-label">Assigned Staff</label>
        <select name="assigned_staff_id" class="form-select">
          <option value="">— None —</option>
          <?php foreach ($staffList as $s): ?>
          <option value="<?= $s['id'] ?>"><?= sanitize($s['full_name']) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <button type="submit" class="btn btn-primary w-100 mt-2"><i class="fas fa-plus"></i> Add Barangay</button>
    </form>
  </div>
</div>

<!-- Edit Modal -->
<div class="modal-overlay" id="editBarangayModal">
  <div class="modal">
    <div class="modal-header">
      <h3 style="color:#e0d7ff">Edit Barangay</h3>
      <button class="modal-close" onclick="closeModal('editBarangayModal')">&times;</button>
    </div>
    <form method="POST" action="<?= APP_URL ?>/admin/barangays/update">
      <?= csrfField() ?>
      <input type="hidden" name="id" id="editId">
      <div class="form-group"><label class="form-label">Barangay Name</label>
        <input type="text" name="name" id="editName" class="form-control" required></div>
      <div class="form-group"><label class="form-label">Municipality</label>
        <input type="text" name="municipality" id="editMunicipality" class="form-control" required></div>
      <div class="form-group"><label class="form-label">Province</label>
        <input type="text" name="province" id="editProvince" class="form-control" required></div>
      <div class="form-group"><label class="form-label">Assigned Staff</label>
        <select name="assigned_staff_id" id="editStaff" class="form-select">
          <option value="">— None —</option>
          <?php foreach ($staffList as $s): ?>
          <option value="<?= $s['id'] ?>"><?= sanitize($s['full_name']) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <button type="submit" class="btn btn-primary w-100 mt-2"><i class="fas fa-save"></i> Save Changes</button>
    </form>
  </div>
</div>

</div></div>

<script>
function openEditBarangay(b) {
  document.getElementById('editId').value           = b.id;
  document.getElementById('editName').value         = b.name;
  document.getElementById('editMunicipality').value = b.municipality;
  document.getElementById('editProvince').value     = b.province;
  document.getElementById('editStaff').value        = b.assigned_staff_id || '';
  openModal('editBarangayModal');
}
</script>

<?php include BASE_PATH . '/views/layouts/footer.php'; ?>
