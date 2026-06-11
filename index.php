<?php
require_once 'includes/connect_db.php';
session_start();

// get the search keyword if it exists
$search = isset($_GET['search']) ? trim($_GET['search']) : '';

// fetch active products for the storefront, filtered if searching
try {
    if (!empty($search)) {
        
        $sql = "SELECT * FROM products WHERE status = 'Available' AND (title LIKE :search OR description LIKE :search) ORDER BY created_at DESC";
        $stmt = $pdo->prepare($sql);
        $stmt->execute(['search' => '%' . $search . '%']);
    } else {
        
        $sql = "SELECT * FROM products WHERE status = 'Available' ORDER BY created_at DESC";
        $stmt = $pdo->prepare($sql);
        $stmt->execute();
    }
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Error loading marketplace: " . $e->getMessage());
}
?> <!DOCTYPE html>
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

    <div class="search-bar-container" style="max-width: 1200px; margin: 25px auto 10px auto; padding: 0 20px; text-align: center;">
        <form action="" method="GET" style="display: inline-flex; width: 100%; max-width: 500px; gap: 8px;">
            <input type="text" name="search" placeholder="Search items by keyword..." value="<?php echo htmlspecialchars($search); ?>" style="flex: 1; padding: 10px 15px; border: 1px solid #ccc; border-radius: 5px; font-size: 15px;">
            <button type="submit" style="background: #007bff; color: white; border: none; padding: 10px 20px; border-radius: 5px; cursor: pointer; font-weight: bold;">Search</button>
            <?php if (!empty($search)): ?>
                <a href="index.php" style="background: #6c757d; color: white; text-decoration: none; padding: 10px 15px; border-radius: 5px; font-size: 14px; display: inline-flex; align-items: center;">Clear</a>
            <?php endif; ?>
        </form>
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
            <div class="empty-state" style="text-align: center; grid-column: 1 / -1; padding: 40px; background: #f8f9fa; border-radius: 5px;">
                <?php if (!empty($search)): ?>
                    <p style="color: #6c757d; font-size: 1.2em;">No items match your search for "<strong><?php echo htmlspecialchars($search); ?></strong>".</p>
                    <a href="index.php" style="color: #007bff; text-decoration: none; font-weight: bold;">Browse all available items</a>
                <?php else: ?>
                    <p>No items are currently available for sale. Check back later!</p>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>

</body>
</html>

