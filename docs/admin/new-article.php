<?php
session_start();
if (empty($_SESSION['cairn_admin_auth'])) { header('Location: index.php'); exit; }

$saved = false;
$error = '';
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
    $num        = preg_replace('/[^0-9]/', '', $_POST['num'] ?? '');
    $slug       = preg_replace('/[^a-z0-9\-]/', '', strtolower(str_replace(' ', '-', trim($_POST['slug'] ?? ''))));
    $title      = trim($_POST['title'] ?? '');
    $title_em   = trim($_POST['title_em'] ?? '');
    $deck       = trim($_POST['deck'] ?? '');
    $reading    = trim($_POST['reading'] ?? '');
    $month      = trim($_POST['month'] ?? '');
    $year       = trim($_POST['year'] ?? date('Y'));
    $watercolor = trim($_POST['watercolor'] ?? ($watercolors[0] ?? 'shoreline.png'));
    $body       = trim($_POST['body'] ?? '');
    $tone       = $_POST['tone'] ?? '';

    if (!$num || !$slug || !$title || !$body) {
        $error = 'Number, slug, title, and body are required.';
    } else {
        $filename = sprintf('%02d-%s.html', (int)$num, $slug);
        $filepath = dirname(__DIR__) . '/articles/' . $filename;

        if (file_exists($filepath)) {
            $error = 'An article with that slug already exists. Choose a different slug.';
        } else {
            $title_html = $title_em
                ? htmlspecialchars($title) . ' <em>' . htmlspecialchars($title_em) . '</em>'
                : htmlspecialchars($title);
            $num_padded = sprintf('%02d', (int)$num);
            $page_title = strip_tags($title . ($title_em ? ' ' . $title_em : ''));

            $sections = preg_split('/\n{2,}/', $body);
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

            $html = <<<HTML
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>{$page_title} — Cairn Advisory</title>
<link rel="stylesheet" href="../tokens.css">
<link rel="stylesheet" href="../nav.css?v=4">
<link rel="stylesheet" href="article.css?v=4">
</head>
<body class="cairn">
<div class="article-page">

  <!-- ══ NAV ══ -->
  <nav class="site-nav">
    <a class="nav-logo" href="../index.html" aria-label="Cairn Advisory home">
      <img src="../homepage-assets/cairn-mark-vibrant.png" alt="" class="nav-mark">
      <div class="nav-wordmark">
        <span class="w-cairn" data-wm-cairn="nav">Cairn</span>
        <span class="w-advisory" data-wm-advisory="nav">Advisory</span>
      </div>
    </a>
    <div class="nav-right">
    <ul class="nav-links">
      <li><a href="../index.html#about">About</a></li>
      <li><a href="../index.html#experience">Experience</a></li>
      <li><a href="../index.html#industries">Industries</a></li>
      <li><a href="../index.html#services">Work</a></li>
      <li><a href="index.html" class="active">Notes</a></li>
    </ul>
      <a class="nav-cta" href="../index.html#contact">Get in Touch</a>
    </div>
  </nav>

  <!-- ══ BANNER + HEADER ══ -->
  <div class="article-banner">
    <img src="../assets/vistas/{$watercolor}" alt="" class="banner-img">
    <div class="banner-content">
      <div class="meta-strip">
        <a href="index.html">Field Notes</a>
        <span class="dot"></span>
        <span>{$reading}</span>
      </div>
      <h1 class="article-title">{$title_html}</h1>
      <p class="article-deck">{$deck}</p>
      <div class="byline">
        <span class="name">Jacob Fenton</span>
        <span>·</span>
        <span>{$month} {$year}</span>
      </div>
    </div>
  </div>

  <!-- ══ ARTICLE ══ -->
  <article class="article">
    <div class="prose">
{$body_html}
    </div>
  </article>

  <!-- ══ FOOTER ══ -->
  <footer class="article-footer">
    <div class="site-foot">
      <span>© {$year} Cairn Advisory · Jacob Fenton</span>
      <a href="index.html">All field notes →</a>
    </div>
  </footer>

</div>
  <script src="../nav-wordmark.js"></script>
</body>
</html>
HTML;

            file_put_contents($filepath, $html);
            $saved = $filename;
        }
    }
}

$next_num = 1;
foreach (glob(dirname(__DIR__) . '/articles/[0-9]*.html') as $f) {
    if (preg_match('/^(\d+)-/', basename($f), $m)) {
        $next_num = max($next_num, (int)$m[1] + 1);
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Cairn CMS — New Article</title>
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
      <img src="../homepage-assets/cairn-mark-cutout.png" alt="" class="sb-mark">
      <div>
        <div class="sb-cairn">Cairn</div>
        <div class="sb-advisory">Content Studio</div>
      </div>
    </div>
    <nav class="sidebar-nav">
      <a href="dashboard.php" class="sn-item">Dashboard</a>
      <a href="articles.php" class="sn-item">Field Notes</a>
      <a href="new-article.php" class="sn-item sn-highlight active">+ New Article</a>
      <a href="edit-notes-index.php" class="sn-item">Notes Index Page</a>
      <a href="edit-homepage.php" class="sn-item">Homepage Copy</a>
    </nav>
    <a href="logout.php" class="sb-logout">Sign out</a>
  </aside>

  <main class="main-content">
    <header class="page-header">
      <h1 class="page-title">New Article</h1>
    </header>

    <?php if ($saved): ?>
      <div class="notice notice-success">
        Article saved as <strong><?= htmlspecialchars($saved) ?></strong>.
        <a href="../articles/<?= htmlspecialchars($saved) ?>" target="_blank">View it →</a><br>
        Remember to add it to the <a href="edit-notes-index.php">Notes Index page</a>.
      </div>
    <?php endif; ?>
    <?php if ($error): ?>
      <div class="notice notice-error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <form method="post" enctype="multipart/form-data" class="article-form">

      <div class="form-row form-row-3">
        <div class="form-field">
          <label class="field-label">Article number</label>
          <input type="number" name="num" class="field-input" value="<?= $next_num ?>" min="1" max="99" required>
        </div>
        <div class="form-field">
          <label class="field-label">Slug (URL)</label>
          <input type="text" name="slug" class="field-input" placeholder="why-ai-fails-operators" required>
          <span class="field-hint">lowercase, hyphens only</span>
        </div>
        <div class="form-field">
          <label class="field-label">Tone</label>
          <select name="tone" class="field-input">
            <option value="">Warm (default)</option>
            <option value="cool">Cool (slate)</option>
            <option value="deep">Deep (umber)</option>
          </select>
        </div>
      </div>

      <div class="form-section-label">Header</div>

      <div class="form-field">
        <label class="field-label">Title</label>
        <input type="text" name="title" class="field-input field-input-lg" placeholder="Why most AI projects stall" required>
        <span class="field-hint">Main title — don't include the italic part here</span>
      </div>
      <div class="form-field">
        <label class="field-label">Title — italic ending <span class="field-opt">(optional)</span></label>
        <input type="text" name="title_em" class="field-input" placeholder="before they even start.">
      </div>
      <div class="form-field">
        <label class="field-label">Deck</label>
        <input type="text" name="deck" class="field-input" placeholder="A one-sentence summary of the article's argument.">
      </div>

      <div class="form-row form-row-3">
        <div class="form-field">
          <label class="field-label">Reading time</label>
          <input type="text" name="reading" class="field-input" placeholder="5 min read">
        </div>
        <div class="form-field">
          <label class="field-label">Month</label>
          <input type="text" name="month" class="field-input" value="<?= date('F') ?>">
        </div>
        <div class="form-field">
          <label class="field-label">Year</label>
          <input type="text" name="year" class="field-input" value="<?= date('Y') ?>">
        </div>
      </div>

      <div class="form-section-label">Banner Image</div>

      <div class="form-field">
        <label class="field-label">Choose a watercolor</label>
        <div class="wc-grid">
          <?php foreach ($watercolors as $i => $wc): ?>
            <div class="wc-card">
              <input type="radio" name="watercolor" id="wc_<?= $i ?>" value="<?= htmlspecialchars($wc) ?>" <?= $i === 0 ? 'checked' : '' ?>>
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
        <span class="field-hint">If you upload an image, it will be used as the banner and saved to the vistas folder for future articles.</span>
      </div>

      <div class="form-section-label">Body</div>

      <div class="form-field">
        <label class="field-label">Article body</label>
        <textarea name="body" class="field-input field-textarea" rows="28" placeholder="Write your article here. Separate paragraphs with a blank line.

You can use HTML for special elements:

<h2><span class=&quot;h2-num&quot;>Section label</span>Section heading goes here.</h2>

<ul>
  <li>List item one</li>
  <li>List item two</li>
</ul>

<blockquote class=&quot;pull-quote&quot;>A memorable pull quote goes here.</blockquote>

<aside class=&quot;callout&quot;>
  <span class=&quot;callout-eyebrow&quot;>Callout label</span>
  <h3 class=&quot;callout-title&quot;>Callout heading</h3>
  <ol class=&quot;callout-list&quot;>
    <li><strong>Point one</strong> — explanation here.</li>
  </ol>
</aside>

<p class=&quot;close-line&quot;>Closing statement for the article.</p>"></textarea>
        <span class="field-hint">Blank lines = new paragraph. Paste raw HTML for callouts, pull quotes, and section headings.</span>
      </div>

      <div class="form-actions">
        <button type="submit" class="btn-primary btn-large">Save Article</button>
        <a href="articles.php" class="btn-ghost">Cancel</a>
      </div>
    </form>
  </main>
</body>
</html>
