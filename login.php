<?php
// ATTACH THE LIVE CONNECTION
require_once 'includes/connect_db.php';

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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>YEBO - Log In</title>
    <style>
        body { font-family: Arial, sans-serif; background-color: #f4f7f6; display: flex; justify-content: center; align-items: center; height: 100vh; margin: 0; }
        .auth-card { background: white; padding: 40px; border-radius: 10px; box-shadow: 0 8px 20px rgba(0,0,0,0.1); width: 100%; max-width: 400px; }
        .auth-card h2 { text-align: center; color: #333; margin-bottom: 20px; font-size: 28px; }
        .input-group { margin-bottom: 20px; position: relative; }
        .input-group label { display: block; margin-bottom: 5px; color: #555; font-size: 14px; font-weight: bold; }
        .input-group input { width: 100%; padding: 12px; border: 1px solid #ccc; border-radius: 5px; font-size: 16px; box-sizing: border-box; outline: none; }
        .input-group input:focus { border-color: #007bff; }
        .btn-primary { width: 100%; padding: 14px; background: #007bff; color: white; border: none; border-radius: 5px; font-size: 18px; font-weight: bold; cursor: pointer; transition: 0.3s; }
        .btn-primary:hover { background: #0056b3; }
        .toggle-password { position: absolute; right: 12px; top: 35px; cursor: pointer; font-size: 14px; color: #007bff; font-weight: bold; user-select: none; }
        .auth-links { text-align: center; margin-top: 15px; font-size: 14px; }
        .auth-links a { color: #007bff; text-decoration: none; }
    </style>
</head>
<body>

    <div class="auth-card">
        <h2>Welcome Back</h2>
        
        <form action="login.php" method="POST">
            
            <div class="input-group">
                <label>Email Address</label>
                <input type="email" name="email" required>
            </div>

            <div class="input-group">
                <label>Password</label>
                <input type="password" name="password" id="login_password" required>
                <span class="toggle-password" id="toggleLogin" onclick="togglePassword('login_password', 'toggleLogin')">Show</span>
            </div>

            <button type="submit" class="btn-primary">Log In</button>
        </form>

        <div class="auth-links">
            <p>Don't have an account? <a href="register.php">Sign Up</a></p>
            <p><a href="index.php">← Back to Storefront</a></p>
        </div>
    </div>

    <script src="assets/js/scripts.js"></script>
</body>
</html>