<?php
// Database configuration
define('DB_HOST', "localhost");
define('DB_USER', "root");
define('DB_PASS', ""); // Empty password for XAMPP/WAMP default
define('DB_NAME', "electricity_billing_system");

// Test connection (optional debugging)
$testConn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
if ($testConn->connect_error) {
    die("Config Error: " . $testConn->connect_error);
} else {
    $testConn->close(); // Close test connection
}
?>