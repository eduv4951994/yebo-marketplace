<?php
// connect to the database and start tracking the session
require_once 'includes/connect_db.php';
session_start();

// set up empty message variables 
$error = "";
$success = "";

// Check if the user submitted the signup form
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    // collect the text fields + trim off any accidental extra spaces
    $name     = trim($_POST['name'] ?? '');
    $email    = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $role     = $_POST['role'] ?? 'Buyer';

    // Basic Validation
    if (empty($name) || empty($email) || empty($password)) {
        $error = "All fields are required! Please fill everything in.";
    } elseif (strlen($password) < 6) {
        $error = "Your password must be at least 6 characters long.";
    } else {
        
        try {
            // check if the email is already being used
            $check_sql = "SELECT id FROM users WHERE email = :email";
            $stmt = $pdo->prepare($check_sql);
            $stmt->execute(['email' => $email]);
            
            if ($stmt->rowCount() > 0) {
                $error = "This email is already registered. Try logging in instead!";
            } else {
                
                // make the password 
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);

                // save the new user account into the database
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
            $error = "Something went wrong with the database: " . $e->getMessage();
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
        /* General Layout  */
        body { font-family: Arial, sans-serif; background-color: #f4f6f9; display: flex; justify-content: center; align-items: center; height: 100vh; margin: 0; }
        .auth-card { background: white; padding: 40px; border-radius: 10px; box-shadow: 0 4px 16px rgba(0,0,0,0.05); width: 100%; max-width: 400px; }
        .auth-card h2 { text-align: center; color: #333; margin-bottom: 6px; font-size: 24px; }
        .auth-card p.subtitle { text-align: center; color: #888; font-size: 13px; margin-bottom: 24px; }

        .input-group { margin-bottom: 18px; position: relative; }
        .input-group label { display: block; margin-bottom: 5px; color: #555; font-size: 13px; font-weight: bold; }
        .input-group input, .input-group select { width: 100%; padding: 12px; border: 1px solid #ddd; border-radius: 6px; font-size: 15px; box-sizing: border-box; outline: none; background: #fafbfc; }
        
        .input-group input:focus { border-color: #007bff; background: #fff; }
        
        .btn-primary { width: 100%; padding: 13px; background: #007bff; color: white; border: none; border-radius: 6px; font-size: 16px; font-weight: bold; cursor: pointer; transition: 0.2s; margin-top: 4px; }
        .btn-primary:hover { background: #0056b3; } /* Smooth darker blue hover transition */
        
        .toggle-password { position: absolute; right: 12px; top: 36px; cursor: pointer; font-size: 13px; color: #888; font-weight: bold; user-select: none; }
        .auth-links { text-align: center; margin-top: 18px; font-size: 13px; color: #888; }
        .auth-links a { color: #007bff; font-weight: bold; text-decoration: none; }
        .auth-links a:hover { text-decoration: underline; }
        
        .msg-error { background: #fff0f0; border: 1px solid #f5c6cb; color: #b04040; font-size: 13px; font-weight: bold; padding: 10px 14px; border-radius: 6px; margin-bottom: 18px; }
        .msg-success { background: #f0fff4; border: 1px solid #b7dfc5; color: #2e7d32; font-size: 13px; font-weight: bold; padding: 10px 14px; border-radius: 6px; margin-bottom: 18px; }
        
        .logo { text-align: center; font-size: 22px; font-weight: bold; letter-spacing: 4px; color: #007bff; margin-bottom: 4px; }
    </style>
</head>
<body>

    <div class="auth-card">

        <div class="logo">YEBO</div>
        <h2>Create account</h2>
        <p class="subtitle">Join the marketplace</p>

        <?php if (!empty($error)): ?>
            <div class="msg-error"><?php echo $error; ?></div>
        <?php endif; ?>

        <?php if (!empty($success)): ?>
            <div class="msg-success">
                <?php echo $success; ?>
                <br><a href="login.php" style="color: #2e7d32;">Click here to log in &rarr;</a>
            </div>
        <?php endif; ?>

        <form action="register.php" method="POST" id="registerForm">
            
            <div class="input-group">
                <label>Username</label>
                <input type="text" name="name" required>
            </div>

            <div class="input-group">
                <label>Email</label>
                <input type="email" name="email" required>
            </div>

            <div class="input-group">
                <label>I want to</label>
                <select name="role">
                    <option value="Buyer">Buy items</option>
                    <option value="Seller">Sell items</option>
                </select>
            </div>

            <div class="input-group">
                <label>Password</label>
                <input type="password" name="password" id="password" required>
                <span class="toggle-password" id="togglePass1" onclick="togglePassword('password', 'togglePass1')">Show</span>
            </div>

            <button type="submit" class="btn-primary">Create Account</button>
        </form>

        <div class="auth-links">
            <p>Already have an account? <a href="login.php">Log in</a></p>
        </div>

    </div>

    <script src="assets/js/scripts.js"></script>
</body>
</html>
