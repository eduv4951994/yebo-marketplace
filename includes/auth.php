<?php
// we ensure the session is active so we can check who is browsing
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// FUNCTION 1: protect pages meant ONLY for loggedin users, buyers & sellers
function check_login() {
    if (!isset($_SESSION['user_id'])) {
        // if no user id is found in the session memory then bounce them to the login page
        header("Location: login.php");
        exit();
    }
}

// FUNCTION 2: protect pages meant only for the Admin
function check_admin() {
    // if they arentt logged in or their tracked database role is not 'Admin' block them
    if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'Admin') {
        // kick them out to the standard login page 
        header("Location: ../login.php?error=unauthorized");
        exit();
    }
}
?>