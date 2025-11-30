<?php
/**
 * API Endpoint: Send WebRTC signaling data
 * Used to exchange connection information between peers
 * Signals can be: offer, answer, or ice-candidate
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
$signal_type = isset($input['signal_type']) ? $input['signal_type'] : '';
$signal_data = isset($input['signal_data']) ? json_encode($input['signal_data']) : '';
$call_type = isset($input['call_type']) ? $input['call_type'] : 'video';

// Validation
if ($to_user_id == 0 || empty($signal_type) || empty($signal_data)) {
    echo json_encode(['success' => false, 'message' => 'Invalid parameters']);
    exit();
}

// Valid signal types
$valid_types = ['offer', 'answer', 'ice-candidate', 'call-request'];
if (!in_array($signal_type, $valid_types)) {
    echo json_encode(['success' => false, 'message' => 'Invalid signal type']);
    exit();
}

// Insert signal into database
$stmt = $conn->prepare("
    INSERT INTO signals (from_user_id, to_user_id, signal_type, signal_data, call_type) 
    VALUES (?, ?, ?, ?, ?)
");
$stmt->bind_param("iisss", $from_user_id, $to_user_id, $signal_type, $signal_data, $call_type);

if ($stmt->execute()) {
    echo json_encode([
        'success' => true,
        'message' => 'Signal sent',
        'signal_id' => $conn->insert_id
    ]);
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to send signal']);
}
?>