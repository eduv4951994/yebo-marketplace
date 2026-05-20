<?php
// 1. ATTACH DATABASE & SECURITY GATE
require_once 'includes/connect_db.php';
require_once 'includes/auth.php';

// Force login check
check_login();

// ROLE RESTRICTION: Only let Sellers view this page
if ($_SESSION['user_role'] !== 'Seller') {
    header("Location: dashboard.php");
    exit();
}

$success = "";
$error = "";

// 2. PROCESS FORM SUBMISSION
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $title       = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $price       = trim($_POST['price'] ?? '');
    $seller_id   = $_SESSION['user_id']; // Grab ID straight out of the active secure session

    if (empty($title) || empty($description) || empty($price)) {
        $error = "All fields are required to list a product.";
    } elseif (!is_numeric($price) || $price <= 0) {
        $error = "Please enter a valid price amount greater than zero.";
    } else {
        try {
            // Safe PDO Insertion mapping
            $sql = "INSERT INTO products (seller_id, title, description, price) 
                    VALUES (:seller_id, :title, :description, :price)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                'seller_id'   => $seller_id,
                'title'       => $title,
                'description' => $description,
                'price'       => $price
            ]);

            $success = "Excellent! Your item has been listed successfully.";
        } catch (PDOException $e) {
            $error = "System error creating listing: " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>YEBO Marketplace - Create a Listing</title>
</head>
<body>

    <p><a href="dashboard.php">← Back to Dashboard</a></p>
    <h2>Add a Product to the Marketplace</h2>

    <?php if (!empty($success)): ?>
        <p style="color: green; font-weight: bold;"><?php echo htmlspecialchars($success); ?></p>
    <?php endif; ?>

    <?php if (!empty($error)): ?>
        <p style="color: red; font-weight: bold;"><?php echo htmlspecialchars($error); ?></p>
    <?php endif; ?>

    <form action="create_listing.php" method="POST">
        <div>
            <label>Item Title:</label><br>
            <input type="text" name="title" placeholder="e.g., iPhone 13 Pro Max - 256GB" required style="width: 300px;">
        </div><br>

        <div>
            <label>Item Description:</label><br>
            <textarea name="description" placeholder="Describe your item's condition, location, and delivery options..." rows="5" style="width: 304px;" required></textarea>
        </div><br>

        <div>
            <label>Price (ZAR):</label><br>
            <input type="number" step="0.01" name="price" placeholder="0.00" required style="width: 300px;">
        </div><br>

        <button type="submit">Publish Listing Live</button>
    </form>

</body>
</html>