<?php
// Database connection
$servername = "localhost";
$username = "root";  // Change as needed
$password = "";      // Change as needed
$dbname = "celestial_jewels";  // Change as needed

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch all customers
$sql = "SELECT customer_id, username, email, phone_number FROM customers_tbl";
$result = $conn->query($sql);

// Create array of customers for initial display
$customers = [];
if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $customers[] = $row;
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Celestial Jewelry - Customers</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css">
    <style>
        body { background: black; color: gold; display: flex; margin: 0; padding: 0; height: 100vh; }
        .sidebar { flex: 0 0 250px; height: 100vh; position: fixed; left: 0; top: 0; z-index: 100; }
        .main-content { flex: 1; margin-left: 250px; padding: 20px; width: calc(100% - 250px); }
        .top-bar { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; }
        .table-container { max-height: calc(100vh - 130px); overflow-y: auto; background: #222; padding: 15px; border-radius: 10px; }
        .search-box { background: #333; border: 1px solid gold; color: white; padding: 8px 15px; border-radius: 5px; margin-right: 10px; }
        .search-box::placeholder { color: #aaa; }
        .modal-content { background-color: #333; color: white; }
        .form-control { background-color: #444; border: 1px solid #555; color: white; margin-bottom: 15px; }
        .form-control:focus { background-color: #555; color: white; }
        .btn-action { margin: 0 2px; }
    </style>
</head>
<body>
    <div class="sidebar">
        <?php include 'sidebar.php'; ?>
    </div>

    <div class="main-content">
        <div class="top-bar">
            <h2 class="page-title">Customers</h2>
            <div>
                <input type="text" class="search-box" id="customerSearch" placeholder="Search customers...">
                <button class="btn btn-outline-warning" id="addCustomerBtn">
                    <i class="bi bi-plus-circle"></i> Add Customer
                </button>
            </div>
        </div>

        <div class="table-container">
            <table class="table table-bordered text-center" id="customersTable">
                <thead class="bg-warning text-dark">
                    <tr>
                        <th>Customer ID</th>
                        <th>Username</th>
                        <th>Email</th>
                        <th>Phone</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody class="text-light">
                    <?php if(empty($customers)): ?>
                        <tr><td colspan="5" class="text-center">No customers found</td></tr>
                    <?php else: ?>
                        <?php foreach($customers as $customer): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($customer['customer_id']); ?></td>
                                <td><?php echo htmlspecialchars($customer['username']); ?></td>
                                <td><?php echo htmlspecialchars($customer['email']); ?></td>
                                <td><?php echo htmlspecialchars($customer['phone_number'] ?? '-'); ?></td>
                                <td>
                                    <button onclick="editCustomer(<?php echo $customer['customer_id']; ?>)" class="btn btn-warning btn-sm btn-action">
                                        <i class="bi bi-pencil"></i>
                                    </button>
                                    <button onclick="confirmDeleteCustomer(<?php echo $customer['customer_id']; ?>)" class="btn btn-danger btn-sm btn-action">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Customer Modal -->
    <div class="modal fade" id="customerModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalTitle">Add Customer</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="customerForm">
                        <input type="hidden" id="customerId" name="customerId">
                        <div class="mb-3">
                            <label for="username" class="form-label">Username:</label>
                            <input type="text" id="username" name="username" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label for="email" class="form-label">Email:</label>
                            <input type="email" id="email" name="email" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label for="phone" class="form-label">Phone:</label>
                            <input type="text" id="phone" name="phone" class="form-control">
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-warning" id="saveCustomer">Save</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Initialize Bootstrap modal
        const customerModal = new bootstrap.Modal(document.getElementById('customerModal'));
        
        // Event listeners
        document.getElementById('addCustomerBtn').addEventListener('click', openAddCustomerModal);
        document.getElementById('saveCustomer').addEventListener('click', saveCustomer);
        document.getElementById('customerSearch').addEventListener('input', searchCustomers);

        // Function to search customers
        function searchCustomers() {
            const searchTerm = document.getElementById('customerSearch').value.trim();
            
            fetch('search_customers.php?term=' + encodeURIComponent(searchTerm))
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Network response was not ok');
                    }
                    return response.json();
                })
                .then(data => populateCustomersTable(data))
                .catch(error => {
                    console.error('Error searching customers:', error);
                    alert('Error searching customers. Please try again.');
                });
        }

        // Function to populate customers table
        function populateCustomersTable(customers) {
            let tableBody = document.querySelector('#customersTable tbody');
            tableBody.innerHTML = '';
            
            if (customers.length === 0) {
                tableBody.innerHTML = '<tr><td colspan="5" class="text-center">No customers found</td></tr>';
                return;
            }
            
            customers.forEach(customer => {
                let row = document.createElement('tr');
                row.innerHTML = `
                    <td>${customer.customer_id}</td>
                    <td>${customer.username}</td>
                    <td>${customer.email}</td>
                    <td>${customer.phone_number || '-'}</td>
                    <td>
                        <button onclick="editCustomer(${customer.customer_id})" class="btn btn-warning btn-sm btn-action">
                            <i class="bi bi-pencil"></i>
                        </button>
                        <button onclick="confirmDeleteCustomer(${customer.customer_id})" class="btn btn-danger btn-sm btn-action">
                            <i class="bi bi-trash"></i>
                        </button>
                    </td>
                `;
                tableBody.appendChild(row);
            });
        }

        // Function to open add customer modal
        function openAddCustomerModal() {
            document.getElementById('modalTitle').textContent = 'Add Customer';
            document.getElementById('customerForm').reset();
            document.getElementById('customerId').value = '';
            customerModal.show();
        }

        // Function to save customer (add or update)
        function saveCustomer() {
            const form = document.getElementById('customerForm');
            const formData = new FormData(form);
            
            fetch('add_customer.php', {
                method: 'POST',
                body: formData
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error('Network response was not ok');
                }
                return response.json();
            })
            .then(data => {
                if (data.success) {
                    customerModal.hide();
                    // Refresh the customers list
                    location.reload();
                    alert(data.message);
                } else {
                    alert('Error: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error saving customer:', error);
                alert('Error saving customer. Please try again.');
            });
        }

        // Function to edit customer
        window.editCustomer = function(customerId) {
            fetch('edit_customer.php?id=' + customerId)
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Network response was not ok');
                    }
                    return response.json();
                })
                .then(data => {
                    if (data.success) {
                        const customer = data.customer;
                        document.getElementById('modalTitle').textContent = 'Edit Customer';
                        document.getElementById('customerId').value = customer.customer_id;
                        document.getElementById('username').value = customer.username;
                        document.getElementById('email').value = customer.email;
                        document.getElementById('phone').value = customer.phone_number || '';
                        customerModal.show();
                    } else {
                        alert('Error: ' + data.message);
                    }
                })
                .catch(error => {
                    console.error('Error fetching customer details:', error);
                    alert('Error fetching customer details. Please try again.');
                });
        }

        // Function to confirm delete customer
        window.confirmDeleteCustomer = function(customerId) {
            if (confirm('Are you sure you want to delete this customer?')) {
                deleteCustomer(customerId);
            }
        }

        // Function to delete customer
        function deleteCustomer(customerId) {
            fetch('delete_customer.php?id=' + customerId)
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Network response was not ok');
                    }
                    return response.json();
                })
                .then(data => {
                    if (data.success) {
                        // Refresh the customers list
                        location.reload();
                        alert(data.message);
                    } else {
                        alert('Error: ' + data.message);
                    }
                })
                .catch(error => {
                    console.error('Error deleting customer:', error);
                    alert('Error deleting customer. Please try again.');
                });
        }
    });
    </script>
</body>
</html>