<?php
require_once 'includes/connect_db.php';
require_once 'includes/auth.php';

// secure the page - kick out anyone not logged in
check_login();

$user_id = $_SESSION['user_id'];
$username = $_SESSION['username'];
$user_role = $_SESSION['user_role'];

$my_products = [];

// If the user is a seller --> load their custom inventory list
if ($user_role === 'Seller') {
    try {
        $sql = "SELECT * FROM products WHERE seller_id = :seller_id ORDER BY created_at DESC";
        $stmt = $pdo->prepare($sql);
        $stmt->execute(['seller_id' => $user_id]);
        $my_products = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        die("Error loading your dashboard inventory: " . $e->getMessage());
    }
}

// handle deleting an item from the database
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['delete_product_id'])) {
    $delete_id = $_POST['delete_product_id'];
    try {
        // make sure that the loggedin seller actually owns the product they are trying to delete
        $delete_sql = "DELETE FROM products WHERE id = :id AND seller_id = :seller_id";
        $delete_stmt = $pdo->prepare($delete_sql);
        $delete_stmt->execute(['id' => $delete_id, 'seller_id' => $user_id]);
        
        // Refresh page to show updated inventory list
        header("Location: dashboard.php");
        exit();
    } catch (PDOException $e) {
        die("Error deleting product: " . $e->getMessage());
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>YEBO Marketplace - Dashboard</title>
    <style>
        /* Minimalist structural resets */
        body { font-family: Arial, sans-serif; background-color: #f4f6f9; margin: 0; padding: 0; color: #333; }
        .container { max-width: 1100px; margin: 30px auto; padding: 0 20px; }
        /* Navigation Bar */
        .navbar { background: white; display: flex; justify-content: space-between; align-items: center; padding: 15px 30px; box-shadow: 0 2px 10px rgba(0,0,0,0.05); }
        .navbar h1 { margin: 0; font-size: 20px; color: #007bff; letter-spacing: 1px; }
        .nav-links a { text-decoration: none; font-size: 14px; margin-left: 20px; transition: 0.2s; }
        .nav-links .back-home { color: #555; }
        .nav-links .back-home:hover { color: #007bff; }
        .nav-links .logout { color: #dc3545; font-weight: bold; }
        .nav-links .logout:hover { text-decoration: underline; }
        /* Dashboard Header Area */
        .welcome-box { background: white; padding: 25px; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.04); margin-bottom: 25px; }
        .welcome-box h2 { margin: 0 0 5px 0; font-size: 24px; color: #222; }
        .welcome-box p { margin: 0; color: #666; font-size: 14px; }
        .role-badge { background: #e1ecf4; color: #007bff; padding: 3px 8px; border-radius: 4px; font-weight: bold; font-size: 12px; }
        /* Table  */
        .inventory-card { background: white; padding: 30px; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.04); }
        .inventory-card h3 { margin-top: 0; margin-bottom: 20px; color: #444; }
        table { width: 100%; border-collapse: collapse; text-align: left; font-size: 14px; }
        th { background: #f8f9fa; padding: 12px 15px; color: #555; border-bottom: 2px solid #eee; }
        td { padding: 14px 15px; border-bottom: 1px solid #eee; vertical-align: middle; }
        /* Status Indicators */
        .badge { padding: 4px 8px; border-radius: 4px; font-size: 11px; font-weight: bold; text-transform: uppercase; display: inline-block; }
        .badge-active { background: #28a745; color: white; }
        .badge-sold { background: #dc3545; color: white; }
        /* actionbuttons */
        .btn { display: inline-block; padding: 8px 14px; text-decoration: none; border-radius: 5px; font-size: 13px; font-weight: bold; transition: 0.2s; border: none; cursor: pointer; }
        .btn-add { background: #007bff; color: white; margin-bottom: 20px; padding: 10px 16px; font-size: 14px; }
        .btn-add:hover { background: #0056b3; }
        .btn-edit { background: #e1ecf4; color: #007bff; margin-right: 10px; }
        .btn-edit:hover { background: #007bff; color: white; }
        .btn-delete { background: none; color: #dc3545; text-decoration: underline; font-weight: bold; font-size: 13px; }
        .btn-delete:hover { color: #bd2130; }
        
        .locked-text { color: #888; font-style: italic; margin-right: 15px; font-size: 13px; }
    </style>
</head>
<body>

    <div class="navbar">
        <h1>YEBO Marketplace</h1>
        <div class="nav-links">
            <a href="index.php" class="back-home">&larr; Go to Storefront</a>
            <a href="logout.php" class="logout">Log Out</a>
        </div>
    </div>

    <div class="container">

        <div class="welcome-box">
            <h2>Welcome back, <?php echo htmlspecialchars($username); ?>!</h2>
            <p>Account Clearance Level: <span class="role-badge"><?php echo htmlspecialchars($user_role); ?></span></p>
        </div>

        <?php if ($user_role === 'Seller'): ?>
            <a href="create_listing.php" class="btn btn-add">+ Create a New Product Listing</a>
            
            <div class="inventory-card">
                <h3>Your Inventory Log</h3>
                <?php if (count($my_products) > 0): ?>
                    <table>
                        <thead>
                            <tr>
                                <th>Product Title</th>
                                <th>Price</th>
                                <th>Status</th>
                                <th>Date Listed</th>
                                <th style="text-align: right;">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($my_products as $item): ?>
                                <tr>
                                    <td><strong><?php echo htmlspecialchars($item['title']); ?></strong></td>
                                    <td>R <?php echo number_format($item['price'], 2); ?></td>
                                    <td>
                                        <?php if (strcasecmp($item['status'], 'sold') === 0): ?>
                                            <span class="badge badge-sold">Sold</span>
                                        <?php else: ?>
                                            <span class="badge badge-active">Active</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo date('Y-m-d', strtotime($item['created_at'])); ?></td>
                                    <td style="text-align: right;">
                                        <?php if (strcasecmp($item['status'], 'sold') !== 0): ?>
                                            <a href="edit_listing.php?id=<?php echo $item['id']; ?>" class="btn btn-edit">Edit</a>
                                        <?php else: ?>
                                            <span class="locked-text">Locked</span>
                                        <?php endif; ?>

                                        <form action="dashboard.php" method="POST" onsubmit="return confirm('Are you sure you want to delete this listing permanently?');" style="display:inline;">
                                            <input type="hidden" name="delete_product_id" value="<?php echo $item['id']; ?>">
                                            <button type="submit" class="btn btn-delete">Delete Record</button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <p style="color: #777; font-style: italic; margin: 0;">You haven't listed any items for sale yet.</p>
                <?php endif; ?>
            </div>

        <?php elseif ($user_role === 'Buyer'): ?>
            <div class="inventory-card">
                <h3>Buyer Activity Center</h3>
                <p style="line-height: 1.6; color: #555; margin-bottom: 20px;">Browse our marketplace storefront to find items you love, and use our instant sim checkout page to run transaction tests.</p>
                <a href="index.php" class="btn btn-add">Browse Marketplace Items</a>
            </div>
        
        <?php else: ?>
            <div class="inventory-card">
                <h3>System Administrator Controls</h3>
                <p style="color: #555; margin-bottom: 20px;">Access level verified. Global marketplace tracking and core oversight panels are active.</p>
                <a href="admin/index.php" class="btn btn-add" style="background: #343a40;">Enter Admin Control Panel &rarr;</a>
            </div>
        <?php endif; ?>

    </div>

</body>
</html>