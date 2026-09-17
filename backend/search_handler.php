<?php
require_once 'config.php';

function getFilteredShops($keyword = '', $location = '', $category_slug = '', $sort_by = 'relevance', $radius = 25, $verified = 0) {
    global $conn;

    $sql = "SELECT b.*, c.name as category_name, c.slug as category_slug FROM businesses b
            JOIN categories c ON b.category_id = c.id
            WHERE b.status = 'approved'";
    $types = '';
    $params = [];

    if (!empty($keyword)) {
        // Match business name, description AND category name,
        // so "doctors" finds Doctors & Hospitals listings.
        $sql .= " AND (b.title LIKE ? OR b.description LIKE ? OR c.name LIKE ? OR b.city LIKE ?)";
        $types .= 'ssss';
        $like = '%' . $keyword . '%';
        $params[] = $like;
        $params[] = $like;
        $params[] = $like;
        $params[] = $like;
    }

    if (!empty($location)) {
        $sql .= " AND (b.city LIKE ? OR b.pincode LIKE ? OR b.address LIKE ? )";
        $types .= 'sss';
        $params[] = '%' . $location . '%';
        $params[] = '%' . $location . '%';
        $params[] = '%' . $location . '%';
    }

    if (!empty($category_slug)) {
        $sql .= " AND c.slug = ?";
        $types .= 's';
        $params[] = $category_slug;
    }

    if (!empty($verified)) {
        $sql .= " AND b.is_premium = 1";
    }

    // Accept the sort values sent by the search page.
    $sort_map = [
        'relevance'     => " ORDER BY b.is_premium DESC, b.created_at DESC",
        'premium_first' => " ORDER BY b.is_premium DESC, b.created_at DESC",
        'newest'        => " ORDER BY b.created_at DESC",
        'oldest'        => " ORDER BY b.created_at ASC",
        'name'          => " ORDER BY b.title ASC",
        'alphabetical'  => " ORDER BY b.title ASC",
        'nearest'       => " ORDER BY b.is_premium DESC, b.created_at DESC",
    ];
    $sql .= $sort_map[$sort_by] ?? $sort_map['relevance'];

    $stmt = amraj_prepare_execute($conn, $sql, $types, $params);
    if (!$stmt) {
        return false;
    }

    return $stmt->get_result();
}
?>