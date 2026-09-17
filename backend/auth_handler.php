<?php
if (session_status() == PHP_SESSION_NONE) { session_start(); }
require_once 'config.php';

// --- REGISTER PROCESS ---
if (isset($_POST['register_user'])) {
    if (!amraj_validate_csrf($_POST['csrf_token'] ?? null)) {
        $_SESSION['error'] = 'Invalid form submission.';
        header('Location: ../frontend/register.php'); exit();
    }
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $password = $_POST['password'] ?? '';
    $role = in_array($_POST['role'] ?? 'user', ['user','owner'], true) ? $_POST['role'] : 'user';

    // Check duplicate using prepared statement
    $stmt = $conn->prepare('SELECT id FROM users WHERE email = ? OR phone = ? LIMIT 1');
    $stmt->bind_param('ss', $email, $phone);
    $stmt->execute();
    $res = $stmt->get_result();
    if ($res && $res->num_rows > 0) {
        $_SESSION['error'] = 'Email or Phone number already registered!';
        header('Location: ../frontend/register.php'); exit();
    }
    $stmt->close();

    $hashed_password = password_hash($password, PASSWORD_BCRYPT);
    $insert = $conn->prepare('INSERT INTO users (name, email, phone, password, role) VALUES (?, ?, ?, ?, ?)');
    $insert->bind_param('sssss', $name, $email, $phone, $hashed_password, $role);
    if ($insert->execute()) {
        $_SESSION['success'] = 'Registration successful! Please login.';
        header('Location: ../frontend/login.php');
    } else {
        $_SESSION['error'] = 'Something went wrong. Try again.';
        header('Location: ../frontend/register.php');
    }
    $insert->close();
    exit();
}

// --- LOGIN PROCESS ---
if (isset($_POST['login_user'])) {
    if (!amraj_validate_csrf($_POST['csrf_token'] ?? null)) {
        $_SESSION['error'] = 'Invalid form submission.';
        header('Location: ../frontend/login.php'); exit();
    }
    $identity = trim($_POST['identity'] ?? '');
    $password = $_POST['password'] ?? '';

    $stmt = $conn->prepare('SELECT id, name, password, role, is_blocked FROM users WHERE email = ? OR phone = ? LIMIT 1');
    $stmt->bind_param('ss', $identity, $identity);
    $stmt->execute();
    $res = $stmt->get_result();
    if ($res && $res->num_rows === 1) {
        $user = $res->fetch_assoc();
        if (password_verify($password, $user['password'])) {
            if (!empty($user['is_blocked'])) {
                $_SESSION['error'] = 'Your account is blocked. Contact admin.';
                header('Location: ../frontend/login.php'); exit();
            }
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['name'];
            $_SESSION['user_role'] = $user['role'];
            
            // Set SweetAlert trigger flag for gorgeous popup transition
            $_SESSION['login_success_alert'] = true;

            // Proper Role-based Routing Fix
            if ($user['role'] == 'admin') {
                header('Location: ../frontend/admin/dashboard.php');
            } elseif ($user['role'] == 'owner') {
                header('Location: ../frontend/owner/dashboard.php');
            } else {
                // Fix: Redirect normal users to their user dashboard instead of marketplace index!
                header('Location: ../frontend/user/dashboard.php');
            }
            exit();
        }
    }
    $_SESSION['error'] = 'Invalid Email/Phone or Password!';
    header('Location: ../frontend/login.php');
    exit();
}
?>