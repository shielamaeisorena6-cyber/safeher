<?php
$pageTitle = 'Login';
$extraCss  = ['/assets/css/auth.css'];
include BASE_PATH . '/views/layouts/header.php';
?>

<div class="auth-page">
  <div class="auth-container">

    <!-- Brand -->
    <div class="auth-brand">
      <div class="brand-icon-large"><i class="fas fa-shield-heart"></i></div>
      <h1>SafeHer</h1>
      <p>Violence Against Women &amp; Children Reporting System</p>
    </div>

    <!-- Alert -->
    <?php if ($error): ?>
    <div class="alert alert-error mb-2">
      <i class="fas fa-exclamation-circle"></i>
      <span><?= sanitize($error) ?></span>
    </div>
    <?php endif; ?>

    <?php if ($success = getFlash('success')): ?>
    <div class="alert alert-success mb-2">
      <i class="fas fa-check-circle"></i>
      <span><?= sanitize($success) ?></span>
    </div>
    <?php endif; ?>

    <!-- Card -->
    <div class="auth-card">
      <h2>Welcome Back</h2>
      <p class="auth-subtitle">Sign in to your account to access your reports.</p>

      <form method="POST" action="<?= APP_URL ?>/login" id="loginForm">
        <?= csrfField() ?>

        <div class="form-group">
          <label class="form-label" for="email">Email Address</label>
          <input
            type="email"
            id="email"
            name="email"
            class="form-control"
            placeholder="you@example.com"
            required
            autocomplete="email"
          >
        </div>

        <div class="form-group">
          <label class="form-label" for="password">Password</label>
          <div style="position:relative">
            <input
              type="password"
              id="password"
              name="password"
              class="form-control"
              placeholder="••••••••"
              required
              autocomplete="current-password"
              style="padding-right:3rem"
            >
            <button type="button" onclick="togglePwd('password','eyeIcon')"
              style="position:absolute;right:0.9rem;top:50%;transform:translateY(-50%);background:none;border:none;color:#7c6fa3;cursor:pointer;font-size:0.9rem">
              <i class="fas fa-eye" id="eyeIcon"></i>
            </button>
          </div>
        </div>

        <button type="submit" class="btn btn-primary w-100 mt-2" id="loginBtn">
          <i class="fas fa-sign-in-alt"></i> Sign In
        </button>
      </form>

      <div class="divider-text mt-3 mb-3"><span>Don't have an account?</span></div>
      <a href="<?= APP_URL ?>/register" class="btn btn-outline w-100">
        <i class="fas fa-user-plus"></i> Create Account as Complainant
      </a>
    </div>

    <div class="auth-footer">
      <a href="<?= APP_URL ?>/">← Back to Home</a>
    </div>

  </div>
</div>

<script>
function togglePwd(inputId, iconId) {
  const inp = document.getElementById(inputId);
  const ico = document.getElementById(iconId);
  inp.type = inp.type === 'password' ? 'text' : 'password';
  ico.className = inp.type === 'password' ? 'fas fa-eye' : 'fas fa-eye-slash';
}
document.getElementById('loginForm').addEventListener('submit', function() {
  const btn = document.getElementById('loginBtn');
  btn.innerHTML = '<span class="spinner"></span> Signing In...';
  btn.disabled = true;
});
</script>

<?php include BASE_PATH . '/views/layouts/footer.php'; ?>
