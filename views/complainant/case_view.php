<?php
/**
 * views/complainant/case_view.php  (also used by barangay/case_view.php and admin/case_view.php via shared template)
 * Shared case detail + update form view.
 */
$isAdmin    = userRole() === 'admin';
$isStaff    = userRole() === 'barangay_staff';
$isComplainant = userRole() === 'complainant';

$navLayout  = match(userRole()) {
    'admin'          => 'admin',
    'barangay_staff' => 'barangay',
    default          => 'complainant',
};
$pageTitle = 'Case ' . ($case['case_number'] ?? '');
$extraCss  = ['/assets/css/dashboard.css'];
include BASE_PATH . '/views/layouts/header.php';
include BASE_PATH . "/views/layouts/{$navLayout}_nav.php";
?>

<div class="page-content">
  <div class="d-flex align-center gap-2 mb-3">
    <a href="<?= APP_URL ?>/<?= $navLayout ?>/<?= $isComplainant ? 'my-cases' : 'cases' ?>" class="btn btn-outline btn-sm">
      <i class="fas fa-arrow-left"></i> Back
    </a>
    <h4 style="color:#e0d7ff">Case Detail</h4>
  </div>

  <?php if ($msg = getFlash('success')): ?><div class="alert alert-success"><i class="fas fa-check-circle"></i><span><?= $msg ?></span></div><?php endif; ?>
  <?php if ($msg = getFlash('error')):   ?><div class="alert alert-error"><i class="fas fa-times-circle"></i><span><?= $msg ?></span></div><?php endif; ?>

  <div class="grid grid-2" style="align-items:start">
    <!-- Case Info Card -->
    <div style="display:flex;flex-direction:column;gap:1.25rem">
      <div class="card">
        <div class="d-flex justify-between align-center mb-2">
          <span class="fw-700 text-purple" style="font-size:1.1rem"><?= sanitize($case['case_number']) ?></span>
          <span class="badge <?= statusBadge($case['status']) ?>"><?= statusLabel($case['status']) ?></span>
        </div>
        <div class="divider" style="margin:0.75rem 0"></div>

        <?php
        $details = [
          ['fas fa-exclamation-triangle','Type of Violence', violenceLabel($case['type_of_violence'])],
          ['fas fa-calendar','Incident Date', fmtDate($case['incident_date'])],
          ['fas fa-map-marker-alt','Location', $case['location'] ?: '—'],
          ['fas fa-building','Barangay', $case['barangay_name'] ?? '—'],
          ['fas fa-clock','Filed On', fmtDateTime($case['created_at'])],
          ['fas fa-sync','Last Updated', fmtDateTime($case['updated_at'])],
        ];
        if (!$isComplainant) {
          $details[] = ['fas fa-user','Complainant', $case['complainant_name'] ?? '—'];
          $details[] = ['fas fa-phone','Phone', $case['complainant_phone'] ?? '—'];
        }
        foreach ($details as [$icon, $label, $value]): ?>
        <div style="display:flex;gap:0.75rem;align-items:flex-start;padding:0.5rem 0;border-bottom:1px solid rgba(255,255,255,0.04)">
          <i class="<?= $icon ?>" style="color:#8b5cf6;width:16px;margin-top:2px;flex-shrink:0"></i>
          <div>
            <div class="fs-sm text-muted"><?= $label ?></div>
            <div style="color:#e0d7ff;font-size:0.9rem"><?= sanitize($value) ?></div>
          </div>
        </div>
        <?php endforeach; ?>

        <?php if ($case['assigned_staff_name']): ?>
        <div style="display:flex;gap:0.75rem;padding:0.5rem 0;margin-top:0.25rem">
          <i class="fas fa-user-tie" style="color:#3b82f6;width:16px;flex-shrink:0;margin-top:2px"></i>
          <div>
            <div class="fs-sm text-muted">Assigned Staff</div>
            <div style="color:#93c5fd;font-size:0.9rem"><?= sanitize($case['assigned_staff_name']) ?></div>
          </div>
        </div>
        <?php endif; ?>
      </div>

      <!-- Description -->
      <div class="card">
        <h4 style="color:#e0d7ff;margin-bottom:0.75rem"><i class="fas fa-file-alt" style="color:#8b5cf6"></i> Incident Description</h4>
        <p style="font-size:0.875rem;line-height:1.8;color:#c4b5fd"><?= nl2br(sanitize($case['description'])) ?></p>
      </div>

      <!-- Evidence -->
      <?php if ($case['evidence_path']): ?>
      <div class="card">
        <h4 style="color:#e0d7ff;margin-bottom:0.75rem"><i class="fas fa-paperclip" style="color:#8b5cf6"></i> Uploaded Evidence</h4>
        <?php
        $ext = strtolower(pathinfo($case['evidence_path'], PATHINFO_EXTENSION));
        $url = UPLOAD_URL . $case['evidence_path'];
        ?>
        <?php if (in_array($ext, ['jpg','jpeg','png','gif'])): ?>
        <img src="<?= $url ?>" alt="Evidence" style="max-width:100%;border-radius:10px;border:1px solid rgba(255,255,255,0.1)">
        <?php elseif ($ext === 'pdf'): ?>
        <a href="<?= $url ?>" target="_blank" class="btn btn-outline btn-sm">
          <i class="fas fa-file-pdf"></i> View PDF Evidence
        </a>
        <?php elseif ($ext === 'mp4'): ?>
        <video controls style="width:100%;border-radius:10px">
          <source src="<?= $url ?>" type="video/mp4">
        </video>
        <?php else: ?>
        <a href="<?= $url ?>" target="_blank" class="btn btn-outline btn-sm">
          <i class="fas fa-download"></i> Download Evidence File
        </a>
        <?php endif; ?>
      </div>
      <?php endif; ?>
    </div>

    <!-- Right Column: Timeline + Update Form -->
    <div style="display:flex;flex-direction:column;gap:1.25rem">
      <!-- Case Timeline -->
      <div class="card">
        <h4 style="color:#e0d7ff;margin-bottom:1rem"><i class="fas fa-history" style="color:#f59e0b"></i> Case History</h4>
        <div class="timeline">
          <!-- Initial filing -->
          <div class="timeline-item">
            <div class="timeline-dot"></div>
            <div class="timeline-date"><?= fmtDateTime($case['created_at']) ?></div>
            <div class="timeline-content">
              <div class="fw-600 fs-sm" style="color:#c4b5fd">Case Filed</div>
              <p class="fs-sm text-muted mt-1">Report submitted by <?= sanitize($case['complainant_name'] ?? '') ?>.</p>
            </div>
          </div>
          <!-- Updates -->
          <?php foreach ($updates as $u): ?>
          <div class="timeline-item">
            <div class="timeline-dot" style="background:linear-gradient(135deg,#3b82f6,#8b5cf6)"></div>
            <div class="timeline-date"><?= fmtDateTime($u['updated_at']) ?></div>
            <div class="timeline-content">
              <div class="fw-600 fs-sm" style="color:#93c5fd">
                Status changed to <span class="badge <?= statusBadge($u['new_status']) ?>"><?= statusLabel($u['new_status']) ?></span>
              </div>
              <?php if ($u['remarks']): ?>
              <p class="fs-sm text-muted mt-1"><?= nl2br(sanitize($u['remarks'])) ?></p>
              <?php endif; ?>
              <p class="fs-sm text-muted mt-1">Updated by <?= sanitize($u['updated_by_name'] ?? '—') ?></p>
            </div>
          </div>
          <?php endforeach; ?>
        </div>
      </div>

      <!-- Update Status (Staff / Admin only) -->
      <?php if ($isStaff || $isAdmin): ?>
      <div class="card card-glow">
        <h4 style="color:#e0d7ff;margin-bottom:1rem"><i class="fas fa-edit" style="color:#10b981"></i> Update Case Status</h4>
        <form method="POST" action="<?= APP_URL ?>/case/update-status">
          <?= csrfField() ?>
          <input type="hidden" name="case_id" value="<?= $case['id'] ?>">
          <div class="form-group">
            <label class="form-label">New Status</label>
            <select name="status" class="form-select" required>
              <?php foreach (['pending','under_investigation','resolved','dismissed'] as $s): ?>
              <option value="<?= $s ?>" <?= $case['status'] === $s ? 'selected' : '' ?>><?= statusLabel($s) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="form-group">
            <label class="form-label">Remarks / Notes <span style="color:#ec4899">*</span></label>
            <textarea name="remarks" class="form-control" rows="4" required
              placeholder="Provide details about this status update. This will be sent via SMS to the complainant."></textarea>
          </div>
          <div style="background:rgba(59,130,246,0.08);border:1px solid rgba(59,130,246,0.2);border-radius:10px;padding:0.85rem;margin-bottom:1rem;font-size:0.82rem;color:#93c5fd">
            <i class="fas fa-comment-sms"></i>
            The complainant will receive an SMS notification with this update.
          </div>
          <button type="submit" class="btn btn-primary w-100"
            onclick="return confirm('Update case status? The complainant will be notified via SMS.')">
            <i class="fas fa-save"></i> Update Status &amp; Notify
          </button>
        </form>
      </div>
      <?php endif; ?>

      <!-- Remarks Box (if case has remarks) -->
      <?php if ($case['remarks'] && $isComplainant): ?>
      <div class="card">
        <h4 style="color:#e0d7ff;margin-bottom:0.75rem"><i class="fas fa-comment" style="color:#3b82f6"></i> Latest Remarks</h4>
        <p style="font-size:0.875rem;color:#93c5fd;line-height:1.7"><?= nl2br(sanitize($case['remarks'])) ?></p>
      </div>
      <?php endif; ?>
    </div>
  </div>
</div>

</div></div>
<?php include BASE_PATH . '/views/layouts/footer.php'; ?>
