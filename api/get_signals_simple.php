<?php
/**
 * Simplified version of get_signals.php for debugging
 * Returns minimal data to avoid errors
 */

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

// Simple query without call_type
$stmt = $conn->prepare("
    SELECT s.id, s.from_user_id, s.signal_type, s.signal_data,
           u.username, u.profile_picture
    FROM signals s
    JOIN users u ON s.from_user_id = u.id
    WHERE s.to_user_id = ? AND s.is_read = 0
    ORDER BY s.created_at ASC
    LIMIT 10
");

if (!$stmt) {
    ob_end_clean();
    echo json_encode([
        'success' => false, 
        'message' => 'Prepare failed: ' . $conn->error,
        'error_code' => $conn->errno
    ]);
    exit();
}

$stmt->bind_param("i", $user_id);

if (!$stmt->execute()) {
    ob_end_clean();
    echo json_encode([
        'success' => false, 
        'message' => 'Execute failed: ' . $stmt->error,
        'error_code' => $stmt->errno
    ]);
    $stmt->close();
    exit();
}

$result = $stmt->get_result();
$signals = [];
$signal_ids = [];

while ($row = $result->fetch_assoc()) {
    $signal_data = $row['signal_data'];
    try {
        $decoded = json_decode($signal_data, true);
        if (json_last_error() === JSON_ERROR_NONE) {
            $signal_data = $decoded;
        }
    } catch (Exception $e) {
        // Keep as string if decode fails
    }
    
    $signals[] = [
        'id' => $row['id'],
        'from_user_id' => $row['from_user_id'],
        'from_username' => $row['username'],
        'from_profile_picture' => $row['profile_picture'],
        'signal_type' => $row['signal_type'],
        'signal_data' => $signal_data,
        'call_type' => 'video' // Default for now
    ];
    $signal_ids[] = $row['id'];
}

$stmt->close();

// Don't mark call-request as read
if (!empty($signal_ids)) {
    $ids_string = implode(',', array_map('intval', $signal_ids));
    $update_query = "UPDATE signals SET is_read = 1 WHERE id IN ($ids_string) AND signal_type != 'call-request'";
    $conn->query($update_query);
}

ob_end_clean();
echo json_encode([
    'success' => true,
    'signals' => $signals,
    'count' => count($signals)
]);
?>

