<?php
require_once 'config.php';

// Unset all session keys completely
$_SESSION = array();

// If session cookies are enabled, delete them
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// Terminate global container data execution
session_destroy();

header("Location: ../frontend/login.php");
exit();
?>