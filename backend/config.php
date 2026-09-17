<?php
require_once __DIR__ . '/env.php';
amraj_load_env(__DIR__ . '/../.env');

// Initialize Secure Session
if (session_status() === PHP_SESSION_NONE) {
    $secureCookie = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off');
    $cookieParams = session_get_cookie_params();
    session_set_cookie_params([
        'lifetime' => $cookieParams['lifetime'],
        'path' => $cookieParams['path'],
        'domain' => $cookieParams['domain'],
        'secure' => $secureCookie,
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
    session_start();
} elseif (session_status() === PHP_SESSION_ACTIVE) {
    // If a session is already active before config is included, do not reconfigure cookie params.
}

if (!headers_sent()) {
    header('X-Frame-Options: SAMEORIGIN');
    header('X-Content-Type-Options: nosniff');
    header('Referrer-Policy: strict-origin-when-cross-origin');
    header('Permissions-Policy: geolocation=(), microphone=()');
}

@ini_set('post_max_size', '210M');
@ini_set('upload_max_filesize', '200M');
@ini_set('memory_limit', '256M');
@ini_set('display_errors', '0');
@ini_set('log_errors', '1');
@ini_set('error_log', __DIR__ . '/../logs/error.log');

define('AMRAJ_LOG_DIR', __DIR__ . '/../logs');
define('AMRAJ_ERROR_LOG', AMRAJ_LOG_DIR . '/app.log');

if (!is_dir(AMRAJ_LOG_DIR)) {
    @mkdir(AMRAJ_LOG_DIR, 0755, true);
}

function amraj_log_error(string $message, array $context = []): void {
    $log_entry = [
        'time' => date('c'),
        'message' => $message,
        'context' => $context,
        'ip' => $_SERVER['REMOTE_ADDR'] ?? null,
        'user_id' => $_SESSION['user_id'] ?? null,
    ];
    $line = json_encode($log_entry, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    if ($line === false) {
        $line = $message;
    }
    @error_log($line . PHP_EOL, 3, AMRAJ_ERROR_LOG);
}

function amraj_handle_db_error(mysqli $conn, string $operation, array $context = []): void {
    amraj_log_error($operation . ' failed: ' . $conn->error, array_merge($context, ['errno' => $conn->errno]));
}

function amraj_bind_params(mysqli_stmt $stmt, string $types, array $params): bool {
    $bind_params = array_merge([$types], $params);
    $refs = [];
    foreach ($bind_params as $key => $value) {
        $refs[$key] = &$bind_params[$key];
    }
    return call_user_func_array([$stmt, 'bind_param'], $refs);
}

function amraj_prepare_execute(mysqli $conn, string $sql, string $types = '', array $params = []): mysqli_stmt|false {
    $stmt = $conn->prepare($sql);
    if ($stmt === false) {
        amraj_handle_db_error($conn, 'Prepare failed', ['query' => $sql]);
        return false;
    }
    if ($types !== '' && !empty($params)) {
        if (!amraj_bind_params($stmt, $types, $params)) {
            amraj_handle_db_error($conn, 'Bind parameters failed', ['query' => $sql, 'types' => $types]);
            return false;
        }
    }
    if (!$stmt->execute()) {
        amraj_handle_db_error($conn, 'Statement execute failed', ['query' => $sql, 'types' => $types, 'params' => $params]);
        return false;
    }
    return $stmt;
}

function amraj_query(mysqli $conn, string $sql): mysqli_result|bool {
    $result = $conn->query($sql);
    if ($result === false) {
        amraj_handle_db_error($conn, 'Query execution failed', ['query' => $sql]);
        return false;
    }
    return $result;
}

// Database Configuration (from .env — never hardcode real credentials here)
define('DB_HOST', amraj_env('DB_HOST', 'localhost'));
define('DB_USER', amraj_env('DB_USER', 'root'));
define('DB_PASS', amraj_env('DB_PASS', ''));
define('DB_NAME', amraj_env('DB_NAME', 'amraj'));

define('UPLOAD_MAX_BYTES', 200 * 1024 * 1024);
define('UPLOAD_ALLOWED_MIME_TYPES', [
    'image/jpeg',
    'image/jpg',
    'image/png',
    'image/webp',
    'image/svg+xml'
]);
define('UPLOAD_ALLOWED_EXTENSIONS', ['jpg', 'jpeg', 'png', 'webp', 'svg']);

// Establish Connection
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

if ($conn->connect_error) {
    amraj_log_error('Database connection failed', ['error' => $conn->connect_error, 'errno' => $conn->connect_errno]);
    http_response_code(503);
    exit('Database unavailable.');
}
$conn->set_charset("utf8mb4");

function amraj_get_unique_filename(string $original_name, string $prefix = 'img_'): string {
    $clean = preg_replace('/[^a-zA-Z0-9._-]+/', '_', basename($original_name));
    $extension = strtolower(pathinfo($clean, PATHINFO_EXTENSION));
    if (!in_array($extension, UPLOAD_ALLOWED_EXTENSIONS, true)) {
        $extension = 'jpg';
    }
    return sprintf('%s%s_%s.%s', $prefix, time(), bin2hex(random_bytes(4)), $extension);
}

function amraj_is_valid_upload(array $file): bool {
    if (!isset($file['error']) || $file['error'] !== UPLOAD_ERR_OK) {
        return false;
    }
    if (!isset($file['size']) || $file['size'] <= 0 || $file['size'] > UPLOAD_MAX_BYTES) {
        return false;
    }
    if (!is_uploaded_file($file['tmp_name'])) {
        return false;
    }
    $mime = mime_content_type($file['tmp_name']);
    if (!in_array($mime, UPLOAD_ALLOWED_MIME_TYPES, true)) {
        return false;
    }
    $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    return in_array($extension, UPLOAD_ALLOWED_EXTENSIONS, true);
}

function amraj_local_upload_image(array $file, string $upload_dir): string|false {
    if (!amraj_is_valid_upload($file)) {
        return false;
    }
    if (!is_dir($upload_dir) && !mkdir($upload_dir, 0755, true) && !is_dir($upload_dir)) {
        return false;
    }
    $filename = amraj_get_unique_filename($file['name'], 'shop_');
    $target_file = rtrim($upload_dir, '/\\') . DIRECTORY_SEPARATOR . $filename;
        // Move uploaded file to temp location
        if (!move_uploaded_file($file['tmp_name'], $target_file)) {
            return false;
        }

        // Attempt to reduce file size to <= 500KB using GD and simple SVG sanitization
            $max_bytes = 500 * 1024;
            $mime = mime_content_type($target_file) ?: '';
            try {
                // Handle SVG safely: reject if it contains scripts or javascript: references
                if ($mime === 'image/svg+xml') {
                    $contents = file_get_contents($target_file);
                    if ($contents === false) { @unlink($target_file); return false; }
                    if (preg_match('/<\/?script|onload\s*=|javascript:/i', $contents)) {
                        @unlink($target_file);
                        return false;
                    }
                    clearstatcache(true, $target_file);
                    if (filesize($target_file) <= $max_bytes) {
                        return $filename;
                    }
                    // SVG too large — reject to enforce policy
                    @unlink($target_file);
                    return false;
                }

                if (in_array($mime, ['image/jpeg','image/jpg','image/png','image/webp'], true)) {
                    $img = null;
                    if ($mime === 'image/jpeg' || $mime === 'image/jpg') $img = imagecreatefromjpeg($target_file);
                    if ($mime === 'image/png') $img = imagecreatefrompng($target_file);
                    if ($mime === 'image/webp') $img = imagecreatefromwebp($target_file);

                    if ($img) {
                        // Resize if large dimensions
                        $w = imagesx($img);
                        $h = imagesy($img);
                        $max_w = 1200;
                        if ($w > $max_w) {
                            $ratio = $h / $w;
                            $new_w = $max_w;
                            $new_h = (int)($new_w * $ratio);
                            $tmp = imagecreatetruecolor($new_w, $new_h);
                            imagecopyresampled($tmp, $img, 0,0,0,0, $new_w, $new_h, $w, $h);
                            imagedestroy($img);
                            $img = $tmp;
                        }

                        // Try quality loop to meet size
                        $quality = 90;
                        $saved = false;
                        while ($quality >= 30) {
                            if ($mime === 'image/png') {
                                // PNG quality is compression level 0-9
                                $level = (int)round((100 - $quality) / 10);
                                imagepng($img, $target_file, $level);
                            } else {
                                imagejpeg($img, $target_file, $quality);
                            }
                            clearstatcache(true, $target_file);
                            if (filesize($target_file) <= $max_bytes) { $saved = true; break; }
                            $quality -= 10;
                        }
                        imagedestroy($img);
                        if ($saved) return $filename;
                        // If we couldn't get under limit, remove file and fail
                        @unlink($target_file);
                        return false;
                    }
                }
            } catch (Throwable $e) {
                // On any processing error, remove file and fail safe
                @unlink($target_file);
                return false;
            }

            // If we reach here, unknown type or processing failed — remove and reject
            @unlink($target_file);
            return false;
}

    // CSRF helpers
    if (!function_exists('amraj_get_csrf_token')) {
        function amraj_get_csrf_token(): string {
            if (empty($_SESSION['amraj_csrf_token'])) {
                $_SESSION['amraj_csrf_token'] = bin2hex(random_bytes(32));
            }
            return $_SESSION['amraj_csrf_token'];
        }
    }

    if (!function_exists('amraj_csrf_input')) {
        function amraj_csrf_input(): string {
            $t = amraj_get_csrf_token();
            return '<input type="hidden" name="csrf_token" value="' . htmlspecialchars($t, ENT_QUOTES, 'UTF-8') . '">';
        }
    }

    if (!function_exists('amraj_validate_csrf')) {
        function amraj_validate_csrf(?string $token): bool {
            if (empty($_SESSION['amraj_csrf_token'])) return false;
            return hash_equals($_SESSION['amraj_csrf_token'], (string)$token);
        }
    }

function amraj_ensure_admin_schema(mysqli $conn) {
    amraj_query($conn, "CREATE TABLE IF NOT EXISTS admin_audit_logs (
        id INT AUTO_INCREMENT PRIMARY KEY,
        admin_id INT NOT NULL,
        action VARCHAR(80) NOT NULL,
        entity_type VARCHAR(40) NOT NULL,
        entity_id INT DEFAULT NULL,
        summary VARCHAR(255) NOT NULL,
        meta_json TEXT DEFAULT NULL,
        ip_address VARCHAR(45) DEFAULT NULL,
        user_agent VARCHAR(255) DEFAULT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        INDEX idx_admin_id (admin_id),
        INDEX idx_entity (entity_type, entity_id),
        INDEX idx_created_at (created_at)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

    $blocked_column = amraj_query($conn, "SHOW COLUMNS FROM users LIKE 'is_blocked'");
    if ($blocked_column && $blocked_column->num_rows === 0) {
        amraj_query($conn, "ALTER TABLE users ADD COLUMN is_blocked TINYINT(1) NOT NULL DEFAULT 0 AFTER role");
    }
}

function amraj_log_admin_action(mysqli $conn, $admin_id, $action, $entity_type, $entity_id, $summary, array $meta = []) {
    $meta_json = !empty($meta) ? json_encode($meta) : null;
    $ip_address = $_SERVER['REMOTE_ADDR'] ?? null;
    $user_agent = substr($_SERVER['HTTP_USER_AGENT'] ?? '', 0, 255);

    $stmt = $conn->prepare("INSERT INTO admin_audit_logs (admin_id, action, entity_type, entity_id, summary, meta_json, ip_address, user_agent) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ississss", $admin_id, $action, $entity_type, $entity_id, $summary, $meta_json, $ip_address, $user_agent);
    $stmt->execute();
    $stmt->close();
}

function amraj_ensure_stats_schema(mysqli $conn) {
    amraj_query($conn, "CREATE TABLE IF NOT EXISTS site_stats (
        stat_key VARCHAR(40) PRIMARY KEY,
        stat_value BIGINT UNSIGNED NOT NULL DEFAULT 0,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

    amraj_query($conn, "CREATE TABLE IF NOT EXISTS site_visits (
        id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        visitor_hash CHAR(64) NOT NULL,
        visit_date DATE NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        UNIQUE KEY uniq_visitor_day (visitor_hash, visit_date),
        INDEX idx_visit_date (visit_date)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
}

/**
 * Records at most one visit per visitor per day (hashed IP + user agent, never
 * stores raw IP) and returns the running totals used for the homepage counter.
 */
function amraj_track_visit(mysqli $conn): array {
    amraj_ensure_stats_schema($conn);

    $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    $ua = $_SERVER['HTTP_USER_AGENT'] ?? '';
    $visitor_hash = hash('sha256', $ip . '|' . $ua);
    $today = date('Y-m-d');

    $stmt = amraj_prepare_execute(
        $conn,
        "INSERT IGNORE INTO site_visits (visitor_hash, visit_date) VALUES (?, ?)",
        'ss',
        [$visitor_hash, $today]
    );
    if ($stmt) {
        $stmt->close();
    }

    $total_row = amraj_query($conn, "SELECT COUNT(*) AS total FROM site_visits");
    $today_row = amraj_query($conn, "SELECT COUNT(*) AS today FROM site_visits WHERE visit_date = CURDATE()");

    return [
        'total_visits' => $total_row ? (int)($total_row->fetch_assoc()['total'] ?? 0) : 0,
        'today_visits' => $today_row ? (int)($today_row->fetch_assoc()['today'] ?? 0) : 0,
    ];
}

/** Real, live platform counters — no hardcoded placeholder numbers. */
function amraj_get_site_counts(mysqli $conn): array {
    $counts = [
        'businesses' => 0,
        'categories' => 0,
        'users' => 0,
        'reviews' => 0,
    ];

    $map = [
        'businesses' => "SELECT COUNT(*) AS c FROM businesses WHERE status = 'approved'",
        'categories' => "SELECT COUNT(*) AS c FROM categories",
        'users' => "SELECT COUNT(*) AS c FROM users WHERE is_blocked = 0",
        'reviews' => "SELECT COUNT(*) AS c FROM reviews",
    ];

    foreach ($map as $key => $sql) {
        $res = amraj_query($conn, $sql);
        if ($res) {
            $counts[$key] = (int)($res->fetch_assoc()['c'] ?? 0);
        }
    }

    return $counts;
}

amraj_ensure_admin_schema($conn);

if (isset($_SESSION['user_id'])) {
    $current_script = basename($_SERVER['SCRIPT_NAME'] ?? '');
    if ($current_script !== 'login.php') {
        $blocked_stmt = $conn->prepare("SELECT is_blocked FROM users WHERE id = ? LIMIT 1");
        $blocked_stmt->bind_param("i", $_SESSION['user_id']);
        $blocked_stmt->execute();
        $blocked_user = $blocked_stmt->get_result()->fetch_assoc();
        $blocked_stmt->close();

        if ($blocked_user && (int) $blocked_user['is_blocked'] === 1) {
            session_unset();
            session_destroy();
            header("Location: /AMRAJ/frontend/login.php?msg=blocked");
            exit();
        }
    }
}

// ImageKit API Settings (from .env — the previous hardcoded key was exposed in
// source control and must be rotated in the ImageKit dashboard before go-live)
define('IMAGEKIT_PUBLIC_KEY', amraj_env('IMAGEKIT_PUBLIC_KEY', ''));
define('IMAGEKIT_PRIVATE_KEY', amraj_env('IMAGEKIT_PRIVATE_KEY', ''));
define('IMAGEKIT_URL_ENDPOINT', amraj_env('IMAGEKIT_URL_ENDPOINT', ''));

// Helper function to upload images directly to ImageKit using cURL API
function uploadToImageKit($file_tmp_path, $file_name) {
    $url = "https://upload.imagekit.io/api/v1/files/upload";
    $mime = mime_content_type($file_tmp_path) ?: 'application/octet-stream';
    $cfile = new CURLFile($file_tmp_path, $mime, $file_name);
    
    $data = [
        'file' => $cfile,
        'fileName' => $file_name,
        'useUniqueFileName' => 'true'
    ];

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_USERPWD, IMAGEKIT_PRIVATE_KEY . ":");
    curl_setopt($ch, CURLOPT_TIMEOUT, 60);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 15);
    
    $response = curl_exec($ch);
    curl_close($ch);
    
    $result = json_decode($response, true);
    return isset($result['url']) ? $result['url'] : false;
}
?>