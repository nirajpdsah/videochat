<?php
/**
 * API Endpoint: Update user status
 * Updates user's status (online, offline, on_call)
 */
require_once '../config.php';

header('Content-Type: application/json');

if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

// Get JSON data from request
$input = json_decode(file_get_contents('php://input'), true);
$status = isset($input['status']) ? $input['status'] : 'online';

// Validate status
$valid_statuses = ['online', 'offline', 'on_call'];
if (!in_array($status, $valid_statuses)) {
    echo json_encode(['success' => false, 'message' => 'Invalid status']);
    exit();
}

$user_id = $_SESSION['user_id'];

// Update user status and last_seen timestamp
$stmt = $conn->prepare("UPDATE users SET status = ?, last_seen = NOW() WHERE id = ?");
$stmt->bind_param("si", $status, $user_id);

if ($stmt->execute()) {
    echo json_encode(['success' => true, 'status' => $status]);
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to update status']);
}
?>