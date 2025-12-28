<?php
/**
 * API Endpoint: Delete a signal (call request)
 * Used to clean up call-request signals after accept/reject
 */

header('Content-Type: application/json');

require_once '../config.php';

if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

$user_id = $_SESSION['user_id'];

// Get JSON data
$input = json_decode(file_get_contents('php://input'), true);
$from_user_id = isset($input['from_user_id']) ? intval($input['from_user_id']) : 0;
$signal_type = isset($input['signal_type']) ? $input['signal_type'] : 'call-request';

if ($from_user_id == 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid user ID']);
    exit();
}

// Delete signals from this user
$stmt = $conn->prepare("
    DELETE FROM signals 
    WHERE from_user_id = ? AND to_user_id = ? AND signal_type = ?
");

$stmt->bind_param("iis", $from_user_id, $user_id, $signal_type);

if ($stmt->execute()) {
    $deleted_count = $stmt->affected_rows;
    $stmt->close();
    echo json_encode([
        'success' => true, 
        'message' => 'Signal deleted',
        'deleted_count' => $deleted_count
    ]);
} else {
    $stmt->close();
    echo json_encode(['success' => false, 'message' => 'Failed to delete signal']);
}
?>
