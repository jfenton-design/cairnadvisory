<?php
session_start();
if (empty($_SESSION['cairn_admin_auth'])) { header('Location: index.php'); exit; }

$saved = false;
$error = '';

$watercolors = [
    'shoreline.png'        => 'Shoreline',
    'river watercolor.png' => 'River',
    'mountain vista.png'   => 'Mountain Vista',
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $num        = preg_replace('/[^0-9]/', '', $_POST['num'] ?? '');
    $slug       = preg_replace('/[^a-z0-9\-]/', '', strtolower(str_replace(' ', '-', trim($_POST['slug'] ?? ''))));
    $title      = trim($_POST['title'] ?? '');
    $title_em   = trim($_POST['title_em'] ?? '');
    $deck       = trim($_POST['deck'] ?? '');
    $eyebrow    = trim($_POST['eyebrow'] ?? '');
    $reading    = trim($_POST['reading'] ?? '');
    $month      = trim($_POST['month'] ?? '');
    $year       = trim($_POST['year'] ?? date('Y'));
    $watercolor = $_POST['watercolor'] ?? 'shoreline.png';
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
            $wc_encoded = rawurlencode($watercolor);
            $tone_class = $tone ? ' ' . htmlspecialchars($tone) : '';
            $title_html = $title_em
                ? htmlspecialchars($title) . ' <em>' . htmlspecialchars($title_em) . '</em>'
                : htmlspecialchars($title);
            $num_padded = sprintf('%02d', (int)$num);
            $page_title = strip_tags($title . ($title_em ? ' ' . $title_em : ''));

            // Build body HTML from textarea — wrap bare paragraphs
            $sections = preg_split('/\n{2,}/', $body);
            $body_html = '';
            foreach ($sections as $s) {
                $s = trim($s);
                if ($s === '') continue;
                // Pass through existing HTML tags unchanged
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
$existing = glob(dirname(__DIR__) . '/articles/[0-9]*.html');
foreach ($existing as $f) {
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
        <a href="../articles/<?= htmlspecialchars($saved) ?>" target="_blank">View it →</a>
        <br>Remember to also add it to the <a href="edit-index.php">Field Notes index</a> and <a href="edit-homepage.php">homepage</a>.
      </div>
    <?php endif; ?>
    <?php if ($error): ?>
      <div class="notice notice-error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <form method="post" class="article-form">

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
        <span class="field-hint">This part renders in italic after the main title</span>
      </div>
      <div class="form-field">
        <label class="field-label">Deck</label>
        <input type="text" name="deck" class="field-input" placeholder="A one-sentence summary of the article's argument.">
      </div>

      <div class="form-row form-row-3">
        <div class="form-field">
          <label class="field-label">Category eyebrow</label>
          <input type="text" name="eyebrow" class="field-input" placeholder="Strategy" value="Strategy">
        </div>
        <div class="form-field">
          <label class="field-label">Reading time</label>
          <input type="text" name="reading" class="field-input" placeholder="5 min read">
        </div>
        <div class="form-field">
          <label class="field-label">Month</label>
          <input type="text" name="month" class="field-input" placeholder="April" value="<?= date('F') ?>">
        </div>
      </div>

      <div class="form-field">
        <label class="field-label">Banner image</label>
        <div class="watercolor-picker">
          <?php foreach ($watercolors as $file => $label): ?>
            <label class="wc-option">
              <input type="radio" name="watercolor" value="<?= htmlspecialchars($file) ?>" <?= $file === 'shoreline.png' ? 'checked' : '' ?>>
              <span class="wc-label"><?= htmlspecialchars($label) ?></span>
            </label>
          <?php endforeach; ?>
        </div>
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
