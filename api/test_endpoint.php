<?php
/**
 * Simple test endpoint to verify API is working
 */
header('Content-Type: application/json');

echo json_encode([
    'success' => true,
    'message' => 'API endpoint is working',
    'timestamp' => date('Y-m-d H:i:s')
]);
?>

