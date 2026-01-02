<?php
/**
 * API Endpoint: Get all users except current user
 * Returns list of users with their status (online, offline, on_call)
 */
require_once '../config.php';

header('Content-Type: application/json');

// Check if user is logged in
if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

$current_user_id = $_SESSION['user_id'];

// First, mark users as offline if they haven't been active in last 15 seconds
$conn->query("UPDATE users SET status = 'offline' WHERE status = 'online' AND last_seen < DATE_SUB(NOW(), INTERVAL 15 SECOND)");

// Get all users except current user
$stmt = $conn->prepare("
    SELECT id, username, profile_picture, status, last_seen 
    FROM users 
    WHERE id != ? 
    ORDER BY status DESC, username ASC
");
$stmt->bind_param("i", $current_user_id);
$stmt->execute();
$result = $stmt->get_result();

$users = [];
while ($row = $result->fetch_assoc()) {
    // Ensure status is never null - default to 'offline'
    $status = $row['status'] ?: 'offline';
    
    $users[] = [
        'id' => $row['id'],
        'username' => $row['username'],
        'profile_picture' => $row['profile_picture'],
        'status' => $status,
        'last_seen' => $row['last_seen']
    ];
}

echo json_encode([
    'success' => true,
    'users' => $users
]);
?>