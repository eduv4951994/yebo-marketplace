<?php
session_start();
session_unset(); // Clear all memory variables
session_destroy(); // Destroy the active session
header("Location: login.php"); // Send back to login
exit();
?>