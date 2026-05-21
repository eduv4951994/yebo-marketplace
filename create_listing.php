<?php
// SETUP & AUTHENTICATION
require_once 'includes/db_connect.php';
require_once 'includes/auth.php';
check_login();

// INITIALIZE VARIABLES
$seller_id   = $_SESSION['user_id'];
$title       = "";
$description = "";
$price       = "";
$error       = "";
$success     = "";

// PROCESS THE FORM WHEN SUBMITTED
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    // Grab text inputs securely
    $title       = trim($_POST['title']);
    $description = trim($_POST['description']);
    $price       = trim($_POST['price']);
    
    // Set a default image path just in case they don't upload one
    $final_image_path = "uploads/default.png";

    // HANDLE THE IMAGE UPLOAD LOGIC
    // Check if a file was actually uploaded and has no errors
    if (isset($_FILES['product_image']) && $_FILES['product_image']['error'] == 0) {
        
        $allowed_types = ['jpg', 'jpeg', 'png', 'webp'];
        $file_name     = $_FILES['product_image']['name'];
        $file_tmp_name = $_FILES['product_image']['tmp_name'];
        
        // Get the file extension (e.g., 'jpg') and make it lowercase
        $file_extension = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));

        // Security Check: Is it a valid image type?
        if (in_array($file_extension, $allowed_types)) {
            
            // Create a unique file name so "iphone.jpg" doesn't overwrite someone else's "iphone.jpg"
            $new_file_name = uniqid("yebo_", true) . "." . $file_extension;
            $upload_destination = "uploads/" . $new_file_name;

            // Move the file from a temporary server location to our official uploads folder
            if (move_uploaded_file($file_tmp_name, $upload_destination)) {
                // Success! Update our database variable to point to the new file
                $final_image_path = $upload_destination;
            } else {
                $error = "Failed to move the uploaded file. Check folder permissions.";
            }
        } else {
            $error = "Invalid file type. Only JPG, PNG, and WEBP are allowed.";
        }
    }

    // SAVE TO DATABASE (If there are no errors)
    if (empty($error)) {
        try {
            $sql = "INSERT INTO products (seller_id, title, description, price, image_path) 
                    VALUES (:seller_id, :title, :description, :price, :image_path)";
            
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                'seller_id'   => $seller_id,
                'title'       => $title,
                'description' => $description,
                'price'       => $price,
                'image_path'  => $final_image_path
            ]);

            $success = "Listing successfully published!";
            // Clear the form fields after success
            $title = $description = $price = ""; 

        } catch (PDOException $e) {
            $error = "Database error: " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Listing - YEBO Marketplace</title>
    <link rel="stylesheet" href="assets/css/styles.css">
</head>
<body>

    <div class="header-container" style="display: flex; justify-content: space-between; align-items: center; padding-bottom: 20px; border-bottom: 2px solid #eee; margin-bottom: 20px;">
        <h1>Create a New Listing</h1>
        <div><a href="dashboard.php">Back to Dashboard</a></div>
    </div>

    <?php if (!empty($error)): ?>
        <p style="color: red; font-weight: bold;"><?php echo $error; ?></p>
    <?php endif; ?>

    <?php if (!empty($success)): ?>
        <p style="color: green; font-weight: bold;"><?php echo $success; ?></p>
    <?php endif; ?>

    <form action="create_listing.php" method="POST" enctype="multipart/form-data" style="max-width: 500px;">
        
        <div style="margin-bottom: 15px;">
            <label>Product Title:</label><br>
            <input type="text" name="title" required value="<?php echo htmlspecialchars($title); ?>" style="width: 100%; padding: 8px;">
        </div>

        <div style="margin-bottom: 15px;">
            <label>Price (ZAR):</label><br>
            <input type="number" name="price" step="0.01" required value="<?php echo htmlspecialchars($price); ?>" style="width: 100%; padding: 8px;">
        </div>

        <div style="margin-bottom: 15px;">
            <label>Description:</label><br>
            <textarea name="description" required rows="5" style="width: 100%; padding: 8px;"><?php echo htmlspecialchars($description); ?></textarea>
        </div>

        <div style="margin-bottom: 15px; border: 1px dashed #ccc; padding: 15px; background: #f9f9f9;">
            <label style="font-weight: bold;">Product Image:</label><br><br>
            <input type="file" name="product_image" accept="image/png, image/jpeg, image/webp">
            <p style="font-size: 0.8em; color: #666;">Max file size: 2MB. Allowed types: JPG, PNG, WEBP.</p>
        </div>

        <button type="submit" style="background: #28a745; color: white; padding: 10px 20px; border: none; cursor: pointer; border-radius: 5px;">Publish Listing</button>
    </form>

</body>
</html>