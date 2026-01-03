<?php
/**
 * API Endpoint: Update user status
 * Updates user's status (online, offline, on_call)
 */
require_once '../config.php';

// Set timezone to Nepal
date_default_timezone_set('Asia/Kathmandu');

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

// Update user status and last_seen timestamp using PHP time
$current_time = date('Y-m-d H:i:s');
$stmt = $conn->prepare("UPDATE users SET status = ?, last_seen = ? WHERE id = ?");
$stmt->bind_param("ssi", $status, $current_time, $user_id);

if ($stmt->execute()) {
    echo json_encode(['success' => true, 'status' => $status]);
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to update status']);
}
?>