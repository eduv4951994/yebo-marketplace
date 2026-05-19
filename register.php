<?php
// ATTACH DATABASE CONNECTION
require_once 'includes/connect_db.php';

// Start session to handle tracking logged-in users later
session_start();

$error = "";
$success = "";

// DETECT IF THE USER CLICKED SIGN UP
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    // Grab inputs and clean extra spaces
    $name     = trim($_POST['name'] ?? '');
    $email    = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $role     = $_POST['role'] ?? 'Buyer';

    // Simple Human Validation Check
    if (empty($name) || empty($email) || empty($password)) {
        $error = "All fields are required! Please fill everything in.";
    } elseif (strlen($password) < 6) {
        $error = "Security warning: Your password must be at least 6 characters long.";
    } else {
        
        try {
            // Check if email already belongs to another registered user
            $check_sql = "SELECT id FROM users WHERE email = :email";
            $stmt = $pdo->prepare($check_sql);
            $stmt->execute(['email' => $email]);
            
            if ($stmt->rowCount() > 0) {
                $error = "This email is already registered. Try logging in!";
            } else {
                
                // HASH THE PASSWORD
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);

                // INSERT THE NEW USER ACCOUNT
                $insert_sql = "INSERT INTO users (username, email, password_hash, role) 
                               VALUES (:name, :email, :hashed_password, :role)";
                
                $insert_stmt = $pdo->prepare($insert_sql);
                $insert_stmt->execute([
                    'name'            => $name,
                    'email'           => $email,
                    'hashed_password' => $hashed_password,
                    'role'            => $role
                ]);

                $success = "Account created successfully! You can now log in.";
            }
            
        } catch (PDOException $e) {
            $error = "Database failure: " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>YEBO Marketplace - Create Account</title>
</head>
<body>

    <h2>Join the YEBO Marketplace</h2>

    <?php if (!empty($error)): ?>
        <p style="color: red; font-weight: bold;"><?php echo htmlspecialchars($error); ?></p>
    <?php endif; ?>

    <?php if (!empty($success)): ?>
        <p style="color: green; font-weight: bold;">
            <?php echo htmlspecialchars($success); ?> <a href="login.php">Click here to Log In</a>
        </p>
    <?php endif; ?>

    <form action="register.php" method="POST">
        
        <div>
            <label>Full Name / Username:</label><br>
            <input type="text" name="name" value="<?php echo htmlspecialchars($_POST['name'] ?? ''); ?>" required>
        </div><br>

        <div>
            <label>Email Address:</label><br>
            <input type="email" name="email" value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>" required>
        </div><br>

        <div>
            <label>Password:</label><br>
            <input type="password" name="password" required>
        </div><br>

        <div>
            <label>Account Type:</label><br>
            <select name="role" required>
                <option value="Buyer">Buyer (I want to buy local goods)</option>
                <option value="Seller">Seller (I want to market my products)</option>
            </select>
        </div><br>

        <button type="submit">Create My Account</button>
    </form>

    <p>Already have a profile? <a href="login.php">Log in here</a></p>

</body>
</html>