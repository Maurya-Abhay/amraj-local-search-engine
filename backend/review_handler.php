<?php
require_once 'config.php';

if (isset($_POST['submit_review']) && isset($_SESSION['user_id'])) {
    if (!amraj_validate_csrf($_POST['csrf_token'] ?? null)) {
        $_SESSION['error'] = 'Invalid form submission.';
        header('Location: ../frontend/shop-details.php?id=' . intval($_POST['shop_id'] ?? 0)); exit();
    }
    $user_id = (int) $_SESSION['user_id'];
    $shop_id = intval($_POST['shop_id'] ?? 0);
    $rating = max(1, min(5, intval($_POST['rating'] ?? 1)));
    $comment = trim($_POST['comment'] ?? '');

    // One review per user per shop: insert new, or update existing rating.
    $ins = $conn->prepare('INSERT INTO reviews (shop_id, user_id, rating, comment) VALUES (?, ?, ?, ?) ON DUPLICATE KEY UPDATE rating = VALUES(rating), comment = VALUES(comment)');
    if ($ins === false) {
        $_SESSION['error'] = 'Unable to process review submission.';
        header('Location: ../frontend/shop-details.php?id=' . $shop_id);
        exit();
    }
    $ins->bind_param('iiis', $shop_id, $user_id, $rating, $comment);
    $ok = $ins->execute();
    $ins->close();


    if (isset($ok) && $ok) {
        $_SESSION['success'] = 'Thank you for your rating!';
    } else {
        $_SESSION['error'] = 'Unable to process review submission.';
    }
    header('Location: ../frontend/shop-details.php?id=' . $shop_id);
    exit();
}
?>