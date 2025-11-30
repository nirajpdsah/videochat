<?php
$host = getenv('MYSQLHOST');
$user = getenv('MYSQLUSER');
$pass = getenv('MYSQLPASSWORD');
$db = getenv('MYSQLDATABASE');
$port = getenv('MYSQLPORT');

$conn = new mysqli($host, $user, $pass, $db, $port);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

echo "Connected successfully<br>";

$sql = "ALTER TABLE signals 
        ADD COLUMN call_type ENUM('video', 'audio') DEFAULT 'video' AFTER signal_data";

if ($conn->query($sql) === TRUE) {
    echo "✅ Column 'call_type' added successfully!<br>";
} else {
    if (strpos($conn->error, 'Duplicate column') !== false) {
        echo "✅ Column 'call_type' already exists!<br>";
    } else {
        echo "❌ Error: " . $conn->error . "<br>";
    }
}

// Verify
$result = $conn->query("SHOW COLUMNS FROM signals LIKE 'call_type'");
if ($result && $result->num_rows > 0) {
    echo "<br>Verification:<br>";
    $row = $result->fetch_assoc();
    echo "Field: " . $row['Field'] . "<br>";
    echo "Type: " . $row['Type'] . "<br>";
    echo "Default: " . $row['Default'] . "<br>";
}

$conn->close();
echo "<br><strong>Done! Delete this file now.</strong>";
?>