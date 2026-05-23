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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>YEBO - Sign Up</title>
    <style>
        /* Card Design */
        body { font-family: Arial, sans-serif; background-color: #f4f7f6; display: flex; justify-content: center; align-items: center; height: 100vh; margin: 0; }
        .auth-card { background: white; padding: 40px; border-radius: 10px; box-shadow: 0 8px 20px rgba(0,0,0,0.1); width: 100%; max-width: 400px; }
        .auth-card h2 { text-align: center; color: #333; margin-bottom: 20px; font-size: 28px; }
        .input-group { margin-bottom: 20px; position: relative; }
        .input-group label { display: block; margin-bottom: 5px; color: #555; font-size: 14px; font-weight: bold; }
        .input-group input, .input-group select { width: 100%; padding: 12px; border: 1px solid #ccc; border-radius: 5px; font-size: 16px; box-sizing: border-box; outline: none; }
        .input-group input:focus { border-color: #007bff; }
        .btn-primary { width: 100%; padding: 14px; background: #007bff; color: white; border: none; border-radius: 5px; font-size: 18px; font-weight: bold; cursor: pointer; transition: 0.3s; }
        .btn-primary:hover { background: #0056b3; }
        .toggle-password { position: absolute; right: 12px; top: 35px; cursor: pointer; font-size: 14px; color: #007bff; font-weight: bold; user-select: none; }
        .error-msg { color: #dc3545; font-size: 13px; display: none; margin-top: 5px; font-weight: bold; }
        .auth-links { text-align: center; margin-top: 15px; font-size: 14px; }
        .auth-links a { color: #007bff; text-decoration: none; }
    </style>
</head>
<body>

    <div class="auth-card">
        <h2>Join YEBO</h2>
        
        <form action="register.php" method="POST" id="registerForm" onsubmit="return validateRegistration(event)">
            
            <div class="input-group">
                <label>Username</label>
                <input type="text" name="username" required>
            </div>

            <div class="input-group">
                <label>Email</label>
                <input type="email" name="email" required>
            </div>

            <div class="input-group">
                <label>Role</label>
                <select name="role">
                    <option value="Buyer">Buyer</option>
                    <option value="Seller">Seller</option>
                </select>
            </div>

            <div class="input-group">
                <label>Password</label>
                <input type="password" name="password" id="password" required>
                <span class="toggle-password" id="togglePass1" onclick="togglePassword('password', 'togglePass1')">Show</span>
                <div id="pass-error" class="error-msg"></div>
            </div>

            <button type="submit" class="btn-primary">Create Account</button>
        </form>

        <div class="auth-links">
            <p>Already have an account? <a href="login.php">Log In</a></p>
        </div>
    </div>

    <script src="assets/js/scripts.js"></script>
</body>
</html>