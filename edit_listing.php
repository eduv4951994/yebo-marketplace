<?php
// SETUP & SECURITY CHECKS
require_once 'includes/connect_db.php';
require_once 'includes/auth.php';
check_login();

$current_user_id = $_SESSION['user_id'];
$error   = "";
$success = "";

// CHECK IF A PRODUCT ID WAS PASSED IN THE URL
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: dashboard.php");
    exit();
}

$product_id = $_GET['id'];

// FETCH THE EXISTING PRODUCT DETAILS (And double check ownership!)
try {
    $sql = "SELECT * FROM products WHERE id = :id AND seller_id = :seller_id";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['id' => $product_id, 'seller_id' => $current_user_id]);
    $product = $stmt->fetch(PDO::FETCH_ASSOC);

    // Security Guard: If the product doesn't exist OR belongs to a different user, kick them out
    if (!$product) {
        die("Error: You do not have permission to edit this listing, or it does not exist.");
    }
} catch (PDOException $e) {
    die("Database error: " . $e->getMessage());
}

// PROCESS THE UPDATES WHEN THE FORM IS SUBMITTED
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $title       = trim($_POST['title']);
    $description = trim($_POST['description']);
    $price       = trim($_POST['price']);
    
    // Default to keeping the old image path if they don't upload a new one
    $final_image_path = $product['image_path'];

    // HANDLE NEW IMAGE UPLOAD (OPTIONAL)
    if (isset($_FILES['product_image']) && $_FILES['product_image']['error'] == 0) {
        $allowed_types  = ['jpg', 'jpeg', 'png', 'webp'];
        $file_name      = $_FILES['product_image']['name'];
        $file_tmp_name  = $_FILES['product_image']['tmp_name'];
        $file_extension = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));

        if (in_array($file_extension, $allowed_types)) {
            $new_file_name = uniqid("yebo_update_", true) . "." . $file_extension;
            $upload_destination = "assets/uploads/" . $new_file_name;

            if (move_uploaded_file($file_tmp_name, $upload_destination)) {
                $final_image_path = $upload_destination;
                
                // Optional: You could write code here to delete the old file off the server,
                // but keeping it simple avoids accidentally losing data right now!
            } else {
                $error = "Failed to save the new image.";
            }
        } else {
            $error = "Invalid file type. Only JPG, PNG, and WEBP allowed.";
        }
    }

    // SAVE UPDATES TO DATABASE
    if (empty($error)) {
        try {
            $sql = "UPDATE products 
                    SET title = :title, description = :description, price = :price, image_path = :image_path 
                    WHERE id = :id AND seller_id = :seller_id";
            
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                'title'       => $title,
                'description' => $description,
                'price'       => $price,
                'image_path'  => $final_image_path,
                'id'          => $product_id,
                'seller_id'   => $current_user_id
            ]);

            $success = "Listing updated successfully!";
            
            // Refresh the local array data so the form shows the updated text immediately
            $product['title']       = $title;
            $product['description'] = $description;
            $product['price']       = $price;
            $product['image_path']  = $final_image_path;

        } catch (PDOException $e) {
            $error = "Database update failed: " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Listing - YEBO Marketplace</title>
    <link rel="stylesheet" href="assets/css/styles.css">
</head>
<body>

    <div class="header-container" style="display: flex; justify-content: space-between; align-items: center; padding-bottom: 20px; border-bottom: 2px solid #eee; margin-bottom: 20px;">
        <h1>Edit Your Listing</h1>
        <div><a href="dashboard.php">Back to Dashboard</a></div>
    </div>

    <?php if (!empty($error)): ?>
        <p style="color: red; font-weight: bold;"><?php echo $error; ?></p>
    <?php endif; ?>

    <?php if (!empty($success)): ?>
        <p style="color: green; font-weight: bold;"><?php echo $success; ?></p>
    <?php endif; ?>

    <form action="edit_listing.php?id=<?php echo $product_id; ?>" method="POST" enctype="multipart/form-data" style="max-width: 500px;">
        
        <div style="margin-bottom: 15px;">
            <label>Product Title:</label><br>
            <input type="text" name="title" required value="<?php echo htmlspecialchars($product['title']); ?>" style="width: 100%; padding: 8px;">
        </div>

        <div style="margin-bottom: 15px;">
            <label>Price (ZAR):</label><br>
            <input type="number" name="price" step="0.01" required value="<?php echo htmlspecialchars($product['price']); ?>" style="width: 100%; padding: 8px;">
        </div>

        <div style="margin-bottom: 15px;">
            <label>Description:</label><br>
            <textarea name="description" required rows="5" style="width: 100%; padding: 8px;"><?php echo htmlspecialchars($product['description']); ?></textarea>
        </div>

        <div style="margin-bottom: 15px; border: 1px dashed #ccc; padding: 15px; background: #f9f9f9;">
            <label style="font-weight: bold;">Current Image:</label><br>
            <img src="<?php echo htmlspecialchars($product['image_path']); ?>" style="width: 100px; height: 100px; object-fit: cover; border-radius: 5px; margin: 10px 0;"><br>
            
            <label style="font-weight: bold;">Change Image (Optional):</label><br><br>
            <input type="file" name="product_image" accept="image/png, image/jpeg, image/webp">
        </div>

        <button type="submit" style="background: #007bff; color: white; padding: 10px 20px; border: none; cursor: pointer; border-radius: 5px;">Save Changes</button>
    </form>

</body>
</html>