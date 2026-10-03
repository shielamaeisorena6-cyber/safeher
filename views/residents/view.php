<?php
$pageTitle = 'Resident Profile – ' . sanitize($resident['first_name'] . ' ' . $resident['last_name']);
$extraCss  = ['/assets/css/dashboard.css'];

$navLayout = userRole() === 'admin' ? 'admin' : 'barangay';
include BASE_PATH . '/views/layouts/header.php';
include BASE_PATH . "/views/layouts/{$navLayout}_nav.php";

$fullName = $resident['first_name'] . ' ' . ($resident['middle_name'] ? $resident['middle_name'] . ' ' : '') . $resident['last_name'] . ($resident['suffix'] ? ' ' . $resident['suffix'] : '');
$age = date_diff(date_create($resident['birth_date']), date_create('today'))->y;
?>

<div class="page-content">
  <div class="d-flex justify-between align-center mb-3 flex-wrap gap-2">
    <div class="d-flex align-center gap-2">
      <a href="<?= APP_URL ?>/residents" class="btn btn-outline btn-sm">
        <i class="fas fa-arrow-left"></i> Back to Roster
      </a>
      <h3 style="color:#e0d7ff;margin:0"><?= sanitize($fullName) ?></h3>
    </div>
    <div class="d-flex gap-2">
      <button class="btn btn-primary btn-sm" onclick="openModal('editResidentModal')">
        <i class="fas fa-edit"></i> Edit Profile
      </button>
      <form method="POST" action="<?= APP_URL ?>/residents/delete" onsubmit="return confirm('Are you sure you want to delete this resident record?')">
        <?= csrfField() ?>
        <input type="hidden" name="id" value="<?= $resident['id'] ?>">
        <button type="submit" class="btn btn-danger btn-sm">
          <i class="fas fa-trash"></i> Delete
        </button>
      </form>
    </div>
  </div>

  <?php if ($msg = getFlash('success')): ?><div class="alert alert-success"><i class="fas fa-check-circle"></i><span><?= $msg ?></span></div><?php endif; ?>
  <?php if ($msg = getFlash('error')):   ?><div class="alert alert-error"><i class="fas fa-times-circle"></i><span><?= $msg ?></span></div><?php endif; ?>

  <div class="grid grid-2" style="align-items:start">
    <!-- Left: Personal & Demographic Info -->
    <div style="display:flex;flex-direction:column;gap:1.25rem">
      <div class="card">
        <div class="d-flex justify-between align-center mb-2">
          <div class="d-flex align-center gap-2">
            <div style="width:48px;height:48px;border-radius:50%;background:linear-gradient(135deg,#8b5cf6,#ec4899);display:flex;align-items:center;justify-content:center;color:white;font-size:1.3rem;font-weight:700">
              <?= strtoupper(substr($resident['first_name'], 0, 1)) ?>
            </div>
            <div>
              <div class="fw-700 text-purple" style="font-size:1.15rem"><?= sanitize($fullName) ?></div>
              <div class="fs-sm text-muted">Barangay Resident ID #<?= str_pad($resident['id'], 5, '0', STR_PAD_LEFT) ?></div>
            </div>
          </div>
          <span class="badge <?= $resident['gender'] === 'female' ? 'badge-pink' : 'badge-info' ?>">
            <i class="fas fa-<?= $resident['gender'] === 'female' ? 'female' : 'male' ?>"></i> <?= ucfirst($resident['gender']) ?>
          </span>
        </div>

        <div class="divider" style="margin:0.75rem 0"></div>

        <?php
        $infoItems = [
          ['fas fa-birthday-cake', 'Birth Date & Age', fmtDate($resident['birth_date']) . " ({$age} years old)"],
          ['fas fa-heart', 'Civil Status', ucfirst(str_replace('_', ' ', $resident['civil_status']))],
          ['fas fa-phone', 'Contact Number', $resident['contact_number']],
          ['fas fa-envelope', 'Email Address', $resident['email'] ?: '—'],
          ['fas fa-map-marker-alt', 'Address / Purok', $resident['address']],
          ['fas fa-building', 'Barangay', $resident['barangay_name'] . ($resident['barangay_municipality'] ? ', ' . $resident['barangay_municipality'] : '')],
          ['fas fa-briefcase', 'Occupation', $resident['occupation'] ?: '—'],
          ['fas fa-calendar-check', 'Registered On', fmtDateTime($resident['created_at'])],
        ];
        foreach ($infoItems as [$icon, $label, $val]): ?>
        <div style="display:flex;gap:0.75rem;align-items:flex-start;padding:0.55rem 0;border-bottom:1px solid rgba(255,255,255,0.04)">
          <i class="<?= $icon ?>" style="color:#8b5cf6;width:18px;margin-top:3px;flex-shrink:0"></i>
          <div>
            <div class="fs-sm text-muted"><?= $label ?></div>
            <div style="color:#e0d7ff;font-size:0.9rem;font-weight:500"><?= sanitize($val) ?></div>
          </div>
        </div>
        <?php endforeach; ?>
      </div>
    </div>

    <!-- Right: Vulnerability & Emergency Info -->
    <div style="display:flex;flex-direction:column;gap:1.25rem">
      <!-- Vulnerability Status Card -->
      <div class="card">
        <h4 style="color:#e0d7ff;margin-bottom:0.75rem">
          <i class="fas fa-shield-alt" style="color:<?= $resident['is_vulnerable'] ? '#f59e0b' : '#10b981' ?>"></i> Vulnerability &amp; Social Protection
        </h4>
        <?php if ($resident['is_vulnerable']): ?>
        <div style="background:rgba(245,158,11,0.08);border:1px solid rgba(245,158,11,0.25);border-radius:10px;padding:1rem;margin-bottom:0.5rem">
          <div class="d-flex align-center gap-2 mb-1">
            <span class="badge badge-warning"><i class="fas fa-exclamation-triangle"></i> Vulnerable Group</span>
            <span class="fw-600 text-purple"><?= sanitize($resident['vulnerability_type'] ?: 'Classified') ?></span>
          </div>
          <p class="fs-sm" style="color:#fcd34d;margin:0">
            Resident is classified under specialized local government monitoring and welfare assistance programs.
          </p>
        </div>
        <?php else: ?>
        <div style="background:rgba(16,185,129,0.08);border:1px solid rgba(16,185,129,0.2);border-radius:10px;padding:1rem">
          <p class="fs-sm" style="color:#6ee7b7;margin:0"><i class="fas fa-check-circle"></i> General Community Resident (Standard Roster).</p>
        </div>
        <?php endif; ?>
      </div>

      <!-- Emergency Contact Card -->
      <div class="card">
        <h4 style="color:#e0d7ff;margin-bottom:0.75rem">
          <i class="fas fa-phone-alt" style="color:#ec4899"></i> Emergency Contact Information
        </h4>
        <?php if (!empty($resident['emergency_contact_name']) || !empty($resident['emergency_contact_phone'])): ?>
        <div style="display:flex;flex-direction:column;gap:0.75rem">
          <div style="display:flex;gap:0.75rem;align-items:center">
            <i class="fas fa-user-circle" style="color:#f472b6;font-size:1.1rem"></i>
            <div>
              <div class="fs-sm text-muted">Contact Person</div>
              <div style="color:#e0d7ff;font-weight:600"><?= sanitize($resident['emergency_contact_name'] ?: '—') ?></div>
            </div>
          </div>
          <div style="display:flex;gap:0.75rem;align-items:center">
            <i class="fas fa-mobile-alt" style="color:#f472b6;font-size:1.1rem"></i>
            <div>
              <div class="fs-sm text-muted">Phone Number</div>
              <div style="color:#e0d7ff;font-weight:600">
                <?= sanitize($resident['emergency_contact_phone'] ?: '—') ?>
              </div>
            </div>
          </div>
        </div>
        <?php else: ?>
        <p class="fs-sm text-muted">No emergency contact details registered for this resident.</p>
        <?php endif; ?>
      </div>

      <!-- System Meta Card -->
      <div class="card">
        <div class="fs-sm text-muted">
          <div><strong>Registered By:</strong> <?= sanitize($resident['encoder_name'] ?? 'System') ?></div>
          <div class="mt-1"><strong>Last Updated:</strong> <?= fmtDateTime($resident['updated_at']) ?></div>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- ── Edit Resident Modal ── -->
<div class="modal-overlay" id="editResidentModal">
  <div class="modal" style="max-width:680px">
    <div class="modal-header">
      <h3 style="color:#e0d7ff"><i class="fas fa-edit" style="color:#8b5cf6"></i> Edit Resident Information</h3>
      <button class="modal-close" onclick="closeModal('editResidentModal')">&times;</button>
    </div>
    <form method="POST" action="<?= APP_URL ?>/residents/update">
      <?= csrfField() ?>
      <input type="hidden" name="id" value="<?= $resident['id'] ?>">

      <div class="grid grid-2">
        <div class="form-group">
          <label class="form-label">First Name <span style="color:#ec4899">*</span></label>
          <input type="text" name="first_name" class="form-control" value="<?= sanitize($resident['first_name']) ?>" required>
        </div>
        <div class="form-group">
          <label class="form-label">Middle Name</label>
          <input type="text" name="middle_name" class="form-control" value="<?= sanitize($resident['middle_name'] ?? '') ?>">
        </div>
      </div>

      <div class="grid grid-2">
        <div class="form-group">
          <label class="form-label">Last Name <span style="color:#ec4899">*</span></label>
          <input type="text" name="last_name" class="form-control" value="<?= sanitize($resident['last_name']) ?>" required>
        </div>
        <div class="form-group">
          <label class="form-label">Suffix</label>
          <input type="text" name="suffix" class="form-control" value="<?= sanitize($resident['suffix'] ?? '') ?>">
        </div>
      </div>

      <div class="grid grid-3">
        <div class="form-group">
          <label class="form-label">Gender</label>
          <select name="gender" class="form-select" required>
            <option value="female" <?= $resident['gender'] === 'female' ? 'selected' : '' ?>>Female</option>
            <option value="male" <?= $resident['gender'] === 'male' ? 'selected' : '' ?>>Male</option>
            <option value="other" <?= $resident['gender'] === 'other' ? 'selected' : '' ?>>Other</option>
          </select>
        </div>
        <div class="form-group">
          <label class="form-label">Birth Date</label>
          <input type="date" name="birth_date" class="form-control" value="<?= $resident['birth_date'] ?>" required max="<?= date('Y-m-d') ?>">
        </div>
        <div class="form-group">
          <label class="form-label">Civil Status</label>
          <select name="civil_status" class="form-select" required>
            <option value="single" <?= $resident['civil_status'] === 'single' ? 'selected' : '' ?>>Single</option>
            <option value="married" <?= $resident['civil_status'] === 'married' ? 'selected' : '' ?>>Married</option>
            <option value="widowed" <?= $resident['civil_status'] === 'widowed' ? 'selected' : '' ?>>Widowed</option>
            <option value="separated" <?= $resident['civil_status'] === 'separated' ? 'selected' : '' ?>>Separated</option>
            <option value="live_in" <?= $resident['civil_status'] === 'live_in' ? 'selected' : '' ?>>Live-In</option>
          </select>
        </div>
      </div>

      <div class="grid grid-2">
        <div class="form-group">
          <label class="form-label">Mobile Number <span style="color:#ec4899">*</span></label>
          <input type="tel" name="contact_number" class="form-control" value="<?= sanitize($resident['contact_number']) ?>" required>
        </div>
        <div class="form-group">
          <label class="form-label">Email Address</label>
          <input type="email" name="email" class="form-control" value="<?= sanitize($resident['email'] ?? '') ?>">
        </div>
      </div>

      <div class="form-group">
        <label class="form-label">Address <span style="color:#ec4899">*</span></label>
        <input type="text" name="address" class="form-control" value="<?= sanitize($resident['address']) ?>" required>
      </div>

      <div class="grid grid-2">
        <div class="form-group">
          <label class="form-label">Occupation</label>
          <input type="text" name="occupation" class="form-control" value="<?= sanitize($resident['occupation'] ?? '') ?>">
        </div>
        <div class="form-group">
          <label class="form-label">Vulnerable Group?</label>
          <select name="is_vulnerable" class="form-select" onchange="document.getElementById('editVulnType').style.display = this.value == '1' ? 'block' : 'none'">
            <option value="0" <?= !$resident['is_vulnerable'] ? 'selected' : '' ?>>No (Standard)</option>
            <option value="1" <?= $resident['is_vulnerable'] ? 'selected' : '' ?>>Yes (Vulnerable / Protected)</option>
          </select>
        </div>
      </div>

      <div class="form-group" id="editVulnType" style="display:<?= $resident['is_vulnerable'] ? 'block' : 'none' ?>">
        <label class="form-label">Vulnerability Category</label>
        <select name="vulnerability_type" class="form-select">
          <option value="Solo Parent" <?= ($resident['vulnerability_type'] ?? '') === 'Solo Parent' ? 'selected' : '' ?>>Solo Parent</option>
          <option value="Senior Citizen" <?= ($resident['vulnerability_type'] ?? '') === 'Senior Citizen' ? 'selected' : '' ?>>Senior Citizen (60+)</option>
          <option value="PWD" <?= ($resident['vulnerability_type'] ?? '') === 'PWD' ? 'selected' : '' ?>>Person with Disability (PWD)</option>
          <option value="Minor / Child at Risk" <?= ($resident['vulnerability_type'] ?? '') === 'Minor / Child at Risk' ? 'selected' : '' ?>>Minor / Child at Risk</option>
          <option value="Pregnant / Lactating Mother" <?= ($resident['vulnerability_type'] ?? '') === 'Pregnant / Lactating Mother' ? 'selected' : '' ?>>Pregnant / Lactating Mother</option>
          <option value="Indigent" <?= ($resident['vulnerability_type'] ?? '') === 'Indigent' ? 'selected' : '' ?>>Indigent Family</option>
        </select>
      </div>

      <div class="divider" style="margin:1rem 0"></div>
      <h4 style="color:#c4b5fd;font-size:0.9rem;margin-bottom:0.75rem"><i class="fas fa-phone-alt"></i> Emergency Contact</h4>

      <div class="grid grid-2">
        <div class="form-group">
          <label class="form-label">Contact Person Name</label>
          <input type="text" name="emergency_contact_name" class="form-control" value="<?= sanitize($resident['emergency_contact_name'] ?? '') ?>">
        </div>
        <div class="form-group">
          <label class="form-label">Contact Person Phone</label>
          <input type="tel" name="emergency_contact_phone" class="form-control" value="<?= sanitize($resident['emergency_contact_phone'] ?? '') ?>">
        </div>
      </div>

      <button type="submit" class="btn btn-primary w-100 mt-2">
        <i class="fas fa-save"></i> Update Resident Profile
      </button>
    </form>
  </div>
</div>

</div></div>

<?php include BASE_PATH . '/views/layouts/footer.php'; ?>
