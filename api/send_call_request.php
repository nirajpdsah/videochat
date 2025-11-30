<?php
/**
 * API Endpoint: Send call request notification
 * Notifies a user that someone wants to call them
 */

// Prevent any output before JSON
error_reporting(E_ALL);
ini_set('display_errors', 0); // Don't display errors, we'll handle them

// Set JSON header immediately - before any includes
header('Content-Type: application/json');

// Start output buffering to catch any errors
ob_start();

try {
    require_once '../config.php';
} catch (Exception $e) {
    ob_end_clean();
    echo json_encode(['success' => false, 'message' => 'Config error: ' . $e->getMessage()]);
    exit();
}

// Check if logged in
if (!isLoggedIn()) {
    ob_end_clean();
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

// Get JSON data
$raw_input = file_get_contents('php://input');
$input = json_decode($raw_input, true);

// Check if JSON parsing failed
if (json_last_error() !== JSON_ERROR_NONE && !empty($raw_input)) {
    ob_end_clean();
    echo json_encode([
        'success' => false, 
        'message' => 'Invalid JSON: ' . json_last_error_msg(),
        'raw_input' => substr($raw_input, 0, 100)
    ]);
    exit();
}

$from_user_id = $_SESSION['user_id'];
$to_user_id = isset($input['to_user_id']) ? intval($input['to_user_id']) : 0;
$call_type = isset($input['call_type']) ? $input['call_type'] : 'video';

// Validation
if ($to_user_id == 0) {
    ob_end_clean();
    echo json_encode(['success' => false, 'message' => 'Invalid user ID', 'input' => $input]);
    exit();
}

// Check if target user is online
$check_stmt = $conn->prepare("SELECT id, status FROM users WHERE id = ?");
$check_stmt->bind_param("i", $to_user_id);
$check_stmt->execute();
$result = $check_stmt->get_result();

if ($result->num_rows === 0) {
    ob_end_clean();
    echo json_encode(['success' => false, 'message' => 'User not found', 'to_user_id' => $to_user_id]);
    $check_stmt->close();
    exit();
}

$target_user = $result->fetch_assoc();
$check_stmt->close();

if ($target_user['status'] === 'on_call') {
    ob_end_clean();
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

// Clear any output buffer
ob_end_clean();

if ($stmt->execute()) {
    $signal_id = $conn->insert_id;
    $stmt->close();
    echo json_encode([
        'success' => true,
        'message' => 'Call request sent',
        'signal_id' => $signal_id
    ]);
    exit();
} else {
    // Get the actual error message
    $error = $stmt->error ?: $conn->error;
    $stmt->close();
    error_log("Call request error: " . $error);
    echo json_encode([
        'success' => false, 
        'message' => 'Failed to send call request: ' . $error,
        'sql_error' => $error
    ]);
    exit();
}
?>

