<?php
// ATTACH DATABASE & START SESSION
require_once 'includes/connect_db.php';
session_start();

// FETCH ALL PRODUCTS FROM THE DATABASE
try {
    // We grab the product details, plus the seller's username and email
    $sql = "SELECT products.*, users.username AS seller_name, users.email AS seller_email 
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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>YEBO Marketplace - Home</title>
    
    <link rel="stylesheet" href="assets/css/styles.css">
</head>
<body>

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

    <div class="product-grid">
        <?php if (count($products) > 0): ?>
            <?php foreach ($products as $item): ?>
                <div class="product-card">
                    <h3><?php echo htmlspecialchars($item['title']); ?></h3>
                    <p class="price">R <?php echo number_format($item['price'], 2); ?></p>
                    <p><?php echo htmlspecialchars($item['description']); ?></p>
                    <p class="seller-badge">Listed by: <?php echo htmlspecialchars($item['seller_name']); ?></p>
                    
                    <?php
                        // Prepare the email data cleanly using variables
                        $safe_email = htmlspecialchars($item['seller_email']);
                        $safe_title = urlencode($item['title']);
                        $subject    = "Interested in your YEBO listing: " . $safe_title;
                        
                        // Build the final mailto link
                        $mailto_link = "mailto:" . $safe_email . "?subject=" . $subject;
                    ?>
                    
                    <a href="<?php echo $mailto_link; ?>" class="btn-contact-seller">
                        👋 Say Hello to the Seller
                    </a>

                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p>The marketplace is currently empty. Be the first to list an item!</p>
        <?php endif; ?>
    </div>

</body>
</html>