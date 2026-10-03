<?php
/**
 * views/layouts/footer.php
 * Shared footer with JS. Variables:
 *   $extraJs  (array) — optional extra script srcs
 */
?>
  <!-- Core JS -->
  <script src="<?= APP_URL ?>/assets/js/dashboard.js" defer></script>

  <?php if (!empty($extraJs)): foreach ($extraJs as $src): ?>
  <script src="<?= str_starts_with($src,'http') ? $src : APP_URL . $src ?>" defer></script>
  <?php endforeach; endif; ?>
</body>
</html>
