<?php
$pageTitle = 'SMS Notifications';
$extraCss  = ['/assets/css/dashboard.css'];
include BASE_PATH . '/views/layouts/header.php';
include BASE_PATH . '/views/layouts/admin_nav.php';
?>

<div class="page-content">
  <div class="page-header"><h2>SMS Notifications</h2><p>Send SMS blasts and view message logs via PhilSMS.</p></div>

  <?php if ($msg = getFlash('success')): ?><div class="alert alert-success"><i class="fas fa-check-circle"></i><span><?= $msg ?></span></div><?php endif; ?>
  <?php if ($msg = getFlash('error')):   ?><div class="alert alert-error"><i class="fas fa-times-circle"></i><span><?= $msg ?></span></div><?php endif; ?>

  <div class="grid grid-2 mb-3" style="align-items:start">
    <!-- SMS Blast Form -->
    <div class="card card-glow">
      <h4 style="color:#e0d7ff;margin-bottom:0.5rem"><i class="fas fa-broadcast-tower" style="color:#ec4899"></i> Send SMS Blast</h4>
      <p class="fs-sm text-muted mb-2">Send a message to all users or a specific role group.</p>
      <form method="POST" action="<?= APP_URL ?>/admin/notifications/send-blast" id="blastForm">
        <?= csrfField() ?>
        <div class="form-group">
          <label class="form-label">Target Audience</label>
          <select name="target" class="form-select" onchange="updatePreview(this.value)">
            <option value="all">All Active Users</option>
            <option value="complainant">Complainants Only</option>
            <option value="barangay_staff">Barangay Staff Only</option>
          </select>
        </div>
        <div class="form-group">
          <label class="form-label">Message <span class="text-muted fs-sm" id="charCount">(0/160)</span></label>
          <textarea name="message" class="form-control" rows="5" maxlength="480"
            placeholder="Type your message here..." required
            oninput="document.getElementById('charCount').textContent='('+this.value.length+'/160 per segment)'"></textarea>
        </div>
        <div style="background:rgba(245,158,11,0.08);border:1px solid rgba(245,158,11,0.25);border-radius:10px;padding:0.85rem;margin-bottom:1rem;font-size:0.82rem;color:#fcd34d">
          <i class="fas fa-exclamation-triangle"></i>
          SMS credits will be consumed for each recipient. Verify message before sending.
        </div>
        <button type="submit" class="btn btn-primary w-100"
          onclick="return confirm('Send this SMS blast? This will consume SMS credits.')">
          <i class="fas fa-paper-plane"></i> Send SMS Blast
        </button>
      </form>
    </div>

    <!-- SMS Config Info -->
    <div class="card" style="height:fit-content">
      <h4 style="color:#e0d7ff;margin-bottom:1rem"><i class="fas fa-cog" style="color:#8b5cf6"></i> PhilSMS Configuration</h4>
      <div style="display:flex;flex-direction:column;gap:0.75rem">
        <?php
        $fields = [
          ['API Status',   SMS_API_KEY !== 'YOUR_PHILSMS_API_KEY_HERE' ? '✅ Configured' : '⚠️ Not Configured', SMS_API_KEY !== 'YOUR_PHILSMS_API_KEY_HERE' ? '#6ee7b7' : '#fcd34d'],
          ['Sender ID',    SMS_SENDER_NAME, '#c4b5fd'],
          ['API Endpoint', SMS_API_URL, '#93c5fd'],
          ['Dry Run Mode', SMS_DRY_RUN ? 'Enabled (no real SMS)' : 'Disabled (live)', SMS_DRY_RUN ? '#fcd34d' : '#6ee7b7'],
        ];
        foreach ($fields as [$label, $value, $color]): ?>
        <div style="display:flex;justify-content:space-between;padding:0.65rem 0;border-bottom:1px solid rgba(255,255,255,0.05)">
          <span class="fs-sm text-muted"><?= $label ?></span>
          <span class="fs-sm fw-600" style="color:<?= $color ?>"><?= sanitize($value) ?></span>
        </div>
        <?php endforeach; ?>
      </div>
      <div class="mt-3">
        <a href="https://app.philsms.com" target="_blank" class="btn btn-outline btn-sm w-100">
          <i class="fas fa-external-link-alt"></i> Open PhilSMS Dashboard
        </a>
      </div>
    </div>
  </div>

  <!-- Log Table -->
  <div class="card">
    <div class="d-flex justify-between align-center mb-2">
      <h4 style="color:#e0d7ff"><i class="fas fa-history" style="color:#8b5cf6"></i> SMS Log</h4>
      <span class="text-muted fs-sm">Showing <?= count($logs['data']) ?> of <?= $logs['total'] ?></span>
    </div>
    <div class="table-wrapper">
      <table class="table">
        <thead><tr><th>Recipient</th><th>User</th><th>Case No.</th><th>Message Preview</th><th>Status</th><th>Sent At</th></tr></thead>
        <tbody>
          <?php if (empty($logs['data'])): ?>
          <tr><td colspan="6" class="text-center text-muted" style="padding:2rem">No SMS logs yet.</td></tr>
          <?php else: foreach ($logs['data'] as $log): ?>
          <tr>
            <td class="fw-600"><?= sanitize($log['recipient_phone']) ?></td>
            <td class="text-muted"><?= sanitize($log['user_name'] ?? '—') ?></td>
            <td class="text-muted"><?= sanitize($log['case_number'] ?? '—') ?></td>
            <td class="text-muted" style="max-width:280px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap" title="<?= sanitize($log['message']) ?>">
              <?= sanitize(substr($log['message'], 0, 60)) . (strlen($log['message']) > 60 ? '...' : '') ?>
            </td>
            <td>
              <span class="badge <?= $log['sms_status'] === 'sent' ? 'badge-success' : ($log['sms_status'] === 'failed' ? 'badge-warning' : 'badge-secondary') ?>">
                <?= ucfirst($log['sms_status']) ?>
              </span>
            </td>
            <td class="text-muted fs-sm"><?= fmtDateTime($log['sent_at']) ?></td>
          </tr>
          <?php endforeach; endif; ?>
        </tbody>
      </table>
    </div>
    <?php if ($logs['pages'] > 1): ?>
    <div class="pagination">
      <?php for ($p=1; $p<=$logs['pages']; $p++): ?>
      <a href="?page=<?= $p ?>" class="page-item <?= $p===$logs['current']?'active':'' ?>"><?= $p ?></a>
      <?php endfor; ?>
    </div>
    <?php endif; ?>
  </div>
</div>

</div></div>
<?php include BASE_PATH . '/views/layouts/footer.php'; ?>
