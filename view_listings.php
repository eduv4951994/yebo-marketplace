<?php

// SYSTEM INITIALIZATION & CORE DEPENDENCIES
require_once 'includes/connect_db.php';
session_start();

// track current session user if logged in
$current_user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;

// INPUT VALIDATION 
if (!isset($_GET['id']) || empty($_GET['id'])) {
    die("Data Routing Error: Product identification parameter is missing.");
}
$product_id = intval($_GET['id']); // convert to integer for simple sanitization

// DATABASE QUERIES & METRIC CALCULATIONS
try {
    // fetch core product information along with the creators username
    $product_query = "SELECT p.*, u.username 
                      FROM products p 
                      JOIN users u ON p.seller_id = u.id 
                      WHERE p.id = ?";
                      
    $product_stmt = $pdo->prepare($product_query);
    $product_stmt->execute([$product_id]);
    $product = $product_stmt->fetch(PDO::FETCH_ASSOC);

    // stop execution instantly if the product does not exist
    if (!$product) {
        die("Record Error: This item is unavailable, sold, or has been removed.");
    }

    // fetch all feedback history posted about this specific merchant
    $review_query = "SELECT r.*, u.username AS reviewer_name 
                     FROM user_reviews r 
                     JOIN users u ON r.reviewer_id = u.id 
                     WHERE r.user_reviewed_id = ? 
                     ORDER BY r.created_at DESC";
                     
    $review_stmt = $pdo->prepare($review_query);
    $review_stmt->execute([$product['seller_id']]);
    $reviews = $review_stmt->fetchAll(PDO::FETCH_ASSOC);

    // calculate the average rating for the merchant profile
    $avg_rating = "No reviews yet";
    if (count($reviews) > 0) {
        $total_stars = array_sum(array_column($reviews, 'rating'));
        $avg_rating = round($total_stars / count($reviews), 1) . " / 5 ⭐";
    }

} catch (PDOException $error) {
    die("Critical Database Connection Fault: " . $error->getMessage());
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
        <h1>YEBO Marketplace</h1>
        <div class="nav-links">
            <a href="index.php" style="margin-right: 15px; color: #333;">&larr; Back to Home</a>
            
            <?php if ($current_user_id): ?>
                <a href="messages.php" style="margin-right: 15px; color: #007bff; font-weight: bold;">My Messages</a>
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
                <strong>Listed by:</strong> <?php echo htmlspecialchars($product['username']); ?> 
                <span style="color: #ffc107; font-weight: bold; margin-left: 10px;"><?php echo $avg_rating; ?></span><br>
                <strong>Date:</strong> <?php echo date('F j, Y', strtotime($product['created_at'])); ?>
            </p>

            <?php if ($current_user_id): ?>
                <?php if ($current_user_id != $product['seller_id']): ?>
                    <form action="purchase.php" method="POST" style="margin-top: 15px;">
                        <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
                        <button type="submit" class="btn-block btn-buy">🛒 Buy Now</button>
                    </form>
                <?php else: ?>
                    <div style="background: #e2e3e5; color: #383d41; padding: 15px; border-radius: 5px; text-align: center; margin-top: 15px; font-weight: bold;">
                        This is your listing.
                    </div>
                <?php endif; ?>
            <?php else: ?>
                <div class="alert-warning" style="margin-top: 15px; margin-bottom: 20px;">
                    You must be <a href="login.php">logged in</a> to purchase this item.
                </div>
            <?php endif; ?>

            <hr class="divider">
            
            <h3 class="section-title">Description</h3>
            <p class="description-text">
                <?php echo nl2br(htmlspecialchars($product['description'])); ?>
            </p>

            <hr class="divider">

            <h3 class="section-title">Message the Seller</h3>
            <?php if ($current_user_id): ?>
                <?php if ($current_user_id != $product['seller_id']): ?>
                    <form action="messages.php" method="POST">
                        <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
                        <input type="hidden" name="receiver_id" value="<?php echo $product['seller_id']; ?>">
                        
                        <textarea name="reply_text" rows="4" class="form-input" placeholder="Hi, is this still available?" required></textarea>
                        <button type="submit" class="btn-contact-seller btn-block">Send Message</button>
                    </form>
                <?php else: ?>
                    <p style="color: #666; font-style: italic;">You cannot message yourself about your own listing.</p>
                <?php endif; ?>
            <?php else: ?>
                <div class="alert-warning">
                    You must be <a href="login.php">logged in</a> to message sellers.
                </div>
            <?php endif; ?>

            <hr class="divider">

            <h3 class="section-title">Rate this Seller</h3>
            <?php if ($current_user_id): ?>
                <?php if ($current_user_id != $product['seller_id']): ?>
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
                    <p style="color: #666; font-style: italic;">You cannot review your own listing.</p>
                <?php endif; ?>
            <?php else: ?>
                <div class="alert-warning">
                    You must be <a href="login.php">logged in</a> to leave a review.
                </div>
            <?php endif; ?>

        </div>
    </div>


    <div style="max-width: 900px; margin: 40px auto; background: white; padding: 30px; border-radius: 10px; box-shadow: 0 4px 10px rgba(0,0,0,0.05);">
        <h3 class="section-title" style="margin-top: 0;">Seller Review History (<?php echo count($reviews); ?>)</h3>
        
        <?php if (count($reviews) > 0): ?>
            <div style="display: flex; flex-direction: column; gap: 15px; margin-top: 20px;">
                <?php foreach ($reviews as $rev): ?>
                    <div style="border-left: 4px solid #007bff; background: #f8f9fa; padding: 15px; border-radius: 0 8px 8px 0;">
                        <div style="display: flex; justify-content: space-between; margin-bottom: 5px;">
                            <strong><?php echo htmlspecialchars($rev['reviewer_name']); ?></strong>
                            <span style="color: #ffc107;">
                                <?php echo str_repeat('⭐', $rev['rating']); ?>
                            </span>
                        </div>
                        <p style="margin: 0; color: #555; font-size: 14px;"><?php echo htmlspecialchars($rev['review_text']); ?></p>
                        <small style="color: #999; font-size: 11px;"><?php echo date('Y-m-d', strtotime($rev['created_at'])); ?></small>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <p style="color: #777; font-style: italic; margin-top: 15px;">This seller hasn't received any feedback yet.</p>
        <?php endif; ?>
    </div>
    
</body>
</html>