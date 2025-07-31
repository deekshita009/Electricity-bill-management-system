<?php
session_start();

// Database connection
$host = "localhost";
$user = "root";
$password = "";
$db = "electricity_billing_system";

$conn = new mysqli($host, $user, $password, $db);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get form data
$user_type = $_POST['userType'];
$login_id = $_POST['loginId'];
$old_password = $_POST['oldPassword'];
$new_password = $_POST['newPassword'];

// Fetch user
$stmt = $conn->prepare("SELECT * FROM users WHERE user_type = ? AND login_id = ?");
$stmt->bind_param("ss", $user_type, $login_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 1) {
    $user = $result->fetch_assoc();

    if ($old_password === $user['password']) {
        // Update password
        $update = $conn->prepare("UPDATE users SET password = ? WHERE login_id = ?");
        $update->bind_param("ss", $new_password, $login_id);

        if ($update->execute()) {
            // Set session
            $_SESSION['login_id'] = $user['login_id'];
            $_SESSION['user_type'] = $user['user_type'];

            // Redirect with JS alert and auto-login
            if ($user_type === 'consumer') {
                echo "<script>alert('Password has been changed successfully!'); window.location.href = 'consumer_dashboard.php';</script>";
            } elseif ($user_type === 'employee') {
                echo "<script>alert('Password has been changed successfully!'); window.location.href = 'employee_dashboard.php';</script>";
            } else {
                echo "<script>alert('Invalid user type.'); window.location.href = 'login.html';</script>";
            }
        } else {
            echo "<script>alert('Failed to update password.'); window.history.back();</script>";
        }
        $update->close();
    } else {
        echo "<script>alert('Old password is incorrect.'); window.history.back();</script>";
    }
} else {
    echo "<script>alert('Invalid login ID or user type.'); window.history.back();</script>";
}

$stmt->close();
$conn->close();
?>
