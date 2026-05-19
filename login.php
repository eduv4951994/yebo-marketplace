<?php
// ATTACH THE LIVE CONNECTION
require_once 'includes/connect_dp.php';

// Start a session to remember who logs in across different browser pages
session_start();

$error = "";

// DETECT IF THE USER CLICKED LOG IN
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    $email    = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($email) || empty($password)) {
        $error = "Please fill in both your email and password.";
    } else {
        try {
            // Find the user by their unique email address
            $sql = "SELECT id, username, password_hash, role FROM users WHERE email = :email";
            $stmt = $pdo->prepare($sql);
            $stmt->execute(['email' => $email]);
            $user = $stmt->fetch(PDO::ATTR_DEFAULT_FETCH_MODE ? PDO::FETCH_ASSOC : PDO::FETCH_ASSOC);

            // VERIFY THE PASSWORD WITH THE HASHED SAFE COPY
            // password_verify() automatically unpacks our hashed string and checks it
            if ($user && password_verify($password, $user['password_hash'])) {
                
                // Set our session variables (This is your core RBAC tracking)
                $_SESSION['user_id']   = $user['id'];
                $_SESSION['username']  = $user['username'];
                $_SESSION['user_role'] = $user['role']; // Will hold 'Buyer', 'Seller', or 'Admin'

                // Redirect the user immediately to their unified dashboard
                header("Location: dashboard.php");
                exit();
                
            } else {
                $error = "Invalid email or password. Please try again.";
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
    <title>YEBO Marketplace - Log In</title>
</head>
<body>

    <h2>Log In to Your Account</h2>

    <?php if (!empty($error)): ?>
        <p style="color: red; font-weight: bold;"><?php echo htmlspecialchars($error); ?></p>
    <?php endif; ?>

    <form action="login.php" method="POST">
        <div>
            <label>Email Address:</label><br>
            <input type="email" name="email" required>
        </div><br>

        <div>
            <label>Password:</label><br>
            <input type="password" name="password" required>
        </div><br>

        <button type="submit">Log In</button>
    </form>

    <p>New to YEBO? <a href="register.php">Register an account here</a></p>

</body>
</html>