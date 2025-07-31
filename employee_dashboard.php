<?php
session_start();
$conn = new mysqli("localhost", "root", "", "electricity_billing_system");

// Display success message if exists
if (isset($_SESSION['message'])) {
    $message = $_SESSION['message'];
    $message_type = $_SESSION['message_type'];
    echo "<div class='alert alert-$message_type'>$message</div>";
    unset($_SESSION['message']);
    unset($_SESSION['message_type']);
}

// Insert Consumption Record
if (isset($_POST['add_consumption'])) {
    $login_id = $_POST['login_id'];
    $usage_unit = $_POST['usage_unit'];
    $due_date = $_POST['due_date'];
    $payment_status = $_POST['payment_status'];

    $today = date('Y-m-d');
    if ($due_date < $today) {
        $_SESSION['message'] = 'Due date cannot be in the past.';
        $_SESSION['message_type'] = 'danger';
        header("Location: ".$_SERVER['PHP_SELF']);
        exit();
    }

    $stmt = $conn->prepare("INSERT INTO consumption_records (login_id, usage_unit, due_date, payment_status) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("sdss", $login_id, $usage_unit, $due_date, $payment_status);
    
    if ($stmt->execute()) {
        $_SESSION['message'] = 'Consumption record added successfully!';
        $_SESSION['message_type'] = 'success';
    } else {
        $_SESSION['message'] = 'Error adding record: ' . $conn->error;
        $_SESSION['message_type'] = 'danger';
    }
    
    header("Location: ".$_SERVER['PHP_SELF']);
    exit();
}

// Update Consumption Record
if (isset($_POST['update_consumption'])) {
    $record_id = $_POST['record_id'];
    $usage_unit = $_POST['usage_unit'];
    $due_date = $_POST['due_date'];
    $payment_status = $_POST['payment_status'];

    $today = date('Y-m-d');
    if ($due_date < $today) {
        $_SESSION['message'] = 'Due date cannot be in the past.';
        $_SESSION['message_type'] = 'danger';
        header("Location: ".$_SERVER['PHP_SELF']);
        exit();
    }

    $stmt = $conn->prepare("UPDATE consumption_records SET usage_unit = ?, due_date = ?, payment_status = ? WHERE id = ?");
    $stmt->bind_param("dssi", $usage_unit, $due_date, $payment_status, $record_id);
    
    if ($stmt->execute()) {
        $_SESSION['message'] = 'Record updated successfully!';
        $_SESSION['message_type'] = 'success';
    } else {
        $_SESSION['message'] = 'Error updating record: ' . $conn->error;
        $_SESSION['message_type'] = 'danger';
    }
    
    header("Location: ".$_SERVER['PHP_SELF']);
    exit();
}

// Fetch all consumers
$consumers = $conn->query("SELECT * FROM consumers") or die("Error: " . $conn->error);

// Fetch all consumption records and auto-mark overdue
$records_query = "SELECT cr.id, c.login_id, c.name, cr.usage_unit, cr.due_date, cr.payment_status 
                  FROM consumption_records cr 
                  JOIN consumers c ON cr.login_id = c.login_id";
$records = $conn->query($records_query) or die("Error: " . $conn->error);

// Auto-mark overdue if due date < today and not paid
while ($row = $records->fetch_assoc()) {
    $status = $row['payment_status'];
    $due = $row['due_date'];
    $id = $row['id'];
    $today = date('Y-m-d');

    if ($status == 'Pending' && $due < $today) {
        $conn->query("UPDATE consumption_records SET payment_status = 'Overdue' WHERE id = $id");
    }
}
$records = $conn->query($records_query); // Re-fetch updated records

// Filtered Consumers
$filtered_query = "SELECT cr.id, c.login_id, c.name, cr.usage_unit, cr.due_date, cr.payment_status 
                   FROM consumption_records cr 
                   JOIN consumers c ON cr.login_id = c.login_id 
                   WHERE cr.due_date <= CURDATE() + INTERVAL 3 DAY";
$filtered = $conn->query($filtered_query) or die($conn->error);

// Auto-mark overdue for filtered consumers
while ($row = $filtered->fetch_assoc()) {
    $status = $row['payment_status'];
    $due = $row['due_date'];
    $id = $row['id'];
    $today = date('Y-m-d');

    if ($status == 'Pending' && $due < $today) {
        $conn->query("UPDATE consumption_records SET payment_status = 'Overdue' WHERE id = $id");
    }
}
$filtered = $conn->query($filtered_query); // Re-fetch updated records
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Employee Dashboard</title>
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
    
    .form-control, .form-select {
        background: white;
        border: 1px solid var(--border-color);
        color: var(--text-color);
        padding: 12px 16px;
        border-radius: 8px;
        width: 100%;
        margin-bottom: 1rem;
        transition: all 0.3s ease;
    }
    
    .form-control:focus, .form-select:focus {
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
        text-decoration: none;
    }
    
    .btn-primary {
        background: var(--primary-color);
        color: white;
    }
    
    .btn-primary:hover {
        background: #3a56d5;
        transform: translateY(-2px);
        color: white;
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
    
    .btn-warning:hover {
        background: #e67e22;
        color: white;
    }
    
    .btn-danger {
        background: var(--danger-color);
        color: white;
    }
    
    .btn-secondary {
        background: #6c757d;
        color: white;
    }
    
    .btn-secondary:hover {
        background: #5a6268;
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

    /* Override custom modal styles to use Bootstrap defaults */
    .modal {
        /* Remove custom modal styles to let Bootstrap handle it */
    }
    
    .modal-dialog {
        /* Remove custom modal-dialog styles to let Bootstrap handle it */
    }
    
    .modal-content {
        /* Remove custom modal-content styles to let Bootstrap handle it */
    }
    
    /* Custom modal header styling */
    .modal-header.bg-warning {
        background-color: var(--warning-color) !important;
        color: white !important;
    }
    
    .modal-header.bg-warning .btn-close {
        filter: invert(1);
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
                <a href="#" class="nav-item active" onclick="showSection('consumerList')">
                    <i class="fas fa-users"></i>
                    <span>Consumer List</span>
                </a>
                <a href="#" class="nav-item" onclick="showSection('addConsumption')">
                    <i class="fas fa-file-invoice-dollar"></i>
                    <span>Enter Consumption</span>
                </a>
                <a href="#" class="nav-item" onclick="showSection('dueConsumers')">
                    <i class="fas fa-clock"></i>
                    <span>Upcoming Dues</span>
                </a>
                <a href="#" class="nav-item" onclick="showSection('viewRecords')">
                    <i class="fas fa-list"></i>
                    <span>View Records</span>
                </a>
            </div>
            
            <div class="user-profile">
                <div class="user-avatar">
                    <i class="fas fa-user-tie"></i>
                </div>
                <div class="user-info">
                    <h4>Employee</h4>
                    <p>Billing Staff</p>
                </div>
            </div>
        </div>
        
        <!-- Main Content -->
        <div class="main-content">
            <div class="dashboard-header">
                <h1 class="page-title" id="current-section-title">Consumer List</h1>
                <a href="logout.php" class="logout-btn">
                    <i class="fas fa-sign-out-alt"></i> Logout
                </a>
            </div>
            
            <!-- Display messages -->
            <?php if (isset($message)): ?>
                <div class="alert alert-<?= $message_type ?>">
                    <i class="fas <?= $message_type == 'success' ? 'fa-check-circle' : 'fa-exclamation-circle' ?>"></i>
                    <?= $message ?>
                </div>
            <?php endif; ?>
            
            <!-- Consumer List Section -->
            <div id="consumerList" class="section animate-fade">
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
                                    <th>ID</th>
                                    <th>Login ID</th>
                                    <th>Name</th>
                                    <th>Email</th>
                                    <th>Phone</th>
                                    <th>Address</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($consumers as $c): ?>
                                    <tr>
                                        <td><?= $c['id'] ?></td>
                                        <td><?= $c['login_id'] ?></td>
                                        <td><?= $c['name'] ?></td>
                                        <td><?= $c['email'] ?></td>
                                        <td><?= $c['phone'] ?></td>
                                        <td><?= $c['address'] ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            
            <!-- Add Consumption Section -->
            <div id="addConsumption" class="section hidden animate-fade">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-file-invoice-dollar"></i>
                            <span>Enter Consumption</span>
                        </h3>
                    </div>
                    <div class="card-body">
                        <form method="POST" class="form-card">
                            <div class="mb-3">
                                <label class="form-label">Consumer</label>
                                <select name="login_id" class="form-select" required>
                                    <option value="">Select Consumer</option>
                                    <?php foreach ($consumers as $c): ?>
                                        <option value="<?= $c['login_id'] ?>"><?= $c['name'] ?> (<?= $c['login_id'] ?>)</option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Usage Unit</label>
                                <input type="number" name="usage_unit" step="0.01" class="form-control" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Due Date</label>
                                <input type="date" name="due_date" class="form-control" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Payment Status</label>
                                <select name="payment_status" class="form-select" required>
                                    <option value="Pending">Pending</option>
                                    <option value="Paid">Paid</option>
                                </select>
                            </div>
                            <button type="submit" name="add_consumption" class="btn btn-primary">
                                <i class="fas fa-plus"></i> Add Record
                            </button>
                        </form>
                    </div>
                </div>
            </div>
            
            <!-- Due Consumers Section -->
            <div id="dueConsumers" class="section hidden animate-fade">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-clock"></i>
                            <span>Upcoming Dues (Within 3 Days)</span>
                        </h3>
                    </div>
                    <div class="table-responsive">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Login ID</th>
                                    <th>Name</th>
                                    <th>Usage Unit</th>
                                    <th>Due Date</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($filtered as $f): ?>
                                    <tr>
                                        <td><?= $f['id'] ?></td>
                                        <td><?= $f['login_id'] ?></td>
                                        <td><?= $f['name'] ?></td>
                                        <td><?= $f['usage_unit'] ?></td>
                                        <td><?= $f['due_date'] ?></td>
                                        <td>
                                            <span class="status-badge <?php
                                                echo $f['payment_status'] == 'Paid' ? 'status-paid' : 
                                                     ($f['payment_status'] == 'Overdue' ? 'status-overdue' : 'status-pending');
                                            ?>">
                                                <i class="fas <?php
                                                    echo $f['payment_status'] == 'Paid' ? 'fa-check-circle' : 
                                                         ($f['payment_status'] == 'Overdue' ? 'fa-exclamation-circle' : 'fa-clock');
                                                ?>"></i>
                                                <?= $f['payment_status'] ?>
                                            </span>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            
            <!-- View Records Section -->
            <div id="viewRecords" class="section hidden animate-fade">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-list"></i>
                            <span>All Consumption Records</span>
                        </h3>
                    </div>
                    <div class="table-responsive">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Login ID</th>
                                    <th>Name</th>
                                    <th>Usage Unit</th>
                                    <th>Due Date</th>
                                    <th>Status</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($records as $r): ?>
                                    <tr>
                                        <td><?= $r['id'] ?></td>
                                        <td><?= $r['login_id'] ?></td>
                                        <td><?= $r['name'] ?></td>
                                        <td><?= $r['usage_unit'] ?></td>
                                        <td><?= $r['due_date'] ?></td>
                                        <td>
                                            <span class="status-badge <?php
                                                echo $r['payment_status'] == 'Paid' ? 'status-paid' : 
                                                     ($r['payment_status'] == 'Overdue' ? 'status-overdue' : 'status-pending');
                                            ?>">
                                                <i class="fas <?php
                                                    echo $r['payment_status'] == 'Paid' ? 'fa-check-circle' : 
                                                         ($r['payment_status'] == 'Overdue' ? 'fa-exclamation-circle' : 'fa-clock');
                                                ?>"></i>
                                                <?= $r['payment_status'] ?>
                                            </span>
                                        </td>
                                        <td>
                                            <button type="button" class="btn btn-warning btn-sm" data-bs-toggle="modal" data-bs-target="#editModal<?= $r['id'] ?>">
                                                <i class="fas fa-edit"></i> Edit
                                            </button>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Edit Modals (Place outside the main content but inside body) -->
  <!-- Edit Modals (Place outside the main content but inside body) -->
    <?php foreach ($records as $r): ?>
    <div class="modal fade" id="editModal<?= $r['id'] ?>" tabindex="-1" aria-labelledby="editModalLabel<?= $r['id'] ?>" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST" action="">
                    <div class="modal-header bg-warning text-white">
                        <h5 class="modal-title" id="editModalLabel<?= $r['id'] ?>">
                            <i class="fas fa-edit me-2"></i>Edit Consumption Record #<?= $r['id'] ?>
                        </h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="record_id" value="<?= $r['id'] ?>">
                        
                        <div class="mb-3">
                            <label for="consumer<?= $r['id'] ?>" class="form-label">Consumer</label>
                            <input type="text" class="form-control" id="consumer<?= $r['id'] ?>" value="<?= htmlspecialchars($r['name']) ?> (<?= htmlspecialchars($r['login_id']) ?>)" readonly>
                        </div>
                        
                        <div class="mb-3">
                            <label for="usage_unit<?= $r['id'] ?>" class="form-label">Usage Unit</label>
                            <input type="number" class="form-control" id="usage_unit<?= $r['id'] ?>" name="usage_unit" step="0.01" value="<?= htmlspecialchars($r['usage_unit']) ?>" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="due_date<?= $r['id'] ?>" class="form-label">Due Date</label>
                            <input type="date" class="form-control" id="due_date<?= $r['id'] ?>" name="due_date" value="<?= htmlspecialchars($r['due_date']) ?>" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="payment_status<?= $r['id'] ?>" class="form-label">Payment Status</label>
                            <select class="form-select" id="payment_status<?= $r['id'] ?>" name="payment_status" required>
                                <option value="Pending" <?= $r['payment_status'] == 'Pending' ? 'selected' : '' ?>>Pending</option>
                                <option value="Paid" <?= $r['payment_status'] == 'Paid' ? 'selected' : '' ?>>Paid</option>
                                <option value="Overdue" <?= $r['payment_status'] == 'Overdue' ? 'selected' : '' ?>>Overdue</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                            <i class="fas fa-times me-1"></i>Cancel
                        </button>
                        <button type="submit" name="update_consumption" class="btn btn-warning">
                            <i class="fas fa-save me-1"></i>Update Record
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <?php endforeach; ?>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- JavaScript for section switching -->
    <script>
        function showSection(sectionName) {
            // Hide all sections
            const sections = document.querySelectorAll('.section');
            sections.forEach(section => {
                section.classList.add('hidden');
            });
            
            // Show selected section
            const targetSection = document.getElementById(sectionName);
            if (targetSection) {
                targetSection.classList.remove('hidden');
            }
            
            // Update active nav item
            const navItems = document.querySelectorAll('.nav-item');
            navItems.forEach(item => {
                item.classList.remove('active');
            });
            
            event.target.closest('.nav-item').classList.add('active');
            
            // Update page title
            const titles = {
                'consumerList': 'Consumer List',
                'addConsumption': 'Enter Consumption',
                'dueConsumers': 'Upcoming Dues',
                'viewRecords': 'View Records'
            };
            
            const titleElement = document.getElementById('current-section-title');
            if (titleElement && titles[sectionName]) {
                titleElement.textContent = titles[sectionName];
            }
        }
        
        // Auto-hide alerts after 5 seconds
        document.addEventListener('DOMContentLoaded', function() {
            const alerts = document.querySelectorAll('.alert');
            alerts.forEach(alert => {
                setTimeout(() => {
                    alert.style.opacity = '0';
                    setTimeout(() => {
                        alert.remove();
                    }, 300);
                }, 5000);
            });
        });
    </script>
</body>
</html>