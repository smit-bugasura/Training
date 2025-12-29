<?php
// Start session to clear user data
session_start();

// Remove all session data and destroy the session
session_unset();
session_destroy();

// Redirect to login page after logout
header('Location: login.php');
exit;
