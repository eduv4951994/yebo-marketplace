<?php
// ROUTING & SECURITY CHECK
require_once '../includes/connect_db.php';
require_once '../includes/auth.php';

check_login();

// Strict Admin Gate
if ($_SESSION['user_role'] !== 'Admin') {
    header("Location: ../dashboard.php");
    exit();
}

$admin_name = $_SESSION['username'] ?? $_SESSION['user_name'] ?? 'Admin';
$all_products = [];

// FETCH EVERY SINGLE LISTING ON THE PLATFORM (Aligned to use 'username')
try {
    $sql = "SELECT products.*, users.username AS seller_name 
            FROM products 
            JOIN users ON products.seller_id = users.id 
            ORDER BY products.created_at DESC";
    
    $stmt = $pdo->query($sql);
    $all_products = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Error loading marketplace catalog: " . $e->getMessage());
}

// PROCESS ADMIN DELETE ACTION
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['admin_delete_id'])) {
    $delete_id = $_POST['admin_delete_id'];
    try {
        $delete_sql = "DELETE FROM products WHERE id = :id";
        $delete_stmt = $pdo->prepare($delete_sql);
        $delete_stmt->execute(['id' => $delete_id]);
        
        header("Location: manage_listings.php");
        exit();
    } catch (PDOException $e) {
        die("Admin deletion failed: " . $e->getMessage());
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Listings - YEBO Admin</title>
    <link rel="stylesheet" href="../assets/css/styles.css">
</head>
<body style="padding: 20px; font-family: Arial, sans-serif;">

    <div style="display: flex; justify-content: space-between; align-items: center; border-bottom: 2px solid #ccc; padding-bottom: 15px; margin-bottom: 20px;">
        <h1>📦 Global Listing Moderation</h1>
        <p><a href="index.php" style="font-weight: bold;">← Back to Admin Center</a></p>
    </div>

    <p>Reviewing all active properties/items currently published on the YEBO Marketplace ecosystem:</p>

    <?php if (count($all_products) > 0): ?>
        <table border="1" cellpadding="10" cellspacing="0" style="width: 100%; border-collapse: collapse; text-align: left;">
            <thead style="background: #343a40; color: white;">
                <tr>
                    <th>Image</th>
                    <th>Product Title</th>
                    <th>Seller Name</th>
                    <th>Price</th>
                    <th>Date Listed</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($all_products as $item): ?>
                    <tr>
                        <td>
                            <img src="../<?php echo htmlspecialchars($item['image_path']); ?>" alt="Product" style="width: 60px; height: 60px; object-fit: cover; border-radius: 4px;">
                        </td>
                        
                        <td><strong><?php echo htmlspecialchars($item['title']); ?></strong></td>
                        <td><?php echo htmlspecialchars($item['seller_name']); ?></td>
                        <td>R <?php echo number_format($item['price'], 2); ?></td>
                        <td><?php echo $item['created_at']; ?></td>
                        
                        <td>
                            <form action="manage_listings.php" method="POST" onsubmit="return confirm('ADMIN WARNING: Are you sure you want to forcefully delete this listing?');">
                                <input type="hidden" name="admin_delete_id" value="<?php echo $item['id']; ?>">
                                <button type="submit" style="color: white; background-color: #dc3545; padding: 6px 12px; border: none; border-radius: 4px; cursor: pointer; font-weight: bold;">
                                    Force Delete
                                </button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php else: ?>
        <p style="background: #f8d7da; color: #721c24; padding: 15px; border-radius: 5px;">No active products found in the database.</p>
    <?php endif; ?>

</body>
</html>