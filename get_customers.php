<?php
include 'db_connection.php';
$sql = "SELECT * FROM customers_tbl";
$result = $conn->query($sql);
$customers = [];
while ($row = $result->fetch_assoc()) {
    $customers[] = $row;
}
echo json_encode($customers);
$conn->close();
?>