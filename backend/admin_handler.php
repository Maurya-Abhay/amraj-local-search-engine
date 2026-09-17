<?php
if (session_status() == PHP_SESSION_NONE) { session_start(); }
require_once 'config.php';

// Verification Guard
if(!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header("Location: ../frontend/login.php");
    exit();
}

if(isset($_GET['action']) && isset($_GET['id'])) {
    $action = $_GET['action'];
    $shop_id = intval($_GET['id']);
    $manage_users_redirect = '../frontend/admin/manage-users.php';
    $manage_shops_redirect = '../frontend/admin/manage-shops.php';
    
    if ($action === 'approve' || $action === 'reject') {
        $status = $action === 'approve' ? 'approved' : 'rejected';
        $stmt = $conn->prepare("UPDATE businesses SET status = ? WHERE id = ?");
        $stmt->bind_param("si", $status, $shop_id);

        if ($stmt->execute()) {
            if (function_exists('amraj_log_admin_action')) {
                amraj_log_admin_action($conn, (int) $_SESSION['user_id'], 'update_shop_status', 'business', $shop_id, ucfirst($action) . ' business listing', ['status' => $status]);
            }
            header("Location: {$manage_shops_redirect}?msg=success");
        } else {
            echo "Execution Failure: " . $conn->error;
        }
        $stmt->close();
        exit();
    }

    if (in_array($action, ['delete_user', 'block_user', 'unblock_user'], true)) {
        $back_role = isset($_GET['role']) ? $_GET['role'] : 'all';
        $back_page = isset($_GET['page']) ? (int) $_GET['page'] : 1;
        $back_query = isset($_GET['q']) ? trim($_GET['q']) : '';

        if ($shop_id === (int) $_SESSION['user_id']) {
            header("Location: {$manage_users_redirect}?msg=error");
            exit();
        }

        if ($action === 'delete_user') {
            $lookup = $conn->prepare("SELECT name, role FROM users WHERE id = ? LIMIT 1");
            $lookup->bind_param("i", $shop_id);
            $lookup->execute();
            $target_user = $lookup->get_result()->fetch_assoc();
            $lookup->close();

            if (!$target_user) {
                header("Location: {$manage_users_redirect}?msg=error");
                exit();
            }

            $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
            $stmt->bind_param("i", $shop_id);
            $ok = $stmt->execute();
            $stmt->close();

            if ($ok && function_exists('amraj_log_admin_action')) {
                amraj_log_admin_action($conn, (int) $_SESSION['user_id'], 'delete_user', 'user', $shop_id, 'Deleted user account', ['name' => $target_user['name'], 'role' => $target_user['role']]);
            }

            $redirect_url = $manage_users_redirect . '?msg=' . ($ok ? 'deleted' : 'error');
        } else {
            $lookup = $conn->prepare("SELECT name, role, is_blocked FROM users WHERE id = ? LIMIT 1");
            $lookup->bind_param("i", $shop_id);
            $lookup->execute();
            $target_user = $lookup->get_result()->fetch_assoc();
            $lookup->close();

            if (!$target_user) {
                header("Location: {$manage_users_redirect}?msg=error");
                exit();
            }

            $new_state = $action === 'block_user' ? 1 : 0;
            $stmt = $conn->prepare("UPDATE users SET is_blocked = ? WHERE id = ?");
            $stmt->bind_param("ii", $new_state, $shop_id);
            $ok = $stmt->execute();
            $stmt->close();

            if ($ok && function_exists('amraj_log_admin_action')) {
                amraj_log_admin_action($conn, (int) $_SESSION['user_id'], $action, 'user', $shop_id, $new_state ? 'Blocked user account' : 'Unblocked user account', ['name' => $target_user['name'], 'role' => $target_user['role']]);
            }

            $redirect_url = $manage_users_redirect . '?msg=' . ($ok ? ($new_state ? 'blocked' : 'unblocked') : 'error');
        }

        $redirect_url .= '&role=' . urlencode($back_role) . '&page=' . $back_page;
        if ($back_query !== '') {
            $redirect_url .= '&q=' . urlencode($back_query);
        }
        header("Location: {$redirect_url}");
        exit();
    }

    header("Location: {$manage_shops_redirect}");
    exit();
} else {
    header("Location: ../frontend/admin/manage-shops.php");
    exit();
}
?>