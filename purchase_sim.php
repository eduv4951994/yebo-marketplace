<?php
// connect to the database and start tracking the session
require_once 'includes/connect_db.php';
session_start();

// Guard:if the user isnt logged in stop 
if (!isset($_SESSION['user_id'])) {
    die("Error: You must be logged in to purchase an item.");
}

$user_id = $_SESSION['user_id'];
$error = "";
$success = "";
$product_title = "Item";
$seller_id = null; 

// only run if the user arrived here via a POST form submission
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['product_id'])) {
    $product_id = intval($_POST['product_id']);

    try {
        // fetch the details 
        $check_sql = "SELECT title, seller_id FROM products WHERE id = ?";
        $stmt = $pdo->prepare($check_sql);
        $stmt->execute([$product_id]);
        $product = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$product) {
            $error = "Error: This product could not be found or has already been bought.";
        } else {
            $product_title = $product['title'];
            $seller_id = $product['seller_id'];

            // update the status to 'Sold' 
            $update_sql = "UPDATE products SET status = 'Sold' WHERE id = ?";
            $update_stmt = $pdo->prepare($update_sql);
            $update_stmt->execute([$product_id]);

            $success = "Simulation Successful! You have bought " . htmlspecialchars($product_title) . ".";
        }

    } catch (PDOException $e) {
        $error = "Database Error: Could not process the simulation. " . $e->getMessage();
    }
} else {
    // if someone tries to load this page file directly in the browser URL bar send home
    header("Location: index.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>YEBO - Order Simulation</title>
    <style>
        /* layout theme */
        body { font-family: Arial, sans-serif; background-color: #f4f6f9; display: flex; justify-content: center; align-items: center; min-height: 100vh; margin: 0; padding: 20px; box-sizing: border-box; }
        .sim-card { background: white; padding: 40px; border-radius: 10px; box-shadow: 0 4px 16px rgba(0,0,0,0.05); width: 100%; max-width: 450px; text-align: center; }

        .logo { font-size: 22px; font-weight: bold; letter-spacing: 4px; color: #007bff; margin-bottom: 15px; }
        h2 { color: #333; margin-bottom: 20px; font-size: 22px; }
        
        .status-box { padding: 15px; border-radius: 6px; font-size: 14px; font-weight: bold; margin-bottom: 25px; text-align: left; }
        .success-box { background: #f0fff4; border: 1px solid #b7dfc5; color: #2e7d32; }
        .error-box { background: #fff0f0; border: 1px solid #f5c6cb; color: #b04040; }
        
        .btn-nav { display: inline-block; width: 100%; padding: 13px; background: #007bff; color: white; border: none; border-radius: 6px; font-size: 16px; font-weight: bold; text-decoration: none; box-sizing: border-box; transition: 0.2s; margin-bottom: 20px; }
        .btn-nav:hover { background: #0056b3; }

        /* REVIEW STYLES */
        .review-section { border-top: 2px dashed #dee2e6; margin-top: 25px; padding-top: 20px; text-align: left; }
        .review-title { font-size: 16px; font-weight: bold; color: #333; margin-bottom: 15px; display: flex; align-items: center; gap: 5px; }
        .form-label { font-size: 13px; color: #555; font-weight: bold; display: block; margin-bottom: 5px; }
        .form-input { width: 100%; padding: 10px; margin-bottom: 15px; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box; font-family: inherit; font-size: 14px; }
        .btn-submit { width: 100%; padding: 12px; background: #28a745; color: white; border: none; border-radius: 6px; font-size: 15px; font-weight: bold; cursor: pointer; transition: 0.2s; }
        .btn-submit:hover { background: #218838; }
    </style>
</head>
<body>

    <div class="sim-card">
        <div class="logo">YEBO MARKETPLACE</div>
        <h2>Order Confirmation</h2>

        <?php if (!empty($success)): ?>
            <div class="status-box success-box">
                 <?php echo $success; ?>
                <br><br>
                <small style="font-weight: normal; color: #555; display:block;">
                    The database table entry has been updated to <strong>status = 'Sold'</strong>.
                </small>
            </div>
        <?php endif; ?>

        <?php if (!empty($error)): ?>
            <div class="status-box error-box">
                 <?php echo $error; ?>
            </div>
        <?php endif; ?>

        <a href="dashboard.php" class="btn-nav">Go to Dashboard</a>

        <?php if (!empty($success) && $seller_id): ?>
            <div class="review-section">
                <div class="review-title">⭐ Give Seller Review</div>
                <form action="submit_review.php" method="POST">
                    <input type="hidden" name="user_reviewed_id" value="<?php echo $seller_id; ?>">
                    
                    <label class="form-label">Rating</label>
                    <select name="rating" required class="form-input">
                        <option value="5">⭐⭐⭐⭐⭐ (5/5 - Great)</option>
                        <option value="4">⭐⭐⭐⭐ (4/5 - Good)</option>
                        <option value="3">⭐⭐⭐ (3/5 - Okay)</option>
                        <option value="2">⭐⭐ (2/5 - Poor)</option>
                        <option value="1">⭐ (1/5 - Terrible)</option>
                    </select>

                    <label class="form-label">Comment</label>
                    <textarea name="review_text" rows="3" required class="form-input" placeholder="How was your experience with this seller?"></textarea>

                    <button type="submit" class="btn-submit">Submit Review</button>
                </form>
            </div>
        <?php endif; ?>
    </div>

</body>
</html>