<?php
// ATTACH THE LIVE SECURITY CHECK AND CONNECTION
require_once 'includes/connect_db.php';
require_once 'includes/auth.php';

// Force the browser to verify the user is logged in. 
// If they aren't, check_login() will instantly bounce them to login.php
check_login();

// Grab their session data for easy usage
$username = $_SESSION['username'];
$role     = $_SESSION['user_role'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>YEBO Marketplace - Dashboard</title>
</head>
<body>

    <p align="right">Logged in as: <strong><?php echo htmlspecialchars($username); ?></strong> (<?php echo htmlspecialchars($role); ?>) | <a href="logout.php">Log Out</a></p>

    <h1>Welcome to your YEBO Dashboard, <?php echo htmlspecialchars($username); ?>!</h1>
    <p>This is your central control center for trading in the marketplace.</p>

    <hr>

    <?php if ($role === 'Seller'): ?>
        <h3>Seller Management Panel</h3>
        <ul>
            <li><a href="create_listing.php"><strong>+ Create a New Product Listing</strong></a></li>
            <li><a href="#">View My Active Listings</a></li>
            <li><a href="#">Track Received Customer Orders</a></li>
        </ul>
    <?php else: ?>
        <h3>Buyer Activity Panel</h3>
        <ul>
            <li><a href="index.php"><strong>Browse the Local Marketplace</strong></a></li>
            <li><a href="#">Track My Purchases / Escrow Status</a></li>
            <li><a href="#">My Inbox Messages</a></li>
        </ul>
    <?php endif; ?>

</body>
</html>