<?php 
$conn = new mysqli("localhost", "root", "", "electricity_billing_system");

// Add Consumer
if (isset($_POST['add_consumer'])) {
    $login_id = $_POST['login_id'];
    $password = $_POST['password'];
    $name = $_POST['name'];
    $email = $_POST['email'];
    $address = $_POST['address'];
    $phone = $_POST['phone'];

    $stmt = $conn->prepare("INSERT INTO users (login_id, password, user_type) VALUES (?, ?, 'consumer')");
    $stmt->bind_param("ss", $login_id, $password);
    $stmt->execute();

    $stmt = $conn->prepare("INSERT INTO consumers (login_id, name, email, address, phone) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("sssss", $login_id, $name, $email, $address, $phone);
    $stmt->execute();

    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
}

// Add Employee
if (isset($_POST['add_employee'])) {
    $login_id = $_POST['login_id'];
    $password = $_POST['password'];
    $name = $_POST['name'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];

    $stmt = $conn->prepare("INSERT INTO users (login_id, password, user_type) VALUES (?, ?, 'employee')");
    $stmt->bind_param("ss", $login_id, $password);
    $stmt->execute();

    $stmt = $conn->prepare("INSERT INTO employees (login_id, name, email, phone) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssss", $login_id, $name, $email, $phone);
    $stmt->execute();

    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
}

// Update Consumer
if (isset($_POST['update_consumer'])) {
    $id = $_POST['id'];
    $name = $_POST['name'];
    $email = $_POST['email'];
    $address = $_POST['address'];
    $phone = $_POST['phone'];

    $stmt = $conn->prepare("UPDATE consumers SET name=?, email=?, address=?, phone=? WHERE id=?");
    $stmt->bind_param("ssssi", $name, $email, $address, $phone, $id);
    $stmt->execute();

    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
}

// Update Employee
if (isset($_POST['update_employee'])) {
    $id = $_POST['id'];
    $name = $_POST['name'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];

    $stmt = $conn->prepare("UPDATE employees SET name=?, email=?, phone=? WHERE id=?");
    $stmt->bind_param("sssi", $name, $email, $phone, $id);
    $stmt->execute();

    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
}

// Handle consumer deletion
if (isset($_POST['delete_consumer'])) {
    $consumer_id = $_POST['delete_consumer'];
    
    // First delete related consumption records
    $delete_consumption = $conn->query("DELETE FROM consumption_records WHERE login_id = '$consumer_id'");
    
    if ($delete_consumption) {
        // Then delete the consumer
        $delete_consumer = $conn->query("DELETE FROM consumers WHERE login_id = '$consumer_id'");
        
        if ($delete_consumer) {
            echo "<script>alert('Consumer records deleted successfully.'); window.location.href=window.location.href;</script>";
        } else {
            echo "<script>alert('Error deleting consumer.');</script>";
        }
    } else {
        echo "<script>alert('Error deleting consumption records.');</script>";
    }
}

// Delete Employee
if (isset($_GET['delete_employee'])) {
    $id = $_GET['delete_employee'];

    $stmt = $conn->prepare("SELECT login_id FROM employees WHERE id=?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->bind_result($login_id);
    $stmt->fetch();
    $stmt->close();

    $conn->query("DELETE FROM employees WHERE id=$id");

    $stmt = $conn->prepare("DELETE FROM users WHERE login_id=?");
    $stmt->bind_param("s", $login_id);
    $stmt->execute();
}

// Fetch data again after actions
$consumers = $conn->query("SELECT * FROM consumers");
$employees = $conn->query("SELECT * FROM employees");
?>

<?php 
$conn = new mysqli("localhost", "root", "", "electricity_billing_system");

// Add Consumer
if (isset($_POST['add_consumer'])) {
    $login_id = $_POST['login_id'];
    $password = $_POST['password'];
    $name = $_POST['name'];
    $email = $_POST['email'];
    $address = $_POST['address'];
    $phone = $_POST['phone'];

    $stmt = $conn->prepare("INSERT INTO users (login_id, password, user_type) VALUES (?, ?, 'consumer')");
    $stmt->bind_param("ss", $login_id, $password);
    $stmt->execute();

    $stmt = $conn->prepare("INSERT INTO consumers (login_id, name, email, address, phone) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("sssss", $login_id, $name, $email, $address, $phone);
    $stmt->execute();

    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
}

// Add Employee
if (isset($_POST['add_employee'])) {
    $login_id = $_POST['login_id'];
    $password = $_POST['password'];
    $name = $_POST['name'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];

    $stmt = $conn->prepare("INSERT INTO users (login_id, password, user_type) VALUES (?, ?, 'employee')");
    $stmt->bind_param("ss", $login_id, $password);
    $stmt->execute();

    $stmt = $conn->prepare("INSERT INTO employees (login_id, name, email, phone) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssss", $login_id, $name, $email, $phone);
    $stmt->execute();

    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
}

// Update Consumer
if (isset($_POST['update_consumer'])) {
    $id = $_POST['id'];
    $name = $_POST['name'];
    $email = $_POST['email'];
    $address = $_POST['address'];
    $phone = $_POST['phone'];

    $stmt = $conn->prepare("UPDATE consumers SET name=?, email=?, address=?, phone=? WHERE id=?");
    $stmt->bind_param("ssssi", $name, $email, $address, $phone, $id);
    $stmt->execute();

    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
}

// Update Employee
if (isset($_POST['update_employee'])) {
    $id = $_POST['id'];
    $name = $_POST['name'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];

    $stmt = $conn->prepare("UPDATE employees SET name=?, email=?, phone=? WHERE id=?");
    $stmt->bind_param("sssi", $name, $email, $phone, $id);
    $stmt->execute();

    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
}

// Handle consumer deletion
if (isset($_POST['delete_consumer'])) {
    $consumer_id = $_POST['delete_consumer'];
    
    // First delete related consumption records
    $delete_consumption = $conn->query("DELETE FROM consumption_records WHERE login_id = '$consumer_id'");
    
    if ($delete_consumption) {
        // Then delete the consumer
        $delete_consumer = $conn->query("DELETE FROM consumers WHERE login_id = '$consumer_id'");
        
        if ($delete_consumer) {
            echo "<script>alert('Consumer records deleted successfully.'); window.location.href=window.location.href;</script>";
        } else {
            echo "<script>alert('Error deleting consumer.');</script>";
        }
    } else {
        echo "<script>alert('Error deleting consumption records.');</script>";
    }
}

// Delete Employee
if (isset($_GET['delete_employee'])) {
    $id = $_GET['delete_employee'];

    $stmt = $conn->prepare("SELECT login_id FROM employees WHERE id=?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->bind_result($login_id);
    $stmt->fetch();
    $stmt->close();

    $conn->query("DELETE FROM employees WHERE id=$id");

    $stmt = $conn->prepare("DELETE FROM users WHERE login_id=?");
    $stmt->bind_param("s", $login_id);
    $stmt->execute();
}

// Fetch data again after actions
$consumers = $conn->query("SELECT * FROM consumers");
$employees = $conn->query("SELECT * FROM employees");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
    :root {
        --primary-color: #4361ee;
        --secondary-color: #7209b7;
        --light-bg: #f8f9fa;
        --card-bg: #ffffff;
        --text-color: #495057;
        --text-light: #6c757d;
        --border-color: #e9ecef;
        --success-color: #2ecc71;
        --warning-color: #f39c12;
        --danger-color: #e74c3c;
        --info-color: #3498db;
    }
    
    * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
    }
    
    body {
        font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
        background-color: var(--light-bg);
        min-height: 100vh;
        color: var(--text-color);
        overflow-x: hidden;
    }
    
    /* Main Dashboard Layout */
    .dashboard-container {
        display: grid;
        grid-template-columns: 280px 1fr;
        min-height: 100vh;
    }
    
    /* Sidebar */
    .sidebar {
        background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
        padding: 2rem 1.5rem;
        position: sticky;
        top: 0;
        height: 100vh;
        display: flex;
        flex-direction: column;
        box-shadow: 0 0 15px rgba(0,0,0,0.1);
    }
    
    .brand {
        display: flex;
        align-items: center;
        gap: 12px;
        margin-bottom: 2.5rem;
    }
    
    .brand-icon {
        font-size: 2rem;
        color: white;
    }
    
    .brand-text {
        font-size: 1.5rem;
        font-weight: 700;
        color: white;
    }
    
    .nav-menu {
        display: flex;
        flex-direction: column;
        gap: 8px;
        flex-grow: 1;
    }
    
    .nav-item {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 12px 16px;
        border-radius: 8px;
        text-decoration: none;
        color: rgba(255,255,255,0.8);
        font-weight: 500;
        transition: all 0.3s ease;
    }
    
    .nav-item:hover, .nav-item.active {
        background: rgba(255, 255, 255, 0.2);
        color: white;
        transform: translateX(4px);
    }
    
    .nav-item i {
        width: 24px;
        text-align: center;
    }
    
    .nav-item.active {
        background: rgba(255,255,255,0.3);
        color: white;
    }
    
    .user-profile {
        margin-top: auto;
        padding-top: 1.5rem;
        border-top: 1px solid rgba(255,255,255,0.2);
        display: flex;
        align-items: center;
        gap: 12px;
    }
    
    .user-avatar {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        background: white;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 600;
        color: var(--primary-color);
    }
    
    .user-info h4 {
        font-size: 0.95rem;
        font-weight: 600;
        margin-bottom: 2px;
        color: white;
    }
    
    .user-info p {
        font-size: 0.8rem;
        color: rgba(255,255,255,0.7);
    }
    
    /* Main Content */
    .main-content {
        padding: 2rem;
        background-color: var(--light-bg);
    }
    
    .dashboard-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 2rem;
    }
    
    .page-title {
        font-size: 1.8rem;
        font-weight: 700;
        color: var(--text-color);
    }
    
    .logout-btn {
        background: rgba(239, 68, 68, 0.1);
        color: #ef4444;
        border: none;
        padding: 8px 16px;
        border-radius: 6px;
        font-weight: 500;
        transition: all 0.3s ease;
    }
    
    .logout-btn:hover {
        background: rgba(239, 68, 68, 0.2);
    }
    
    /* Cards */
    .card {
        background: var(--card-bg);
        border-radius: 12px;
        padding: 1.5rem;
        margin-bottom: 1.5rem;
        transition: transform 0.3s ease, box-shadow 0.3s ease;
        box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        border: 1px solid var(--border-color);
    }
    
    .card:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
    }
    
    .card-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 1.25rem;
        padding-bottom: 1rem;
        border-bottom: 1px solid var(--border-color);
    }
    
    .card-title {
        font-size: 1.25rem;
        font-weight: 600;
        display: flex;
        align-items: center;
        gap: 10px;
        color: var(--text-color);
    }
    
    .card-title i {
        color: var(--primary-color);
    }
    
    /* Tables */
    .data-table {
        width: 100%;
        border-collapse: collapse;
    }
    
    .data-table th {
        text-align: left;
        padding: 12px 16px;
        background: #f1f3f5;
        color: var(--text-light);
        font-weight: 500;
        text-transform: uppercase;
        font-size: 0.75rem;
        letter-spacing: 0.5px;
    }
    
    .data-table td {
        padding: 16px;
        border-bottom: 1px solid var(--border-color);
        color: var(--text-color);
    }
    
    .data-table tr:last-child td {
        border-bottom: none;
    }
    
    .status-badge {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 6px 12px;
        border-radius: 20px;
        font-size: 0.85rem;
        font-weight: 500;
    }
    
    .status-paid {
        background: rgba(46, 204, 113, 0.1);
        color: var(--success-color);
    }
    
    .status-pending {
        background: rgba(243, 156, 18, 0.1);
        color: var(--warning-color);
    }
    
    .status-overdue {
        background: rgba(231, 76, 60, 0.1);
        color: var(--danger-color);
    }
    
    /* Form Elements */
    .form-card {
        background: var(--card-bg);
        padding: 1.5rem;
        border-radius: 12px;
        border: 1px solid var(--border-color);
    }
    
    .form-label {
        display: block;
        margin-bottom: 8px;
        font-weight: 500;
        color: var(--text-light);
    }
    
    .form-control {
        background: white;
        border: 1px solid var(--border-color);
        color: var(--text-color);
        padding: 12px 16px;
        border-radius: 8px;
        width: 100%;
        margin-bottom: 1rem;
        transition: all 0.3s ease;
    }
    
    .form-control:focus {
        outline: none;
        border-color: var(--primary-color);
        box-shadow: 0 0 0 3px rgba(67, 97, 238, 0.1);
    }
    
    /* Buttons */
    .btn {
        padding: 12px 24px;
        border-radius: 8px;
        font-weight: 600;
        border: none;
        cursor: pointer;
        transition: all 0.3s ease;
        display: inline-flex;
        align-items: center;
        gap: 8px;
    }
    
    .btn-primary {
        background: var(--primary-color);
        color: white;
    }
    
    .btn-primary:hover {
        background: #3a56d5;
        transform: translateY(-2px);
    }
    
    .btn-success {
        background: var(--success-color);
        color: white;
    }
    
    .btn-info {
        background: var(--info-color);
        color: white;
    }
    
    .btn-warning {
        background: var(--warning-color);
        color: white;
    }
    
    .btn-danger {
        background: var(--danger-color);
        color: white;
    }
    
    .btn-sm {
        padding: 6px 12px;
        font-size: 0.875rem;
    }
    
    .btn-lg {
        padding: 16px 32px;
        font-size: 1rem;
    }
    
    /* Animations */
    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(10px); }
        to { opacity: 1; transform: translateY(0); }
    }
    
    .animate-fade {
        animation: fadeIn 0.5s ease-out;
    }
    
    /* Alert Styles */
    .alert {
        padding: 1rem;
        border-radius: 8px;
        margin-bottom: 1rem;
        display: flex;
        align-items: center;
        gap: 12px;
        border: 1px solid transparent;
    }
    
    .alert-danger {
        background: rgba(231, 76, 60, 0.1);
        border-color: rgba(231, 76, 60, 0.2);
        color: var(--danger-color);
    }
    
    .alert-success {
        background: rgba(46, 204, 113, 0.1);
        border-color: rgba(46, 204, 113, 0.2);
        color: var(--success-color);
    }
    
    .alert-info {
        background: rgba(52, 152, 219, 0.1);
        border-color: rgba(52, 152, 219, 0.2);
        color: var(--info-color);
    }
    
    .alert-warning {
        background: rgba(243, 156, 18, 0.1);
        border-color: rgba(243, 156, 18, 0.2);
        color: var(--warning-color);
    }
    
    /* Responsive */
    @media (max-width: 1024px) {
        .dashboard-container {
            grid-template-columns: 1fr;
        }
        
        .sidebar {
            display: none;
        }
    }
    
    /* Utility Classes */
    .text-gradient {
        background: linear-gradient(90deg, var(--primary-color), var(--secondary-color));
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
    }
    
    .mb-4 {
        margin-bottom: 1.5rem;
    }
    
    .mt-4 {
        margin-top: 1.5rem;
    }
    
    .hidden {
        display: none;
    }
    
    .text-center {
        text-align: center;
    }
    
    .text-muted {
        color: var(--text-light);
    }
</style>
</head>
<body>
    <div class="dashboard-container">
        <!-- Sidebar -->
        <div class="sidebar">
            <div class="brand">
                <div class="brand-icon">
                    <i class="fas fa-bolt"></i>
                </div>
                <div class="brand-text">EnergyPro</div>
            </div>
            
            <div class="nav-menu">
                <a href="#" class="nav-item active" onclick="showSection('viewConsumers')">
                    <i class="fas fa-users"></i>
                    <span>View Consumers</span>
                </a>
                <a href="#" class="nav-item" onclick="showSection('viewEmployees')">
                    <i class="fas fa-user-tie"></i>
                    <span>View Employees</span>
                </a>
                <a href="#" class="nav-item" onclick="showSection('manageConsumers')">
                    <i class="fas fa-user-edit"></i>
                    <span>Manage Consumers</span>
                </a>
                <a href="#" class="nav-item" onclick="showSection('manageEmployees')">
                    <i class="fas fa-user-cog"></i>
                    <span>Manage Employees</span>
                </a>
            </div>
            
            <div class="user-profile">
                <div class="user-avatar">
                    <i class="fas fa-user-shield"></i>
                </div>
                <div class="user-info">
                    <h4>Administrator</h4>
                    <p>System Admin</p>
                </div>
            </div>
        </div>
        
        <!-- Main Content -->
        <div class="main-content">
            <div class="dashboard-header">
                <h1 class="page-title" id="current-section-title">View Consumers</h1>
                <a href="logout.php" class="logout-btn">
                    <i class="fas fa-sign-out-alt"></i> Logout
                </a>
            </div>
            
            <!-- View Consumers Section -->
            <div id="viewConsumers" class="section animate-fade">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-users"></i>
                            <span>All Consumers</span>
                        </h3>
                    </div>
                    <div class="table-responsive">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    
                                    <th>Login ID</th>
                                    <th>Name</th>
                                    <th>Email</th>
                                    <th>Phone</th>
                                    <th>Address</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($row = $consumers->fetch_assoc()): ?>
                                    <tr>
                                        
                                        <td><?= $row['login_id'] ?></td>
                                        <td><?= $row['name'] ?></td>
                                        <td><?= $row['email'] ?></td>
                                        <td><?= $row['phone'] ?></td>
                                        <td><?= $row['address'] ?></td>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            
            <!-- View Employees Section -->
            <div id="viewEmployees" class="section hidden animate-fade">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-user-tie"></i>
                            <span>All Employees</span>
                        </h3>
                    </div>
                    <div class="table-responsive">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    
                                    <th>Login ID</th>
                                    <th>Name</th>
                                    <th>Email</th>
                                    <th>Phone</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($row = $employees->fetch_assoc()): ?>
                                    <tr>
                                        
                                        <td><?= $row['login_id'] ?></td>
                                        <td><?= $row['name'] ?></td>
                                        <td><?= $row['email'] ?></td>
                                        <td><?= $row['phone'] ?></td>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            
            <!-- Manage Consumers Section -->
            <div id="manageConsumers" class="section hidden animate-fade">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-user-edit"></i>
                            <span>Manage Consumers</span>
                        </h3>
                        <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#addConsumerModal">
                            <i class="fas fa-plus"></i> Add Consumer
                        </button>
                    </div>
                    <?php $consumers = $conn->query("SELECT * FROM consumers"); ?>
                    <div class="table-responsive">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Login ID</th>
                                    <th>Name</th>
                                    <th>Email</th>
                                    <th>Phone</th>
                                    <th>Address</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($row = $consumers->fetch_assoc()): ?>
                                    <tr>
                                        <form method="POST">
                                            <td><?= $row['id'] ?></td>
                                            <td><?= $row['login_id'] ?></td>
                                            <td><input name="name" value="<?= $row['name'] ?>" class="form-control"></td>
                                            <td><input name="email" value="<?= $row['email'] ?>" class="form-control"></td>
                                            <td><input name="phone" value="<?= $row['phone'] ?>" class="form-control"></td>
                                            <td><input name="address" value="<?= $row['address'] ?>" class="form-control"></td>
                                            <td>
                                                <div class="d-flex gap-2">
                                                    <input type="hidden" name="id" value="<?= $row['id'] ?>">
                                                    <button type="submit" name="update_consumer" class="btn btn-warning btn-sm">
                                                        <i class="fas fa-save"></i> Update
                                                    </button>
                                                    <form method="POST" class="d-inline">
                                                        <input type="hidden" name="delete_consumer" value="<?= $row['login_id'] ?>">
                                                        <button type="submit" class="btn btn-danger btn-sm">
                                                            <i class="fas fa-trash"></i> Delete
                                                        </button>
                                                    </form>
                                                </div>
                                            </td>
                                        </form>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            
            <!-- Manage Employees Section -->
            <div id="manageEmployees" class="section hidden animate-fade">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-user-cog"></i>
                            <span>Manage Employees</span>
                        </h3>
                        <button class="btn btn-info" data-bs-toggle="modal" data-bs-target="#addEmployeeModal">
                            <i class="fas fa-plus"></i> Add Employee
                        </button>
                    </div>
                    <?php $employees = $conn->query("SELECT * FROM employees"); ?>
                    <div class="table-responsive">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Login ID</th>
                                    <th>Name</th>
                                    <th>Email</th>
                                    <th>Phone</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($row = $employees->fetch_assoc()): ?>
                                    <tr>
                                        <form method="POST">
                                            <td><?= $row['id'] ?></td>
                                            <td><?= $row['login_id'] ?></td>
                                            <td><input name="name" value="<?= $row['name'] ?>" class="form-control"></td>
                                            <td><input name="email" value="<?= $row['email'] ?>" class="form-control"></td>
                                            <td><input name="phone" value="<?= $row['phone'] ?>" class="form-control"></td>
                                            <td>
                                                <div class="d-flex gap-2">
                                                    <input type="hidden" name="id" value="<?= $row['id'] ?>">
                                                    <button type="submit" name="update_employee" class="btn btn-warning btn-sm">
                                                        <i class="fas fa-save"></i> Update
                                                    </button>
                                                    <a href="?delete_employee=<?= $row['id'] ?>" class="btn btn-danger btn-sm">
                                                        <i class="fas fa-trash"></i> Delete
                                                    </a>
                                                </div>
                                            </td>
                                        </form>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Add Consumer Modal -->
    <div class="modal fade" id="addConsumerModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST">
                    <div class="modal-header bg-success text-white">
                        <h5 class="modal-title"><i class="fas fa-user-plus"></i> Add New Consumer</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Login ID</label>
                            <input type="text" name="login_id" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Password</label>
                            <input type="password" name="password" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Name</label>
                            <input type="text" name="name" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Email</label>
                            <input type="email" name="email" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Phone</label>
                            <input type="text" name="phone" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Address</label>
                            <input type="text" name="address" class="form-control" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" name="add_consumer" class="btn btn-success">Add Consumer</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Add Employee Modal -->
    <div class="modal fade" id="addEmployeeModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST">
                    <div class="modal-header bg-info text-white">
                        <h5 class="modal-title"><i class="fas fa-user-plus"></i> Add New Employee</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Login ID</label>
                            <input type="text" name="login_id" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Password</label>
                            <input type="password" name="password" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Name</label>
                            <input type="text" name="name" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Email</label>
                            <input type="email" name="email" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Phone</label>
                            <input type="text" name="phone" class="form-control" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" name="add_employee" class="btn btn-info">Add Employee</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function showSection(sectionId) {
            // Hide all sections
            document.querySelectorAll('.section').forEach(section => {
                section.classList.add('hidden');
            });
            
            // Show selected section
            document.getElementById(sectionId).classList.remove('hidden');
            
            // Update active nav item
            document.querySelectorAll('.nav-item').forEach(item => {
                item.classList.remove('active');
            });
            
            // Find and activate the corresponding nav item
            const navItems = document.querySelectorAll('.nav-item');
            for (let item of navItems) {
                if (item.getAttribute('onclick').includes(sectionId)) {
                    item.classList.add('active');
                    break;
                }
            }
            
            // Update page title
            const titleMap = {
                'viewConsumers': 'View Consumers',
                'viewEmployees': 'View Employees',
                'manageConsumers': 'Manage Consumers',
                'manageEmployees': 'Manage Employees'
            };
            
            document.getElementById('current-section-title').textContent = titleMap[sectionId];
            
            // Scroll to top
            window.scrollTo({ top: 0, behavior: 'smooth' });
        }
        window.history.pushState(null, null, window.location.href);
window.onpopstate = function () {
    window.history.go(1);
};

    </script>
</body>
</html>