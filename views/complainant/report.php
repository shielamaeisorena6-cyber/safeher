<?php
$pageTitle = 'File a Report';
$extraCss  = ['/assets/css/dashboard.css'];
include BASE_PATH . '/views/layouts/header.php';
include BASE_PATH . '/views/layouts/complainant_nav.php';
?>

<div class="page-content">
  <div class="page-header">
    <h2>File a New Report</h2>
    <p>Your report is strictly confidential. Please provide as much detail as possible.</p>
  </div>

  <?php if (!empty($error)): ?><div class="alert alert-error"><i class="fas fa-times-circle"></i><span><?= $error ?></span></div><?php endif; ?>

  <div style="max-width:720px">
    <div class="card card-glow">
      <!-- Step Indicator -->
      <div style="display:flex;gap:0.35rem;margin-bottom:2rem;border-radius:10px;overflow:hidden;flex-wrap:wrap">
        <?php
        $steps = ['Incident Details','Location & Evidence','Review & Submit'];
        foreach ($steps as $i => $s): ?>
        <div id="step-ind-<?= $i+1 ?>" style="flex:1 1 120px;padding:0.6rem 0.4rem;text-align:center;font-size:0.75rem;font-weight:600;border-radius:6px;
          background:<?= $i===0 ? 'linear-gradient(135deg,#8b5cf6,#ec4899)':'rgba(255,255,255,0.04)' ?>;
          color:<?= $i===0 ? '#fff':'#7c6fa3' ?>;
          border:1px solid <?= $i===0 ? 'transparent':'rgba(255,255,255,0.08)' ?>;
          transition:all 0.3s">
          <span style="display:inline-block">Step <?= $i+1 ?>:</span> <span><?= $s ?></span>
        </div>
        <?php endforeach; ?>
      </div>

      <form method="POST" action="<?= APP_URL ?>/complainant/report" enctype="multipart/form-data" id="reportForm">
        <?= csrfField() ?>

        <!-- Step 1: Incident Details -->
        <div id="step-1" class="form-step">
          <h4 style="color:#e0d7ff;margin-bottom:1.25rem">
            <i class="fas fa-info-circle" style="color:#8b5cf6"></i> Incident Details
          </h4>

          <div class="form-group">
            <label class="form-label">Type of Violence <span style="color:#ec4899">*</span></label>
            <select name="type_of_violence" class="form-select" required>
              <option value="">— Select Type —</option>
              <?php foreach (['physical','sexual','psychological','economic','others'] as $t): ?>
              <option value="<?= $t ?>"><?= violenceLabel($t) ?></option>
              <?php endforeach; ?>
            </select>
          </div>

          <div class="form-group">
            <label class="form-label">Date of Incident <span style="color:#ec4899">*</span></label>
            <input type="date" name="incident_date" class="form-control" required
              max="<?= date('Y-m-d') ?>"
              value="<?= sanitize($_POST['incident_date'] ?? date('Y-m-d')) ?>">
          </div>

          <div class="form-group">
            <label class="form-label">Incident Description <span style="color:#ec4899">*</span></label>
            <textarea name="description" class="form-control" rows="6" required
              placeholder="Describe what happened in as much detail as you are comfortable sharing. Include who, what, where, and when..."><?= sanitize($_POST['description'] ?? '') ?></textarea>
          </div>

          <button type="button" class="btn btn-primary" onclick="goStep(2)">
            Next: Location &amp; Evidence <i class="fas fa-arrow-right"></i>
          </button>
        </div>

        <!-- Step 2: Location & Evidence -->
        <div id="step-2" class="form-step" style="display:none">
          <h4 style="color:#e0d7ff;margin-bottom:1.25rem">
            <i class="fas fa-map-marker-alt" style="color:#ec4899"></i> Location &amp; Evidence
          </h4>

          <div class="form-group">
            <label class="form-label">Barangay Coverage</label>
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

          <div class="form-group">
            <label class="form-label">Specific Landmark / House Address</label>
            <input type="text" name="location" class="form-control"
              placeholder="e.g. Near the Barangay Hall, Riverside path, Purok 2..."
              value="<?= sanitize($_POST['location'] ?? '') ?>">
          </div>

          <div class="form-group">
            <label class="form-label">Upload Evidence <span class="text-muted fs-sm">(Optional)</span></label>
            <div style="border:2px dashed rgba(139,92,246,0.3);border-radius:12px;padding:2rem;text-align:center;cursor:pointer;transition:all 0.2s"
              onclick="document.getElementById('evidenceFile').click()"
              id="dropZone">
              <i class="fas fa-cloud-upload-alt" style="font-size:2rem;color:#8b5cf6;margin-bottom:0.5rem"></i>
              <p style="color:#7c6fa3;font-size:0.85rem;margin:0">Click to upload photo, document, or video</p>
              <p style="color:#4a4070;font-size:0.75rem;margin-top:0.25rem">JPG, PNG, PDF, MP4 — Max <?= MAX_UPLOAD_MB ?>MB</p>
              <div id="fileSelected" style="display:none;color:#a78bfa;font-size:0.85rem;margin-top:0.5rem"></div>
            </div>
            <input type="file" id="evidenceFile" name="evidence" class="hidden"
              accept="image/*,application/pdf,video/mp4"
              onchange="showFile(this)">
          </div>

          <div class="d-flex gap-2">
            <button type="button" class="btn btn-outline" onclick="goStep(1)">
              <i class="fas fa-arrow-left"></i> Back
            </button>
            <button type="button" class="btn btn-primary" onclick="goStep(3)">
              Review Report <i class="fas fa-arrow-right"></i>
            </button>
          </div>
        </div>

        <!-- Step 3: Review -->
        <div id="step-3" class="form-step" style="display:none">
          <h4 style="color:#e0d7ff;margin-bottom:1.25rem">
            <i class="fas fa-check-circle" style="color:#10b981"></i> Review &amp; Submit
          </h4>

          <div style="background:rgba(139,92,246,0.06);border:1px solid rgba(139,92,246,0.2);border-radius:12px;padding:1.25rem;margin-bottom:1.5rem">
            <p class="fs-sm" style="color:#c4b5fd">
              <i class="fas fa-lock"></i> Your report will be submitted securely. You will receive an SMS confirmation
              with your case number. Please ensure all information is accurate before submitting.
            </p>
          </div>

          <div style="background:rgba(239,68,68,0.06);border:1px solid rgba(239,68,68,0.2);border-radius:12px;padding:1.25rem;margin-bottom:1.5rem">
            <p class="fs-sm" style="color:#fca5a5">
              <i class="fas fa-exclamation-triangle"></i>
              <strong>Emergency?</strong> If you are in immediate danger, please call <strong>911</strong> or the PNP hotline
              <strong>117</strong> immediately before filing this report.
            </p>
          </div>

          <div class="d-flex gap-2">
            <button type="button" class="btn btn-outline" onclick="goStep(2)">
              <i class="fas fa-arrow-left"></i> Back
            </button>
            <button type="submit" class="btn btn-primary" id="submitBtn" style="flex:1">
              <i class="fas fa-paper-plane"></i> Submit Report
            </button>
          </div>
        </div>

      </form>
    </div>
  </div>
</div>

</div></div>

<script>
function goStep(n) {
  [1,2,3].forEach(i => {
    document.getElementById('step-'+i).style.display = i===n?'block':'none';
    const ind = document.getElementById('step-ind-'+i);
    ind.style.background = i===n ? 'linear-gradient(135deg,#8b5cf6,#ec4899)' : 'rgba(255,255,255,0.04)';
    ind.style.color = i===n ? '#fff' : '#7c6fa3';
    ind.style.borderColor = i===n ? 'transparent' : 'rgba(255,255,255,0.08)';
  });
}
function showFile(input) {
  const sel = document.getElementById('fileSelected');
  if (input.files.length) {
    sel.style.display = 'block';
    sel.textContent = '✅ ' + input.files[0].name;
    document.getElementById('dropZone').style.borderColor = '#8b5cf6';
  }
}
document.getElementById('reportForm').addEventListener('submit', function() {
  const btn = document.getElementById('submitBtn');
  btn.innerHTML = '<span class="spinner"></span> Submitting...';
  btn.disabled = true;
});
</script>

<?php include BASE_PATH . '/views/layouts/footer.php'; ?>
