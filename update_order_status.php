<?php
require_once 'config.php';

// Check if seller is logged in
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'seller') {
    header('Location: signin.php');
    exit;
}

$seller_id = $_SESSION['user_id'];

// Check if form was submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['order_id'], $_POST['status'])) {
    $order_id = (int)$_POST['order_id'];
    $new_status = $_POST['status'];

    // Validate status
    $allowed_statuses = ['pending', 'paid', 'shipped', 'delivered', 'canceled'];
    if (!in_array($new_status, $allowed_statuses)) {
        // Invalid status – redirect with error
        header('Location: sellerDashboard.php?error=invalid_status');
        exit;
    }

    // Verify that this order contains products from the seller's store
    $sql = "
        SELECT o.id
        FROM orders o
        JOIN order_items oi ON o.id = oi.order_id
        JOIN products p ON oi.product_id = p.id
        JOIN stores s ON p.store_id = s.id
        WHERE o.id = ? AND s.seller_id = ?
        LIMIT 1
    ";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $order_id, $seller_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $order = $result->fetch_assoc();
    $stmt->close();

    if (!$order) {
        // Order does not belong to this seller – redirect with error
        header('Location: sellerDashboard.php?error=unauthorized');
        exit;
    }

    // Update the order status
    $update_sql = "UPDATE orders SET status = ? WHERE id = ?";
    $update_stmt = $conn->prepare($update_sql);
    $update_stmt->bind_param("si", $new_status, $order_id);
    if ($update_stmt->execute()) {
        // Success – redirect with success message
        header('Location: sellerDashboard.php?success=status_updated');
    } else {
        // Error – redirect with error
        header('Location: sellerDashboard.php?error=update_failed');
    }
    $update_stmt->close();
    exit;
} else {
    // Invalid request
    header('Location: sellerDashboard.php');
    exit;
}
?>