<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

$host = getenv('MYSQLHOST');
$user = getenv('MYSQLUSER');
$pass = getenv('MYSQLPASSWORD');
$db = getenv('MYSQLDATABASE');
$port = getenv('MYSQLPORT');
?>
<!DOCTYPE html>
<html>
<head>
    <title>Database Update</title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 800px; margin: 50px auto; padding: 20px; background: #f0f2f5; }
        .container { background: white; padding: 30px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        h1 { color: #1a73e8; }
        .success { color: #0f9d58; padding: 12px; background: #e6f4ea; border-left: 4px solid #0f9d58; margin: 10px 0; }
        .error { color: #d93025; padding: 12px; background: #fce8e6; border-left: 4px solid #d93025; margin: 10px 0; }
        .info { color: #1967d2; padding: 12px; background: #e8f0fe; border-left: 4px solid #1967d2; margin: 10px 0; }
        .warning { color: #f9ab00; padding: 12px; background: #fef7e0; border-left: 4px solid #f9ab00; margin: 10px 0; }
        .btn { display: inline-block; padding: 12px 24px; background: #1a73e8; color: white; text-decoration: none; border-radius: 4px; margin: 10px 10px 10px 0; }
        .code { background: #263238; color: #aed581; padding: 15px; border-radius: 4px; font-family: monospace; margin: 10px 0; }
        pre { background: #f5f5f5; padding: 15px; border-radius: 4px; overflow-x: auto; }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔄 Database Update</h1>
        <p>Updating signals table to add 'call-request' signal type...</p>

<?php

echo "<div class='info'><strong>Connecting to database...</strong></div>";

$conn = new mysqli($host, $user, $pass, $db, $port);

if ($conn->connect_error) {
    echo "<div class='error'>❌ Connection failed: " . $conn->connect_error . "</div>";
    echo "</div></body></html>";
    exit;
}

echo "<div class='success'>✅ Connected successfully!</div>";

// Show current structure
echo "<h3>Current signal_type column:</h3>";
$result = $conn->query("SHOW COLUMNS FROM signals LIKE 'signal_type'");
if ($result && $row = $result->fetch_assoc()) {
    echo "<pre>";
    print_r($row);
    echo "</pre>";
}

// Update the signals table
echo "<h3>Updating signals table...</h3>";

$sql = "ALTER TABLE signals 
        MODIFY signal_type ENUM('offer', 'answer', 'ice-candidate', 'call-request') NOT NULL";

if ($conn->query($sql) === TRUE) {
    echo "<div class='success'>✅ Table 'signals' updated successfully!</div>";
    echo "<div class='info'>Added 'call-request' to signal_type ENUM values.</div>";
} else {
    echo "<div class='error'>❌ Error updating table: " . $conn->error . "</div>";
}

// Verify the update
echo "<h3>Verifying update:</h3>";
$result = $conn->query("SHOW COLUMNS FROM signals LIKE 'signal_type'");
if ($result && $row = $result->fetch_assoc()) {
    echo "<div class='success'><strong>Updated signal_type column:</strong></div>";
    echo "<pre>";
    print_r($row);
    echo "</pre>";
    
    // Check if 'call-request' is in the Type field
    if (strpos($row['Type'], 'call-request') !== false) {
        echo "<div class='success'>✅ Verification successful! 'call-request' is now available.</div>";
    } else {
        echo "<div class='error'>⚠️ 'call-request' not found in ENUM values. Please check manually.</div>";
    }
}

$conn->close();

echo "<div class='success' style='text-align: center; padding: 20px; margin-top: 20px;'>";
echo "<h2>🎉 Update Complete!</h2>";
echo "<p>The signals table has been updated with the new signal type.</p>";
echo "</div>";

echo "<div class='warning'>";
echo "<h3>⚠️ Security Warning</h3>";
echo "<p><strong>DELETE THIS FILE NOW!</strong></p>";
echo "<div class='code'>";
echo "git rm update-db.php<br>";
echo "git commit -m \"Remove update script\"<br>";
echo "git push";
echo "</div>";
echo "</div>";

echo "<a href='index.php' class='btn'>Go to Home Page</a>";

?>

    </div>
</body>
</html>