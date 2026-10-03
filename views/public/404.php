<?php
$pageTitle = '404 - Page Not Found';
$extraCss  = ['/assets/css/auth.css'];
include BASE_PATH . '/views/layouts/header.php';
?>

<div class="auth-wrapper" style="min-height: 80vh; display: flex; align-items: center; justify-content: center;">
  <div class="card text-center" style="max-width: 520px; width: 100%; margin: 2rem auto; padding: 2.5rem; border-radius: 1rem; border: 1px solid rgba(139,92,246,0.15); box-shadow: 0 20px 40px -15px rgba(109,40,217,0.15); background: white;">
    <div style="font-size: 4.5rem; font-weight: 800; line-height: 1; background: linear-gradient(135deg, #7c3aed, #ec4899); -webkit-background-clip: text; -webkit-text-fill-color: transparent; margin-bottom: 1rem;">
      404
    </div>
    <div style="font-size: 2.5rem; margin-bottom: 1rem;">🛡️</div>
    <h2 style="font-size: 1.5rem; font-weight: 700; color: #1e1b4b; margin-bottom: 0.5rem;">Page Not Found</h2>
    <p style="color: #64748b; font-size: 0.95rem; margin-bottom: 2rem;">
      The page you are looking for might have been removed, had its name changed, or is temporarily unavailable.
    </p>
    <div style="display: flex; gap: 0.75rem; justify-content: center; flex-wrap: wrap;">
      <a href="<?= APP_BASE ?>/" class="btn btn-primary" style="background: linear-gradient(135deg, #7c3aed, #6d28d9); color: white; padding: 0.75rem 1.5rem; border-radius: 0.5rem; text-decoration: none; font-weight: 600; display: inline-flex; align-items: center; gap: 0.5rem;">
        <i class="fas fa-home"></i> Back to Home
      </a>
      <a href="javascript:history.back()" class="btn" style="background: #f1f5f9; color: #475569; padding: 0.75rem 1.5rem; border-radius: 0.5rem; text-decoration: none; font-weight: 600; border: 1px solid #cbd5e1;">
        Go Back
      </a>
    </div>
  </div>
</div>

<?php include BASE_PATH . '/views/layouts/footer.php'; ?>
