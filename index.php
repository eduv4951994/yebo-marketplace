<?php
require_once 'includes/connect_db.php';
session_start();

// Fetch all active products for the storefront
try {
    $sql = "SELECT * FROM products WHERE status = 'Available' ORDER BY created_at DESC";
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
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
    
    <link rel="stylesheet" href="assets/css/styles.css?v=<?php echo time(); ?>">
</head>
<body>

    <div class="navbar">
        <h1>YEBO Marketplace</h1>
        <div class="nav-links">
            <?php if (isset($_SESSION['user_id'])): ?>
                <a href="messages.php" style="margin-right: 15px; color: #007bff; font-weight: bold;">Messages</a>
                <a href="dashboard.php">My Dashboard</a>
                <a href="logout.php" style="color: #dc3545;">Log Out</a>
            <?php else: ?>
                <a href="login.php">Log In</a>
                <a href="register.php" style="background: #007bff; color: white; padding: 8px 15px; border-radius: 5px;">Sign Up</a>
            <?php endif; ?>
        </div>
    </div>

    <div class="product-grid">
        <?php if (count($products) > 0): ?>
            <?php foreach ($products as $item): ?>
                
                <div class="product-card">
                    <img src="<?php echo htmlspecialchars($item['image_path']); ?>" alt="Product Image" style="width: 100%; height: 200px; object-fit: cover; border-radius: 5px; margin-bottom: 10px;">

                    <h2 style="margin-top: 0; font-size: 1.2em;"><?php echo htmlspecialchars($item['title']); ?></h2>
                    
                    <div class="seller-badge">Listed: <?php echo date('Y-m-d', strtotime($item['created_at'])); ?></div>
                    <div class="price">R <?php echo number_format($item['price'], 2); ?></div>
                    
                    <a href="view_listings.php?id=<?php echo $item['id']; ?>" class="btn-contact-seller">
                        View & Contact Seller
                    </a>
                </div>

            <?php endforeach; ?>
        <?php else: ?>
            <div class="empty-state">
                <p>No items are currently available for sale. Check back later!</p>
            </div>
        <?php endif; ?>
    </div>

</body>
</html>