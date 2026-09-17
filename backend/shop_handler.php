<?php
if (session_status() == PHP_SESSION_NONE) { session_start(); }
require_once 'config.php';

if(!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'owner') {
    header("Location: ../frontend/login.php");
    exit();
}

if(isset($_POST['add_shop'])) {
    if (!amraj_validate_csrf($_POST['csrf_token'] ?? null)) {
        $_SESSION['error'] = 'Invalid form submission.';
        header('Location: ../frontend/owner/add-shop.php'); exit();
    }
    $owner_id    = $_SESSION['user_id'];
    $title       = trim((string)($_POST['title'] ?? ''));
    $category_id = intval($_POST['category_id']);
    $phone       = trim((string)($_POST['phone'] ?? ''));
    $whatsapp    = trim((string)($_POST['whatsapp'] ?? ''));
    $city        = trim((string)($_POST['city'] ?? ''));
    $pincode     = trim((string)($_POST['pincode'] ?? ''));
    $address     = trim((string)($_POST['address'] ?? ''));
    $timing      = trim((string)($_POST['timing'] ?? ''));
    $description = trim((string)($_POST['description'] ?? ''));
    
    $image_url = '';
    $upload_dir = dirname(__DIR__) . '/uploads/';

    $image_error = false;
    if (isset($_FILES['shop_image']) && amraj_is_valid_upload($_FILES['shop_image'])) {
        $uploaded_name = amraj_local_upload_image($_FILES['shop_image'], $upload_dir);
        if ($uploaded_name !== false) {
            $image_url = 'uploads/' . $uploaded_name;
        } else {
            // local processing failed (likely >500KB after compression or invalid svg)
            // attempt remote upload as a fallback
            if (isset($_FILES['shop_image']) && $_FILES['shop_image']['error'] === UPLOAD_ERR_OK) {
                $uploaded_name = amraj_get_unique_filename($_FILES['shop_image']['name'], 'shop_');
                $remote_url = uploadToImageKit($_FILES['shop_image']['tmp_name'], $uploaded_name);
                if ($remote_url) {
                    $image_url = $remote_url;
                } else {
                    $image_error = true;
                }
            } else {
                $image_error = true;
            }
        }
    }

    if (empty($image_url) && $image_error) {
        $_SESSION['error'] = 'Image must be a valid image and compressible to 500KB or less.';
        header("Location: ../frontend/owner/add-shop.php");
        exit();
    }

    // Insert Data State Execution
    $query = "INSERT INTO businesses (owner_id, category_id, title, description, address, city, pincode, phone, whatsapp, timing, image_url, status) 
              VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending')";
              
    $stmt = $conn->prepare($query);
    $stmt->bind_param("iisssssssss", $owner_id, $category_id, $title, $description, $address, $city, $pincode, $phone, $whatsapp, $timing, $image_url);
    
    if($stmt->execute()) {
        header("Location: ../frontend/owner/dashboard.php?listing=submitted");
    } else {
        echo "Database Write Fault Error: " . $conn->error;
    }
    $stmt->close();
} else {
    header("Location: ../frontend/owner/add-shop.php");
    exit();
}
?>