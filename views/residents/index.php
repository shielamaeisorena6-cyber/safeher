<?php
$pageTitle = 'Resident Profiling';
$extraCss  = ['/assets/css/dashboard.css'];

$navLayout = userRole() === 'admin' ? 'admin' : 'barangay';
include BASE_PATH . '/views/layouts/header.php';
include BASE_PATH . "/views/layouts/{$navLayout}_nav.php";

$isStaff = userRole() === 'barangay_staff';
?>

<div class="page-content">
  <div class="page-header d-flex justify-between align-center flex-wrap gap-2">
    <div>
      <h2><i class="fas fa-address-book" style="color:#8b5cf6"></i> Resident Profiling</h2>
      <p>Registry of barangay residents for community tracking, protection, and VAWC monitoring.</p>
    </div>
    <div class="d-flex gap-2">
      <a href="<?= APP_URL ?>/residents/export-csv<?= !empty($_GET['barangay_id']) ? '?barangay_id=' . (int)$_GET['barangay_id'] : '' ?>" class="btn btn-outline btn-sm">
        <i class="fas fa-file-csv"></i> Export Roster
      </a>
      <button class="btn btn-primary btn-sm" onclick="openModal('addResidentModal')">
        <i class="fas fa-user-plus"></i> Add Resident
      </button>
    </div>
  </div>

  <!-- Flash Messages -->
  <?php if ($msg = getFlash('success')): ?><div class="alert alert-success"><i class="fas fa-check-circle"></i><span><?= $msg ?></span></div><?php endif; ?>
  <?php if ($msg = getFlash('error')):   ?><div class="alert alert-error"><i class="fas fa-times-circle"></i><span><?= $msg ?></span></div><?php endif; ?>

  <!-- ── Stat Cards ── -->
  <div class="grid grid-4 mb-3">
    <div class="stat-card">
      <div class="stat-icon"><i class="fas fa-users"></i></div>
      <div>
        <div class="stat-value"><?= (int)$stats['total'] ?></div>
        <div class="stat-label">Total Residents</div>
      </div>
    </div>
    <div class="stat-card">
      <div class="stat-icon" style="background:linear-gradient(135deg,#ec4899,#f472b6)"><i class="fas fa-female"></i></div>
      <div>
        <div class="stat-value"><?= (int)$stats['female'] ?></div>
        <div class="stat-label">Female Population</div>
      </div>
    </div>
    <div class="stat-card">
      <div class="stat-icon" style="background:linear-gradient(135deg,#3b82f6,#60a5fa)"><i class="fas fa-male"></i></div>
      <div>
        <div class="stat-value"><?= (int)$stats['male'] ?></div>
        <div class="stat-label">Male Population</div>
      </div>
    </div>
    <div class="stat-card">
      <div class="stat-icon" style="background:linear-gradient(135deg,#f59e0b,#ef4444)"><i class="fas fa-hand-holding-heart"></i></div>
      <div>
        <div class="stat-value"><?= (int)$stats['vulnerable'] ?></div>
        <div class="stat-label">Vulnerable / At Risk</div>
      </div>
    </div>
  </div>

  <!-- ── Filters & Search ── -->
  <div class="card mb-3">
    <form method="GET" action="<?= APP_URL ?>/residents" class="filter-bar">
      <div style="flex:1;min-width:200px">
        <input type="text" name="search" class="form-control" placeholder="Search name, phone, address..." value="<?= sanitize($filters['search'] ?? '') ?>">
      </div>

      <div>
        <select name="purok" class="form-select">
          <option value="">— All Puroks / Zones —</option>
          <?php foreach (BARANGAY_PUROKS as $p): ?>
          <option value="<?= $p ?>" <?= (!empty($_GET['purok']) && $_GET['purok'] === $p) ? 'selected' : '' ?>>
            <?= $p ?>
          </option>
          <?php endforeach; ?>
        </select>
      </div>

      <div>
        <select name="gender" class="form-select">
          <option value="">— Gender —</option>
          <option value="female" <?= ($filters['gender'] ?? '') === 'female' ? 'selected' : '' ?>>Female</option>
          <option value="male" <?= ($filters['gender'] ?? '') === 'male' ? 'selected' : '' ?>>Male</option>
          <option value="other" <?= ($filters['gender'] ?? '') === 'other' ? 'selected' : '' ?>>Other</option>
        </select>
      </div>

      <div>
        <select name="civil_status" class="form-select">
          <option value="">— Civil Status —</option>
          <option value="single" <?= ($filters['civil_status'] ?? '') === 'single' ? 'selected' : '' ?>>Single</option>
          <option value="married" <?= ($filters['civil_status'] ?? '') === 'married' ? 'selected' : '' ?>>Married</option>
          <option value="widowed" <?= ($filters['civil_status'] ?? '') === 'widowed' ? 'selected' : '' ?>>Widowed</option>
          <option value="separated" <?= ($filters['civil_status'] ?? '') === 'separated' ? 'selected' : '' ?>>Separated</option>
          <option value="live_in" <?= ($filters['civil_status'] ?? '') === 'live_in' ? 'selected' : '' ?>>Live-In</option>
        </select>
      </div>

      <div>
        <select name="is_vulnerable" class="form-select">
          <option value="">— Vulnerability —</option>
          <option value="1" <?= ($filters['is_vulnerable'] ?? '') === '1' ? 'selected' : '' ?>>Vulnerable / Protected</option>
          <option value="0" <?= ($filters['is_vulnerable'] ?? '') === '0' ? 'selected' : '' ?>>General</option>
        </select>
      </div>

      <div class="d-flex gap-1">
        <button type="submit" class="btn btn-primary btn-sm"><i class="fas fa-filter"></i> Filter</button>
        <a href="<?= APP_URL ?>/residents" class="btn btn-outline btn-sm">Reset</a>
      </div>
    </form>
  </div>

  <!-- ── Residents Table ── -->
  <div class="card">
    <div class="table-wrapper">
      <table class="table">
        <thead>
          <tr>
            <th>Full Name</th>
            <th>Gender / Age</th>
            <th>Barangay</th>
            <th>Contact</th>
            <th>Address</th>
            <th>Civil Status</th>
            <th>Status / Category</th>
            <th>Action</th>
          </tr>
        </thead>
        <tbody>
          <?php if (empty($residents['data'])): ?>
          <tr><td colspan="8" class="text-center text-muted" style="padding:2.5rem">No residents found in the registry.</td></tr>
          <?php else: foreach ($residents['data'] as $r): ?>
          <tr>
            <td>
              <div class="fw-600 text-purple"><?= sanitize($r['first_name'] . ' ' . ($r['middle_name'] ? $r['middle_name'][0] . '. ' : '') . $r['last_name'] . ($r['suffix'] ? ' ' . $r['suffix'] : '')) ?></div>
              <?php if (!empty($r['email'])): ?><div class="fs-sm text-muted"><?= sanitize($r['email']) ?></div><?php endif; ?>
            </td>
            <td>
              <span class="badge <?= $r['gender'] === 'female' ? 'badge-pink' : 'badge-info' ?>">
                <i class="fas fa-<?= $r['gender'] === 'female' ? 'female' : 'male' ?>"></i> <?= ucfirst($r['gender']) ?>
              </span>
              <span class="fs-sm text-muted" style="margin-left:0.25rem"><?= $r['age'] ?> yrs</span>
            </td>
            <td class="text-muted"><?= sanitize($r['barangay_name'] ?? '—') ?></td>
            <td><?= sanitize($r['contact_number']) ?></td>
            <td class="text-muted fs-sm"><?= sanitize($r['address']) ?></td>
            <td><span class="badge badge-secondary"><?= ucfirst(str_replace('_', ' ', $r['civil_status'])) ?></span></td>
            <td>
              <?php if ($r['is_vulnerable']): ?>
                <span class="badge badge-warning" title="<?= sanitize($r['vulnerability_type'] ?? '') ?>">
                  <i class="fas fa-shield-alt"></i> <?= sanitize($r['vulnerability_type'] ?: 'Vulnerable') ?>
                </span>
              <?php else: ?>
                <span class="fs-sm text-muted">Standard</span>
              <?php endif; ?>
            </td>
            <td>
              <div class="d-flex gap-1">
                <a href="<?= APP_URL ?>/residents/view?id=<?= $r['id'] ?>" class="btn btn-outline btn-sm" title="View Profile">
                  <i class="fas fa-eye"></i>
                </a>
                <form method="POST" action="<?= APP_URL ?>/residents/delete" onsubmit="return confirm('Delete resident record for <?= sanitize($r['first_name'] . ' ' . $r['last_name']) ?>?')">
                  <?= csrfField() ?>
                  <input type="hidden" name="id" value="<?= $r['id'] ?>">
                  <button type="submit" class="btn btn-danger btn-sm" title="Delete Resident">
                    <i class="fas fa-trash"></i>
                  </button>
                </form>
              </div>
            </td>
          </tr>
          <?php endforeach; endif; ?>
        </tbody>
      </table>
    </div>

    <!-- Pagination -->
    <?php if ($residents['pages'] > 1): ?>
    <div class="pagination">
      <?php for ($p = 1; $p <= $residents['pages']; $p++): ?>
      <a href="?page=<?= $p ?>&search=<?= urlencode($filters['search'] ?? '') ?>&gender=<?= urlencode($filters['gender'] ?? '') ?>&civil_status=<?= urlencode($filters['civil_status'] ?? '') ?>&is_vulnerable=<?= urlencode($filters['is_vulnerable'] ?? '') ?>" class="page-item <?= $p === $residents['current'] ? 'active' : '' ?>">
        <?= $p ?>
      </a>
      <?php endfor; ?>
    </div>
    <?php endif; ?>
  </div>
</div>

<!-- ── Add Resident Modal ── -->
<div class="modal-overlay" id="addResidentModal">
  <div class="modal" style="max-width:680px">
    <div class="modal-header">
      <h3 style="color:#e0d7ff"><i class="fas fa-user-plus" style="color:#8b5cf6"></i> Register New Resident</h3>
      <button class="modal-close" onclick="closeModal('addResidentModal')">&times;</button>
    </div>
    <form method="POST" action="<?= APP_URL ?>/residents/create">
      <?= csrfField() ?>

      <div class="form-group">
        <label class="form-label">Barangay</label>
        <div style="background:rgba(139,92,246,0.12);border:1px solid rgba(139,92,246,0.3);border-radius:8px;padding:0.75rem 1rem;color:#e0d7ff;font-weight:600;display:flex;align-items:center;gap:0.5rem">
          <i class="fas fa-building" style="color:#8b5cf6"></i> <?= LGU_FULL_NAME ?>
        </div>
        <input type="hidden" name="barangay_id" value="1">
      </div>

      <div class="form-group">
        <label class="form-label">Purok / Zone in Gogon Centro <span style="color:#ec4899">*</span></label>
        <select name="purok" class="form-select" required>
          <option value="">— Select Purok / Zone —</option>
          <?php foreach (BARANGAY_PUROKS as $p): ?>
          <option value="<?= $p ?>"><?= $p ?></option>
          <?php endforeach; ?>
        </select>
      </div>

      <div class="grid grid-2">
        <div class="form-group">
          <label class="form-label">First Name <span style="color:#ec4899">*</span></label>
          <input type="text" name="first_name" class="form-control" placeholder="e.g. Maria" required>
        </div>
        <div class="form-group">
          <label class="form-label">Middle Name</label>
          <input type="text" name="middle_name" class="form-control" placeholder="e.g. Santos">
        </div>
      </div>

      <div class="grid grid-2">
        <div class="form-group">
          <label class="form-label">Last Name <span style="color:#ec4899">*</span></label>
          <input type="text" name="last_name" class="form-control" placeholder="e.g. Reyes" required>
        </div>
        <div class="form-group">
          <label class="form-label">Suffix</label>
          <input type="text" name="suffix" class="form-control" placeholder="e.g. Jr., III">
        </div>
      </div>

      <div class="grid grid-3">
        <div class="form-group">
          <label class="form-label">Gender <span style="color:#ec4899">*</span></label>
          <select name="gender" class="form-select" required>
            <option value="female">Female</option>
            <option value="male">Male</option>
            <option value="other">Other</option>
          </select>
        </div>
        <div class="form-group">
          <label class="form-label">Birth Date <span style="color:#ec4899">*</span></label>
          <input type="date" name="birth_date" class="form-control" required max="<?= date('Y-m-d') ?>">
        </div>
        <div class="form-group">
          <label class="form-label">Civil Status <span style="color:#ec4899">*</span></label>
          <select name="civil_status" class="form-select" required>
            <option value="single">Single</option>
            <option value="married">Married</option>
            <option value="widowed">Widowed</option>
            <option value="separated">Separated</option>
            <option value="live_in">Live-In</option>
          </select>
        </div>
      </div>

      <div class="grid grid-2">
        <div class="form-group">
          <label class="form-label">Mobile Number <span style="color:#ec4899">*</span></label>
          <input type="tel" name="contact_number" class="form-control" placeholder="09XXXXXXXXX" required>
        </div>
        <div class="form-group">
          <label class="form-label">Email Address</label>
          <input type="email" name="email" class="form-control" placeholder="resident@example.com">
        </div>
      </div>

      <div class="form-group">
        <label class="form-label">Address / Street / Purok <span style="color:#ec4899">*</span></label>
        <input type="text" name="address" class="form-control" placeholder="e.g. Block 4 Lot 12, Purok 3" required>
      </div>

      <div class="grid grid-2">
        <div class="form-group">
          <label class="form-label">Occupation / Source of Income</label>
          <input type="text" name="occupation" class="form-control" placeholder="e.g. Vendor, Teacher, Self-employed">
        </div>
        <div class="form-group">
          <label class="form-label">Vulnerable / Protected Group?</label>
          <select name="is_vulnerable" class="form-select" onchange="document.getElementById('vulnTypeGroup').style.display = this.value == '1' ? 'block' : 'none'">
            <option value="0">No (Standard)</option>
            <option value="1">Yes (Vulnerable / Protected)</option>
          </select>
        </div>
      </div>

      <div class="form-group" id="vulnTypeGroup" style="display:none">
        <label class="form-label">Vulnerability Category</label>
        <select name="vulnerability_type" class="form-select">
          <option value="Solo Parent">Solo Parent</option>
          <option value="Senior Citizen">Senior Citizen (60+)</option>
          <option value="PWD">Person with Disability (PWD)</option>
          <option value="Minor / Child at Risk">Minor / Child at Risk</option>
          <option value="Pregnant / Lactating Mother">Pregnant / Lactating Mother</option>
          <option value="Indigent">Indigent Family</option>
        </select>
      </div>

      <div class="divider" style="margin:1rem 0"></div>
      <h4 style="color:#c4b5fd;font-size:0.9rem;margin-bottom:0.75rem"><i class="fas fa-phone-alt"></i> Emergency Contact</h4>

      <div class="grid grid-2">
        <div class="form-group">
          <label class="form-label">Contact Person Name</label>
          <input type="text" name="emergency_contact_name" class="form-control" placeholder="e.g. Juan Reyes">
        </div>
        <div class="form-group">
          <label class="form-label">Contact Person Phone</label>
          <input type="tel" name="emergency_contact_phone" class="form-control" placeholder="09XXXXXXXXX">
        </div>
      </div>

      <button type="submit" class="btn btn-primary w-100 mt-2">
        <i class="fas fa-save"></i> Save Resident Profile
      </button>
    </form>
  </div>
</div>

</div></div>

<?php include BASE_PATH . '/views/layouts/footer.php'; ?>
