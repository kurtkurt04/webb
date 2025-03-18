<?php
include 'functions.php';

if (isset($_GET['type'])) {
    if ($_GET['type'] == "sales") {
        echo getSalesData();
    } elseif ($_GET['type'] == "users") {
        echo getUserData();
    }
}
?>
