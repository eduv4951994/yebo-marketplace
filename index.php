<?php
// ATTACH DATABASE & START SESSION
require_once 'includes/db_connect.php';
session_start();

// FETCH ALL PRODUCTS FROM THE DATABASE
try {
    // We use a JOIN to grab the product details AND the seller's username at the same time
    $sql = "SELECT products.*, users.username AS seller_name 
            FROM products 
            JOIN users ON products.seller_id = users.id 
            ORDER BY products.created_at DESC";
    $stmt = $pdo->query($sql);
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Error loading marketplace: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>YEBO Marketplace - Home</title>
    <style>
        /* A little bit of CSS to make the products look like actual store cards */
        .product-grid { display: flex; flex-wrap: wrap; gap: 20px; }
        .product-card { border: 1px solid #ccc; padding: 15px; border-radius: 8px; width: 250px; background: #f9f9f9; }
        .price { color: green; font-size: 1.2em; font-weight: bold; }
        .seller-badge { font-size: 0.8em; color: #666; }
    </style>
</head>
<body>

    <div style="padding-bottom: 20px; border-bottom: 2px solid #eee; margin-bottom: 20px;">
        <h1 style="display: inline;">Welcome to YEBO Marketplace</h1>
        <div style="float: right; margin-top: 20px;">
            <?php if (isset($_SESSION['user_id'])): ?>
                <a href="dashboard.php">My Dashboard</a> | 
                <a href="logout.php">Log Out</a>
            <?php else: ?>
                <a href="login.php">Log In</a> | 
                <a href="register.php">Sign Up</a>
            <?php endif; ?>
        </div>
    </div>

    <h2>Latest Listings</h2>

    <div class="product-grid">
        <?php if (count($products) > 0): ?>
            <?php foreach ($products as $item): ?>
                <div class="product-card">
                    <h3><?php echo htmlspecialchars($item['title']); ?></h3>
                    <p class="price">R <?php echo number_format($item['price'], 2); ?></p>
                    <p><?php echo htmlspecialchars($item['description']); ?></p>
                    <p class="seller-badge">Listed by: <?php echo htmlspecialchars($item['seller_name']); ?></p>
                    <button>Message Seller</button>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p>The marketplace is currently empty. Be the first to list an item!</p>
        <?php endif; ?>
    </div>

</body>
</html>