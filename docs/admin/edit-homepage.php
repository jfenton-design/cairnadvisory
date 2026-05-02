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
    return preg_replace($pattern, '<!-- editable:' . $key . ' -->' . $value . '<!-- /editable -->', $html);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $html = file_get_contents($filepath);

    $fields = [
        'hero-eyebrow', 'hero-headline', 'hero-sub',
        'statement',
        'about-quote', 'about-bio',
        'stat-1-num', 'stat-1-label', 'stat-2-num', 'stat-2-label',
        'stat-3-num', 'stat-3-label', 'stat-4-num', 'stat-4-label',
        'exp-1-name', 'exp-1-role', 'exp-1-desc',
        'exp-2-name', 'exp-2-role', 'exp-2-desc',
        'exp-3-name', 'exp-3-role', 'exp-3-desc',
        'exp-4-name', 'exp-4-role', 'exp-4-desc',
        'industries-title',
        'ind-1-name', 'ind-1-desc', 'ind-2-name', 'ind-2-desc',
        'ind-3-name', 'ind-3-desc', 'ind-4-name', 'ind-4-desc',
        'ind-5-name', 'ind-5-desc',
        'services-title', 'services-aside',
        'svc-1-title', 'svc-1-text', 'svc-2-title', 'svc-2-text', 'svc-3-title', 'svc-3-text',
        'approach-headline', 'approach-intro',
        'approach-1-title', 'approach-1-text',
        'approach-2-title', 'approach-2-text',
        'approach-3-title', 'approach-3-text',
        'notes-title',
        'contact-title', 'contact-text',
    ];

    foreach ($fields as $key) {
        $post_key = str_replace('-', '_', $key);
        if (isset($_POST[$post_key])) {
            $html = set_editable($html, $key, $_POST[$post_key]);
        }
    }

    // Eyebrow font size (inline style on span)
    if (!empty($_POST['hero_eyebrow_size'])) {
        $size = max(8, min(32, (int)$_POST['hero_eyebrow_size']));
        $html = preg_replace(
            '/(<span class="hero-eyebrow-text" style=")([^"]*)(")/i',
            '${1}color:#2C1E10; font-size:' . $size . 'px${3}',
            $html
        );
    }

    if (file_put_contents($filepath, $html) !== false) {
        $saved = true;
    } else {
        $error = 'Could not save. Check server write permissions.';
    }
}

$html = file_get_contents($filepath);
$e = function($key) use ($html) {
    return htmlspecialchars(get_editable($html, $key));
};

preg_match('/hero-eyebrow-text" style="[^"]*font-size:\s*(\d+)px/i', $html, $szm);
$eyebrow_size = $szm[1] ?? 10;
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

      <!-- ── HERO ── -->
      <div class="form-section-label">Hero</div>

      <div class="form-row" style="grid-template-columns: 1fr 160px; gap: 16px;">
        <div class="form-field">
          <label class="field-label">Eyebrow Text</label>
          <input type="text" name="hero_eyebrow" class="field-input" value="<?= $e('hero-eyebrow') ?>">
        </div>
        <div class="form-field">
          <label class="field-label">Font Size (px)</label>
          <input type="number" name="hero_eyebrow_size" class="field-input" value="<?= (int)$eyebrow_size ?>" min="8" max="32">
        </div>
      </div>
      <div class="form-field">
        <label class="field-label">Headline</label>
        <input type="text" name="hero_headline" class="field-input field-input-lg" value="<?= $e('hero-headline') ?>">
        <span class="field-hint">Use &lt;em style="color:#8B6A48;"&gt;italic text&lt;/em&gt; for the styled italic portion.</span>
      </div>
      <div class="form-field">
        <label class="field-label">Subtext</label>
        <textarea name="hero_sub" class="field-input field-textarea" rows="3"><?= $e('hero-sub') ?></textarea>
      </div>

      <!-- ── STATEMENT ── -->
      <div class="form-section-label">Statement</div>
      <div class="form-field">
        <label class="field-label">Statement Text</label>
        <textarea name="statement" class="field-input field-textarea" rows="4"><?= $e('statement') ?></textarea>
        <span class="field-hint">Use &lt;em&gt; for italic, &lt;span class="grey"&gt; for grey text, &lt;br&gt; for line breaks.</span>
      </div>

      <!-- ── ABOUT ── -->
      <div class="form-section-label">About</div>
      <div class="form-field">
        <label class="field-label">Pull Quote</label>
        <textarea name="about_quote" class="field-input field-textarea" rows="3"><?= $e('about-quote') ?></textarea>
        <span class="field-hint">Use &lt;em&gt; for the italic portion.</span>
      </div>
      <div class="form-field">
        <label class="field-label">Bio Paragraphs</label>
        <textarea name="about_bio" class="field-input field-textarea" rows="10"><?= $e('about-bio') ?></textarea>
        <span class="field-hint">Include &lt;p&gt;...&lt;/p&gt; tags for each paragraph.</span>
      </div>

      <!-- ── ABOUT STATS ── -->
      <div class="form-section-label">About — Stat Cards</div>
      <?php for ($i = 1; $i <= 4; $i++): ?>
        <div class="form-row" style="grid-template-columns: 140px 1fr; gap: 14px; align-items: start;">
          <div class="form-field">
            <label class="field-label">Stat <?= $i ?> Number</label>
            <input type="text" name="stat_<?= $i ?>_num" class="field-input" value="<?= $e("stat-$i-num") ?>">
            <span class="field-hint">e.g. 20&lt;sup&gt;+&lt;/sup&gt;</span>
          </div>
          <div class="form-field">
            <label class="field-label">Stat <?= $i ?> Label</label>
            <input type="text" name="stat_<?= $i ?>_label" class="field-input" value="<?= $e("stat-$i-label") ?>">
          </div>
        </div>
      <?php endfor; ?>

      <!-- ── EXPERIENCE ── -->
      <div class="form-section-label">Where I've Operated</div>
      <?php
        $exp_names = ['Amazon', 'StockX', 'GoFundMe', 'Tespo'];
        for ($i = 1; $i <= 4; $i++):
      ?>
        <div class="form-field">
          <label class="field-label"><?= $exp_names[$i-1] ?></label>
          <div class="form-row" style="grid-template-columns: 1fr 1fr; gap: 12px; margin-bottom: 8px;">
            <input type="text" name="exp_<?= $i ?>_name" class="field-input" value="<?= $e("exp-$i-name") ?>" placeholder="Company">
            <input type="text" name="exp_<?= $i ?>_role" class="field-input" value="<?= $e("exp-$i-role") ?>" placeholder="Role">
          </div>
          <textarea name="exp_<?= $i ?>_desc" class="field-input field-textarea" rows="2"><?= $e("exp-$i-desc") ?></textarea>
        </div>
      <?php endfor; ?>

      <!-- ── INDUSTRIES ── -->
      <div class="form-section-label">Industries</div>
      <div class="form-field">
        <label class="field-label">Section Headline</label>
        <input type="text" name="industries_title" class="field-input field-input-lg" value="<?= $e('industries-title') ?>">
        <span class="field-hint">Use &lt;em&gt; for italic.</span>
      </div>
      <?php for ($i = 1; $i <= 5; $i++): ?>
        <div class="form-field">
          <label class="field-label">Industry <?= $i ?></label>
          <div style="margin-bottom: 8px;">
            <input type="text" name="ind_<?= $i ?>_name" class="field-input" value="<?= $e("ind-$i-name") ?>" placeholder="Industry name">
          </div>
          <textarea name="ind_<?= $i ?>_desc" class="field-input field-textarea" rows="2"><?= $e("ind-$i-desc") ?></textarea>
        </div>
      <?php endfor; ?>

      <!-- ── SERVICES ── -->
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
      <?php
        $svc_labels = ['AI Readiness Assessment', 'Implementation & Integration', 'Ongoing Advisory'];
        for ($i = 1; $i <= 3; $i++):
      ?>
        <div class="form-field">
          <label class="field-label">Service <?= $i ?> — <?= $svc_labels[$i-1] ?></label>
          <input type="text" name="svc_<?= $i ?>_title" class="field-input" value="<?= $e("svc-$i-title") ?>" style="margin-bottom: 8px;">
          <textarea name="svc_<?= $i ?>_text" class="field-input field-textarea" rows="3"><?= $e("svc-$i-text") ?></textarea>
        </div>
      <?php endfor; ?>

      <!-- ── APPROACH ── -->
      <div class="form-section-label">How I Work</div>
      <div class="form-field">
        <label class="field-label">Section Headline</label>
        <input type="text" name="approach_headline" class="field-input field-input-lg" value="<?= $e('approach-headline') ?>">
        <span class="field-hint">Use &lt;br&gt; for line break.</span>
      </div>
      <div class="form-field">
        <label class="field-label">Section Intro</label>
        <textarea name="approach_intro" class="field-input field-textarea" rows="3"><?= $e('approach-intro') ?></textarea>
      </div>
      <?php for ($i = 1; $i <= 3; $i++): ?>
        <div class="form-field">
          <label class="field-label">Principle <?= $i ?></label>
          <input type="text" name="approach_<?= $i ?>_title" class="field-input" value="<?= $e("approach-$i-title") ?>" style="margin-bottom: 8px;">
          <textarea name="approach_<?= $i ?>_text" class="field-input field-textarea" rows="3"><?= $e("approach-$i-text") ?></textarea>
        </div>
      <?php endfor; ?>

      <!-- ── FIELD NOTES ── -->
      <div class="form-section-label">Field Notes Section</div>
      <div class="form-field">
        <label class="field-label">Section Headline</label>
        <input type="text" name="notes_title" class="field-input field-input-lg" value="<?= $e('notes-title') ?>">
        <span class="field-hint">Use &lt;em&gt; for italic.</span>
      </div>

      <!-- ── CONTACT ── -->
      <div class="form-section-label">Contact</div>
      <div class="form-field">
        <label class="field-label">Headline</label>
        <input type="text" name="contact_title" class="field-input field-input-lg" value="<?= $e('contact-title') ?>">
        <span class="field-hint">Use &lt;br&gt; for line break, &lt;em&gt; for italic.</span>
      </div>
      <div class="form-field">
        <label class="field-label">Subtext</label>
        <textarea name="contact_text" class="field-input field-textarea" rows="3"><?= $e('contact-text') ?></textarea>
      </div>

      <div class="form-actions">
        <button type="submit" class="btn-primary btn-large">Save All Changes</button>
        <a href="../index.html" target="_blank" class="btn-ghost">Preview →</a>
      </div>

    </form>
  </main>
</body>
</html>
