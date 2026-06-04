<?php
// ROUTING & SECURITY CHECK

require_once '../includes/connect_db.php';
require_once '../includes/auth.php';

// secure the page make sure thatthey are logged in
check_login();

// RBAC Guard --> if they arent an Admin take them back to noraml dashboard
if ($_SESSION['user_role'] !== 'Admin') {
    header("Location: ../dashboard.php");
    exit();
}

$admin_name = $_SESSION['username'];

// FETCH ENTERPRISE SYSTEM METRICS
$total_users = 0;
$total_products = 0;

try {
    // Count total registered accounts
    $user_count_stmt = $pdo->query("SELECT COUNT(*) FROM users");
    $total_users = $user_count_stmt->fetchColumn();

    // count total active marketplace items
    $product_count_stmt = $pdo->query("SELECT COUNT(*) FROM products");
    $total_products = $product_count_stmt->fetchColumn();

} catch (PDOException $e) {
    die("System Metrics Error: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>YEBO Marketplace - Admin Command Center</title>
    <link rel="stylesheet" href="../assets/css/styles.css">
</head>
<body style="padding: 20px; font-family: Arial, sans-serif;">

    <div style="display: flex; justify-content: space-between; align-items: center; border-bottom: 3px solid #333; padding-bottom: 15px; margin-bottom: 30px;">
        <h1> YEBO Admin Command Center </h1>
        <p>Logged in as: <strong><?php echo htmlspecialchars($admin_name); ?></strong> | <a href="../dashboard.php">Exit Control Panel</a></p>
    </div>

    <h2>System Overview</h2>
    
    <div style="display: flex; gap: 20px; margin-bottom: 40px;">
        <div style="background: #f4f4f4; padding: 20px; border-radius: 8px; border-left: 5px solid #007bff; min-width: 150px;">
            <h3 style="margin: 0; color: #555;">Total Users</h3>
            <p style="font-size: 32px; font-weight: bold; margin: 10px 0 0 0;"><?php echo $total_users; ?></p>
        </div>
        
        <div style="background: #f4f4f4; padding: 20px; border-radius: 8px; border-left: 5px solid #28a745; min-width: 150px;">
            <h3 style="margin: 0; color: #555;">Active Listings</h3>
            <p style="font-size: 32px; font-weight: bold; margin: 10px 0 0 0;"><?php echo $total_products; ?></p>
        </div>
    </div>

    <hr style="border: 1px solid #eee; margin-bottom: 30px;">

    <h2>Administrative Management Controls</h2>
    <p>Select a module below to moderate and maintain the YEBO Marketplace platform ecosystem:</p>
    
    <div style="display: flex; gap: 15px; margin-top: 20px;">
        <a href="manage_users.php" style="background: #343a40; color: white; padding: 15px 25px; text-decoration: none; border-radius: 5px; font-weight: bold;">
            Manage User Accounts
        </a>
        <a href="manage_listings.php" style="background: #343a40; color: white; padding: 15px 25px; text-decoration: none; border-radius: 5px; font-weight: bold;">
            Manage Product Listings
        </a>
    </div>

</body>
</html>