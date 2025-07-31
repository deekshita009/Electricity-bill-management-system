<?php
$host = "localhost";
$user = "root";
$password = ""; // Update this if your MySQL password is set
$dbname = "electricity_billing_system";

// Create connection
$conn = mysqli_connect($host, $user, $password, $dbname);

// Check connection
if (!$conn) {
    die("Database connection failed: " . mysqli_connect_error());
}
?>
