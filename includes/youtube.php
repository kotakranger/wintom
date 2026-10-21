<?php
// Wintom Curtain — YouTube URL helpers

/**
 * Extract the video id from a YouTube watch / youtu.be / shorts / embed / live URL
 * (or a bare 11-character id). Returns ['id' => string, 'is_short' => bool] or null.
 */
function youtube_parse(string $input): ?array
{
    $input = trim($input);
    if ($input === '') {
        return null;
    }
    if (preg_match('/^[A-Za-z0-9_-]{11}$/', $input)) {
        return ['id' => $input, 'is_short' => false];
    }
    if (!preg_match('#^https?://#i', $input)) {
        $input = 'https://' . $input;
    }
    $parts = parse_url($input);
    $host  = strtolower($parts['host'] ?? '');
    $host  = preg_replace('/^(www\.|m\.|music\.)/', '', $host);
    $path  = $parts['path'] ?? '';
    $id    = null;
    $short = false;

    if ($host === 'youtu.be') {
        $id = trim($path, '/');
    } elseif ($host === 'youtube.com' || $host === 'youtube-nocookie.com') {
        if (preg_match('#^/(shorts|embed|live|v)/([A-Za-z0-9_-]{11})#', $path, $m)) {
            $id    = $m[2];
            $short = $m[1] === 'shorts';
        } elseif ($path === '/watch') {
            parse_str($parts['query'] ?? '', $q);
            $id = $q['v'] ?? null;
        }
    }

    if (is_string($id) && preg_match('/^[A-Za-z0-9_-]{11}$/', $id)) {
        return ['id' => $id, 'is_short' => $short];
    }
    return null;
}
