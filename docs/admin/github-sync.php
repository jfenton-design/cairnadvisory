<?php
// Token lives in github-config.php (not tracked by git — create on server manually)
$_gh_config = __DIR__ . '/github-config.php';
if (file_exists($_gh_config)) {
    require_once $_gh_config;
}
if (!defined('GH_TOKEN')) define('GH_TOKEN', '');

define('GH_REPO', 'jfenton-design/cairnadvisory');

function push_to_github($local_file, $github_path, $message = 'CMS: content update') {
    if (!GH_TOKEN) return 'GitHub sync skipped: github-config.php not found or GH_TOKEN not set';
    $url = 'https://api.github.com/repos/' . GH_REPO . '/contents/' . $github_path;
    $headers = [
        'Authorization: token ' . GH_TOKEN,
        'User-Agent: CairnCMS/1.0',
        'Accept: application/vnd.github.v3+json',
        'Content-Type: application/json',
    ];

    // Get current SHA
    $ch = curl_init($url);
    curl_setopt_array($ch, [CURLOPT_RETURNTRANSFER => true, CURLOPT_HTTPHEADER => $headers]);
    $res  = curl_exec($ch);
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($code !== 200) return 'GitHub: could not fetch file (HTTP ' . $code . ')';
    $sha = json_decode($res, true)['sha'] ?? null;
    if (!$sha) return 'GitHub: no SHA returned';

    // Push updated file
    $payload = json_encode([
        'message' => $message,
        'content' => base64_encode(file_get_contents($local_file)),
        'sha'     => $sha,
    ]);
    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_CUSTOMREQUEST  => 'PUT',
        CURLOPT_POSTFIELDS     => $payload,
        CURLOPT_HTTPHEADER     => $headers,
    ]);
    $res  = curl_exec($ch);
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($code === 200 || $code === 201) return null; // success
    $msg = json_decode($res, true)['message'] ?? 'unknown error';
    return 'GitHub sync failed: ' . $msg;
}
