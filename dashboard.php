<?php
require_once 'includes/connect_db.php';
require_once 'includes/auth.php';

// Secure the page - kick out anyone not logged in
check_login();

$user_id = $_SESSION['user_id'];
$username = $_SESSION['username'];
$user_role = $_SESSION['user_role'];

$my_products = [];

// IF SELLER: Fetch only the items this specific user has listed
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

// PROCESS DELETE ACTION (The "D" in CRUD)
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['delete_product_id'])) {
    $delete_id = $_POST['delete_product_id'];
    try {
        // Ensure the logged-in seller actually owns the product they are trying to delete!
        $delete_sql = "DELETE FROM products WHERE id = :id AND seller_id = :seller_id";
        $delete_stmt = $pdo->prepare($delete_sql);
        $delete_stmt->execute(['id' => $delete_id, 'seller_id' => $user_id]);
        
        // Refresh the page to show it's gone
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
    <title>YEBO Marketplace - Dashboard</title>
</head>
<body style="padding: 20px; font-family: Arial, sans-serif;">

    <p style="float: right;"><a href="logout.php">Log Out</a> | <a href="index.php" style="font-weight: bold;">Go to Storefront</a></p>
    <h1>Welcome back, <?php echo htmlspecialchars($username); ?>!</h1>
    <p>Account Type: <strong><?php echo htmlspecialchars($user_role); ?></strong></p>

    <hr>

    <?php if ($user_role === 'Seller'): ?>
        <h2>Seller Management Panel</h2>
        <p style="margin-bottom: 25px;"><a href="create_listing.php" style="background: green; color: white; padding: 10px 15px; text-decoration: none; border-radius: 5px; font-weight: bold;">+ Create a New Product Listing</a></p>
        
        <h3>Your Inventory Log</h3>
        <?php if (count($my_products) > 0): ?>
            <table border="1" cellpadding="10" cellspacing="0" style="width: 100%; border-collapse: collapse; text-align: left;">
                <thead style="background: #f4f4f4;">
                    <tr>
                        <th>Product Title</th>
                        <th>Price</th>
                        <th>Status</th> <th>Date Listed</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($my_products as $item): ?>
                        <tr>
                            <td><strong><?php echo htmlspecialchars($item['title']); ?></strong></td>
                            <td>R <?php echo number_format($item['price'], 2); ?></td>
                            
                            <td>
                                <?php if ($item['status'] === 'Sold'): ?>
                                    <span style="background: #dc3545; color: white; padding: 4px 8px; border-radius: 4px; font-size: 12px; font-weight: bold; display: inline-block;">
                                        SOLD 
                                    </span>
                                <?php else: ?>
                                    <span style="background: #28a745; color: white; padding: 4px 8px; border-radius: 4px; font-size: 12px; font-weight: bold; display: inline-block;">
                                        ACTIVE
                                    </span>
                                <?php endif; ?>
                            </td>
                            
                            <td><?php echo $item['created_at']; ?></td>
                            <td>
                                <?php if ($item['status'] !== 'Sold'): ?>
                                    <a href="edit_listing.php?id=<?php echo $item['id']; ?>" 
                                       style="background-color: #007bff; color: white; padding: 6px 12px; text-decoration: none; border-radius: 4px; display: inline-block; font-size: 14px; margin-right: 5px; font-weight: bold;">
                                        Edit
                                    </a>
                                <?php else: ?>
                                    <span style="color: #666; font-size: 14px; margin-right: 15px; font-style: italic;">Locked</span>
                                <?php endif; ?>

                                <form action="dashboard.php" method="POST" onsubmit="return confirm('Are you sure you want to delete this listing permanently?');" style="display:inline;">
                                    <input type="hidden" name="delete_product_id" value="<?php echo $item['id']; ?>">
                                    <button type="submit" style="background: none; border: none; color: #dc3545; cursor: pointer; text-decoration: underline; font-weight: bold;">Delete Record</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p>You haven't listed any items for sale yet.</p>
        <?php endif; ?>

    <?php elseif ($user_role === 'Buyer'): ?>
        <h2>Buyer Activity Center</h2>
        <p>Browse our homepage to find items you love, and use the simulated checkout to make instant test purchases.</p>
        <p><a href="index.php" style="background: blue; color: white; padding: 10px 15px; text-decoration: none; border-radius: 5px; font-weight: bold;">Browse Marketplace Items</a></p>
    
    <?php else: ?>
        <h2>System Administrator Controls</h2>
        <p>Access level verified. Global marketplace overview active.</p>
        <p><a href="admin/index.php" style="background: #343a40; color: white; padding: 10px 15px; text-decoration: none; border-radius: 5px; font-weight: bold;">Enter Admin Control Panel →</a></p>
    <?php endif; ?>

</body>
</html>