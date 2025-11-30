<?php
/**
 * Debug page to check signal status
 * Use this to verify signals are being created and retrieved correctly
 */

require_once 'config.php';

if (!isLoggedIn()) {
    die('Please login first');
}

$current_user = getCurrentUser();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Signal Debugger</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            padding: 20px;
            background: #f0f0f0;
        }
        .container {
            max-width: 1200px;
            margin: 0 auto;
            background: white;
            padding: 20px;
            border-radius: 8px;
        }
        h1 {
            color: #333;
        }
        .section {
            margin: 20px 0;
            padding: 15px;
            background: #f9f9f9;
            border-left: 4px solid #4CAF50;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 10px 0;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 10px;
            text-align: left;
        }
        th {
            background: #4CAF50;
            color: white;
        }
        .refresh-btn {
            padding: 10px 20px;
            background: #4CAF50;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
        }
        .refresh-btn:hover {
            background: #45a049;
        }
        .status {
            display: inline-block;
            padding: 5px 10px;
            border-radius: 4px;
            color: white;
        }
        .status.unread {
            background: #ff9800;
        }
        .status.read {
            background: #9e9e9e;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>📡 Signal Debugger</h1>
        <p>Current User: <strong><?php echo htmlspecialchars($current_user['username']); ?></strong> (ID: <?php echo $current_user['id']; ?>)</p>
        <button class="refresh-btn" onclick="location.reload()">🔄 Refresh</button>
        <button class="refresh-btn" onclick="window.location.href='dashboard.php'">← Back to Dashboard</button>
        
        <div class="section">
            <h2>Incoming Signals (to me)</h2>
            <?php
            $query = "
                SELECT s.*, u.username as from_username
                FROM signals s
                LEFT JOIN users u ON s.from_user_id = u.id
                WHERE s.to_user_id = ?
                ORDER BY s.created_at DESC
                LIMIT 20
            ";
            $stmt = $conn->prepare($query);
            $stmt->bind_param("i", $current_user['id']);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows > 0) {
                echo '<table>';
                echo '<tr><th>ID</th><th>From</th><th>Signal Type</th><th>Call Type</th><th>Status</th><th>Created</th><th>Data Preview</th></tr>';
                while ($row = $result->fetch_assoc()) {
                    $status_class = isset($row['is_read']) && $row['is_read'] ? 'read' : 'unread';
                    $status_text = isset($row['is_read']) && $row['is_read'] ? 'Read' : 'Unread';
                    $data_preview = substr($row['signal_data'], 0, 50);
                    echo '<tr>';
                    echo '<td>' . $row['id'] . '</td>';
                    echo '<td>' . htmlspecialchars($row['from_username']) . '</td>';
                    echo '<td><strong>' . $row['signal_type'] . '</strong></td>';
                    echo '<td>' . ($row['call_type'] ?? 'N/A') . '</td>';
                    echo '<td><span class="status ' . $status_class . '">' . $status_text . '</span></td>';
                    echo '<td>' . $row['created_at'] . '</td>';
                    echo '<td>' . htmlspecialchars($data_preview) . '...</td>';
                    echo '</tr>';
                }
                echo '</table>';
            } else {
                echo '<p>No incoming signals found.</p>';
            }
            $stmt->close();
            ?>
        </div>
        
        <div class="section">
            <h2>Outgoing Signals (from me)</h2>
            <?php
            $query = "
                SELECT s.*, u.username as to_username
                FROM signals s
                LEFT JOIN users u ON s.to_user_id = u.id
                WHERE s.from_user_id = ?
                ORDER BY s.created_at DESC
                LIMIT 20
            ";
            $stmt = $conn->prepare($query);
            $stmt->bind_param("i", $current_user['id']);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows > 0) {
                echo '<table>';
                echo '<tr><th>ID</th><th>To</th><th>Signal Type</th><th>Call Type</th><th>Status</th><th>Created</th><th>Data Preview</th></tr>';
                while ($row = $result->fetch_assoc()) {
                    $status_class = isset($row['is_read']) && $row['is_read'] ? 'read' : 'unread';
                    $status_text = isset($row['is_read']) && $row['is_read'] ? 'Read' : 'Unread';
                    $data_preview = substr($row['signal_data'], 0, 50);
                    echo '<tr>';
                    echo '<td>' . $row['id'] . '</td>';
                    echo '<td>' . htmlspecialchars($row['to_username']) . '</td>';
                    echo '<td><strong>' . $row['signal_type'] . '</strong></td>';
                    echo '<td>' . ($row['call_type'] ?? 'N/A') . '</td>';
                    echo '<td><span class="status ' . $status_class . '">' . $status_text . '</span></td>';
                    echo '<td>' . $row['created_at'] . '</td>';
                    echo '<td>' . htmlspecialchars($data_preview) . '...</td>';
                    echo '</tr>';
                }
                echo '</table>';
            } else {
                echo '<p>No outgoing signals found.</p>';
            }
            $stmt->close();
            ?>
        </div>
        
        <div class="section">
            <h2>All Users</h2>
            <?php
            $query = "SELECT id, username, status, last_seen FROM users ORDER BY username";
            $result = $conn->query($query);
            
            if ($result->num_rows > 0) {
                echo '<table>';
                echo '<tr><th>ID</th><th>Username</th><th>Status</th><th>Last Seen</th></tr>';
                while ($row = $result->fetch_assoc()) {
                    echo '<tr>';
                    echo '<td>' . $row['id'] . '</td>';
                    echo '<td>' . htmlspecialchars($row['username']) . '</td>';
                    echo '<td>' . $row['status'] . '</td>';
                    echo '<td>' . ($row['last_seen'] ?? 'Never') . '</td>';
                    echo '</tr>';
                }
                echo '</table>';
            }
            ?>
        </div>
        
        <div class="section">
            <h2>Live Signal Check</h2>
            <p>Click the button below to test the get_signals API:</p>
            <button class="refresh-btn" onclick="testGetSignals()">Test get_signals.php</button>
            <pre id="signalResult" style="background: #333; color: #0f0; padding: 15px; margin: 10px 0; border-radius: 4px; max-height: 300px; overflow: auto;"></pre>
        </div>
    </div>
    
    <script>
        async function testGetSignals() {
            const resultEl = document.getElementById('signalResult');
            resultEl.textContent = 'Loading...';
            
            try {
                const response = await fetch('api/get_signals.php');
                const data = await response.json();
                resultEl.textContent = JSON.stringify(data, null, 2);
            } catch (error) {
                resultEl.textContent = 'Error: ' + error.message;
            }
        }
        
        // Auto-refresh every 5 seconds
        let countdown = 10;
        setInterval(() => {
            countdown--;
            if (countdown <= 0) {
                location.reload();
            }
        }, 1000);
    </script>
</body>
</html>
