<?php
/**
 * API Endpoint: Get pending WebRTC signals for current user
 * Retrieves unread signals sent to this user
 */

// Prevent any output before JSON
error_reporting(E_ALL);
ini_set('display_errors', 0);
ob_start();

header('Content-Type: application/json');

try {
    require_once '../config.php';
} catch (Exception $e) {
    ob_end_clean();
    echo json_encode(['success' => false, 'message' => 'Config error: ' . $e->getMessage()]);
    exit();
}

if (!isLoggedIn()) {
    ob_end_clean();
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

$user_id = $_SESSION['user_id'];

// Check if call_type column exists
$has_call_type = false;
$check_column = $conn->query("SHOW COLUMNS FROM signals LIKE 'call_type'");
if ($check_column && $check_column->num_rows > 0) {
    $has_call_type = true;
    if ($check_column) $check_column->free();
}

// Build query based on whether call_type exists
if ($has_call_type) {
    $query = "
        SELECT s.id, s.from_user_id, s.signal_type, s.signal_data, s.call_type,
               u.username, u.profile_picture
        FROM signals s
        JOIN users u ON s.from_user_id = u.id
        WHERE s.to_user_id = ? AND s.is_read = 0
        ORDER BY s.created_at ASC
    ";
} else {
    $query = "
        SELECT s.id, s.from_user_id, s.signal_type, s.signal_data,
               u.username, u.profile_picture
        FROM signals s
        JOIN users u ON s.from_user_id = u.id
        WHERE s.to_user_id = ? AND s.is_read = 0
        ORDER BY s.created_at ASC
    ";
}

$stmt = $conn->prepare($query);

if (!$stmt) {
    ob_end_clean();
    echo json_encode([
        'success' => false, 
        'message' => 'Database error: ' . $conn->error,
        'error_code' => $conn->errno
    ]);
    exit();
}

$stmt->bind_param("i", $user_id);

if (!$stmt->execute()) {
    ob_end_clean();
    echo json_encode([
        'success' => false, 
        'message' => 'Query error: ' . $stmt->error,
        'error_code' => $stmt->errno
    ]);
    $stmt->close();
    exit();
}

$result = $stmt->get_result();

$signals = [];
$signal_ids = [];

while ($row = $result->fetch_assoc()) {
    $signal_data = json_decode($row['signal_data'], true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        $signal_data = $row['signal_data']; // Use raw if JSON decode fails
    }
    
    $signals[] = [
        'id' => $row['id'],
        'from_user_id' => $row['from_user_id'],
        'from_username' => $row['username'],
        'from_profile_picture' => $row['profile_picture'],
        'signal_type' => $row['signal_type'],
        'signal_data' => $signal_data,
        'call_type' => isset($row['call_type']) ? $row['call_type'] : 'video'
    ];
    $signal_ids[] = $row['id'];
}

$stmt->close();

// Mark signals as read (but NOT call-request signals - they should stay until accepted/rejected)
if (!empty($signal_ids)) {
    // Only mark non-call-request signals as read
    // Call requests should stay unread until handled
    $ids_string = implode(',', array_map('intval', $signal_ids));
    $update_stmt = $conn->prepare("
        UPDATE signals 
        SET is_read = 1 
        WHERE id IN ($ids_string) AND signal_type != 'call-request'
    ");
    if ($update_stmt) {
        $update_stmt->execute();
        $update_stmt->close();
    }
}

ob_end_clean();
echo json_encode([
    'success' => true,
    'signals' => $signals
]);
?>