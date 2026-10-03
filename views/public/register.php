<?php
$pageTitle = 'Register';
$extraCss  = ['/assets/css/auth.css'];
include BASE_PATH . '/views/layouts/header.php';
?>

<div class="auth-page">
  <div class="auth-container" style="max-width:520px">

    <div class="auth-brand">
      <div class="brand-icon-large"><i class="fas fa-user-shield"></i></div>
      <h1>SafeHer</h1>
      <p>Create your secure complainant account</p>
    </div>

    <?php if ($error): ?>
    <div class="alert alert-error mb-2">
      <i class="fas fa-exclamation-circle"></i>
      <span><?= sanitize($error) ?></span>
    </div>
    <?php endif; ?>

    <div class="auth-card">
      <h2>Create Account</h2>
      <p class="auth-subtitle">Your information is kept private and secure.</p>

      <form method="POST" action="<?= APP_URL ?>/register" id="registerForm">
        <?= csrfField() ?>

        <div class="form-group">
          <label class="form-label" for="full_name">Full Name</label>
          <input type="text" id="full_name" name="full_name" class="form-control"
            placeholder="Juan Dela Cruz" required autocomplete="name"
            value="<?= sanitize($_POST['full_name'] ?? '') ?>">
        </div>

        <div class="form-group">
          <label class="form-label" for="email">Email Address</label>
          <input type="email" id="email" name="email" class="form-control"
            placeholder="you@example.com" required autocomplete="email"
            value="<?= sanitize($_POST['email'] ?? '') ?>">
        </div>

        <div class="form-group">
          <label class="form-label" for="phone">Mobile Number</label>
          <input type="tel" id="phone" name="phone" class="form-control"
            placeholder="09171234567" required autocomplete="tel"
            pattern="[0-9]{11}" maxlength="11"
            value="<?= sanitize($_POST['phone'] ?? '') ?>">
          <small class="text-muted fs-sm">Used for SMS case updates (PH number)</small>
        </div>

        <div class="form-group">
          <label class="form-label" for="password">Password</label>
          <div style="position:relative">
            <input type="password" id="password" name="password" class="form-control"
              placeholder="Min 8 characters" required minlength="8"
              style="padding-right:3rem" oninput="checkStrength(this.value)">
            <button type="button" onclick="togglePwd('password','eyeIcon1')"
              style="position:absolute;right:0.9rem;top:50%;transform:translateY(-50%);background:none;border:none;color:#7c6fa3;cursor:pointer;font-size:0.9rem">
              <i class="fas fa-eye" id="eyeIcon1"></i>
            </button>
          </div>
          <div class="password-strength-bar" id="strengthBar"></div>
          <small class="text-muted fs-sm" id="strengthText"></small>
        </div>

        <div class="form-group">
          <label class="form-label" for="confirm_password">Confirm Password</label>
          <div style="position:relative">
            <input type="password" id="confirm_password" name="confirm_password" class="form-control"
              placeholder="Repeat password" required style="padding-right:3rem">
            <button type="button" onclick="togglePwd('confirm_password','eyeIcon2')"
              style="position:absolute;right:0.9rem;top:50%;transform:translateY(-50%);background:none;border:none;color:#7c6fa3;cursor:pointer;font-size:0.9rem">
              <i class="fas fa-eye" id="eyeIcon2"></i>
            </button>
          </div>
        </div>

        <div style="background:rgba(139,92,246,0.08);border:1px solid rgba(139,92,246,0.2);border-radius:10px;padding:0.9rem;margin-bottom:1.25rem;font-size:0.82rem;color:#8c7ab5">
          <i class="fas fa-lock" style="color:#a78bfa"></i>
          By registering, you agree that your information will be used solely for case management
          and will be kept strictly confidential in accordance with VAWC privacy guidelines.
        </div>

        <button type="submit" class="btn btn-primary w-100" id="registerBtn">
          <i class="fas fa-user-plus"></i> Create Account
        </button>
      </form>

      <div class="divider-text mt-3 mb-3"><span>Already have an account?</span></div>
      <a href="<?= APP_URL ?>/login" class="btn btn-outline w-100">
        <i class="fas fa-sign-in-alt"></i> Sign In
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
function checkStrength(val) {
  const bar  = document.getElementById('strengthBar');
  const text = document.getElementById('strengthText');
  bar.className = 'password-strength-bar';
  if (!val) { text.textContent = ''; return; }
  if (val.length < 8) {
    bar.classList.add('strength-weak');
    text.textContent = 'Weak — at least 8 characters required';
    text.style.color = '#ef4444';
  } else if (val.length < 12 || !/\d/.test(val)) {
    bar.classList.add('strength-medium');
    text.textContent = 'Fair — add numbers to strengthen';
    text.style.color = '#f59e0b';
  } else {
    bar.classList.add('strength-strong');
    text.textContent = 'Strong password!';
    text.style.color = '#10b981';
  }
}
document.getElementById('registerForm').addEventListener('submit', function(e) {
  const pw = document.getElementById('password').value;
  const cf = document.getElementById('confirm_password').value;
  if (pw !== cf) {
    e.preventDefault();
    alert('Passwords do not match!');
    return;
  }
  const btn = document.getElementById('registerBtn');
  btn.innerHTML = '<span class="spinner"></span> Creating Account...';
  btn.disabled = true;
});
</script>

<?php include BASE_PATH . '/views/layouts/footer.php'; ?>
