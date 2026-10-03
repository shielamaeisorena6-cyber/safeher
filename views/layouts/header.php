<?php
/**
 * views/layouts/header.php
 * Shared <head> section. Variables expected:
 *   $pageTitle  (string) — browser tab title
 *   $extraCss   (array)  — optional extra CSS hrefs
 */
$pageTitle = $pageTitle ?? APP_NAME;
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="description" content="SafeHer – VAWC Reporting & Case Analytics System for Barangay Gogon Centro, Virac, Catanduanes">
  <meta name="robots" content="noindex, nofollow">
  <title><?= sanitize($pageTitle) ?> — <?= APP_NAME ?> (<?= BARANGAY_NAME ?>)</title>

  <!-- Google Fonts -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

  <!-- Font Awesome -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

  <!-- Core CSS -->
  <link rel="stylesheet" href="<?= APP_URL ?>/assets/css/main.css">

  <?php if (!empty($extraCss)): foreach ($extraCss as $href): ?>
  <link rel="stylesheet" href="<?= APP_URL . $href ?>">
  <?php endforeach; endif; ?>

  <link rel="icon" type="image/svg+xml" href="<?= APP_URL ?>/assets/img/favicon.svg">
</head>
<body>
