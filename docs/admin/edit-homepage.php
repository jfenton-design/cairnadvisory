<?php
session_start();
if (empty($_SESSION['cairn_admin_auth'])) { header('Location: index.php'); exit; }

$filepath = dirname(__DIR__) . '/index.html';
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

    $fields = [
        'hero-headline', 'hero-sub',
        'statement',
        'about-quote', 'about-bio',
        'services-title', 'services-aside',
        'notes-title',
        'contact-title', 'contact-text',
    ];

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
$e = function($key) use ($html) {
    return htmlspecialchars(get_editable($html, $key));
};
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
      <a href="edit-notes-index.php" class="sn-item">Notes Index Page</a>
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
    <?php if ($error): ?>
      <div class="notice notice-error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <form method="post" class="article-form">

      <div class="form-section-label">Hero</div>

      <div class="form-field">
        <label class="field-label">Hero Headline</label>
        <input type="text" name="hero_headline" class="field-input field-input-lg" value="<?= $e('hero-headline') ?>">
        <span class="field-hint">Use &lt;em style="color:#8B6A48;"&gt;italic part&lt;/em&gt; for the styled italic portion.</span>
      </div>
      <div class="form-field">
        <label class="field-label">Hero Subtext</label>
        <textarea name="hero_sub" class="field-input field-textarea" rows="3"><?= $e('hero-sub') ?></textarea>
      </div>

      <div class="form-section-label">Statement</div>

      <div class="form-field">
        <label class="field-label">Statement Text</label>
        <textarea name="statement" class="field-input field-textarea" rows="4"><?= $e('statement') ?></textarea>
        <span class="field-hint">You can use &lt;em&gt; for italic, &lt;span class="grey"&gt; for grey text, and &lt;br&gt; for line breaks.</span>
      </div>

      <div class="form-section-label">About</div>

      <div class="form-field">
        <label class="field-label">Pull Quote</label>
        <textarea name="about_quote" class="field-input field-textarea" rows="3"><?= $e('about-quote') ?></textarea>
        <span class="field-hint">Use &lt;em&gt; for the italic portion.</span>
      </div>
      <div class="form-field">
        <label class="field-label">Bio Paragraphs</label>
        <textarea name="about_bio" class="field-input field-textarea" rows="10"><?= $e('about-bio') ?></textarea>
        <span class="field-hint">Include the full &lt;p&gt;...&lt;/p&gt; tags for each paragraph.</span>
      </div>

      <div class="form-section-label">Work / Services</div>

      <div class="form-field">
        <label class="field-label">Section Headline</label>
        <input type="text" name="services_title" class="field-input field-input-lg" value="<?= $e('services-title') ?>">
        <span class="field-hint">Use &lt;br&gt; for a line break.</span>
      </div>
      <div class="form-field">
        <label class="field-label">Section Intro</label>
        <textarea name="services_aside" class="field-input field-textarea" rows="3"><?= $e('services-aside') ?></textarea>
      </div>

      <div class="form-section-label">Field Notes Section</div>

      <div class="form-field">
        <label class="field-label">Section Headline</label>
        <input type="text" name="notes_title" class="field-input field-input-lg" value="<?= $e('notes-title') ?>">
        <span class="field-hint">Use &lt;em&gt; for italic.</span>
      </div>

      <div class="form-section-label">Contact</div>

      <div class="form-field">
        <label class="field-label">Contact Headline</label>
        <input type="text" name="contact_title" class="field-input field-input-lg" value="<?= $e('contact-title') ?>">
        <span class="field-hint">Use &lt;br&gt; for line break, &lt;em&gt; for italic.</span>
      </div>
      <div class="form-field">
        <label class="field-label">Contact Subtext</label>
        <textarea name="contact_text" class="field-input field-textarea" rows="3"><?= $e('contact-text') ?></textarea>
      </div>

      <div class="form-actions">
        <button type="submit" class="btn-primary btn-large">Save Changes</button>
        <a href="../index.html" target="_blank" class="btn-ghost">Preview →</a>
      </div>

    </form>
  </main>
</body>
</html>
