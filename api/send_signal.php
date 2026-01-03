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
    error_log("send_signal.php validation failed - to_user_id: $to_user_id, signal_type: '$signal_type', signal_data empty: " . (empty($signal_data) ? 'yes' : 'no'));
    echo json_encode([
        'success' => false, 
        'message' => 'Invalid parameters',
        'debug' => [
            'to_user_id' => $to_user_id,
            'signal_type' => $signal_type,
            'has_signal_data' => !empty($signal_data)
        ]
    ]);
    exit();
}

// Valid signal types
$valid_types = ['offer', 'answer', 'ice-candidate', 'call-request', 'call-accepted', 'call-rejected', 'call-ended', 'receiver-ready', 'video-status', 'audio-status'];
if (!in_array($signal_type, $valid_types)) {
    error_log("send_signal.php invalid signal type: '$signal_type'");
    echo json_encode(['success' => false, 'message' => 'Invalid signal type: ' . $signal_type]);
    exit();
}

// Log what we're about to insert
error_log("send_signal.php inserting - from: $from_user_id, to: $to_user_id, type: '$signal_type', call_type: '$call_type'");

// Insert signal into database - use direct query instead of bind_param to debug
$signal_type_escaped = $conn->real_escape_string($signal_type);
$signal_data_escaped = $conn->real_escape_string($signal_data);
$call_type_escaped = $conn->real_escape_string($call_type);

$query = "INSERT INTO signals (from_user_id, to_user_id, signal_type, signal_data, call_type) 
          VALUES ($from_user_id, $to_user_id, '$signal_type_escaped', '$signal_data_escaped', '$call_type_escaped')";

if ($conn->query($query)) {
    $signal_id = $conn->insert_id;
    
    // Immediately query back what was inserted to verify
    $verify = $conn->query("SELECT signal_type, call_type FROM signals WHERE id = $signal_id");
    $inserted = $verify->fetch_assoc();
    
    echo json_encode([
        'success' => true,
        'message' => 'Signal sent',
        'signal_id' => $signal_id,
        'debug' => [
            'sent_signal_type' => $signal_type,
            'sent_call_type' => $call_type,
            'db_signal_type' => $inserted['signal_type'],
            'db_call_type' => $inserted['call_type'],
            'query' => $query
        ]
    ]);
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to send signal: ' . $conn->error, 'query' => $query]);
}
?>