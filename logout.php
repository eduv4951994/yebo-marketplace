<?php
session_start();
session_unset(); // xlear all memory variables
session_destroy(); // destroy the active session
header("Location: login.php"); // send back to login
exit();
?>