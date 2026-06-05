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

// only run if the user arrived here via a POST form submission
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['product_id'])) {
    $product_id = intval($_POST['product_id']);

    try {
        // double check if the product exists before doing anything
        $check_sql = "SELECT title, status FROM products WHERE id = ?";
        $stmt = $pdo->prepare($check_sql);
        $stmt->execute([$product_id]);
        $product = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$product) {
            $error = "Error: This product could not be found in the database.";
        } else {
            $product_title = $product['title'];

            // run the simulation update to take it off the market
            // this mark the item as 'sold' so it no longer displays to buyers
            $update_sql = "UPDATE products SET status = 'sold' WHERE id = ?";
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
        body { font-family: Arial, sans-serif; background-color: #f4f6f9; display: flex; justify-content: center; align-items: center; height: 100vh; margin: 0; }
        .sim-card { background: white; padding: 40px; border-radius: 10px; box-shadow: 0 4px 16px rgba(0,0,0,0.05); width: 100%; max-width: 450px; text-align: center; }

        .logo { font-size: 22px; font-weight: bold; letter-spacing: 4px; color: #007bff; margin-bottom: 15px; }
        h2 { color: #333; margin-bottom: 20px; font-size: 22px; }
        p { color: #555; font-size: 15px; line-height: 1.5; margin-bottom: 25px; }
        
        .status-box { padding: 15px; border-radius: 6px; font-size: 14px; font-weight: bold; margin-bottom: 25px; text-align: left; }
        .success-box { background: #f0fff4; border: 1px solid #b7dfc5; color: #2e7d32; }
        .error-box { background: #fff0f0; border: 1px solid #f5c6cb; color: #b04040; }
        
        .btn-nav { display: inline-block; width: 100%; padding: 13px; background: #007bff; color: white; border: none; border-radius: 6px; font-size: 16px; font-weight: bold; text-decoration: none; box-sizing: border-box; transition: 0.2s; }
        .btn-nav:hover { background: #0056b3; }
        .secondary-link { display: inline-block; margin-top: 15px; color: #666; font-size: 13px; text-decoration: none; }
        .secondary-link:hover { text-decoration: underline; color: #007bff; }
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
                    The database table entry has been updated to <strong>status = 'sold'</strong>. It will no longer show up on any active buyer listing feeds.
                </small>
            </div>
        <?php endif; ?>

        <?php if (!empty($error)): ?>
            <div class="status-box error-box">
                 <?php echo $error; ?>
            </div>
        <?php endif; ?>

        <a href="dashboard.php" class="btn-nav">Go to Dashboard</a>
        <br>
        <a href="index.php" class="secondary-link">&larr; Return to Marketplace Home</a>
    </div>

</body>
</html>