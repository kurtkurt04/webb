<?php
include 'functions.php'; // Include database connection

// Handle product deletion
if (isset($_POST['delete_product']) && isset($_POST['id'])) {
    $id = $_POST['id'];
    // First get the image path to delete the file
    $image_result = mysqli_query($conn, "SELECT image_path FROM products WHERE id='$id'");
    $image_data = mysqli_fetch_assoc($image_result);
    
    // Delete the image file if it exists
    if ($image_data && !empty($image_data['image_path']) && file_exists($image_data['image_path'])) {
        unlink($image_data['image_path']);
    }
    
    // Delete the product record
    mysqli_query($conn, "DELETE FROM products WHERE id='$id'");
    
    // Redirect to products page
    $_SESSION['success_message'] = "Product successfully deleted!";
    header("Location: products.php");
    exit;
}

// Fetch product details if editing
$product = null;
if (isset($_GET['id'])) {
    $id = $_GET['id'];
    // Use prepared statement to prevent SQL injection
    $stmt = mysqli_prepare($conn, "SELECT * FROM products WHERE id=?");
    mysqli_stmt_bind_param($stmt, "i", $id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $product = mysqli_fetch_assoc($result);
    
    if (!$product) {
        $_SESSION['error_message'] = "Product not found!";
        header("Location: products.php");
        exit;
    }
} else {
    $_SESSION['error_message'] = "No product ID provided!";
    header("Location: products.php");
    exit;
}

// Handle editing a product
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_product'])) {
    $id = $_POST['id'];
    $name = $_POST['name'];
    $category_id = $_POST['category_id'];
    $size = $_POST['size'];
    $selling_price = $_POST['selling_price'];
    $stocks = $_POST['stocks'];
    
    // Handle image upload
    if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
        $target_dir = "uploads/";
        // Create directory if it doesn't exist
        if (!file_exists($target_dir)) {
            mkdir($target_dir, 0777, true);
        }
        
        // Generate unique filename
        $file_extension = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
        $unique_filename = uniqid() . '.' . $file_extension;
        $target_file = $target_dir . $unique_filename;
        
        if (move_uploaded_file($_FILES['image']['tmp_name'], $target_file)) {
            // Delete old image if it exists
            if (!empty($product['image_path']) && file_exists($product['image_path'])) {
                unlink($product['image_path']);
            }
            $image_path = $target_file;
            
            // Update with new image
            $stmt = mysqli_prepare($conn, "UPDATE products SET name=?, category_id=?, size=?, selling_price=?, stocks=?, image_path=? WHERE id=?");
            mysqli_stmt_bind_param($stmt, "ssssdsi", $name, $category_id, $size, $selling_price, $stocks, $image_path, $id);
        } else {
            $upload_error = "Failed to upload image.";
        }
    } else {
        // Update without changing image
        $stmt = mysqli_prepare($conn, "UPDATE products SET name=?, category_id=?, size=?, selling_price=?, stocks=? WHERE id=?");
        mysqli_stmt_bind_param($stmt, "ssssdi", $name, $category_id, $size, $selling_price, $stocks, $id);
    }
    
    if (isset($stmt) && mysqli_stmt_execute($stmt)) {
        $_SESSION['success_message'] = "Product updated successfully!";
        header("Location: products.php");
        exit;
    } else {
        $db_error = "Database error: " . mysqli_error($conn);
    }
}

// Fetch categories for dropdown
$categories_query = "SELECT id, name FROM categories_tbl ORDER BY name";
$categories_result = mysqli_query($conn, $categories_query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Product</title>
    <style>
        :root {
            --primary-color: #4361ee;
            --secondary-color: #3f37c9;
            --accent-color: #4895ef;
            --light-color: #f8f9fa;
            --dark-color: #212529;
            --success-color: #4caf50;
            --error-color: #f44336;
            --danger-color: #dc3545;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
            color: var(--dark-color);
            background-color: #f5f5f5;
            padding: 20px;
        }
        
        .container {
            max-width: 800px;
            margin: 0 auto;
            background-color: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.1);
        }
        
        h2 {
            color: var(--primary-color);
            margin-bottom: 30px;
            text-align: center;
            font-size: 28px;
            border-bottom: 2px solid var(--accent-color);
            padding-bottom: 10px;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: var(--dark-color);
        }
        
        input[type="text"],
        input[type="number"],
        select {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 16px;
            transition: border-color 0.3s;
        }
        
        input[type="text"]:focus,
        input[type="number"]:focus,
        select:focus {
            outline: none;
            border-color: var(--accent-color);
            box-shadow: 0 0 5px rgba(67, 97, 238, 0.3);
        }
        
        .file-input-container {
            position: relative;
            margin-bottom: 20px;
        }
        
        .file-input-label {
            display: block;
            background-color: var(--light-color);
            color: var(--dark-color);
            padding: 12px;
            border-radius: 4px;
            border: 1px dashed #aaa;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s;
        }
        
        .file-input-label:hover {
            background-color: #e9ecef;
            border-color: var(--accent-color);
        }
        
        input[type="file"] {
            position: absolute;
            width: 0.1px;
            height: 0.1px;
            opacity: 0;
            overflow: hidden;
            z-index: -1;
        }
        
        .file-name {
            margin-top: 8px;
            font-size: 14px;
            color: #666;
        }
        
        .current-image {
            margin-top: 10px;
            border: 1px solid #ddd;
            padding: 5px;
            border-radius: 4px;
            max-width: 150px;
        }
        
        .current-image img {
            max-width: 100%;
            height: auto;
            display: block;
            border-radius: 3px;
        }
        
        .current-image p {
            margin-top: 5px;
            font-size: 12px;
            text-align: center;
            color: #666;
        }
        
        .preview-image {
            max-width: 100%;
            max-height: 200px;
            display: none;
            margin-top: 10px;
            border-radius: 4px;
        }
        
        .btn {
            display: inline-block;
            background-color: var(--primary-color);
            color: white;
            padding: 12px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 1px;
            transition: background-color 0.3s;
            margin: 0 10px;
            text-decoration: none;
        }
        
        .btn:hover {
            background-color: var(--secondary-color);
        }
        
        .btn-back {
            background-color: #6c757d;
        }
        
        .btn-back:hover {
            background-color: #5a6268;
        }
        
        .btn-danger {
            background-color: var(--danger-color);
        }
        
        .btn-danger:hover {
            background-color: #bd2130;
        }
        
        .btn-container {
            display: flex;
            justify-content: center;
            margin-top: 30px;
        }
        
        .error-message {
            color: var(--error-color);
            margin-top: 5px;
            font-size: 14px;
        }
        
        .success-message {
            color: var(--success-color);
            margin-bottom: 20px;
            padding: 10px;
            border-radius: 4px;
            background-color: rgba(76, 175, 80, 0.1);
            border: 1px solid var(--success-color);
            text-align: center;
        }
        
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
        }
        
        .modal-content {
            background-color: white;
            margin: 15% auto;
            padding: 20px;
            border-radius: 8px;
            width: 80%;
            max-width: 500px;
            text-align: center;
        }
        
        .modal-title {
            color: var(--danger-color);
            margin-bottom: 20px;
        }
        
        .modal-text {
            margin-bottom: 20px;
        }
        
        .modal-buttons {
            display: flex;
            justify-content: center;
        }
        
        .modal-btn {
            padding: 10px 20px;
            margin: 0 10px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-weight: 600;
        }
        
        .modal-btn-cancel {
            background-color: #6c757d;
            color: white;
        }
        
        .modal-btn-confirm {
            background-color: var(--danger-color);
            color: white;
        }
        
        .close {
            color: #aaa;
            float: right;
            font-size: 28px;
            font-weight: bold;
            cursor: pointer;
        }
        
        .close:hover {
            color: black;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Edit Product</h2>
        
        <?php if (isset($db_error)): ?>
            <div class="error-message"><?php echo $db_error; ?></div>
        <?php endif; ?>
        
        <?php if (isset($upload_error)): ?>
            <div class="error-message"><?php echo $upload_error; ?></div>
        <?php endif; ?>
        
        <form method="POST" enctype="multipart/form-data" id="productForm">
            <input type="hidden" name="id" value="<?php echo $product['id']; ?>">
            
            <div class="form-group">
                <label for="name">Product Name:</label>
                <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($product['name']); ?>" required>
            </div>
            
            <div class="form-group">
                <label for="category_id">Category:</label>
                <select id="category_id" name="category_id" required>
                    <?php if ($categories_result && mysqli_num_rows($categories_result) > 0): ?>
                        <?php while ($category = mysqli_fetch_assoc($categories_result)): ?>
                            <option value="<?php echo $category['id']; ?>" <?php echo ($category['id'] == $product['category_id']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($category['name']); ?>
                            </option>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <option value="<?php echo $product['category_id']; ?>">Current Category</option>
                    <?php endif; ?>
                </select>
            </div>
            
            <div class="form-group">
                <label for="size">Size:</label>
                <input type="text" id="size" name="size" value="<?php echo htmlspecialchars($product['size']); ?>" required>
            </div>
            
            <div class="form-group">
                <label for="selling_price">Price:</label>
                <input type="number" id="selling_price" name="selling_price" step="0.01" min="0" value="<?php echo $product['selling_price']; ?>" required>
            </div>
            
            <div class="form-group">
                <label for="stocks">Stocks:</label>
                <input type="number" id="stocks" name="stocks" min="0" value="<?php echo $product['stocks']; ?>" required>
            </div>
            
            <div class="form-group">
                <label>Product Image:</label>
                <?php if (!empty($product['image_path'])): ?>
                    <div class="current-image">
                        <img src="<?php echo htmlspecialchars($product['image_path']); ?>" alt="Current Product Image">
                        <p>Current Image</p>
                    </div>
                <?php endif; ?>
                
                <div class="file-input-container">
                    <label for="image" class="file-input-label">
                        <span id="file-chosen">Choose a new image (optional)</span>
                    </label>
                    <input type="file" id="image" name="image" accept="image/*">
                    <p class="file-name" id="file-name"></p>
                    <img id="preview-image" class="preview-image" src="#" alt="Image preview">
                </div>
            </div>
            
            <div class="btn-container">
                <a href="products.php" class="btn btn-back">Back</a>
                <button type="submit" name="edit_product" class="btn">Update Product</button>
                <button type="button" id="deleteBtn" class="btn btn-danger">Delete Product</button>
            </div>
        </form>
    </div>
    
    <!-- Delete Confirmation Modal -->
    <div id="deleteModal" class="modal">
        <div class="modal-content">
            <span class="close">&times;</span>
            <h3 class="modal-title">Confirm Deletion</h3>
            <p class="modal-text">Are you sure you want to delete this product? This action cannot be undone.</p>
            <div class="modal-buttons">
                <button class="modal-btn modal-btn-cancel" id="cancelDelete">Cancel</button>
                <form method="POST" id="deleteForm">
                    <input type="hidden" name="id" value="<?php echo $product['id']; ?>">
                    <button type="submit" name="delete_product" class="modal-btn modal-btn-confirm">Delete</button>
                </form>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // File input preview
            const fileInput = document.getElementById('image');
            const fileChosen = document.getElementById('file-chosen');
            const fileName = document.getElementById('file-name');
            const previewImage = document.getElementById('preview-image');
            
            fileInput.addEventListener('change', function() {
                if (this.files && this.files[0]) {
                    const file = this.files[0];
                    fileChosen.textContent = 'File selected';
                    fileName.textContent = file.name;
                    
                    // Image preview
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        previewImage.src = e.target.result;
                        previewImage.style.display = 'block';
                    }
                    reader.readAsDataURL(file);
                } else {
                    fileChosen.textContent = 'Choose a new image (optional)';
                    fileName.textContent = '';
                    previewImage.style.display = 'none';
                }
            });
            
            // Delete confirmation modal
            const modal = document.getElementById('deleteModal');
            const deleteBtn = document.getElementById('deleteBtn');
            const closeBtn = document.getElementsByClassName('close')[0];
            const cancelBtn = document.getElementById('cancelDelete');
            
            deleteBtn.addEventListener('click', function() {
                modal.style.display = 'block';
            });
            
            closeBtn.addEventListener('click', function() {
                modal.style.display = 'none';
            });
            
            cancelBtn.addEventListener('click', function() {
                modal.style.display = 'none';
            });
            
            window.addEventListener('click', function(event) {
                if (event.target == modal) {
                    modal.style.display = 'none';
                }
            });
            
            // Form validation
            const form = document.getElementById('productForm');
            form.addEventListener('submit', function(event) {
                const name = document.getElementById('name').value;
                const category = document.getElementById('category_id').value;
                const size = document.getElementById('size').value;
                const price = document.getElementById('selling_price').value;
                const stocks = document.getElementById('stocks').value;
                
                if (!name || !category || !size || !price || !stocks) {
                    event.preventDefault();
                    alert('Please fill all required fields.');
                }
            });
        });
    </script>
</body>
</html>