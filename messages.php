<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once 'includes/connect_db.php';
session_start();

// force login
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$my_id = $_SESSION['user_id'];

// HANDLE SENDING A REPLY
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['reply_text'])) {
    $product_id = intval($_POST['product_id']);
    $receiver_id = intval($_POST['receiver_id']);
    $reply_text = trim($_POST['reply_text']);

    if (!empty($reply_text)) {
        $insert_sql = "INSERT INTO messages (sender_id, receiver_id, product_id, message_text) VALUES (?, ?, ?, ?)";
        $stmt = $pdo->prepare($insert_sql);
        $stmt->execute([$my_id, $receiver_id, $product_id, $reply_text]);
    }

    // refresh to show the new message
    header("Location: messages.php?product_id=$product_id&chat_with=$receiver_id");
    exit();
}

// check if specific chat thread is opened
$is_thread_open = isset($_GET['product_id']) && isset($_GET['chat_with']);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Messages - YEBO</title>
    <link rel="stylesheet" href="assets/css/styles.css?v=<?php echo time(); ?>">
    <style>
        .msg-layout { max-width: 800px; margin: 30px auto; background: white; padding: 20px; border-radius: 8px; box-shadow: 0 4px 10px rgba(0,0,0,0.05); }
        .chat-list-item { padding: 15px; border-bottom: 1px solid #eee; display: block; text-decoration: none; color: #333; }
        .chat-list-item:hover { background: #f8f9fa; }
        .bubble-container { height: 350px; overflow-y: auto; background: #f4f7f6; padding: 15px; border-radius: 5px; margin-bottom: 15px; display: flex; flex-direction: column; gap: 10px; }
        .bubble { padding: 10px 15px; border-radius: 8px; max-width: 70%; word-wrap: break-word; }
        .bubble.me { background: #007bff; color: white; align-self: flex-end; }
        .bubble.them { background: #e2e3e5; color: #333; align-self: flex-start; }
    </style>
</head>
<body>

    <div class="navbar">
        <h1> YEBO Marketplace</h1>
        <div class="nav-links">
            <a href="index.php" style="margin-right: 15px; color: #333;">&larr; Back to Home</a>
            <a href="messages.php" style="font-weight: bold; color: #007bff;">Inbox Home</a>
        </div>
    </div>

    <div class="msg-layout">

        <?php if (!$is_thread_open): ?>
            <h3>Your Conversations</h3>
            <?php
            $inbox_sql = "SELECT m.*, p.title AS product_title, 
                                 u_sender.username AS sender_name, 
                                 u_rcvr.username AS receiver_name 
                          FROM messages m
                          JOIN products p ON m.product_id = p.id
                          JOIN users u_sender ON m.sender_id = u_sender.id
                          JOIN users u_rcvr ON m.receiver_id = u_rcvr.id
                          WHERE m.sender_id = ? OR m.receiver_id = ?
                          ORDER BY m.created_at DESC";
            $stmt = $pdo->prepare($inbox_sql);
            $stmt->execute([$my_id, $my_id]);
            $all_messages = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $printed_chats = [];

            if (count($all_messages) > 0) {
                foreach ($all_messages as $msg) {
                    $partner_id = ($msg['sender_id'] == $my_id) ? $msg['receiver_id'] : $msg['sender_id'];
                    $partner_name = ($msg['sender_id'] == $my_id) ? $msg['receiver_name'] : $msg['sender_name'];
                    $unique_key = $msg['product_id'] . '_' . $partner_id;

                    if (!in_array($unique_key, $printed_chats)) {
                        $printed_chats[] = $unique_key;
                        ?>
                        <a href="messages.php?product_id=<?php echo $msg['product_id']; ?>&chat_with=<?php echo $partner_id; ?>" class="chat-list-item">
                            <strong>Chat with <?php echo htmlspecialchars($partner_name); ?></strong><br>
                            <small style="color: #666;">Regarding: <?php echo htmlspecialchars($msg['product_title']); ?></small><br>
                            <span style="font-size: 13px; color: #888;"><?php echo htmlspecialchars($msg['message_text']); ?></span>
                        </a>
                        <?php
                    }
                }
            } else {
                echo "<p style='color:#666;'>You don't have any active chats yet.</p>";
            }
            ?>

        <?php else: ?>
            <?php
            $pid = intval($_GET['product_id']);
            $with_id = intval($_GET['chat_with']);

            $p_stmt = $pdo->prepare("SELECT title FROM products WHERE id = ?");
            $p_stmt->execute([$pid]);
            $prod = $p_stmt->fetch(PDO::FETCH_ASSOC);

            $u_stmt = $pdo->prepare("SELECT username FROM users WHERE id = ?");
            $u_stmt->execute([$with_id]);
            $partner = $u_stmt->fetch(PDO::FETCH_ASSOC);

            // get the chat history
            $chat_sql = "SELECT m.* FROM messages m 
                         WHERE m.product_id = ? 
                         AND ((m.sender_id = ? AND m.receiver_id = ?) OR (m.sender_id = ? AND m.receiver_id = ?))
                         ORDER BY m.created_at ASC";
            $c_stmt = $pdo->prepare($chat_sql);
            $c_stmt->execute([$pid, $my_id, $with_id, $with_id, $my_id]);
            $chat_history = $c_stmt->fetchAll(PDO::FETCH_ASSOC);
            ?>

            <div style="margin-bottom: 15px;">
                <a href="messages.php" style="text-decoration: none; color: #007bff;">&larr; Back to Inbox List</a>
                <h3 style="margin-top: 10px;">Chat with <?php echo htmlspecialchars($partner['username'] ?? 'User'); ?></h3>
                <small style="color: #555;">Item: <strong><?php echo htmlspecialchars($prod['title'] ?? 'Unknown Product'); ?></strong></small>
            </div>

            <div class="bubble-container">
                <?php if (count($chat_history) > 0): ?>
                    <?php foreach ($chat_history as $chat): ?>
                        <?php $class = ($chat['sender_id'] == $my_id) ? 'me' : 'them'; ?>
                        <div class="bubble <?php echo $class; ?>">
                            <p style="margin: 0; font-size: 14px;"><?php echo htmlspecialchars($chat['message_text']); ?></p>
                            <small style="font-size: 10px; opacity: 0.8; display: block; text-align: right; margin-top: 3px;">
                                <?php echo date('H:i', strtotime($chat['created_at'])); ?>
                            </small>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p style="text-align: center; color: #888; margin-top: 100px;">No messages in this conversation yet.</p>
                <?php endif; ?>
            </div>

            <form action="messages.php" method="POST" style="display: flex; gap: 10px;">
                <input type="hidden" name="product_id" value="<?php echo $pid; ?>">
                <input type="hidden" name="receiver_id" value="<?php echo $with_id; ?>">
                <input type="text" name="reply_text" placeholder="Type a response..." required style="flex: 1; padding: 10px; border: 1px solid #ccc; border-radius: 4px;">
                <button type="submit" style="background: #007bff; color: white; border: none; padding: 0 20px; border-radius: 4px; font-weight: bold; cursor: pointer;">Send</button>
            </form>

        <?php endif; ?>

    </div>

</body>
</html>