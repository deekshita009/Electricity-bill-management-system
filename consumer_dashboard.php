<?php
session_start();
if (!isset($_SESSION['login_id'])) {
    header("Location: login.php");
    exit();
}
$login_id = $_SESSION['login_id'];
include 'db_connection.php';

// Fetch consumer details with error handling
$consumer_query = "SELECT name, email, address, phone FROM consumers WHERE login_id = '$login_id'";
$consumer_result = mysqli_query($conn, $consumer_query);

if (!$consumer_result) {
    die("Database query failed: " . mysqli_error($conn));
}

$consumer = mysqli_fetch_assoc($consumer_result);

// Check if consumer data was found
if (!$consumer) {
    die("No consumer record found for your account. Please contact support.");
}

$calc_success = false;

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['calculate_bill'])) {
    $usage_unit = floatval($_POST['usage_unit']);
    $original_usage_unit = $usage_unit; // Store the original value before slab calculation
    $bill_amount = 0;

    // Slab logic
    $query = "SELECT * FROM tariff_slabs ORDER BY min_unit ASC";
    $result = mysqli_query($conn, $query);
    
    if ($result) {
        $previous_max_unit = 0; // To handle each range properly
        
        while ($row = mysqli_fetch_assoc($result)) {
            $min_unit = $row['min_unit'];
            $max_unit = $row['max_unit'];
            $rate_per_unit = $row['rate_per_unit'];

            // Determine how much usage falls into this slab range
            if ($usage_unit > $min_unit) {
                // If there's no upper limit, we use the usage_unit itself
                $upper_limit = ($max_unit === NULL) ? $usage_unit : min($usage_unit, $max_unit);
                
                // Calculate units within this range
                $units_in_range = $upper_limit - $min_unit + 1;
                
                // Calculate amount for this slab
                $bill_amount += $units_in_range * $rate_per_unit;
                
                // Decrease the usage to reflect units already calculated
                $usage_unit -= $units_in_range;

                // If usage is finished, break the loop
                if ($usage_unit <= 0) {
                    break;
                }
            }
        }
    }
    $bill_amount = round($bill_amount, 2);
    $login_id = $_SESSION['login_id'];

    // Fetch name and address of consumer
    $con_query = "SELECT name, address FROM consumers WHERE login_id = '$login_id'";
    $con_result = mysqli_query($conn, $con_query);

    if ($con_result && mysqli_num_rows($con_result) > 0) {
        $con = mysqli_fetch_assoc($con_result);
        $name = $con['name'];
        $address = $con['address'];

        $payment_status = 'Paid'; // Set default

        // Insert full bill entry using the original usage unit
        $insert_query = "INSERT INTO bill_receipt 
            (login_id, name, address, usage_unit, amount, payment_status, created_at)
            VALUES 
            ('$login_id', '$name', '$address', '$original_usage_unit', '$bill_amount', '$payment_status', NOW())";

        if (mysqli_query($conn, $insert_query)) {
            $calc_success = true;
        } else {
            echo "Error inserting bill: " . mysqli_error($conn);
        }
    } else {
        echo "Error: Consumer not found.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Energy Dashboard</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
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
    
    /* Welcome Section */
    .welcome-banner {
        background: linear-gradient(135deg, rgba(67, 97, 238, 0.1), rgba(114, 9, 183, 0.1));
        padding: 2rem;
        border-radius: 12px;
        margin-bottom: 2rem;
        border: 1px solid var(--border-color);
    }
    
    .welcome-text {
        font-size: 1.5rem;
        font-weight: 700;
        margin-bottom: 0.5rem;
        color: var(--text-color);
    }
    
    .welcome-subtext {
        color: var(--text-light);
        margin-bottom: 1.5rem;
    }
    
    .user-details-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
        gap: 1rem;
        margin-top: 1.5rem;
    }
    
    .detail-card {
        background: var(--card-bg);
        border-radius: 8px;
        padding: 1rem;
        display: flex;
        align-items: center;
        gap: 12px;
        border: 1px solid var(--border-color);
    }
    
    .detail-icon {
        width: 40px;
        height: 40px;
        border-radius: 8px;
        background: rgba(67, 97, 238, 0.1);
        display: flex;
        align-items: center;
        justify-content: center;
        color: var(--primary-color);
    }
    
    .detail-text h5 {
        font-size: 0.85rem;
        color: var(--text-light);
        margin-bottom: 4px;
    }
    
    .detail-text p {
        font-weight: 500;
        color: var(--text-color);
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
    
    .btn-lg {
        padding: 16px 32px;
        font-size: 1rem;
    }
    
    /* Bill Card */
    .bill-card {
        background: var(--card-bg);
        border-radius: 12px;
        overflow: hidden;
        border: 1px solid var(--border-color);
    }
    
    .bill-header {
        background: linear-gradient(90deg, var(--primary-color), var(--secondary-color));
        padding: 1.5rem;
        color: white;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    
    .bill-title {
        font-size: 1.25rem;
        font-weight: 600;
    }
    
    .bill-status {
        background: rgba(255, 255, 255, 0.2);
        padding: 6px 12px;
        border-radius: 20px;
        font-size: 0.85rem;
        font-weight: 500;
    }
    
    .bill-body {
        padding: 1.5rem;
    }
    
    .bill-row {
        display: flex;
        justify-content: space-between;
        margin-bottom: 1rem;
        padding-bottom: 1rem;
        border-bottom: 1px solid var(--border-color);
    }
    
    .bill-row:last-child {
        border-bottom: none;
        margin-bottom: 0;
        padding-bottom: 0;
    }
    
    .bill-label {
        color: var(--text-light);
    }
    
    .bill-value {
        font-weight: 500;
        color: var(--text-color);
    }
    
    .bill-amount {
        font-size: 1.5rem;
        font-weight: 700;
        color: var(--success-color);
    }
    
    /* Grid Layout */
    .grid-cols-2 {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 1.5rem;
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
        
        .grid-cols-2 {
            grid-template-columns: 1fr;
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
    
    .d-grid {
        display: grid;
    }
    
    .gap-2 {
        gap: 1rem;
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
                <a href="#" class="nav-item active" onclick="showSection('welcome')">
                    <i class="fas fa-home"></i>
                    <span>Dashboard</span>
                </a>
                <a href="#" class="nav-item" onclick="showSection('viewUsage')">
                    <i class="fas fa-chart-line"></i>
                    <span>Usage History</span>
                </a>
                <a href="#" class="nav-item" onclick="showSection('calculateBill')">
                    <i class="fas fa-calculator"></i>
                    <span>Calculate Bill</span>
                </a>
                <a href="#" class="nav-item" onclick="showSection('generateBill')">
                    <i class="fas fa-file-invoice-dollar"></i>
                    <span>My Bills</span>
                </a>
            </div>
            
            <div class="user-profile">
                <div class="user-avatar">
                    <?php echo strtoupper(substr($consumer['name'], 0, 1)); ?>
                </div>
                <div class="user-info">
                    <h4><?php echo htmlspecialchars($consumer['name']); ?></h4>
                    <p>Consumer</p>
                </div>
            </div>
        </div>
        
        <!-- Main Content -->
        <div class="main-content">
            <div class="dashboard-header">
                <h1 class="page-title" id="current-section-title">Dashboard</h1>
                <a href="logout.php" class="logout-btn">
                    <i class="fas fa-sign-out-alt"></i> Logout
                </a>
            </div>
            
            <!-- Welcome Section -->
            <div id="welcome" class="section animate-fade">
                <div class="welcome-banner">
                    <h2 class="welcome-text">Welcome  <?php echo htmlspecialchars($consumer['name']); ?>!</h2>
                    <p class="welcome-subtext">Manage your energy consumption and billing in one place</p>
                </div>
                
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-user-circle"></i>
                            <span>Your Profile</span>
                        </h3>
                    </div>
                    <div class="user-details-grid">
                        <div class="detail-card">
                            <div class="detail-icon">
                                <i class="fas fa-envelope"></i>
                            </div>
                            <div class="detail-text">
                                <h5>Email Address</h5>
                                <p><?php echo htmlspecialchars($consumer['email']); ?></p>
                            </div>
                        </div>
                        
                        <div class="detail-card">
                            <div class="detail-icon">
                                <i class="fas fa-phone"></i>
                            </div>
                            <div class="detail-text">
                                <h5>Phone Number</h5>
                                <p><?php echo htmlspecialchars($consumer['phone']); ?></p>
                            </div>
                        </div>
                        
                        <div class="detail-card">
                            <div class="detail-icon">
                                <i class="fas fa-map-marker-alt"></i>
                            </div>
                            <div class="detail-text">
                                <h5>Service Address</h5>
                                <p><?php echo htmlspecialchars($consumer['address']); ?></p>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-bolt"></i>
                            <span>Quick Actions</span>
                        </h3>
                    </div>
                    <div class="grid-cols-2">
                        <div class="card">
                            <h4 class="card-title">
                                <i class="fas fa-calculator text-gradient"></i>
                                <span>Calculate Bill</span>
                            </h4>
                            <p>Estimate your upcoming electricity bill based on your usage</p>
                            <button class="btn btn-primary mt-4" onclick="showSection('calculateBill')">
                                <i class="fas fa-calculator"></i> Calculate Now
                            </button>
                        </div>
                        
                        <div class="card">
                            <h4 class="card-title">
                                <i class="fas fa-file-invoice-dollar text-gradient"></i>
                                <span>View Bills</span>
                            </h4>
                            <p>Access your billing history and download past receipts</p>
                            <button class="btn btn-primary mt-4" onclick="showSection('generateBill')">
                                <i class="fas fa-file-invoice-dollar"></i> View Bills
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- View Usage Section -->
            <div id="viewUsage" class="section hidden animate-fade">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-chart-line"></i>
                            <span>Your Energy Usage</span>
                        </h3>
                    </div>
                    
                    <?php
                    $usage_query = "SELECT usage_unit, due_date, payment_status FROM consumption_records WHERE login_id = '$login_id'";
                    $usage_result = mysqli_query($conn, $usage_query);
                    
                    if (!$usage_result) {
                        echo '<div class="alert alert-danger"><i class="fas fa-exclamation-circle"></i> Error fetching usage records: ' . htmlspecialchars(mysqli_error($conn)) . '</div>';
                    } elseif (mysqli_num_rows($usage_result) > 0) {
                        echo '<div class="table-responsive">
                                <table class="data-table">
                                    <thead>
                                        <tr>
                                            <th>Usage (kWh)</th>
                                            <th>Due Date</th>
                                            <th>Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>';
                        
                        while ($row = mysqli_fetch_assoc($usage_result)) {
                            $status_class = '';
                            if ($row['payment_status'] === 'Paid') {
                                $status_class = 'status-paid';
                            } elseif ($row['payment_status'] === 'Pending') {
                                $status_class = 'status-pending';
                            } else {
                                $status_class = 'status-overdue';
                            }
                            
                            echo '<tr>
                                    <td>' . htmlspecialchars($row['usage_unit']) . ' kWh</td>
                                    <td>' . htmlspecialchars($row['due_date']) . '</td>
                                    <td><span class="status-badge ' . $status_class . '">
                                        <i class="fas ' . 
                                        ($row['payment_status'] === 'Paid' ? 'fa-check-circle' : 
                                         ($row['payment_status'] === 'Pending' ? 'fa-clock' : 'fa-exclamation-circle')) . 
                                        '"></i> ' . htmlspecialchars($row['payment_status']) . '
                                    </span></td>
                                  </tr>';
                        }
                        
                        echo '</tbody></table></div>';
                    } else {
                        echo '<div class="alert alert-info"><i class="fas fa-info-circle"></i> No usage records found.</div>';
                    }
                    ?>
                </div>
            </div>
            
            <!-- Calculate Bill Section -->
            <div id="calculateBill" class="section hidden animate-fade">
                <div class="grid-cols-2">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">
                                <i class="fas fa-calculator"></i>
                                <span>Calculate Your Bill</span>
                            </h3>
                        </div>
                        
                        <?php if ($calc_success): ?>
                        <div class="alert alert-success">
                            <i class="fas fa-check-circle"></i> Bill calculated and stored successfully!
                        </div>
                        <?php endif; ?>
                        
                        <form method="POST" class="form-card">
                            <div class="mb-4">
                                <label for="usage_unit" class="form-label">Enter Your Usage (kWh)</label>
                                <input type="number" min="0" step="0.01" name="usage_unit" id="usage_unit" 
                                       class="form-control" placeholder="e.g. 250.5" required>
                            </div>
                            
                            <button type="submit" name="calculate_bill" class="btn btn-primary btn-lg w-100">
                                <i class="fas fa-calculator"></i> Calculate Bill
                            </button>
                        </form>
                    </div>
                    
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">
                                <i class="fas fa-info-circle"></i>
                                <span>Current Rates</span>
                            </h3>
                        </div>
                        
                        <div class="form-card">
                            <?php
                            $rate_result = mysqli_query($conn, "SELECT current_tariff FROM tariff ORDER BY created_at DESC LIMIT 1");
                            if ($rate_result && mysqli_num_rows($rate_result) > 0) {
                                $rate_data = mysqli_fetch_assoc($rate_result);
                                echo '<div class="text-center mb-4">
                                        <p class="text-muted">Current Electricity Rate</p>
                                        <h2 class="text-gradient" style="font-size: 2.5rem;">₹' . number_format($rate_data['current_tariff'], 2) . '</h2>
                                        <p class="text-muted">per kWh</p>
                                      </div>';
                            } else {
                                echo '<div class="alert alert-warning">
                                        <i class="fas fa-exclamation-triangle"></i> Rate information not available
                                      </div>';
                            }
                            ?>
                            
                            <div class="bill-tips">
                                <h4 class="mb-3"><i class="fas fa-lightbulb"></i> Energy Saving Tips</h4>
                                <ul style="list-style-type: none; padding-left: 0;">
                                    <li class="mb-2"><i class="fas fa-check-circle text-success me-2"></i> Switch to LED bulbs</li>
                                    <li class="mb-2"><i class="fas fa-check-circle text-success me-2"></i> Unplug devices when not in use</li>
                                    <li class="mb-2"><i class="fas fa-check-circle text-success me-2"></i> Use energy-efficient appliances</li>
                                    <li class="mb-2"><i class="fas fa-check-circle text-success me-2"></i> Set thermostat to optimal temperature</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Generate Bill Section -->
            <div id="generateBill" class="section hidden animate-fade">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-file-invoice-dollar"></i>
                            <span>Your Bill Receipt</span>
                        </h3>
                    </div>
                    
                    <?php
                    $receipt_query = "SELECT br.*, cr.usage_unit AS actual_usage 
                                    FROM bill_receipt br
                                    LEFT JOIN consumption_records cr ON br.login_id = cr.login_id 
                                    AND cr.created_at = (
                                        SELECT MAX(created_at) 
                                        FROM consumption_records 
                                        WHERE login_id = br.login_id
                                    )
                                    WHERE br.login_id = '$login_id' 
                                    ORDER BY br.created_at DESC LIMIT 1";
                    $receipt_result = mysqli_query($conn, $receipt_query);
                    
                    if (!$receipt_result) {
                        echo '<div class="alert alert-danger">
                                <i class="fas fa-exclamation-circle"></i> Error fetching receipt: ' . htmlspecialchars(mysqli_error($conn)) . '
                              </div>';
                    } elseif (mysqli_num_rows($receipt_result) > 0) {
                        $bill = mysqli_fetch_assoc($receipt_result);
                        
                        // Use actual_usage if available, otherwise fall back to usage_unit
                        $display_usage = isset($bill['actual_usage']) ? $bill['actual_usage'] : $bill['usage_unit'];
                        
                        echo '<div class="bill-card">
                                <div class="bill-header">
                                    <div class="bill-title">EnergyPro Bill Receipt</div>
                                    <div class="bill-status">PAID</div>
                                </div>
                                <div class="bill-body">
                                    <div class="bill-row">
                                        <div>
                                            <div class="bill-label">Bill ID</div>
                                            <div class="bill-value">' . htmlspecialchars($bill['bill_id']) . '</div>
                                        </div>
                                        <div>
                                            <div class="bill-label">Date</div>
                                            <div class="bill-value">' . date('F j, Y', strtotime($bill['created_at'])) . '</div>
                                        </div>
                                    </div>
                                    
                                    <div class="bill-row">
                                        <div>
                                            <div class="bill-label">Customer</div>
                                            <div class="bill-value">' . htmlspecialchars($bill['name']) . '</div>
                                        </div>
                                        <div>
                                            <div class="bill-label">Account</div>
                                            <div class="bill-value">' . htmlspecialchars($consumer['email']) . '</div>
                                        </div>
                                    </div>
                                    
                                    <div class="bill-row">
                                        <div>
                                            <div class="bill-label">Service Address</div>
                                            <div class="bill-value">' . htmlspecialchars($bill['address']) . '</div>
                                        </div>
                                    </div>
                                    
                                    <div class="bill-row">
                                        <div>
                                            <div class="bill-label">Energy Usage</div>
                                            <div class="bill-value">' . htmlspecialchars($display_usage) . ' kWh</div>
                                        </div>
                                    </div>
                                    
                                    <div class="bill-row mt-4">
                                        <div>
                                            <div class="bill-label">Total Amount Due</div>
                                        </div>
                                        <div class="bill-amount">₹' . number_format($bill['amount'], 2) . '</div>
                                    </div>
                                    
                                    <div class="d-grid gap-2 mt-4">
                                        <a href="payment.html" class="btn btn-primary">
                                            <i></i> Pay now
                                         </a>
                                    </div>

                                </div>
                              </div>';
                    } else {
                        echo '<div class="alert alert-info">
                                <i class="fas fa-info-circle"></i> No bill found. Please calculate your bill first.
                              </div>';
                    }
                    ?>
                </div>
            </div>
        </div>
    </div>

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
                'welcome': 'Dashboard',
                'viewUsage': 'Usage History',
                'calculateBill': 'Calculate Bill',
                'generateBill': 'My Bills'
            };
            
            document.getElementById('current-section-title').textContent = titleMap[sectionId];
            
            // Scroll to top
            window.scrollTo({ top: 0, behavior: 'smooth' });
        }
    </script>
</body>
</html>