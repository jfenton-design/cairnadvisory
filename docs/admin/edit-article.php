<?php
session_start();
if (empty($_SESSION['cairn_admin_auth'])) { header('Location: index.php'); exit; }

$file = basename($_GET['file'] ?? '');
if (!$file || !preg_match('/^[0-9a-z\-]+\.html$/', $file)) {
    header('Location: articles.php'); exit;
}

$filepath = dirname(__DIR__) . '/articles/' . $file;
if (!file_exists($filepath)) { header('Location: articles.php'); exit; }

$saved = false;
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $html = file_get_contents($filepath);

    // Update title
    if (!empty($_POST['title'])) {
        $new_title = trim($_POST['title']);
        $new_em    = trim($_POST['title_em'] ?? '');
        $title_html = $new_em
            ? htmlspecialchars($new_title) . ' <em>' . htmlspecialchars($new_em) . '</em>'
            : htmlspecialchars($new_title);
        $html = preg_replace('/<h1 class="article-title">.*?<\/h1>/si',
            '<h1 class="article-title">' . $title_html . '</h1>', $html);
    }

    // Update deck
    if (isset($_POST['deck'])) {
        $html = preg_replace('/<p class="article-deck">.*?<\/p>/si',
            '<p class="article-deck">' . htmlspecialchars(trim($_POST['deck'])) . '</p>', $html);
    }

    // Update reading time
    if (!empty($_POST['reading'])) {
        $html = preg_replace('/(<span class="dot"><\/span>\s*<span>).*?(<\/span>)/si',
            '${1}' . htmlspecialchars(trim($_POST['reading'])) . '${2}', $html);
    }

    // Update body prose
    if (!empty($_POST['body'])) {
        $sections = preg_split('/\n{2,}/', trim($_POST['body']));
        $body_html = '';
        foreach ($sections as $s) {
            $s = trim($s);
            if ($s === '') continue;
            if (preg_match('/^\s*<[a-z]/', $s)) {
                $body_html .= "\n      " . $s . "\n";
            } else {
                $body_html .= "\n      <p>" . nl2br(htmlspecialchars($s)) . "</p>\n";
            }
        }
        $html = preg_replace('/<div class="prose">(.*?)<\/div>/si',
            '<div class="prose">' . $body_html . "\n    </div>", $html);
    }

    file_put_contents($filepath, $html);
    $saved = true;
}

// Parse current values from file
$html = file_get_contents($filepath);
preg_match('/<h1 class="article-title">(.*?)<\/h1>/si', $html, $tm);
$raw_title = isset($tm[1]) ? $tm[1] : '';
$title_plain = preg_replace('/<em>(.*?)<\/em>/si', '', $raw_title);
$title_plain = trim(strip_tags($title_plain));
preg_match('/<em>(.*?)<\/em>/si', $raw_title, $em_m);
$title_em = isset($em_m[1]) ? strip_tags($em_m[1]) : '';

preg_match('/<p class="article-deck">(.*?)<\/p>/si', $html, $dm);
$deck = isset($dm[1]) ? strip_tags($dm[1]) : '';

preg_match('/<span class="dot"><\/span>\s*<span>(.*?)<\/span>/si', $html, $rm);
$reading = isset($rm[1]) ? strip_tags($rm[1]) : '';

preg_match('/<div class="prose">(.*?)<\/div>/si', $html, $bm);
$body_raw = isset($bm[1]) ? trim($bm[1]) : '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Cairn CMS — Edit Article</title>
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
      <a href="articles.php" class="sn-item active">Field Notes</a>
      <a href="new-article.php" class="sn-item sn-highlight">+ New Article</a>
      <a href="edit-homepage.php" class="sn-item">Homepage Copy</a>
    </nav>
    <a href="logout.php" class="sb-logout">Sign out</a>
  </aside>

  <main class="main-content">
    <header class="page-header">
      <h1 class="page-title">Edit: <?= htmlspecialchars($file) ?></h1>
      <a href="../articles/<?= htmlspecialchars($file) ?>" target="_blank" class="btn-ghost">View live →</a>
    </header>

    <?php if ($saved): ?>
      <div class="notice notice-success">Saved. <a href="../articles/<?= htmlspecialchars($file) ?>" target="_blank">View article →</a></div>
    <?php endif; ?>

    <form method="post" class="article-form">

      <div class="form-field">
        <label class="field-label">Title</label>
        <input type="text" name="title" class="field-input field-input-lg" value="<?= htmlspecialchars($title_plain) ?>">
      </div>
      <div class="form-field">
        <label class="field-label">Title — italic ending <span class="field-opt">(optional)</span></label>
        <input type="text" name="title_em" class="field-input" value="<?= htmlspecialchars($title_em) ?>">
      </div>
      <div class="form-field">
        <label class="field-label">Deck</label>
        <input type="text" name="deck" class="field-input" value="<?= htmlspecialchars($deck) ?>">
      </div>
      <div class="form-field">
        <label class="field-label">Reading time</label>
        <input type="text" name="reading" class="field-input" value="<?= htmlspecialchars($reading) ?>" style="max-width: 200px">
      </div>

      <div class="form-section-label">Body</div>

      <div class="form-field">
        <label class="field-label">Article body</label>
        <textarea name="body" class="field-input field-textarea" rows="32"><?= htmlspecialchars($body_raw) ?></textarea>
        <span class="field-hint">Blank lines = new paragraph. HTML for callouts, pull quotes, and headings is preserved.</span>
      </div>

      <div class="form-actions">
        <button type="submit" class="btn-primary btn-large">Save Changes</button>
        <a href="articles.php" class="btn-ghost">← Back to articles</a>
      </div>
    </form>
  </main>
</body>
</html>
