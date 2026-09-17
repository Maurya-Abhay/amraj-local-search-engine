<?php
/**
 * Root entry point.
 *
 * All public pages live in /frontend, admin/owner/user dashboards in
 * /frontend/<role>, and shared PHP helpers in /backend. This file simply
 * forwards anyone hitting the web root to the public homepage so that both
 * http://localhost/amraj/ and https://yourdomain.com/ land on the site.
 */

$target = rtrim(dirname($_SERVER['SCRIPT_NAME'] ?? '/'), '/\\') . '/frontend/index.php';

if (!headers_sent()) {
    header('Location: ' . $target, true, 302);
    exit;
}

// Fallback if headers were already sent for some reason.
echo '<!DOCTYPE html><html lang="en"><head><meta charset="UTF-8">'
    . '<meta http-equiv="refresh" content="0;url=' . htmlspecialchars($target, ENT_QUOTES, 'UTF-8') . '">'
    . '<title>AMRAJ</title></head><body>'
    . '<p>Continue to <a href="' . htmlspecialchars($target, ENT_QUOTES, 'UTF-8') . '">AMRAJ</a>.</p>'
    . '</body></html>';