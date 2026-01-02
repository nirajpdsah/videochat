<?php
/**
 * Logout Script
 * Logs out the user and updates their status to offline
 */
require_once 'config.php';

if (isLoggedIn()) {
    // Update user status to offline and update last_seen
    $user_id = $_SESSION['user_id'];
    $stmt = $conn->prepare("UPDATE users SET status = 'offline', last_seen = NOW() WHERE id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    
    // Destroy session
    session_unset();
    session_destroy();
}

// Redirect to login page
header('Location: login.php');
exit();
?>