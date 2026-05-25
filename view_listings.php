<?php
require_once 'includes/connect_db.php';
session_start();

// 1. Make sure we actually have an ID in the URL
if (!isset($_GET['id']) || empty($_GET['id'])) {
    die("Product ID is missing.");
}
$product_id = $_GET['id'];

// 2. Fetch the product AND the seller's username using a JOIN
try {
    $sql = "SELECT p.*, u.username 
            FROM products p 
            JOIN users u ON p.seller_id = u.id 
            WHERE p.id = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$product_id]);
    $product = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$product) {
        die("This product does not exist or has been removed.");
    }
} catch (PDOException $e) {
    die("Error loading product: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($product['title']); ?> - YEBO</title>
    
    <link rel="stylesheet" href="assets/css/styles.css?v=<?php echo time(); ?>">
</head>
<body>

    <div class="navbar">
        <h1> YEBO Marketplace</h1>
        <div class="nav-links">
            <a href="index.php" style="margin-right: 15px; color: #333;">&larr; Back to Home</a>
            <?php if (isset($_SESSION['user_id'])): ?>
                <a href="dashboard.php">My Dashboard</a>
                <a href="logout.php" style="color: #dc3545;">Log Out</a>
            <?php else: ?>
                <a href="login.php">Log In</a>
                <a href="register.php" style="background: #007bff; color: white; padding: 8px 15px; border-radius: 5px;">Sign Up</a>
            <?php endif; ?>
        </div>
    </div>

    <div class="listing-container">
        
        <div class="listing-col">
            <img src="<?php echo htmlspecialchars($product['image_path']); ?>" alt="Product Image" class="listing-image">
        </div>

        <div class="listing-col">
            <h1 class="listing-title"><?php echo htmlspecialchars($product['title']); ?></h1>
            <h2 class="listing-price">R <?php echo number_format($product['price'], 2); ?></h2>
            
            <p class="listing-meta">
                <strong>Listed by:</strong> <?php echo htmlspecialchars($product['username']); ?><br>
                <strong>Date:</strong> <?php echo date('F j, Y', strtotime($product['created_at'])); ?>
            </p>

            <hr class="divider">
            
            <h3 class="section-title">Description</h3>
            <p class="description-text">
                <?php echo nl2br(htmlspecialchars($product['description'])); ?>
            </p>

            <hr class="divider">

            <h3 class="section-title">Message the Seller</h3>
            <?php if (isset($_SESSION['user_id'])): ?>
                <textarea rows="4" class="form-input" placeholder="Hi, is this still available?"></textarea>
                <button class="btn-contact-seller btn-block">Send Message</button>
            <?php else: ?>
                <div class="alert-warning">
                    You must be <a href="login.php">logged in</a> to message sellers.
                </div>
            <?php endif; ?>

            <hr class="divider">

            <h3 class="section-title">Rate this Seller</h3>
            <?php if (isset($_SESSION['user_id'])): ?>
                
                <?php if ($_SESSION['user_id'] != $product['seller_id']): ?>
                    <div class="review-box">
                        <form action="submit_review.php" method="POST">
                            <input type="hidden" name="user_reviewed_id" value="<?php echo $product['seller_id']; ?>">
                            
                            <label class="form-label">Rating:</label>
                            <select name="rating" required class="form-input">
                                <option value="5">⭐⭐⭐⭐⭐ (5/5 - Great)</option>
                                <option value="4">⭐⭐⭐⭐ (4/5 - Good)</option>
                                <option value="3">⭐⭐⭐ (3/5 - Okay)</option>
                                <option value="2">⭐⭐ (2/5 - Poor)</option>
                                <option value="1">⭐ (1/5 - Terrible)</option>
                            </select>

                            <label class="form-label">Comment:</label>
                            <textarea name="review_text" rows="3" required class="form-input" placeholder="How was your experience?"></textarea>

                            <button type="submit" class="btn-contact-seller btn-block btn-blue">Submit Review</button>
                        </form>
                    </div>
                <?php else: ?>
                    <p class="text-muted">You cannot review your own listing.</p>
                <?php endif; ?>

            <?php else: ?>
                <div class="alert-warning">
                    You must be <a href="login.php">logged in</a> to leave a review.
                </div>
            <?php endif; ?>

        </div>
    </div>

</body>
</html>