<?php
// ATTACH DATABASE & SYSTEM SECURITY
require_once 'includes/connect_db.php';
require_once 'includes/auth.php';

// Check if a product ID was passed in the URL 
if (!isset($_GET['id']) || empty($_GET['id'])) {
    die("Error: Product ID is missing.");
}
$product_id = $_GET['id'];

// PROCESS THE SIMULATED PURCHASE 
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['buy_item'])) {
    // Force them to log in before they can buy anything
    check_login(); 
    
    try {
        // Change the status column from 'Available' to 'Sold'
        $buy_sql = "UPDATE products SET status = 'Sold' WHERE id = :id";
        $buy_stmt = $pdo->prepare($buy_sql);
        $buy_stmt->execute(['id' => $product_id]);
        
        // Pop up a clean success alert and send them back to the storefront
        echo "<script>
                alert('Purchase Successful! You have successfully simulated buying this item.'); 
                window.location.href='index.php';
              </script>";
        exit();
    } catch (PDOException $e) {
        die("Purchase transaction failed: " . $e->getMessage());
    }
}

// FETCH THE SPECIFIC PRODUCT DETAILS FOR DISPLAY 
try {
    // Join with users table to get the sellers username
    $sql = "SELECT products.*, users.username AS seller_name 
            FROM products 
            JOIN users ON products.seller_id = users.id 
            WHERE products.id = :id";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['id' => $product_id]);
    $product = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // If the ID doesn't match anything in the database
    if (!$product) {
        die("Error: This marketplace item no longer exists.");
    }
} catch (PDOException $e) {
    die("Error loading item details: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($product['title']); ?> - YEBO Marketplace</title>
    <link rel="stylesheet" href="assets/css/styles.css">
</head>
<body style="padding: 20px; font-family: Arial, sans-serif;">

    <div style="margin-bottom: 30px; border-bottom: 2px solid #eee; padding-bottom: 15px;">
        <p><a href="index.php" style="font-weight: bold; text-decoration: none; color: #007bff;">← Back to Storefront Marketplace</a></p>
    </div>

    <div style="display: flex; flex-wrap: wrap; gap: 40px; max-width: 1000px; margin: 0 auto;">
        
        <div style="flex: 1; min-width: 300px;">
            <img src="<?php echo htmlspecialchars($product['image_path']); ?>" 
                 alt="<?php echo htmlspecialchars($product['title']); ?>" 
                 style="width: 100%; max-height: 450px; object-fit: cover; border-radius: 8px; box-shadow: 0 4px 8px rgba(0,0,0,0.1);">
        </div>
        
        <div style="flex: 1; min-width: 300px; display: flex; flex-direction: column; justify-content: space-between;">
            <div>
                <h1 style="margin: 0 0 10px 0; font-size: 32px;"><?php echo htmlspecialchars($product['title']); ?></h1>
                <p style="font-size: 28px; color: #28a745; font-weight: bold; margin: 0 0 20px 0;">
                    R <?php echo number_format($product['price'], 2); ?>
                </p>
                
                <hr style="border: 0; border-top: 1px solid #eee; margin-bottom: 20px;">
                
                <p style="font-size: 14px; color: #666; margin-bottom: 5px;">Seller Profile:</p>
                <p style="margin: 0 0 20px 0; font-weight: bold; font-size: 16px;">👤 <?php echo htmlspecialchars($product['seller_name']); ?></p>
                
                <p style="font-size: 14px; color: #666; margin-bottom: 5px;">Item Description:</p>
                <p style="color: #333; line-height: 1.6; font-size: 16px; background: #f9f9f9; padding: 15px; border-radius: 6px;">
                    <?php echo nl2br(htmlspecialchars($product['description'])); ?>
                </p>
            </div>
            
            <div style="margin-top: 30px;">
                <?php if ($product['status'] === 'Sold'): ?>
                    <div style="background: #dc3545; color: white; padding: 15px; text-align: center; font-weight: bold; border-radius: 6px; font-size: 18px;">
                        This item has been sold!
                    </div>
                <?php else: ?>
                    <form action="view_listings.php?id=<?php echo $product['id']; ?>" method="POST" onsubmit="return confirm('Are you sure you want to buy this item? (This simulates a transaction)');">
                        <button type="submit" name="buy_item" style="width: 100%; background: #007bff; color: white; padding: 15px; border: none; border-radius: 6px; font-size: 18px; cursor: pointer; font-weight: bold; box-shadow: 0 4px 6px rgba(0,123,255,0.2);">
                            Buy Now (Simulate Purchase)
                        </button>
                    </form>
                <?php endif; ?>
            </div>
        </div>

    </div>

</body>
</html>