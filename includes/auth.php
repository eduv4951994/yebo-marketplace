<?php
// We ensure a session is active so we can check who is browsing
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// FUNCTION 1: Protect pages meant ONLY for logged-in users (Buyers or Sellers)
function check_login() {
    if (!isset($_SESSION['user_id'])) {
        // If no user id is found in the session memory, bounce them to the login page
        header("Location: login.php");
        exit();
    }
}

// FUNCTION 2: Protect pages meant ONLY for the Admin
function check_admin() {
    // If they aren't logged in, or their tracked database role is not 'Admin', block them
    if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'Admin') {
        // Kick them out to the standard login page with a warning
        header("Location: ../login.php?error=unauthorized");
        exit();
    }
}
?>