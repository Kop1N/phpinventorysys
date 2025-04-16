<?php
session_start();

// Destroy the session to log out
session_unset();  // Remove session variables
session_destroy(); // Destroy the session

// Redirect to login page
header("Location: login.php");
exit();
?>
