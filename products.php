<?php
include 'functions.php'; // Include database connection

// Fetch products from the database with category names
$query = "SELECT p.*, c.Name as category_name 
          FROM products p
          LEFT JOIN categories_tbl c ON p.category_id = c.categoryId";
$result = mysqli_query($conn, $query);

// Check for success message
$success_message = '';
if (isset($_SESSION['success_message'])) {
    $success_message = $_SESSION['success_message'];
    unset($_SESSION['success_message']);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Celestial Jewelry - Products</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background: black;
            color: gold;
            display: flex;
            margin: 0;
            padding: 0;
            height: 100vh;
        }
        .topbar {
            position: absolute;
            top: 0;
            right: 0;
            background: black;
            padding: 10px 20px;
            z-index: 200;
        }
        .sidebar {
            flex: 0 0 250px;
            height: 100vh;
            position: fixed;
            left: 0;
            top: 0;
            z-index: 100;
        }
        .main-content {
            flex: 1;
            margin-left: 250px;
            padding: 20px;
            width: calc(100% - 250px);
        }
        .table-container {
            max-height: calc(100vh - 160px);
            overflow-y: auto;
            background: #222;
            padding: 15px;
            border-radius: 10px;
        }
        table {
            color: white;
        }
        thead {
            background: gold;
            color: black;
            position: sticky;
            top: 0;
        }
        th, td {
            padding: 12px;
        }
        .btn-update {
            background: gold;
            color: black;
            border: none;
            padding: 8px 12px;
            border-radius: 5px;
            cursor: pointer;
            margin-right: 5px;
        }
        .btn-delete {
            background: #ff4d4d;
            color: white;
            border: none;
            padding: 8px 12px;
            border-radius: 5px;
            cursor: pointer;
        }
        .product-img {
            width: 50px;
            height: 50px;
            object-fit: cover;
            border-radius: 5px;
        }
        .alert {
            padding: 10px 15px;
            margin-bottom: 15px;
            border-radius: 5px;
        }
        .alert-success {
            background: rgba(0, 128, 0, 0.2);
            border: 1px solid green;
            color: #adff2f;
        }
        .buttons-container {
            display: flex;
            justify-content: center;
            gap: 5px;
        }
    </style>
</head>
<body>
    <div class="topbar">
        <?php include 'topbar.php'; ?>
    </div>
    <div class="sidebar">
        <?php include 'sidebar.php'; ?>
    </div>
    
    <div class="main-content">
        <?php if (!empty($success_message)): ?>
            <div class="alert alert-success">
                <?php echo $success_message; ?>
            </div>
        <?php endif; ?>
        
        <button class="btn-update mb-3" id="addProductBtn">+ Add Product</button>
        <div class="table-container">
            <table class="table table-bordered text-center">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Image</th>
                        <th>Name</th>
                        <th>Category</th>
                        <th>Size</th>
                        <th>Selling Price</th>
                        <th>Stocks</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = mysqli_fetch_assoc($result)) { ?>
                        <tr>
                            <td><?php echo $row['id']; ?></td>
                            <td><img src="<?php echo $row['image_path']; ?>" class="product-img"></td>
                            <td><?php echo $row['name']; ?></td>
                            <td><?php echo $row['category_name']; ?></td>
                            <td><?php echo $row['size']; ?></td>
                            <td>₱<?php echo number_format($row['selling_price'], 2); ?></td>
                            <td><?php echo $row['stocks']; ?></td>
                            <td class="buttons-container">
                                <button class="btn-update edit-btn" data-id="<?php echo $row['id']; ?>">Edit</button>
                                <button class="btn-delete delete-btn" data-id="<?php echo $row['id']; ?>" data-name="<?php echo htmlspecialchars($row['name']); ?>">Delete</button>
                            </td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="deleteModalLabel">Confirm Delete</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    Are you sure you want to delete <span id="deleteProductName"></span>?
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <a href="#" id="confirmDelete" class="btn btn-danger">Delete</a>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.getElementById("addProductBtn").addEventListener("click", function() {
            window.location.href = "add_product.php";
        });

        document.addEventListener("click", function(event) {
            if (event.target.classList.contains("edit-btn")) {
                let id = event.target.getAttribute("data-id");
                window.location.href = "edit_product.php?id=" + id;
            }
            
            if (event.target.classList.contains("delete-btn")) {
                let id = event.target.getAttribute("data-id");
                let name = event.target.getAttribute("data-name");
                
                // Set data for the modal
                document.getElementById("deleteProductName").textContent = name;
                document.getElementById("confirmDelete").href = "delete_product.php?id=" + id;
                
                // Show the modal
                let deleteModal = new bootstrap.Modal(document.getElementById('deleteModal'));
                deleteModal.show();
            }
        });
    </script>
</body>
</html>