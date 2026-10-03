<?php
$pageTitle = 'Home';
$extraCss  = ['/assets/css/auth.css'];
include BASE_PATH . '/views/layouts/header.php';
?>

<!-- ── Landing Nav ────────────────────────────── -->
<nav class="landing-nav">
  <a href="<?= APP_URL ?>/" class="nav-brand">
    <div class="nav-icon"><i class="fas fa-shield-heart"></i></div>
    <span class="nav-title">SafeHer</span>
  </a>
  <div class="nav-links">
    <a href="#how-it-works" class="hide-mobile">How It Works</a>
    <a href="#about" class="hide-mobile">About</a>
    <a href="<?= APP_URL ?>/login" class="btn btn-outline btn-sm">Login</a>
    <a href="<?= APP_URL ?>/register" class="btn btn-primary btn-sm">Report Now</a>
  </div>
</nav>

<!-- ── Hero ───────────────────────────────────── -->
<section class="hero">
  <div>
    <div class="hero-badge"><i class="fas fa-shield-heart"></i> <?= LGU_FULL_NAME ?></div>
    <h1>
      Your Voice Deserves<br>
      <span class="highlight">To Be Heard</span>
    </h1>
    <p>
      Official Violence Against Women and Children (VAWC) reporting, resident profiling, and case monitoring system of <strong><?= LGU_FULL_NAME ?></strong>.
      Every report is handled with strict confidentiality, care, and immediate action.
    </p>
    <div class="hero-cta">
      <a href="<?= APP_URL ?>/register" class="btn btn-primary btn-lg glow-anim">
        <i class="fas fa-plus-circle"></i> File a Report
      </a>
      <a href="<?= APP_URL ?>/login" class="btn btn-outline btn-lg">
        <i class="fas fa-sign-in-alt"></i> Track My Case
      </a>
    </div>
  </div>
</section>

<!-- ── Stats Banner ───────────────────────────── -->
<div class="stats-banner">
  <div class="stats-grid">
    <div class="stat-item">
      <div class="stat-number" data-count="<?= (int)$totalCases ?>"><?= (int)$totalCases ?></div>
      <div class="stat-desc">Cases Reported</div>
    </div>
    <div class="stat-item">
      <div class="stat-number" data-count="<?= (int)$resolved ?>"><?= (int)$resolved ?></div>
      <div class="stat-desc">Cases Resolved</div>
    </div>
    <div class="stat-item">
      <div class="stat-number">7</div>
      <div class="stat-desc">Puroks / Zones Covered</div>
    </div>
    <div class="stat-item">
      <div class="stat-number" style="font-size:2rem">24/7</div>
      <div class="stat-desc">VAW Desk Response</div>
    </div>
  </div>
</div>

<!-- ── How It Works ───────────────────────────── -->
<section class="section" id="how-it-works">
  <h2 class="section-title">How It Works</h2>
  <p class="section-sub">Three simple steps to get help and track your case.</p>
  <div class="steps-grid">
    <div class="step-card">
      <div class="step-number"><i class="fas fa-user-plus"></i></div>
      <h4>1. Create an Account</h4>
      <p>Register securely with your name, email, and phone number. Your privacy is our priority.</p>
    </div>
    <div class="step-card">
      <div class="step-number"><i class="fas fa-file-alt"></i></div>
      <h4>2. File Your Report</h4>
      <p>Describe the incident, upload any evidence, and select your barangay. Submit with confidence.</p>
    </div>
    <div class="step-card">
      <div class="step-number"><i class="fas fa-bell"></i></div>
      <h4>3. Get Notified</h4>
      <p>Receive real-time SMS updates as your case progresses through the resolution process.</p>
    </div>
    <div class="step-card">
      <div class="step-number"><i class="fas fa-check-circle"></i></div>
      <h4>4. Track Progress</h4>
      <p>Log in anytime to see your case status, history, and remarks from barangay officials.</p>
    </div>
  </div>
</section>

<!-- ── About ──────────────────────────────────── -->
<section class="section" id="about" style="text-align:center;">
  <h2 class="section-title">Why SafeHer?</h2>
  <p class="section-sub" style="max-width:650px;margin:0 auto 3rem;">
    SafeHer was built to empower survivors and ensure every report reaches the right authorities quickly and securely.
    We use SMS notifications, case tracking, and analytics to improve response times across barangays.
  </p>
  <div class="grid grid-3" style="max-width:900px;margin:0 auto;text-align:left">
    <?php
    $features = [
      ['fas fa-lock','Secure & Confidential','Reports and personal identities are kept strictly private and protected.'],
      ['fas fa-comment-sms','PhilSMS Notifications','Instant automated SMS alerts for case submissions and status updates.'],
      ['fas fa-crown','Barangay Captain Oversight','Empowers the Punong Barangay and VAW desk staff with real-time case analytics.'],
    ];
    foreach ($features as [$icon,$title,$desc]): ?>
    <div class="card card-glow">
      <div style="font-size:1.8rem;background:linear-gradient(135deg,#8b5cf6,#ec4899);-webkit-background-clip:text;-webkit-text-fill-color:transparent;margin-bottom:0.75rem">
        <i class="<?= $icon ?>"></i>
      </div>
      <h4 style="color:#e0d7ff;margin-bottom:0.4rem"><?= $title ?></h4>
      <p style="font-size:0.85rem"><?= $desc ?></p>
    </div>
    <?php endforeach; ?>
  </div>
</section>

<!-- ── CTA ────────────────────────────────────── -->
<div class="cta-section">
  <h2>Ready to Make Your Voice Heard?</h2>
  <p>Join thousands who have safely reported incidents through SafeHer.</p>
  <a href="<?= APP_URL ?>/register" class="btn btn-primary btn-lg">
    <i class="fas fa-shield-heart"></i> Get Started — It's Free
  </a>
</div>

<!-- ── Footer ─────────────────────────────────── -->
<footer class="landing-footer">
  <p>&copy; <?= date('Y') ?> SafeHer — <?= LGU_FULL_NAME ?> VAWC Reporting &amp; Resident Profiling System. All rights reserved.</p>
</footer>

<script>
// Animate stat counters
document.querySelectorAll('[data-count]').forEach(el => {
  const target = parseInt(el.dataset.count);
  if (!target) return;
  let count = 0;
  const step = Math.max(1, Math.floor(target / 60));
  const interval = setInterval(() => {
    count = Math.min(count + step, target);
    el.textContent = count;
    if (count >= target) clearInterval(interval);
  }, 20);
});
</script>

<?php include BASE_PATH . '/views/layouts/footer.php'; ?>
