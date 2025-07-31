<?php
session_start();

// Database connection
$host = "localhost";
$user = "root";
$password = ""; // DB password
$db = "electricity_billing_system";

$conn = new mysqli($host, $user, $password, $db);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get form values
$user_type = $_POST['userType'];
$login_id = $_POST['loginId'];
$password_input = $_POST['password'];

// Fetch user
$stmt = $conn->prepare("SELECT * FROM users WHERE user_type = ? AND login_id = ?");
$stmt->bind_param("ss", $user_type, $login_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 1) {
    $user = $result->fetch_assoc();

    if ($password_input === $user['password']) {
        // Set session
        $_SESSION['login_id'] = $user['login_id'];
        $_SESSION['user_type'] = $user['user_type'];

        // Redirect based on user type
        if ($user['user_type'] === 'admin') {
            header("Location: admin_dashboard.php");
        } elseif ($user['user_type'] === 'consumer') {
            header("Location: consumer_dashboard.php");
        } elseif ($user['user_type'] === 'employee') {
            header("Location: employee_dashboard.php");
        } else {
            echo "<script>alert('Unknown user type'); window.history.back();</script>";
        }
        exit();
    } else {
        echo "<script>alert('Invalid password'); window.history.back();</script>";
    }
} else {
    echo "<script>alert('Invalid login ID or user type'); window.history.back();</script>";
}

$stmt->close();
$conn->close();
?>
