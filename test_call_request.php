<?php
/**
 * Test Call Request API
 * Use this to debug call request issues
 * Visit: your-app.railway.app/test_call_request.php
 */

require_once 'config.php';

if (!isLoggedIn()) {
    die("Please login first: <a href='login.php'>Login</a>");
}

$current_user = getCurrentUser();
?>
<!DOCTYPE html>
<html>
<head>
    <title>Test Call Request</title>
    <style>
        body { font-family: Arial; padding: 20px; max-width: 800px; margin: 0 auto; }
        .success { background: #d4edda; padding: 15px; border-radius: 5px; margin: 10px 0; }
        .error { background: #f8d7da; padding: 15px; border-radius: 5px; margin: 10px 0; }
        .info { background: #d1ecf1; padding: 15px; border-radius: 5px; margin: 10px 0; }
        button { padding: 10px 20px; margin: 5px; cursor: pointer; }
        pre { background: #f5f5f5; padding: 10px; border-radius: 5px; overflow-x: auto; }
    </style>
</head>
<body>
    <h1>🔧 Call Request API Test</h1>
    
    <div class="info">
        <strong>Current User:</strong> <?php echo htmlspecialchars($current_user['username']); ?> (ID: <?php echo $current_user['id']; ?>)
    </div>
    
    <h2>Test 1: Check Database Schema</h2>
    <?php
    // Check if signal_type supports 'call-request'
    $result = $conn->query("SHOW COLUMNS FROM signals WHERE Field = 'signal_type'");
    if ($result && $row = $result->fetch_assoc()) {
        $enum = $row['Type'];
        echo "<div class='info'>";
        echo "<strong>Current signal_type definition:</strong><br>";
        echo "<pre>$enum</pre>";
        
        if (strpos($enum, 'call-request') !== false) {
            echo "<div class='success'>✅ 'call-request' is in the ENUM</div>";
        } else {
            echo "<div class='error'>❌ 'call-request' is NOT in the ENUM</div>";
            echo "<p><strong>Fix:</strong> Run this SQL:</p>";
            echo "<pre>ALTER TABLE signals MODIFY signal_type ENUM('offer', 'answer', 'ice-candidate', 'call-request') NOT NULL;</pre>";
        }
        echo "</div>";
    } else {
        echo "<div class='error'>❌ Could not check signals table</div>";
    }
    ?>
    
    <h2>Test 2: Get Other Users</h2>
    <?php
    $stmt = $conn->prepare("SELECT id, username, status FROM users WHERE id != ? LIMIT 5");
    $stmt->bind_param("i", $current_user['id']);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        echo "<p>Select a user to test call request:</p>";
        while ($user = $result->fetch_assoc()) {
            echo "<button onclick='testCallRequest({$user['id']}, \"{$user['username']}\")'>";
            echo "Test Call Request to {$user['username']} (ID: {$user['id']}, Status: {$user['status']})";
            echo "</button><br>";
        }
    } else {
        echo "<div class='error'>No other users found. Create another account to test.</div>";
    }
    $stmt->close();
    ?>
    
    <h2>Test 3: Manual API Test</h2>
    <button onclick="testAPI()">Test API Endpoint</button>
    <div id="apiResult"></div>
    
    <h2>Test 4: Check Recent Signals</h2>
    <?php
    $stmt = $conn->prepare("
        SELECT s.*, u1.username as from_username, u2.username as to_username 
        FROM signals s
        LEFT JOIN users u1 ON s.from_user_id = u1.id
        LEFT JOIN users u2 ON s.to_user_id = u2.id
        ORDER BY s.created_at DESC 
        LIMIT 10
    ");
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        echo "<table border='1' cellpadding='10' style='border-collapse: collapse; width: 100%;'>";
        echo "<tr><th>ID</th><th>From</th><th>To</th><th>Type</th><th>Call Type</th><th>Created</th></tr>";
        while ($signal = $result->fetch_assoc()) {
            echo "<tr>";
            echo "<td>{$signal['id']}</td>";
            echo "<td>{$signal['from_username']} ({$signal['from_user_id']})</td>";
            echo "<td>{$signal['to_username']} ({$signal['to_user_id']})</td>";
            echo "<td>{$signal['signal_type']}</td>";
            echo "<td>{$signal['call_type']}</td>";
            echo "<td>{$signal['created_at']}</td>";
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "<div class='info'>No signals found yet.</div>";
    }
    $stmt->close();
    ?>
    
    <hr>
    <p><a href="dashboard.php">← Back to Dashboard</a></p>
    
    <script>
        async function testCallRequest(userId, username) {
            const resultDiv = document.getElementById('apiResult');
            resultDiv.innerHTML = '<div class="info">Testing call request to ' + username + '...</div>';
            
            try {
                const response = await fetch('api/send_call_request.php', {
                    method: 'POST',
                    headers: {'Content-Type': 'application/json'},
                    body: JSON.stringify({
                        to_user_id: userId,
                        call_type: 'video'
                    })
                });
                
                const data = await response.json();
                
                if (data.success) {
                    resultDiv.innerHTML = '<div class="success">✅ Success: ' + data.message + '</div>';
                } else {
                    resultDiv.innerHTML = '<div class="error">❌ Error: ' + data.message + '</div>';
                }
            } catch (error) {
                resultDiv.innerHTML = '<div class="error">❌ Network Error: ' + error.message + '</div>';
            }
        }
        
        async function testAPI() {
            const resultDiv = document.getElementById('apiResult');
            resultDiv.innerHTML = '<div class="info">Testing API endpoint...</div>';
            
            try {
                const response = await fetch('api/send_call_request.php', {
                    method: 'POST',
                    headers: {'Content-Type': 'application/json'},
                    body: JSON.stringify({
                        to_user_id: 999, // Invalid ID for testing
                        call_type: 'video'
                    })
                });
                
                const data = await response.json();
                resultDiv.innerHTML = '<div class="info"><pre>' + JSON.stringify(data, null, 2) + '</pre></div>';
            } catch (error) {
                resultDiv.innerHTML = '<div class="error">❌ Error: ' + error.message + '</div>';
            }
        }
    </script>
</body>
</html>

