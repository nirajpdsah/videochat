<?php
/**
 * API Endpoint: Send call request notification
 * Notifies a user that someone wants to call them
 */

// Set error handler to catch all errors
function handleError($errno, $errstr, $errfile, $errline) {
    header('Content-Type: application/json');
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'PHP Error',
        'error' => $errstr,
        'file' => basename($errfile),
        'line' => $errline
    ]);
    exit();
}

function handleFatalError() {
    $error = error_get_last();
    if ($error !== NULL && in_array($error['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR])) {
        header('Content-Type: application/json');
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'message' => 'Fatal Error',
            'error' => $error['message'],
            'file' => basename($error['file']),
            'line' => $error['line']
        ]);
        exit();
    }
}

register_shutdown_function('handleFatalError');
set_error_handler('handleError', E_ALL);

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
} catch (Error $e) {
    ob_end_clean();
    echo json_encode(['success' => false, 'message' => 'Config error: ' . $e->getMessage()]);
    exit();
}

// Check if logged in
if (!function_exists('isLoggedIn')) {
    ob_end_clean();
    echo json_encode(['success' => false, 'message' => 'isLoggedIn function not found']);
    exit();
}

if (!isLoggedIn()) {
    ob_end_clean();
    echo json_encode(['success' => false, 'message' => 'Unauthorized', 'session_id' => session_id()]);
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

// Check if session has user_id
if (!isset($_SESSION['user_id'])) {
    ob_end_clean();
    echo json_encode(['success' => false, 'message' => 'Session user_id not set']);
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

// Check database connection
if (!isset($conn) || $conn->connect_error) {
    ob_end_clean();
    echo json_encode([
        'success' => false, 
        'message' => 'Database connection error',
        'error' => isset($conn) ? $conn->connect_error : 'Connection not set'
    ]);
    exit();
}

// Check if target user is online
$check_stmt = $conn->prepare("SELECT id, status FROM users WHERE id = ?");
if (!$check_stmt) {
    ob_end_clean();
    echo json_encode([
        'success' => false, 
        'message' => 'Failed to prepare statement',
        'error' => $conn->error
    ]);
    exit();
}

$check_stmt->bind_param("i", $to_user_id);
if (!$check_stmt->execute()) {
    ob_end_clean();
    echo json_encode([
        'success' => false, 
        'message' => 'Failed to execute query',
        'error' => $check_stmt->error
    ]);
    $check_stmt->close();
    exit();
}
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

if (!$stmt) {
    ob_end_clean();
    echo json_encode([
        'success' => false, 
        'message' => 'Failed to prepare INSERT statement',
        'error' => $conn->error
    ]);
    exit();
}

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

