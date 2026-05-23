<?php
// ATTACH DATABASE & START SESSION
require_once 'includes/connect_db.php';
session_start();

// FETCH ALL AVAILABLE PRODUCTS FROM THE DATABASE
try {
    // We added the WHERE clause here to hide sold items!
    $sql = "SELECT products.*, users.username AS seller_name, users.email AS seller_email 
            FROM products 
            JOIN users ON products.seller_id = users.id 
            WHERE products.status = 'Available'
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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>YEBO Marketplace - Home</title>
    
    <link rel="stylesheet" href="assets/css/styles.css">
</head>
<body style="padding: 20px; font-family: Arial, sans-serif;">

    <div class="header-container" style="display: flex; justify-content: space-between; align-items: center; padding-bottom: 20px; border-bottom: 2px solid #eee; margin-bottom: 20px;">
        <h1>YEBO Marketplace</h1>
        <div>
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

    <div class="product-grid" style="display: flex; flex-wrap: wrap; gap: 20px;">
        <?php if (count($products) > 0): ?>
            <?php foreach ($products as $item): ?>
                <div class="product-card" style="border: 1px solid #ccc; padding: 15px; border-radius: 8px; width: 300px;">
                    
                    <img src="<?php echo htmlspecialchars($item['image_path']); ?>" 
                         alt="<?php echo htmlspecialchars($item['title']); ?>" 
                         style="width: 100%; height: 200px; object-fit: cover; border-radius: 5px; margin-bottom: 10px;">

                    <h3 style="margin: 0 0 10px 0;"><?php echo htmlspecialchars($item['title']); ?></h3>
                    <p class="price" style="font-size: 18px; color: #28a745; font-weight: bold; margin: 0 0 10px 0;">R <?php echo number_format($item['price'], 2); ?></p>
                    
                    <p style="color: #555; height: 40px; overflow: hidden;"><?php echo htmlspecialchars($item['description']); ?></p>
                    <p class="seller-badge" style="font-size: 14px; color: #888;">Listed by: <?php echo htmlspecialchars($item['seller_name']); ?></p>
                    
                    <a href="view_listings.php?id=<?php echo $item['id']; ?>" class="btn-contact-seller" style="display: block; text-align: center; background: #007bff; color: white; padding: 10px; text-decoration: none; border-radius: 5px; margin-top: 15px; font-weight: bold;">
                        View Details & Buy
                    </a>

                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p style="background: #f4f4f4; padding: 20px; border-radius: 5px;">The marketplace is currently empty, or all items have been sold. Check back later!</p>
        <?php endif; ?>
    </div>

</body>
</html>