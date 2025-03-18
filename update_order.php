<?php
include 'functions.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $orderId = $_POST['order_id'];
    $status = $_POST['status'];

    if (updateOrderStatus($orderId, $status)) {
        echo "Order updated!";
    } else {
        echo "Failed to update order.";
    }
}
?>
