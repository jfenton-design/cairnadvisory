<?php
session_start();
if (empty($_SESSION['cairn_admin_auth'])) { header('Location: index.php'); exit; }

$filepath = dirname(__DIR__) . '/index.html';
$saved = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $html = file_get_contents($filepath);

    // Hero sub
    if (isset($_POST['hero_sub'])) {
        $html = preg_replace('/(<p class="hero-sub"[^>]*>).*?(<\/p>)/si',
            '${1}' . htmlspecialchars(trim($_POST['hero_sub'])) . '${2}', $html);
    }

    // About pull quote
    if (isset($_POST['about_quote'])) {
        $html = preg_replace('/(<p class="about-pull-quote">).*?(<\/p>)/si',
            '${1}' . trim($_POST['about_quote']) . '${2}', $html, 1);
    }

    // Bio paragraphs
    if (!empty($_POST['bio'])) {
        $paras = array_filter(array_map('trim', explode("\n\n", trim($_POST['bio']))));
        $bio_html = '';
        foreach ($paras as $p) {
            $bio_html .= '          <p>' . htmlspecialchars($p) . '</p>' . "\n";
        }
        $html = preg_replace('/(<div class="about-bio">).*?(<\/div>)/si',
            '${1}' . "\n" . $bio_html . '        ${2}', $html);
    }

    // Statement text
    if (isset($_POST['statement'])) {
        $html = preg_replace('/(<p class="statement-text">).*?(<\/p>)/si',
            '${1}' . trim($_POST['statement']) . '${2}', $html);
    }

    file_put_contents($filepath, $html);
    $saved = true;
    $html = file_get_contents($filepath); // re-read for display
}

$html = file_get_contents($filepath);

preg_match('/<p class="hero-sub"[^>]*>(.*?)<\/p>/si', $html, $hs);
$hero_sub = isset($hs[1]) ? strip_tags($hs[1]) : '';

preg_match('/<p class="about-pull-quote">(.*?)<\/p>/si', $html, $aq);
$about_quote = isset($aq[1]) ? trim($aq[1]) : '';

preg_match('/<div class="about-bio">(.*?)<\/div>/si', $html, $bm);
$bio_html_raw = isset($bm[1]) ? $bm[1] : '';
preg_match_all('/<p>(.*?)<\/p>/si', $bio_html_raw, $bp_m);
$bio_text = implode("\n\n", array_map('strip_tags', $bp_m[1] ?? []));

preg_match('/<p class="statement-text">(.*?)<\/p>/si', $html, $sm);
$statement = isset($sm[1]) ? trim($sm[1]) : '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Cairn CMS — Homepage Copy</title>
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
      <a href="dashboard.php" class="sn-item">Dashboard</a>
      <a href="articles.php" class="sn-item">Field Notes</a>
      <a href="new-article.php" class="sn-item sn-highlight">+ New Article</a>
      <a href="edit-homepage.php" class="sn-item active">Homepage Copy</a>
    </nav>
    <a href="logout.php" class="sb-logout">Sign out</a>
  </aside>

  <main class="main-content">
    <header class="page-header">
      <h1 class="page-title">Homepage Copy</h1>
      <a href="../index.html" target="_blank" class="btn-ghost">View homepage →</a>
    </header>

    <?php if ($saved): ?>
      <div class="notice notice-success">Saved. <a href="../index.html" target="_blank">View homepage →</a></div>
    <?php endif; ?>

    <form method="post" class="article-form">

      <div class="form-section-label">Hero</div>
      <div class="form-field">
        <label class="field-label">Hero subheadline</label>
        <textarea name="hero_sub" class="field-input field-textarea" rows="4"><?= htmlspecialchars($hero_sub) ?></textarea>
        <span class="field-hint">The paragraph that appears below "Find your path forward."</span>
      </div>

      <div class="form-section-label">Statement</div>
      <div class="form-field">
        <label class="field-label">Statement text</label>
        <textarea name="statement" class="field-input field-textarea" rows="5"><?= htmlspecialchars($statement) ?></textarea>
        <span class="field-hint">The large quote in the section after the hero. HTML tags like &lt;span&gt; and &lt;em&gt; are preserved.</span>
      </div>

      <div class="form-section-label">About</div>
      <div class="form-field">
        <label class="field-label">Pull quote</label>
        <textarea name="about_quote" class="field-input field-textarea" rows="3"><?= htmlspecialchars($about_quote) ?></textarea>
        <span class="field-hint">The large quote next to your photo. Use &lt;em&gt; for italic.</span>
      </div>
      <div class="form-field">
        <label class="field-label">Bio paragraphs</label>
        <textarea name="bio" class="field-input field-textarea" rows="10"><?= htmlspecialchars($bio_text) ?></textarea>
        <span class="field-hint">Separate paragraphs with a blank line.</span>
      </div>

      <div class="form-actions">
        <button type="submit" class="btn-primary btn-large">Save Changes</button>
      </div>
    </form>
  </main>
</body>
</html>
