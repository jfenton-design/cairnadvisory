<?php
session_start();
if (empty($_SESSION['cairn_admin_auth'])) { header('Location: index.php'); exit; }

$articles_dir = dirname(__DIR__) . '/articles/';
$articles = [];
$files = glob($articles_dir . '[0-9]*.html');
sort($files);

foreach ($files as $file) {
    $filename = basename($file);
    if ($filename === 'index.html') continue;
    $html = file_get_contents($file);

    preg_match('/<title>(.*?) — Cairn/i', $html, $tm);
    preg_match('/<h1 class="article-title">(.*?)<\/h1>/si', $html, $hm);
    preg_match('/<span>(\d+ min read)<\/span>/', $html, $rm);

    $title = isset($hm[1]) ? strip_tags($hm[1]) : ($tm[1] ?? $filename);
    $reading = $rm[1] ?? '';
    $articles[] = ['file' => $filename, 'title' => $title, 'reading' => $reading];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Cairn CMS — Field Notes</title>
<link rel="stylesheet" href="admin.css">
</head>
<body>
  <aside class="sidebar">
    <div class="sidebar-logo">
      <img src="../homepage-assets/cairn-mark-cutout.png" alt="" class="sb-mark">
      <div>
        <div class="sb-cairn">Cairn</div>
        <div class="sb-advisory">Content Studio</div>
      </div>
    </div>
    <nav class="sidebar-nav">
      <a href="dashboard.php" class="sn-item">Dashboard</a>
      <a href="articles.php" class="sn-item active">Field Notes</a>
      <a href="new-article.php" class="sn-item sn-highlight">+ New Article</a>
      <a href="edit-notes-index.php" class="sn-item">Notes Index Page</a>
      <a href="edit-homepage.php" class="sn-item">Homepage Copy</a>
    </nav>
    <a href="logout.php" class="sb-logout">Sign out</a>
  </aside>

  <main class="main-content">
    <header class="page-header">
      <h1 class="page-title">Field Notes</h1>
      <a href="new-article.php" class="btn-primary">+ New Article</a>
    </header>

    <div class="article-list">
      <?php if (empty($articles)): ?>
        <p class="empty-state">No articles yet. <a href="new-article.php">Create the first one →</a></p>
      <?php else: ?>
        <?php foreach ($articles as $a): ?>
          <div class="al-row">
            <div class="al-info">
              <span class="al-title"><?= htmlspecialchars($a['title']) ?></span>
              <span class="al-meta"><?= htmlspecialchars($a['file']) ?><?= $a['reading'] ? ' · ' . htmlspecialchars($a['reading']) : '' ?></span>
            </div>
            <div class="al-actions">
              <a href="../articles/<?= htmlspecialchars($a['file']) ?>" target="_blank" class="btn-ghost">View →</a>
              <a href="edit-article.php?file=<?= urlencode($a['file']) ?>" class="btn-secondary">Edit</a>
            </div>
          </div>
        <?php endforeach; ?>
      <?php endif; ?>
    </div>
  </main>
</body>
</html>
