<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Get Railway environment variables
$host = getenv('MYSQLHOST');
$user = getenv('MYSQLUSER');
$pass = getenv('MYSQLPASSWORD');
$db = getenv('MYSQLDATABASE');
$port = getenv('MYSQLPORT');
?>
<!DOCTYPE html>
<html>
<head>
    <title>Database Update - Add call-request Signal Type</title>
    <style>
        body { 
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Arial, sans-serif;
            max-width: 900px; 
            margin: 40px auto; 
            padding: 20px; 
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
        }
        .container { 
            background: white; 
            padding: 40px; 
            border-radius: 12px; 
            box-shadow: 0 10px 40px rgba(0,0,0,0.2); 
        }
        h1 { 
            color: #333; 
            margin-bottom: 10px;
            font-size: 28px;
        }
        h2 {
            color: #555;
            font-size: 20px;
            margin-top: 30px;
            border-bottom: 2px solid #667eea;
            padding-bottom: 10px;
        }
        .subtitle {
            color: #666;
            margin-bottom: 30px;
        }
        .success { 
            color: #0f9d58; 
            padding: 15px; 
            background: #e6f4ea; 
            border-left: 5px solid #0f9d58; 
            margin: 15px 0; 
            border-radius: 4px;
            font-weight: 500;
        }
        .error { 
            color: #d93025; 
            padding: 15px; 
            background: #fce8e6; 
            border-left: 5px solid #d93025; 
            margin: 15px 0; 
            border-radius: 4px;
            font-weight: 500;
        }
        .info { 
            color: #1967d2; 
            padding: 15px; 
            background: #e8f0fe; 
            border-left: 5px solid #1967d2; 
            margin: 15px 0; 
            border-radius: 4px;
        }
        .warning { 
            color: #f9ab00; 
            padding: 15px; 
            background: #fef7e0; 
            border-left: 5px solid #f9ab00; 
            margin: 15px 0; 
            border-radius: 4px;
            font-weight: 500;
        }
        .btn { 
            display: inline-block; 
            padding: 12px 30px; 
            background: #667eea; 
            color: white; 
            text-decoration: none; 
            border-radius: 6px; 
            margin: 10px 10px 10px 0;
            font-weight: 600;
            transition: all 0.3s;
        }
        .btn:hover { 
            background: #5568d3;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
        }
        .code { 
            background: #1e1e1e; 
            color: #d4d4d4; 
            padding: 20px; 
            border-radius: 6px; 
            font-family: 'Courier New', monospace; 
            margin: 15px 0;
            overflow-x: auto;
            font-size: 14px;
            line-height: 1.6;
        }
        .code .keyword { color: #569cd6; }
        .code .string { color: #ce9178; }
        pre { 
            background: #f8f9fa; 
            padding: 15px; 
            border-radius: 6px; 
            overflow-x: auto;
            border: 1px solid #e0e0e0;
            margin: 15px 0;
        }
        .step {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
            margin: 20px 0;
        }
        .checkmark {
            color: #0f9d58;
            font-size: 24px;
            margin-right: 10px;
        }
        .crossmark {
            color: #d93025;
            font-size: 24px;
            margin-right: 10px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 15px 0;
        }
        table th {
            background: #667eea;
            color: white;
            padding: 12px;
            text-align: left;
            font-weight: 600;
        }
        table td {
            padding: 10px 12px;
            border-bottom: 1px solid #e0e0e0;
        }
        table tr:hover {
            background: #f8f9fa;
        }
        .highlight {
            background: #fff3cd;
            padding: 2px 6px;
            border-radius: 3px;
            font-weight: 600;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔄 Database Update</h1>
        <p class="subtitle">Adding 'call-request' signal type and 'call_type' column to the signals table</p>

<?php

echo "<div class='step'>";
echo "<h2>Step 1: Connection</h2>";
echo "<div class='info'><strong>🔌 Connecting to database...</strong><br>";
echo "Host: " . $host . "<br>";
echo "Database: " . $db . "<br>";
echo "Port: " . $port . "</div>";

$conn = new mysqli($host, $user, $pass, $db, $port);

if ($conn->connect_error) {
    echo "<div class='error'><span class='crossmark'>✗</span><strong>Connection Failed!</strong><br>";
    echo "Error: " . $conn->connect_error . "</div>";
    echo "</div></div></body></html>";
    exit;
}

echo "<div class='success'><span class='checkmark'>✓</span><strong>Connected Successfully!</strong></div>";
echo "</div>";

// Show current structure
echo "<div class='step'>";
echo "<h2>Step 2: Current Structure</h2>";
echo "<p>Checking current <span class='highlight'>signal_type</span> column configuration...</p>";

$result = $conn->query("SHOW COLUMNS FROM signals LIKE 'signal_type'");
if ($result && $row = $result->fetch_assoc()) {
    echo "<table>";
    echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th></tr>";
    echo "<tr>";
    echo "<td>" . htmlspecialchars($row['Field']) . "</td>";
    echo "<td><code>" . htmlspecialchars($row['Type']) . "</code></td>";
    echo "<td>" . htmlspecialchars($row['Null']) . "</td>";
    echo "<td>" . htmlspecialchars($row['Key']) . "</td>";
    echo "<td>" . htmlspecialchars($row['Default'] ?? 'NULL') . "</td>";
    echo "</tr>";
    echo "</table>";
    
    if (strpos($row['Type'], 'call-request') !== false) {
        echo "<div class='warning'><span class='checkmark'>✓</span><strong>Note:</strong> The 'call-request' signal type already exists in the database. No update needed.</div>";
        $needs_update = false;
    } else {
        echo "<div class='info'><strong>Current ENUM values:</strong> offer, answer, ice-candidate<br>";
        echo "<strong>Missing:</strong> call-request</div>";
        $needs_update = true;
    }
} else {
    echo "<div class='error'><span class='crossmark'>✗</span>Could not retrieve column information.</div>";
    $needs_update = false;
}
echo "</div>";

// Check if call_type column exists
echo "<div class='step'>";
echo "<h2>Step 3: Check call_type Column</h2>";
echo "<p>Checking if <span class='highlight'>call_type</span> column exists...</p>";

$result = $conn->query("SHOW COLUMNS FROM signals LIKE 'call_type'");
$call_type_exists = ($result && $result->num_rows > 0);

if ($call_type_exists) {
    $row = $result->fetch_assoc();
    echo "<div class='info'><span class='checkmark'>✓</span><strong>Column exists:</strong> call_type<br>";
    echo "<strong>Type:</strong> <code>" . htmlspecialchars($row['Type']) . "</code></div>";
    $needs_call_type_update = false;
} else {
    echo "<div class='warning'><strong>Missing:</strong> call_type column not found. Will be added.</div>";
    $needs_call_type_update = true;
}
echo "</div>";

// Update the signals table
if ($needs_update || $needs_call_type_update) {
    echo "<div class='step'>";
    echo "<h2>Step 4: Updating Database</h2>";
    
    $update_count = 0;
    $update_errors = 0;
    
    // Update 1: Modify signal_type ENUM
    if ($needs_update) {
        echo "<h3>Update 1: Adding 'call-request' to signal_type</h3>";
        echo "<div class='code'>";
        echo "<span class='keyword'>ALTER TABLE</span> signals<br>";
        echo "<span class='keyword'>MODIFY</span> signal_type <span class='keyword'>ENUM</span>(<span class='string'>'offer'</span>, <span class='string'>'answer'</span>, <span class='string'>'ice-candidate'</span>, <span class='string'>'call-request'</span>) <span class='keyword'>NOT NULL</span>;";
        echo "</div>";

        $sql = "ALTER TABLE signals 
                MODIFY signal_type ENUM('offer', 'answer', 'ice-candidate', 'call-request') NOT NULL";

        if ($conn->query($sql) === TRUE) {
            echo "<div class='success'><span class='checkmark'>✓</span><strong>Success!</strong> signal_type updated with 'call-request'.</div>";
            $update_count++;
        } else {
            echo "<div class='error'><span class='crossmark'>✗</span><strong>Failed!</strong> Error: " . $conn->error . "</div>";
            $update_errors++;
        }
    }
    
    // Update 2: Add call_type column
    if ($needs_call_type_update) {
        echo "<h3>Update 2: Adding call_type column</h3>";
        echo "<div class='code'>";
        echo "<span class='keyword'>ALTER TABLE</span> signals<br>";
        echo "<span class='keyword'>ADD COLUMN</span> call_type <span class='keyword'>ENUM</span>(<span class='string'>'video'</span>, <span class='string'>'audio'</span>) <span class='keyword'>DEFAULT</span> <span class='string'>'video'</span> <span class='keyword'>AFTER</span> signal_data;";
        echo "</div>";

        $sql = "ALTER TABLE signals 
                ADD COLUMN call_type ENUM('video', 'audio') DEFAULT 'video' AFTER signal_data";

        if ($conn->query($sql) === TRUE) {
            echo "<div class='success'><span class='checkmark'>✓</span><strong>Success!</strong> call_type column added.</div>";
            $update_count++;
        } else {
            // Check if error is because column already exists
            if (strpos($conn->error, 'Duplicate column') !== false) {
                echo "<div class='info'><span class='checkmark'>✓</span><strong>Column already exists.</strong> No action needed.</div>";
            } else {
                echo "<div class='error'><span class='crossmark'>✗</span><strong>Failed!</strong> Error: " . $conn->error . "</div>";
                $update_errors++;
            }
        }
    }
    
    echo "<div class='info'><strong>Updates completed:</strong> $update_count<br>";
    if ($update_errors > 0) {
        echo "<strong style='color: #d93025;'>Errors encountered:</strong> $update_errors";
    }
    echo "</div>";
    echo "</div>";

    // Verify the updates
    echo "<div class='step'>";
    echo "<h2>Step 5: Verification</h2>";
    echo "<p>Confirming all updates were applied correctly...</p>";

    // Check signal_type
    echo "<h3>signal_type column:</h3>";
    $result = $conn->query("SHOW COLUMNS FROM signals LIKE 'signal_type'");
    if ($result && $row = $result->fetch_assoc()) {
        echo "<table>";
        echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Default</th></tr>";
        echo "<tr>";
        echo "<td>" . htmlspecialchars($row['Field']) . "</td>";
        echo "<td><code>" . htmlspecialchars($row['Type']) . "</code></td>";
        echo "<td>" . htmlspecialchars($row['Null']) . "</td>";
        echo "<td>" . htmlspecialchars($row['Default'] ?? 'NULL') . "</td>";
        echo "</tr>";
        echo "</table>";
        
        if (strpos($row['Type'], 'call-request') !== false) {
            echo "<div class='success'><span class='checkmark'>✓</span><strong>Verified:</strong> 'call-request' is available<br>";
            echo "<strong>ENUM values:</strong> offer, answer, ice-candidate, <span class='highlight'>call-request</span></div>";
        } else {
            echo "<div class='warning'><span class='crossmark'>✗</span>'call-request' not found in ENUM values.</div>";
        }
    }
    
    // Check call_type
    echo "<h3>call_type column:</h3>";
    $result = $conn->query("SHOW COLUMNS FROM signals LIKE 'call_type'");
    if ($result && $row = $result->fetch_assoc()) {
        echo "<table>";
        echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Default</th></tr>";
        echo "<tr>";
        echo "<td>" . htmlspecialchars($row['Field']) . "</td>";
        echo "<td><code>" . htmlspecialchars($row['Type']) . "</code></td>";
        echo "<td>" . htmlspecialchars($row['Null']) . "</td>";
        echo "<td>" . htmlspecialchars($row['Default'] ?? 'NULL') . "</td>";
        echo "</tr>";
        echo "</table>";
        
        echo "<div class='success'><span class='checkmark'>✓</span><strong>Verified:</strong> call_type column exists<br>";
        echo "<strong>ENUM values:</strong> <span class='highlight'>video</span>, <span class='highlight'>audio</span><br>";
        echo "<strong>Default:</strong> video</div>";
    } else {
        echo "<div class='error'><span class='crossmark'>✗</span>call_type column not found!</div>";
    }
    
    // Show all signals columns
    echo "<h3>Complete signals table structure:</h3>";
    $result = $conn->query("SHOW COLUMNS FROM signals");
    if ($result) {
        echo "<table>";
        echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th></tr>";
        while ($row = $result->fetch_assoc()) {
            echo "<tr>";
            echo "<td><strong>" . htmlspecialchars($row['Field']) . "</strong></td>";
            echo "<td><code>" . htmlspecialchars($row['Type']) . "</code></td>";
            echo "<td>" . htmlspecialchars($row['Null']) . "</td>";
            echo "<td>" . htmlspecialchars($row['Key']) . "</td>";
            echo "<td>" . htmlspecialchars($row['Default'] ?? 'NULL') . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    }
    
    echo "</div>";
}

$conn->close();

echo "<div class='success' style='text-align: center; padding: 30px; margin-top: 30px; font-size: 18px;'>";
echo "<h2 style='color: #0f9d58; margin-top: 0;'>🎉 Database Update Complete!</h2>";
echo "<p style='margin: 10px 0;'>Your signals table has been successfully updated with:</p>";
echo "<ul style='text-align: left; display: inline-block; margin: 15px 0;'>";
echo "<li>✓ 'call-request' signal type</li>";
echo "<li>✓ 'call_type' column for video/audio calls</li>";
echo "</ul>";
echo "<p style='font-size: 14px; color: #666;'>The video chat application can now handle both video and audio call requests properly.</p>";
echo "</div>";

echo "<div class='warning'>";
echo "<h3 style='margin-top: 0;'>⚠️ CRITICAL: Delete This File</h3>";
echo "<p><strong>This file must be removed from your server immediately!</strong></p>";
echo "<p>Run these commands to delete it:</p>";
echo "<div class='code'>";
echo "git rm update-db.php<br>";
echo "git commit -m <span class='string'>\"Remove database update script\"</span><br>";
echo "git push";
echo "</div>";
echo "</div>";

echo "<div style='text-align: center; margin-top: 30px;'>";
echo "<a href='index.php' class='btn'>🏠 Go to Home Page</a>";
echo "<a href='dashboard.php' class='btn'>📱 Open Dashboard</a>";
echo "</div>";

?>

    </div>
</body>
</html>