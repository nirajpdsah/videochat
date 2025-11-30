<?php
/**
 * Database Connection Test
 * Use this file to verify your database connection in Railway
 * Visit: your-app.railway.app/test_connection.php
 */

require_once 'config.php';

?>
<!DOCTYPE html>
<html>
<head>
    <title>Database Connection Test</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 800px;
            margin: 50px auto;
            padding: 20px;
            background: #f5f5f5;
        }
        .success {
            background: #d4edda;
            color: #155724;
            padding: 15px;
            border-radius: 5px;
            margin: 10px 0;
        }
        .error {
            background: #f8d7da;
            color: #721c24;
            padding: 15px;
            border-radius: 5px;
            margin: 10px 0;
        }
        .info {
            background: #d1ecf1;
            color: #0c5460;
            padding: 15px;
            border-radius: 5px;
            margin: 10px 0;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }
        th, td {
            padding: 10px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        th {
            background: #667eea;
            color: white;
        }
    </style>
</head>
<body>
    <h1>🔌 Database Connection Test</h1>
    
    <?php
    // Test 1: Check if connection object exists
    echo "<h2>Test 1: Connection Object</h2>";
    if (isset($conn)) {
        echo "<div class='success'>✅ Connection object created</div>";
    } else {
        echo "<div class='error'>❌ Connection object not found</div>";
        exit;
    }
    
    // Test 2: Check connection
    echo "<h2>Test 2: Database Connection</h2>";
    if ($conn->connect_error) {
        echo "<div class='error'>❌ Connection failed: " . $conn->connect_error . "</div>";
        echo "<div class='info'>";
        echo "<strong>Connection Details:</strong><br>";
        echo "Host: " . DB_HOST . "<br>";
        echo "User: " . DB_USER . "<br>";
        echo "Database: " . DB_NAME . "<br>";
        echo "Port: " . DB_PORT . "<br>";
        echo "</div>";
    } else {
        echo "<div class='success'>✅ Successfully connected to database!</div>";
        echo "<div class='info'>";
        echo "<strong>Connection Details:</strong><br>";
        echo "Host: " . DB_HOST . "<br>";
        echo "User: " . DB_USER . "<br>";
        echo "Database: " . DB_NAME . "<br>";
        echo "Port: " . DB_PORT . "<br>";
        echo "</div>";
    }
    
    // Test 3: Check environment variables
    echo "<h2>Test 3: Environment Variables</h2>";
    echo "<table>";
    echo "<tr><th>Variable</th><th>Value</th><th>Status</th></tr>";
    
    $vars = [
        'MYSQLHOST' => getenv('MYSQLHOST'),
        'MYSQLUSER' => getenv('MYSQLUSER'),
        'MYSQLPASSWORD' => getenv('MYSQLPASSWORD') ? '***hidden***' : 'Not set',
        'MYSQLDATABASE' => getenv('MYSQLDATABASE'),
        'MYSQLPORT' => getenv('MYSQLPORT')
    ];
    
    foreach ($vars as $var => $value) {
        $status = $value ? '✅ Set' : '❌ Not set';
        $display = $value ?: 'Not set';
        echo "<tr><td>$var</td><td>$display</td><td>$status</td></tr>";
    }
    echo "</table>";
    
    // Test 4: Check if tables exist
    if (!$conn->connect_error) {
        echo "<h2>Test 4: Database Tables</h2>";
        $result = $conn->query("SHOW TABLES");
        
        if ($result) {
            $tables = [];
            while ($row = $result->fetch_array()) {
                $tables[] = $row[0];
            }
            
            if (count($tables) > 0) {
                echo "<div class='success'>✅ Found " . count($tables) . " table(s):</div>";
                echo "<ul>";
                foreach ($tables as $table) {
                    echo "<li>$table</li>";
                }
                echo "</ul>";
                
                // Check for required tables
                $required = ['users', 'signals', 'messages'];
                $missing = array_diff($required, $tables);
                
                if (empty($missing)) {
                    echo "<div class='success'>✅ All required tables exist!</div>";
                } else {
                    echo "<div class='error'>⚠️ Missing tables: " . implode(', ', $missing) . "</div>";
                    echo "<div class='info'>Run database.sql to create missing tables.</div>";
                }
            } else {
                echo "<div class='error'>❌ No tables found in database</div>";
                echo "<div class='info'>You need to run database.sql to create tables.</div>";
            }
        } else {
            echo "<div class='error'>❌ Error checking tables: " . $conn->error . "</div>";
        }
        
        // Test 5: Test query
        echo "<h2>Test 5: Test Query</h2>";
        $test_query = $conn->query("SELECT 1 as test");
        if ($test_query) {
            echo "<div class='success'>✅ Test query executed successfully</div>";
        } else {
            echo "<div class='error'>❌ Test query failed: " . $conn->error . "</div>";
        }
    }
    ?>
    
    <hr>
    <p><strong>Next Steps:</strong></p>
    <ol>
        <li>If connection failed, check Railway MySQL service is running</li>
        <li>If tables are missing, run database.sql in Railway MySQL console</li>
        <li>If everything is ✅, your database is ready to use!</li>
    </ol>
    
    <p><a href="index.php">← Back to Home</a></p>
</body>
</html>

