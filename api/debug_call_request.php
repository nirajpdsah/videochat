<?php
/**
 * Debug version of send_call_request.php
 * Shows detailed error information
 */

// Enable error display for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

header('Content-Type: application/json');

echo json_encode([
    'step' => 'Starting debug',
    'config_exists' => file_exists('../config.php')
]);

require_once '../config.php';

echo json_encode([
    'step' => 'Config loaded',
    'conn_set' => isset($conn),
    'conn_error' => isset($conn) ? $conn->connect_error : 'Not set',
    'session_started' => session_status() === PHP_SESSION_ACTIVE,
    'user_id' => isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 'Not set'
]);

if (!isLoggedIn()) {
    echo json_encode(['step' => 'Not logged in']);
    exit();
}

$raw_input = file_get_contents('php://input');
$input = json_decode($raw_input, true);

echo json_encode([
    'step' => 'Input received',
    'raw_input' => $raw_input,
    'parsed_input' => $input,
    'json_error' => json_last_error_msg()
]);

$from_user_id = $_SESSION['user_id'];
$to_user_id = isset($input['to_user_id']) ? intval($input['to_user_id']) : 0;
$call_type = isset($input['call_type']) ? $input['call_type'] : 'video';

echo json_encode([
    'step' => 'Variables set',
    'from_user_id' => $from_user_id,
    'to_user_id' => $to_user_id,
    'call_type' => $call_type
]);

// Test database query
$test_stmt = $conn->prepare("SELECT id, status FROM users WHERE id = ?");
if (!$test_stmt) {
    echo json_encode(['step' => 'Prepare failed', 'error' => $conn->error]);
    exit();
}

$test_stmt->bind_param("i", $to_user_id);
if (!$test_stmt->execute()) {
    echo json_encode(['step' => 'Execute failed', 'error' => $test_stmt->error]);
    exit();
}

$result = $test_stmt->get_result();
echo json_encode([
    'step' => 'Query executed',
    'num_rows' => $result->num_rows
]);

if ($result->num_rows > 0) {
    $user = $result->fetch_assoc();
    echo json_encode(['step' => 'User found', 'user' => $user]);
}

$test_stmt->close();

// Test INSERT
$signal_data = json_encode(['call_request' => true, 'call_type' => $call_type]);
$insert_stmt = $conn->prepare("
    INSERT INTO signals (from_user_id, to_user_id, signal_type, signal_data, call_type) 
    VALUES (?, ?, 'call-request', ?, ?)
");

if (!$insert_stmt) {
    echo json_encode(['step' => 'INSERT prepare failed', 'error' => $conn->error]);
    exit();
}

$insert_stmt->bind_param("iiss", $from_user_id, $to_user_id, $signal_data, $call_type);

if ($insert_stmt->execute()) {
    echo json_encode([
        'step' => 'INSERT successful',
        'signal_id' => $conn->insert_id,
        'success' => true
    ]);
} else {
    echo json_encode([
        'step' => 'INSERT failed',
        'error' => $insert_stmt->error,
        'success' => false
    ]);
}

$insert_stmt->close();
?>

