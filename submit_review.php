<?php
require_once 'includes/connect_db.php';
session_start();

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    die("You must be logged in to leave a review.");
}

// check if the form was actually submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $reviewer_id = $_SESSION['user_id'];
    $user_reviewed_id = $_POST['user_reviewed_id'];
    $rating = $_POST['rating'];
    $review_text = trim($_POST['review_text']);

    // Prevent users from reviewing themselves
    if ($reviewer_id == $user_reviewed_id) {
        die("You cannot review yourself!");
    }

    // insert the review into the database
    try {
        $sql = "INSERT INTO user_reviews (reviewer_id, user_reviewed_id, rating, review_text) 
                VALUES (?, ?, ?, ?)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$reviewer_id, $user_reviewed_id, $rating, $review_text]);
        
        // Redirect back to the dashboard
        header("Location: dashboard.php?msg=ReviewSubmitted");
        exit;
    } catch (PDOException $e) {
        die("Error saving review: " . $e->getMessage());
    }
}
?>