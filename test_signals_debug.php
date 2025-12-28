<?php
require_once 'config.php';

echo "<h2>Table Structure:</h2>";
$structure = $conn->query("DESCRIBE signals");
echo "<table border='1' style='border-collapse: collapse;'>";
echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th></tr>";
while ($row = $structure->fetch_assoc()) {
    echo "<tr>";
    echo "<td>" . $row['Field'] . "</td>";
    echo "<td>" . $row['Type'] . "</td>";
    echo "<td>" . $row['Null'] . "</td>";
    echo "<td>" . $row['Key'] . "</td>";
    echo "<td>" . ($row['Default'] ?? 'NULL') . "</td>";
    echo "<td>" . $row['Extra'] . "</td>";
    echo "</tr>";
}
echo "</table>";

echo "<h2>Recent Signals:</h2>";

// Check recent signals
$query = "SELECT id, from_user_id, to_user_id, signal_type, call_type, is_read, created_at 
          FROM signals 
          ORDER BY created_at DESC 
          LIMIT 10";

$result = $conn->query($query);

echo "<table border='1' style='border-collapse: collapse;'>";
echo "<tr><th>ID</th><th>From</th><th>To</th><th>Signal Type</th><th>Call Type</th><th>Is Read</th><th>Created</th></tr>";

while ($row = $result->fetch_assoc()) {
    echo "<tr>";
    echo "<td>" . $row['id'] . "</td>";
    echo "<td>" . $row['from_user_id'] . "</td>";
    echo "<td>" . $row['to_user_id'] . "</td>";
    echo "<td>" . ($row['signal_type'] ?? 'NULL') . "</td>";
    echo "<td>" . ($row['call_type'] ?? 'NULL') . "</td>";
    echo "<td>" . ($row['is_read'] ?? 'NULL') . "</td>";
    echo "<td>" . $row['created_at'] . "</td>";
    echo "</tr>";
}

echo "</table>";
?>
