<?php
include 'db_connect.php'; // Include database connection

// Check if ID is provided
if (isset($_GET['id']) && !empty($_GET['id'])) {
    $product_id = mysqli_real_escape_string($conn, $_GET['id']);
    
    // First get the image path to delete the file
    $query = "SELECT image_path FROM products WHERE id = ?";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "i", $product_id);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_bind_result($stmt, $image_path);
    mysqli_stmt_fetch($stmt);
    mysqli_stmt_close($stmt);
    
    // Delete the product from database
    $delete_query = "DELETE FROM products WHERE id = ?";
    $stmt = mysqli_prepare($conn, $delete_query);
    mysqli_stmt_bind_param($stmt, "i", $product_id);
    
    if (mysqli_stmt_execute($stmt)) {
        // Delete the image file if it exists
        if (!empty($image_path) && file_exists($image_path)) {
            unlink($image_path);
        }
        
        $_SESSION['success_message'] = "Product deleted successfully!";
    } else {
        $_SESSION['success_message'] = "Error deleting product: " . mysqli_error($conn);
    }
    
    mysqli_stmt_close($stmt);
} else {
    $_SESSION['success_message'] = "Product ID not provided!";
}

// Redirect back to products page
header("Location: products.php");
exit;
?>