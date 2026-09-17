<?php
require_once 'config.php';

if (isset($_GET['toggle_bookmark']) && isset($_SESSION['user_id'])) {
    $user_id = (int) $_SESSION['user_id'];
    $shop_id = isset($_GET['shop_id']) ? intval($_GET['shop_id']) : 0;

    $check = $conn->prepare('SELECT id FROM bookmarks WHERE user_id = ? AND shop_id = ? LIMIT 1');
    $check->bind_param('ii', $user_id, $shop_id);
    $check->execute();
    $res = $check->get_result();
    if ($res && $res->num_rows > 0) {
        $del = $conn->prepare('DELETE FROM bookmarks WHERE user_id = ? AND shop_id = ?');
        $del->bind_param('ii', $user_id, $shop_id);
        $del->execute();
        echo json_encode(['status' => 'removed']);
    } else {
        $ins = $conn->prepare('INSERT INTO bookmarks (user_id, shop_id) VALUES (?, ?)');
        $ins->bind_param('ii', $user_id, $shop_id);
        $ins->execute();
        echo json_encode(['status' => 'bookmarked']);
    }
    exit();
}
?>