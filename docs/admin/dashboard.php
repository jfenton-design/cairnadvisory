<?php
session_start();
if (empty($_SESSION['cairn_admin_auth'])) { header('Location: index.php'); exit; }
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Cairn CMS — Dashboard</title>
<link rel="stylesheet" href="admin.css">
</head>
<body>
  <aside class="sidebar">
    <div class="sidebar-logo">
      <img src="../homepage-assets/cairn-mark-vibrant.png" alt="" class="sb-mark">
      <div>
        <div class="sb-cairn">Cairn</div>
        <div class="sb-advisory">Content Studio</div>
      </div>
    </div>
    <nav class="sidebar-nav">
      <a href="dashboard.php" class="sn-item active">Dashboard</a>
      <a href="articles.php" class="sn-item">Field Notes</a>
      <a href="new-article.php" class="sn-item sn-highlight">+ New Article</a>
      <a href="edit-homepage.php" class="sn-item">Homepage Copy</a>
    </nav>
    <a href="logout.php" class="sb-logout">Sign out</a>
  </aside>

  <main class="main-content">
    <header class="page-header">
      <h1 class="page-title">Dashboard</h1>
    </header>

    <div class="dash-cards">
      <a href="articles.php" class="dash-card">
        <span class="dc-eyebrow">Content</span>
        <span class="dc-title">Field Notes</span>
        <p class="dc-desc">Edit existing articles or view all published notes.</p>
        <span class="dc-arrow">View all →</span>
      </a>
      <a href="new-article.php" class="dash-card dash-card-highlight">
        <span class="dc-eyebrow">Create</span>
        <span class="dc-title">New Article</span>
        <p class="dc-desc">Write a new field note. Your brand styles are applied automatically.</p>
        <span class="dc-arrow">Start writing →</span>
      </a>
      <a href="edit-homepage.php" class="dash-card">
        <span class="dc-eyebrow">Site</span>
        <span class="dc-title">Homepage Copy</span>
        <p class="dc-desc">Update your bio, services description, and other homepage text.</p>
        <span class="dc-arrow">Edit copy →</span>
      </a>
    </div>

    <div class="dash-rule"></div>

    <div class="quick-tip">
      <span class="qt-label">How publishing works</span>
      <p>Changes you make here are saved directly to your site files. After saving, <strong>run the deploy command</strong> on Hostinger to push everything live:<br>
      <code>cd ~/domains/cairnadvisory.co/public_html && git fetch origin && git reset --hard origin/main</code></p>
    </div>
  </main>
</body>
</html>
