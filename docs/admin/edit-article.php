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
$sync_warning = '';
$vistas_dir = dirname(__DIR__) . '/assets/vistas/';
$vistas_url = '../assets/vistas/';

// Handle watercolor upload
if (!empty($_FILES['new_watercolor']['name'])) {
    $allowed = ['image/png', 'image/jpeg', 'image/jpg', 'image/webp'];
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mime = finfo_file($finfo, $_FILES['new_watercolor']['tmp_name']);
    finfo_close($finfo);

    if (!in_array($mime, $allowed)) {
        $error = 'Uploaded file must be a PNG, JPG, or WebP image.';
    } elseif ($_FILES['new_watercolor']['size'] > 10 * 1024 * 1024) {
        $error = 'Image must be under 10MB.';
    } else {
        $ext = pathinfo($_FILES['new_watercolor']['name'], PATHINFO_EXTENSION);
        $upload_name = preg_replace('/[^a-z0-9\-]/', '', strtolower(str_replace(' ', '-', pathinfo($_FILES['new_watercolor']['name'], PATHINFO_FILENAME)))) . '.' . strtolower($ext);
        $upload_path = $vistas_dir . $upload_name;
        if (move_uploaded_file($_FILES['new_watercolor']['tmp_name'], $upload_path)) {
            $_POST['watercolor'] = $upload_name;
        } else {
            $error = 'Upload failed. Check directory write permissions.';
        }
    }
}

// Scan available watercolors
$watercolors = [];
foreach (glob($vistas_dir . '*.{png,jpg,jpeg,webp}', GLOB_BRACE) as $f) {
    $watercolors[] = basename($f);
}
sort($watercolors);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && empty($error)) {
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

    // Update month/year in byline
    if (!empty($_POST['month']) || !empty($_POST['year'])) {
        $month = htmlspecialchars(trim($_POST['month'] ?? ''));
        $year  = htmlspecialchars(trim($_POST['year'] ?? ''));
        $html = preg_replace(
            '/(<span class="name">Jacob Fenton<\/span>\s*<span>·<\/span>\s*<span>).*?(<\/span>)/si',
            '${1}' . $month . ' ' . $year . '${2}',
            $html
        );
        // Update footer year too
        if ($year) {
            $html = preg_replace('/© \d{4} Cairn Advisory/', '© ' . $year . ' Cairn Advisory', $html);
        }
    }

    // Per-watercolor object-position for optimal banner crop
    $wc_positions = [
        'bistro.png'               => 'center 50%',
        'city watercolor.png'      => 'center 65%',
        'mountain-vista.png'       => 'center 35%',
        'river.png'                => 'center 50%',
        'row-houses.png'           => 'center 55%',
        'shoreline-watercolor.png' => 'center 40%',
    ];

    // Update watercolor banner
    $wc_changed = false;
    $wc = '';
    if (!empty($_POST['watercolor'])) {
        $wc = preg_replace('/[^a-z0-9\-\.\s%]/', '', $_POST['watercolor']);
        $pos = $wc_positions[$wc] ?? 'center 50%';
        $html_before = $html;
        // Replace the whole img tag to update both src and object-position
        $html = preg_replace(
            '/<img src="[^"]*" alt="" class="banner-img"[^>]*>/i',
            '<img src="../assets/vistas/' . $wc . '" alt="" class="banner-img" style="object-position: ' . $pos . ';">',
            $html
        );
        $wc_changed = ($html !== $html_before);
    }

    // Update body prose
    if (isset($_POST['body']) && trim($_POST['body']) !== '') {
        $body_input = trim($_POST['body']);
        // If the body is raw HTML, preserve it verbatim.
        // Otherwise treat as plain-text paragraphs separated by blank lines.
        if (preg_match('/^\s*</i', $body_input)) {
            $body_html = "\n" . $body_input . "\n    ";
        } else {
            $sections = preg_split('/\n{2,}/', $body_input);
            $body_html = "\n";
            foreach ($sections as $s) {
                $s = trim($s);
                if ($s === '') continue;
                $body_html .= "      <p>" . nl2br(htmlspecialchars($s)) . "</p>\n\n";
            }
            $body_html .= "    ";
        }
        // Use callback to safely embed body without PCRE backreference issues
        $html = preg_replace_callback(
            '/<div class="prose">.*<\/div>(\s*<\/article>)/si',
            function ($m) use ($body_html) {
                return '<div class="prose">' . $body_html . '</div>' . $m[1];
            },
            $html
        );
    }

    if (file_put_contents($filepath, $html) !== false) {
        $saved = true;
        require_once __DIR__ . '/github-sync.php';
        $sync_err = push_to_github($filepath, 'docs/articles/' . $file);
        if ($sync_err) $sync_warning = $sync_err;

        // Sync watercolor to homepage and notes index cards
        if ($wc_changed && $wc) {
            $hp_path = dirname(__DIR__) . '/index.html';
            $ni_path = dirname(__DIR__) . '/articles/index.html';

            // Homepage: url('assets/vistas/IMAGE')
            $hp = file_get_contents($hp_path);
            $hp_new = preg_replace(
                '/(<a\b[^>]*href="articles\/' . preg_quote($file, '/') . '"[^>]*url\(\')([^\']+)(\'\))/si',
                '${1}assets/vistas/' . $wc . '${3}',
                $hp
            );
            if ($hp_new !== $hp) {
                file_put_contents($hp_path, $hp_new);
                $e2 = push_to_github($hp_path, 'docs/index.html');
                if ($e2 && !$sync_warning) $sync_warning = $e2;
            }

            // Notes index: url('../assets/vistas/IMAGE')
            $ni = file_get_contents($ni_path);
            $ni_new = preg_replace(
                '/(<a\b[^>]*href="' . preg_quote($file, '/') . '"[^>]*url\(\')([^\']+)(\'\))/si',
                '${1}../assets/vistas/' . $wc . '${3}',
                $ni
            );
            if ($ni_new !== $ni) {
                file_put_contents($ni_path, $ni_new);
                $e3 = push_to_github($ni_path, 'docs/articles/index.html');
                if ($e3 && !$sync_warning) $sync_warning = $e3;
            }
        }
    } else {
        $error = 'Could not save. Check server write permissions.';
    }
}

// Parse current values from file
$html = file_get_contents($filepath);

preg_match('/<h1 class="article-title">(.*?)<\/h1>/si', $html, $tm);
$raw_title   = $tm[1] ?? '';
$title_plain = trim(strip_tags(preg_replace('/<em>.*?<\/em>/si', '', $raw_title)));
preg_match('/<em>(.*?)<\/em>/si', $raw_title, $em_m);
$title_em = isset($em_m[1]) ? strip_tags($em_m[1]) : '';

preg_match('/<p class="article-deck">(.*?)<\/p>/si', $html, $dm);
$deck = isset($dm[1]) ? strip_tags($dm[1]) : '';

preg_match('/<span class="dot"><\/span>\s*<span>(.*?)<\/span>/si', $html, $rm);
$reading = isset($rm[1]) ? strip_tags($rm[1]) : '';

preg_match('/<span class="name">Jacob Fenton<\/span>\s*<span>·<\/span>\s*<span>(.*?)<\/span>/si', $html, $bym);
$byline_parts = isset($bym[1]) ? explode(' ', trim(strip_tags($bym[1])), 2) : ['', ''];
$month = $byline_parts[0] ?? '';
$year  = $byline_parts[1] ?? date('Y');

preg_match('/assets\/vistas\/([^"]+)" alt="" class="banner-img"/i', $html, $wm);
$current_watercolor = $wm[1] ?? ($watercolors[0] ?? '');

// Greedy match to capture full prose including any nested divs
preg_match('/<div class="prose">(.*)<\/div>\s*<\/article>/si', $html, $bm);
$body_raw = isset($bm[1]) ? trim($bm[1]) : '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Cairn CMS — Edit Article</title>
<link rel="stylesheet" href="admin.css">
<style>
  .wc-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(140px, 1fr)); gap: 12px; }
  .wc-card { position: relative; }
  .wc-card input[type="radio"] { position: absolute; opacity: 0; width: 0; height: 0; }
  .wc-thumb {
    display: block; cursor: pointer;
    border: 2px solid transparent;
    border-radius: 8px; overflow: hidden;
    aspect-ratio: 3/2; transition: border-color 0.15s, box-shadow 0.15s;
  }
  .wc-thumb img { width: 100%; height: 100%; object-fit: cover; display: block; }
  .wc-thumb-name {
    display: block; font-size: 11px; letter-spacing: 0.1em; text-transform: uppercase;
    color: var(--cairn-umber); margin-top: 6px; text-align: center;
  }
  .wc-card input:checked + .wc-thumb { border-color: var(--cairn-amber); box-shadow: 0 0 0 3px rgba(196,168,122,0.25); }
  .wc-upload-box {
    border: 1.5px dashed rgba(196,168,122,0.5); border-radius: 8px;
    padding: 24px; text-align: center; cursor: pointer;
    transition: border-color 0.15s, background 0.15s;
    background: var(--cairn-white);
  }
  .wc-upload-box:hover { border-color: var(--cairn-amber); background: var(--cairn-linen); }
  .wc-upload-box input[type="file"] { display: none; }
  .wc-upload-label { font-size: 12px; font-weight: 500; letter-spacing: 0.14em; text-transform: uppercase; color: var(--cairn-umber); }
  .wc-upload-sub { font-size: 11px; color: var(--cairn-slate); margin-top: 4px; }
</style>
</head>
<body>
  <aside class="sidebar">
    <div class="sidebar-logo">
      <img src="../homepage-assets/cairn-mark-master.png" alt="" class="sb-mark">
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
      <h1 class="page-title">Edit: <?= htmlspecialchars($file) ?></h1>
      <a href="../articles/<?= htmlspecialchars($file) ?>" target="_blank" class="btn-ghost">View live →</a>
    </header>

    <?php if ($saved && !$sync_warning): ?>
      <div class="notice notice-success">Saved &amp; synced to GitHub. <a href="../articles/<?= htmlspecialchars($file) ?>" target="_blank">View article →</a></div>
    <?php elseif ($saved && $sync_warning): ?>
      <div class="notice notice-success">Saved locally. <a href="../articles/<?= htmlspecialchars($file) ?>" target="_blank">View article →</a></div>
      <div class="notice notice-error">GitHub sync failed: <?= htmlspecialchars($sync_warning) ?></div>
    <?php endif; ?>
    <?php if ($error): ?>
      <div class="notice notice-error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <form method="post" enctype="multipart/form-data" class="article-form">

      <div class="form-section-label">Header</div>

      <div class="form-field">
        <label class="field-label">Title</label>
        <input type="text" name="title" class="field-input field-input-lg" value="<?= htmlspecialchars($title_plain) ?>">
        <span class="field-hint">Main title — don't include the italic part here</span>
      </div>
      <div class="form-field">
        <label class="field-label">Title — italic ending <span class="field-opt">(optional)</span></label>
        <input type="text" name="title_em" class="field-input" value="<?= htmlspecialchars($title_em) ?>">
      </div>
      <div class="form-field">
        <label class="field-label">Deck</label>
        <input type="text" name="deck" class="field-input" value="<?= htmlspecialchars($deck) ?>">
      </div>

      <div class="form-row form-row-3">
        <div class="form-field">
          <label class="field-label">Reading time</label>
          <input type="text" name="reading" class="field-input" value="<?= htmlspecialchars($reading) ?>" placeholder="5 min read">
        </div>
        <div class="form-field">
          <label class="field-label">Month</label>
          <input type="text" name="month" class="field-input" value="<?= htmlspecialchars($month) ?>">
        </div>
        <div class="form-field">
          <label class="field-label">Year</label>
          <input type="text" name="year" class="field-input" value="<?= htmlspecialchars($year) ?>">
        </div>
      </div>

      <div class="form-section-label">Banner Image</div>

      <div class="form-field">
        <label class="field-label">Choose a watercolor</label>
        <div class="wc-grid">
          <?php foreach ($watercolors as $i => $wc): ?>
            <div class="wc-card">
              <input type="radio" name="watercolor" id="wc_<?= $i ?>" value="<?= htmlspecialchars($wc) ?>" <?= $wc === $current_watercolor ? 'checked' : '' ?>>
              <label for="wc_<?= $i ?>" class="wc-thumb">
                <img src="<?= $vistas_url . rawurlencode($wc) ?>" alt="<?= htmlspecialchars($wc) ?>" loading="lazy">
              </label>
              <span class="wc-thumb-name"><?= htmlspecialchars(pathinfo($wc, PATHINFO_FILENAME)) ?></span>
            </div>
          <?php endforeach; ?>
        </div>
      </div>

      <div class="form-field">
        <label class="field-label">Or upload a new watercolor <span class="field-opt">(PNG, JPG — saves for future use)</span></label>
        <div class="wc-upload-box" onclick="document.getElementById('new_watercolor').click()">
          <input type="file" name="new_watercolor" id="new_watercolor" accept="image/png,image/jpeg,image/webp" onchange="document.getElementById('upload-name').textContent = this.files[0]?.name || ''">
          <div class="wc-upload-label">Click to upload image</div>
          <div class="wc-upload-sub" id="upload-name">PNG, JPG or WebP · max 10MB</div>
        </div>
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
