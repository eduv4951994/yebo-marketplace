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
        body { font-family: Arial, sans-serif; background-color: #f5f4f0; display: flex; justify-content: center; align-items: center; height: 100vh; margin: 0; }
        .auth-card { background: white; padding: 40px; border-radius: 10px; box-shadow: 0 4px 16px rgba(0,0,0,0.08); width: 100%; max-width: 400px; }
        .auth-card h2 { text-align: center; color: #1a1a1a; margin-bottom: 6px; font-size: 24px; letter-spacing: 1px; }
        .auth-card p.subtitle { text-align: center; color: #888; font-size: 13px; margin-bottom: 24px; }
        .input-group { margin-bottom: 18px; position: relative; }
        .input-group label { display: block; margin-bottom: 5px; color: #444; font-size: 13px; font-weight: bold; }
        .input-group input, .input-group select { width: 100%; padding: 12px; border: 1px solid #ddd; border-radius: 6px; font-size: 15px; box-sizing: border-box; outline: none; background: #fafaf8; }
        .input-group input:focus { border-color: #1a1a1a; background: #fff; }
        .btn-primary { width: 100%; padding: 13px; background: #1a1a1a; color: white; border: none; border-radius: 6px; font-size: 16px; font-weight: bold; cursor: pointer; transition: 0.2s; margin-top: 4px; }
        .btn-primary:hover { background: #333; }
        .toggle-password { position: absolute; right: 12px; top: 36px; cursor: pointer; font-size: 13px; color: #888; font-weight: bold; user-select: none; }
        .auth-links { text-align: center; margin-top: 18px; font-size: 13px; color: #888; }
        .auth-links a { color: #1a1a1a; font-weight: bold; text-decoration: none; }
        .auth-links a:hover { text-decoration: underline; }
        .msg-error { background: #fff0f0; border: 1px solid #f5c6cb; color: #b04040; font-size: 13px; font-weight: bold; padding: 10px 14px; border-radius: 6px; margin-bottom: 18px; }
        .msg-success { background: #f0fff4; border: 1px solid #b7dfc5; color: #2e7d32; font-size: 13px; font-weight: bold; padding: 10px 14px; border-radius: 6px; margin-bottom: 18px; }
        .logo { text-align: center; font-size: 22px; font-weight: bold; letter-spacing: 4px; color: #1a1a1a; margin-bottom: 4px; }
    </style>
</head>
<body>

    <div class="auth-card">

        <div class="logo">YEBO</div>
        <h2>Create account</h2>
        <p class="subtitle">Join the marketplace</p>

        <!-- ERROR MESSAGE -->
        <?php if (!empty($error)): ?>
            <div class="msg-error"><?php echo $error; ?></div>
        <?php endif; ?>

        <!-- SUCCESS MESSAGE -->
        <?php if (!empty($success)): ?>
            <div class="msg-success">
                <?php echo $success; ?>
                <br><a href="login.php" style="color: #2e7d32;">Click here to log in →</a>
            </div>
        <?php endif; ?>

        <form action="register.php" method="POST" id="registerForm">
            
            <div class="input-group">
                <label>Username</label>
                <!-- FIX: name="name" must match $_POST['name'] in PHP above -->
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