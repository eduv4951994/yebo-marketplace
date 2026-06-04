<?php
// ROUTING & SECURITY CHECK
require_once '../includes/connect_db.php';
require_once '../includes/auth.php';

check_login();

// strict Admin gate
if ($_SESSION['user_role'] !== 'Admin') {
    header("Location: ../dashboard.php");
    exit();
}

$admin_id = $_SESSION['user_id'];
$all_users = [];

// PROCESS ROLE UPDATE ACTION 
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_role_user_id'])) {
    $target_user_id = $_POST['update_role_user_id'];
    $new_role       = $_POST['new_role'];

    try {
        if ($target_user_id == $admin_id) {
            die("Security Error: You cannot alter your own admin privileges.");
        }

        $role_sql = "UPDATE users SET role = :role WHERE id = :id";
        $role_stmt = $pdo->prepare($role_sql);
        $role_stmt->execute(['role' => $new_role, 'id' => $target_user_id]);

        header("Location: manage_users.php");
        exit();
    } catch (PDOException $e) {
        die("Failed to update user privileges: " . $e->getMessage());
    }
}

// PROCESS USER DELETION ACTION
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['delete_user_id'])) {
    $delete_user_id = $_POST['delete_user_id'];

    try {
        if ($delete_user_id == $admin_id) {
            die("Security Error: You cannot delete your own administrator account.");
        }

        $delete_sql = "DELETE FROM users WHERE id = :id";
        $delete_stmt = $pdo->prepare($delete_sql);
        $delete_stmt->execute(['id' => $delete_user_id]);

        header("Location: manage_users.php");
        exit();
    } catch (PDOException $e) {
        die("Failed to remove user account: " . $e->getMessage());
    }
}

// FETCH ALL USERS FROM THE DATABASE ('username' & 'role')
try {
    $sql = "SELECT id, username, role, created_at FROM users ORDER BY created_at DESC";
    $stmt = $pdo->query($sql);
    $all_users = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Error loading user ledger: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Users - YEBO Admin</title>
    <link rel="stylesheet" href="../assets/css/styles.css">
</head>
<body style="padding: 20px; font-family: Arial, sans-serif;">

    <div style="display: flex; justify-content: space-between; align-items: center; border-bottom: 2px solid #ccc; padding-bottom: 15px; margin-bottom: 20px;">
        <h1>👥 System User Management</h1>
        <p><a href="index.php" style="font-weight: bold;">← Back to Admin Center</a></p>
    </div>

    <p>Global registry of all accounts registered on the YEBO Marketplace platform ecosystem:</p>

    <table border="1" cellpadding="10" cellspacing="0" style="width: 100%; border-collapse: collapse; text-align: left;">
        <thead style="background: #343a40; color: white;">
            <tr>
                <th>User ID</th>
                <th>Username</th>
                <th>Current Role</th>
                <th>Account Status / Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($all_users as $user): ?>
                <tr>
                    <td>#<?php echo $user['id']; ?></td>
                    <td><strong><?php echo htmlspecialchars($user['username']); ?></strong></td>
                    <td>
                        <form action="manage_users.php" method="POST" style="display: inline;">
                            <input type="hidden" name="update_role_user_id" value="<?php echo $user['id']; ?>">
                            <select name="new_role" onchange="this.form.submit()" <?php echo ($user['id'] == $admin_id) ? 'disabled' : ''; ?> style="padding: 5px变量;">
                                <option value="Buyer" <?php echo ($user['role'] === 'Buyer') ? 'selected' : ''; ?>>Buyer</option>
                                <option value="Seller" <?php echo ($user['role'] === 'Seller') ? 'selected' : ''; ?>>Seller</option>
                                <option value="Admin" <?php echo ($user['role'] === 'Admin') ? 'selected' : ''; ?>>Admin</option>
                            </select>
                        </form>
                    </td>
                    <td>
                        <?php if ($user['id'] == $admin_id): ?>
                            <span style="color: #007bff; font-weight: bold;">Active Session (You)</span>
                        <?php else: ?>
                            <form action="manage_users.php" method="POST" onsubmit="return confirm('ADMIN WARNING: Are you sure you want to permanently terminate this user account?');" style="display: inline;">
                                <input type="hidden" name="delete_user_id" value="<?php echo $user['id']; ?>">
                                <button type="submit" style="color: white; background-color: #dc3545; padding: 5px 10px; border: none; border-radius: 4px; cursor: pointer;">
                                    Terminate Account
                                </button>
                            </form>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

</body>
</html>