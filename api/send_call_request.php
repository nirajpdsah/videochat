<?php
/**
 * API Endpoint: Send call request notification
 * Notifies a user that someone wants to call them
 */
require_once '../config.php';

header('Content-Type: application/json');

if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

// Get JSON data
$input = json_decode(file_get_contents('php://input'), true);

$from_user_id = $_SESSION['user_id'];
$to_user_id = isset($input['to_user_id']) ? intval($input['to_user_id']) : 0;
$call_type = isset($input['call_type']) ? $input['call_type'] : 'video';

// Validation
if ($to_user_id == 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid user ID']);
    exit();
}

// Check if target user is online
$check_stmt = $conn->prepare("SELECT id, status FROM users WHERE id = ?");
$check_stmt->bind_param("i", $to_user_id);
$check_stmt->execute();
$result = $check_stmt->get_result();

if ($result->num_rows === 0) {
    echo json_encode(['success' => false, 'message' => 'User not found']);
    $check_stmt->close();
    exit();
}

$target_user = $result->fetch_assoc();
$check_stmt->close();

if ($target_user['status'] === 'on_call') {
    echo json_encode(['success' => false, 'message' => 'User is busy']);
    exit();
}

// Send call request as a special signal
$signal_data = json_encode(['call_request' => true, 'call_type' => $call_type]);

$stmt = $conn->prepare("
    INSERT INTO signals (from_user_id, to_user_id, signal_type, signal_data, call_type) 
    VALUES (?, ?, 'call-request', ?, ?)
");
$stmt->bind_param("iiss", $from_user_id, $to_user_id, $signal_data, $call_type);

if ($stmt->execute()) {
    echo json_encode([
        'success' => true,
        'message' => 'Call request sent'
    ]);
} else {
    // Get the actual error message
    $error = $stmt->error ?: $conn->error;
    error_log("Call request error: " . $error);
    echo json_encode([
        'success' => false, 
        'message' => 'Failed to send call request: ' . $error
    ]);
}
$stmt->close();
?>

