<?php
include 'functions.php'; // Include database connection

// Initialize variables
$db_error = null;
$upload_error = null;

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'];
    $category_id = $_POST['category_id'];
    $size = $_POST['size'];
    $selling_price = $_POST['selling_price'];
    $stocks = $_POST['stocks'];
    
    // Handle image upload
    $image_path = '';
    if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
        $target_dir = "uploads/";
        // Create directory if it doesn't exist
        if (!file_exists($target_dir)) {
            mkdir($target_dir, 0777, true);
        }
        
        // Generate unique filename to prevent overwriting
        $file_extension = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
        $unique_filename = uniqid() . '.' . $file_extension;
        $target_file = $target_dir . $unique_filename;
        
        if (move_uploaded_file($_FILES['image']['tmp_name'], $target_file)) {
            $image_path = $target_file;
        } else {
            $upload_error = "Failed to upload image.";
        }
    }

    // Use prepared statements to prevent SQL injection
    $query = "INSERT INTO products (name, category_id, size, selling_price, stocks, image_path) 
              VALUES (?, ?, ?, ?, ?, ?)";
    
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "sssdis", $name, $category_id, $size, $selling_price, $stocks, $image_path);
    
    if (mysqli_stmt_execute($stmt)) {
        $_SESSION['success_message'] = "Product added successfully!";
        header("Location: products.php");
        exit;
    } else {
        $db_error = "Database error: " . mysqli_error($conn);
    }
}

// Fetch categories for dropdown - UPDATED to use correct column names
$categories_result = false;
if (isset($conn)) {
    $categories_query = "SELECT categoryId, Name FROM categories_tbl ORDER BY Name";
    $categories_result = mysqli_query($conn, $categories_query);
    if (!$categories_result) {
        $db_error = "Error fetching categories: " . mysqli_error($conn);
    }
} else {
    $db_error = "Database connection not established.";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Product</title>
    <style>
        :root {
            --primary-color: #4361ee;
            --secondary-color: #3f37c9;
            --accent-color: #4895ef;
            --light-color: #f8f9fa;
            --dark-color: #212529;
            --success-color: #4caf50;
            --error-color: #f44336;
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
        
        .btn-container {
            text-align: center;
            margin-top: 30px;
            display: flex;
            justify-content: center;
        }
        
        .error-message {
            color: var(--error-color);
            margin-top: 5px;
            font-size: 14px;
            padding: 10px;
            border-radius: 4px;
            background-color: rgba(244, 67, 54, 0.1);
            border: 1px solid var(--error-color);
            text-align: center;
            margin-bottom: 20px;
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
    </style>
</head>
<body>
    <div class="container">
        <h2>Add Product</h2>
        
        <?php if (isset($db_error)): ?>
            <div class="error-message"><?php echo $db_error; ?></div>
        <?php endif; ?>
        
        <?php if (isset($upload_error)): ?>
            <div class="error-message"><?php echo $upload_error; ?></div>
        <?php endif; ?>
        
        <?php if (isset($_SESSION['success_message'])): ?>
            <div class="success-message"><?php echo $_SESSION['success_message']; ?></div>
            <?php unset($_SESSION['success_message']); ?>
        <?php endif; ?>
        
        <form method="POST" enctype="multipart/form-data" id="productForm">
            <div class="form-group">
                <label for="name">Product Name:</label>
                <input type="text" id="name" name="name" required>
            </div>
            
            <div class="form-group">
                <label for="category_id">Category:</label>
                <select id="category_id" name="category_id" required>
                    <option value="">Select a category</option>
                    <?php if ($categories_result && mysqli_num_rows($categories_result) > 0): ?>
                        <?php while ($category = mysqli_fetch_assoc($categories_result)): ?>
                            <option value="<?php echo htmlspecialchars($category['categoryId']); ?>">
                                <?php echo htmlspecialchars($category['Name']); ?>
                            </option>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <option value="1">Default Category</option>
                    <?php endif; ?>
                </select>
            </div>
            
            <div class="form-group">
                <label for="size">Size:</label>
                <input type="text" id="size" name="size" required>
            </div>
            
            <div class="form-group">
                <label for="selling_price">Price:</label>
                <input type="number" id="selling_price" name="selling_price" step="0.01" min="0" required>
            </div>
            
            <div class="form-group">
                <label for="stocks">Stocks:</label>
                <input type="number" id="stocks" name="stocks" min="0" required>
            </div>
            
            <div class="form-group">
                <label>Product Image:</label>
                <div class="file-input-container">
                    <label for="image" class="file-input-label">
                        <span id="file-chosen">Choose a file</span>
                    </label>
                    <input type="file" id="image" name="image" accept="image/*" required>
                    <p class="file-name" id="file-name"></p>
                    <img id="preview-image" class="preview-image" src="#" alt="Image preview">
                </div>
            </div>
            
            <div class="btn-container">
                <a href="products.php" class="btn btn-back">Back</a>
                <button type="submit" class="btn">Add Product</button>
            </div>
        </form>
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
                    fileChosen.textContent = 'Choose a file';
                    fileName.textContent = '';
                    previewImage.style.display = 'none';
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
                
                if (!name || !category || !size || !price || !stocks || !fileInput.files[0]) {
                    event.preventDefault();
                    alert('Please fill all required fields and upload an image.');
                }
            });
        });
    </script>
</body>
</html>