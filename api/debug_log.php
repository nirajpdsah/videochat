<?php
// Simple debug logger: stores logs in session and displays them
// Access via browser to see all logs, or POST to add logs

session_start();

// Initialize log storage in session
if (!isset($_SESSION['debug_logs'])) {
    $_SESSION['debug_logs'] = [];
}

// Handle GET request - display logs as HTML
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    echo '<!DOCTYPE html><html><head><title>Debug Logs</title><style>
    body { font-family: monospace; padding: 20px; background: #1e1e1e; color: #d4d4d4; }
    .log { padding: 10px; margin: 5px 0; background: #2d2d2d; border-left: 3px solid #007acc; }
    .event { color: #4ec9b0; font-weight: bold; }
    .time { color: #858585; font-size: 0.9em; }
    .payload { color: #ce9178; margin-top: 5px; }
    button { padding: 10px 20px; background: #007acc; color: white; border: none; cursor: pointer; margin-bottom: 20px; }
    button:hover { background: #005a9e; }
    </style></head><body>';
    
    echo '<h1>WebRTC Debug Logs</h1>';
    echo '<button onclick="location.reload()">Refresh</button>';
    echo '<button onclick="fetch(\'debug_log.php?clear=1\').then(() => location.reload())">Clear Logs</button>';
    echo '<p>Total logs: ' . count($_SESSION['debug_logs']) . ' | Session ID: ' . session_id() . '</p>';
    
    // Show backup file logs if session is empty
    $backupFile = __DIR__ . '/../debug_logs_backup.txt';
    if (file_exists($backupFile)) {
        $backupContent = file_get_contents($backupFile);
        $backupLines = array_filter(explode("\n", $backupContent));
        echo '<p style="color: #ce9178;">Backup file has ' . count($backupLines) . ' entries. <a href="?show_backup=1" style="color: #007acc;">Show backup logs</a></p>';
        
        if (isset($_GET['show_backup'])) {
            echo '<h2>Backup Logs (from file)</h2>';
            foreach (array_reverse($backupLines) as $line) {
                $log = json_decode($line, true);
                if ($log) {
                    echo '<div class="log">';
                    echo '<span class="time">' . htmlspecialchars($log['ts']) . '</span> ';
                    echo '<span class="event">' . htmlspecialchars($log['event']) . '</span>';
                    if (!empty($log['payload'])) {
                        echo '<div class="payload">' . htmlspecialchars(json_encode($log['payload'], JSON_PRETTY_PRINT)) . '</div>';
                    }
                    echo '</div>';
                }
            }
            echo '</body></html>';
            exit();
        }
    }
    
    if (isset($_GET['clear'])) {
        $_SESSION['debug_logs'] = [];
        echo '<p style="color: #4ec9b0;">Logs cleared!</p>';
    }
    
    if (empty($_SESSION['debug_logs'])) {
        echo '<p>No logs yet. Start a call to see debug events.</p>';
    } else {
        foreach (array_reverse($_SESSION['debug_logs']) as $log) {
            echo '<div class="log">';
            echo '<span class="time">' . htmlspecialchars($log['ts']) . '</span> ';
            echo '<span class="event">' . htmlspecialchars($log['event']) . '</span>';
            if (!empty($log['payload'])) {
                echo '<div class="payload">' . htmlspecialchars(json_encode($log['payload'], JSON_PRETTY_PRINT)) . '</div>';
            }
            echo '</div>';
        }
    }
    
    echo '</body></html>';
    exit();
}

// Handle POST request - add log entry
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    $event = isset($input['event']) ? $input['event'] : 'unknown';
    $payload = isset($input['payload']) ? $input['payload'] : [];
    
    $log = [
        'ts' => date('H:i:s'),
        'event' => $event,
        'payload' => $payload,
        'session_id' => session_id() // Track session
    ];
    
    // Use a simple file fallback if session isn't persisting
    $logFile = __DIR__ . '/../debug_logs_backup.txt';
    file_put_contents($logFile, json_encode($log) . "\n", FILE_APPEND);
    
    $_SESSION['debug_logs'][] = $log;
    
    // Keep only last 100 logs to avoid memory issues
    if (count($_SESSION['debug_logs']) > 100) {
        $_SESSION['debug_logs'] = array_slice($_SESSION['debug_logs'], -100);
    }
    
    echo json_encode(['success' => true, 'logged' => count($_SESSION['debug_logs'])]);
    exit();
}
?>