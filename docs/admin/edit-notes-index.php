<?php
session_start();
if (empty($_SESSION['cairn_admin_auth'])) { header('Location: index.php'); exit; }

$filepath = dirname(__DIR__) . '/articles/index.html';
$saved = false;
$error = '';

function get_editable($html, $key) {
    preg_match('/<!-- editable:' . preg_quote($key, '/') . ' -->(.*?)<!-- \/editable -->/si', $html, $m);
    return isset($m[1]) ? trim($m[1]) : '';
}

function set_editable($html, $key, $value) {
    $pattern = '/<!-- editable:' . preg_quote($key, '/') . ' -->.*?<!-- \/editable -->/si';
    $replacement = '<!-- editable:' . $key . ' -->' . $value . '<!-- /editable -->';
    return preg_replace($pattern, $replacement, $html);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $html = file_get_contents($filepath);

    $fields = ['index-headline', 'index-deck', 'set-label', 'set-meta'];
    foreach ($fields as $key) {
        $post_key = str_replace('-', '_', $key);
        if (isset($_POST[$post_key])) {
            $html = set_editable($html, $key, $_POST[$post_key]);
        }
    }

    if (file_put_contents($filepath, $html) !== false) {
        $saved = true;
    } else {
        $error = 'Could not save file. Check server write permissions.';
    }
}

$html = file_get_contents($filepath);

function e($key) use ($html) {
    return htmlspecialchars(get_editable($html, $key));
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Cairn CMS — Notes Index</title>
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
      <a href="articles.php" class="sn-item">Field Notes</a>
      <a href="new-article.php" class="sn-item sn-highlight">+ New Article</a>
      <a href="edit-notes-index.php" class="sn-item active">Notes Index Page</a>
      <a href="edit-homepage.php" class="sn-item">Homepage Copy</a>
    </nav>
    <a href="logout.php" class="sb-logout">Sign out</a>
  </aside>

  <main class="main-content">
    <header class="page-header">
      <h1 class="page-title">Notes Index Page</h1>
      <a href="../articles/index.html" target="_blank" class="btn-ghost">View page →</a>
    </header>

    <?php if ($saved): ?>
      <div class="notice notice-success">Saved. <a href="../articles/index.html" target="_blank">View page →</a></div>
    <?php endif; ?>
    <?php if ($error): ?>
      <div class="notice notice-error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <form method="post" class="article-form">

      <div class="form-section-label">Page Header</div>

      <div class="form-field">
        <label class="field-label">Main Headline</label>
        <input type="text" name="index_headline" class="field-input field-input-lg" value="<?= e('index-headline') ?>">
        <span class="field-hint">Use &lt;em&gt; for the italic portion.</span>
      </div>
      <div class="form-field">
        <label class="field-label">Deck / Description</label>
        <textarea name="index_deck" class="field-input field-textarea" rows="3"><?= e('index-deck') ?></textarea>
        <span class="field-hint">Use &lt;strong&gt; for bold text.</span>
      </div>

      <div class="form-section-label">Article Set Label</div>

      <div class="form-field">
        <label class="field-label">Set Label</label>
        <input type="text" name="set_label" class="field-input" value="<?= e('set-label') ?>" placeholder="The current set · April 2026">
        <span class="field-hint">Update when you publish a new batch of articles.</span>
      </div>
      <div class="form-field">
        <label class="field-label">Set Meta</label>
        <input type="text" name="set_meta" class="field-input" value="<?= e('set-meta') ?>" placeholder="3 essays · 18 minutes total" style="max-width: 300px">
      </div>

      <div class="form-actions">
        <button type="submit" class="btn-primary btn-large">Save Changes</button>
        <a href="../articles/index.html" target="_blank" class="btn-ghost">Preview →</a>
      </div>

    </form>
  </main>
</body>
</html>
