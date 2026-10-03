<?php
$pageTitle = 'User Management';
$extraCss  = ['/assets/css/dashboard.css'];
include BASE_PATH . '/views/layouts/header.php';
include BASE_PATH . '/views/layouts/admin_nav.php';
?>

<div class="page-content">
  <div class="page-header d-flex justify-between align-center">
    <div><h2>User Management</h2><p>Manage all users: admins, complainants, and barangay staff.</p></div>
    <button class="btn btn-primary btn-sm" onclick="openModal('createUserModal')">
      <i class="fas fa-user-plus"></i> Add User
    </button>
  </div>

  <?php if ($msg = getFlash('success')): ?><div class="alert alert-success"><i class="fas fa-check-circle"></i><span><?= $msg ?></span></div><?php endif; ?>
  <?php if ($msg = getFlash('error')):   ?><div class="alert alert-error"><i class="fas fa-times-circle"></i><span><?= $msg ?></span></div><?php endif; ?>

  <!-- Role Filters -->
  <div class="d-flex gap-1 mb-3 flex-wrap">
    <?php foreach (['' => 'All','admin'=>'Barangay Captain (Admin)','complainant'=>'Complainant','barangay_staff'=>'Barangay Staff'] as $r => $label): ?>
    <a href="?role=<?= $r ?>" class="btn <?= ($roleFilter ?? '') === $r ? 'btn-primary' : 'btn-outline' ?> btn-sm"><?= $label ?></a>
    <?php endforeach; ?>
  </div>

  <div class="card">
    <div class="table-wrapper">
      <table class="table">
        <thead>
          <tr><th>Name</th><th>Email</th><th>Phone</th><th>Role</th><th>Barangay</th><th>Joined</th><th>Status</th><th>Actions</th></tr>
        </thead>
        <tbody>
          <?php if (empty($users['data'])): ?>
          <tr><td colspan="8" class="text-center text-muted" style="padding:2rem">No users found.</td></tr>
          <?php else: foreach ($users['data'] as $u): ?>
          <tr>
            <td class="fw-600"><?= sanitize($u['full_name']) ?></td>
            <td class="text-muted"><?= sanitize($u['email']) ?></td>
            <td class="text-muted"><?= sanitize($u['phone']) ?></td>
            <td>
              <span class="badge <?= $u['role'] === 'admin' ? 'badge-purple' : ($u['role'] === 'barangay_staff' ? 'badge-info' : 'badge-pink') ?>">
                <?= roleLabel($u['role']) ?>
              </span>
            </td>
            <td class="text-muted"><?= sanitize($u['barangay_name'] ?? '—') ?></td>
            <td class="text-muted"><?= fmtDate($u['created_at']) ?></td>
            <td>
              <span class="badge <?= $u['status'] === 'active' ? 'badge-success' : 'badge-secondary' ?>">
                <?= ucfirst($u['status']) ?>
              </span>
            </td>
            <td>
              <div class="d-flex gap-1">
                <?php if ($u['id'] != $_SESSION['user_id']): ?>
                <form method="POST" action="<?= APP_URL ?>/admin/users/toggle-status">
                  <?= csrfField() ?>
                  <input type="hidden" name="id" value="<?= $u['id'] ?>">
                  <button class="btn btn-outline btn-sm" title="Toggle Status">
                    <i class="fas fa-<?= $u['status'] === 'active' ? 'ban' : 'check' ?>"></i>
                  </button>
                </form>
                <form method="POST" action="<?= APP_URL ?>/admin/users/delete"
                  onsubmit="return confirm('Delete <?= sanitize($u['full_name']) ?>?')">
                  <?= csrfField() ?>
                  <input type="hidden" name="id" value="<?= $u['id'] ?>">
                  <button class="btn btn-danger btn-sm" title="Delete"><i class="fas fa-trash"></i></button>
                </form>
                <?php else: ?>
                <span class="text-muted fs-sm">(You)</span>
                <?php endif; ?>
              </div>
            </td>
          </tr>
          <?php endforeach; endif; ?>
        </tbody>
      </table>
    </div>
    <?php if ($users['pages'] > 1): ?>
    <div class="pagination">
      <?php for ($p = 1; $p <= $users['pages']; $p++): ?>
      <a href="?page=<?= $p ?>&role=<?= urlencode($roleFilter ?? '') ?>" class="page-item <?= $p === $users['current'] ? 'active' : '' ?>"><?= $p ?></a>
      <?php endfor; ?>
    </div>
    <?php endif; ?>
  </div>
</div>

<!-- ── Create User Modal ── -->
<div class="modal-overlay" id="createUserModal">
  <div class="modal">
    <div class="modal-header">
      <h3 style="color:#e0d7ff">Add New User</h3>
      <button class="modal-close" onclick="closeModal('createUserModal')">&times;</button>
    </div>
    <form method="POST" action="<?= APP_URL ?>/admin/users/create">
      <?= csrfField() ?>
      <div class="form-group">
        <label class="form-label">Full Name</label>
        <input type="text" name="full_name" class="form-control" required>
      </div>
      <div class="form-group">
        <label class="form-label">Email</label>
        <input type="email" name="email" class="form-control" required>
      </div>
      <div class="form-group">
        <label class="form-label">Phone</label>
        <input type="tel" name="phone" class="form-control" placeholder="09XXXXXXXXX" required>
      </div>
      <div class="form-group">
        <label class="form-label">Role</label>
        <select name="role" class="form-select" id="roleSelect" onchange="toggleBarangay(this.value)">
          <option value="complainant">Complainant</option>
          <option value="barangay_staff">Barangay Staff / VAW Officer</option>
          <option value="admin">Barangay Captain (Admin)</option>
        </select>
      </div>
      <div class="form-group" id="barangayField" style="display:none">
        <label class="form-label">Assign Barangay</label>
        <select name="barangay_id" class="form-select">
          <option value="">— Select Barangay —</option>
          <?php foreach ($barangays as $b): ?>
          <option value="<?= $b['id'] ?>"><?= sanitize($b['name']) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="form-group">
        <label class="form-label">Password</label>
        <input type="text" name="password" class="form-control" value="SafeHer@<?= date('Y') ?>" required>
        <small class="text-muted fs-sm">User should change this after first login.</small>
      </div>
      <button type="submit" class="btn btn-primary w-100 mt-2">
        <i class="fas fa-user-plus"></i> Create User
      </button>
    </form>
  </div>
</div>

</div></div>

<script>
function toggleBarangay(role) {
  document.getElementById('barangayField').style.display = role === 'barangay_staff' ? 'block' : 'none';
}
</script>

<?php include BASE_PATH . '/views/layouts/footer.php'; ?>
