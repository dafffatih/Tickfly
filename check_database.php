<?php
// Simple database connection test script
$host = "localhost";
$dbname = "tickfly";
$username = "root";
$password = "";

echo "<h1>Database Connection Test</h1>";

// Test if MySQL extension is loaded
echo "<h2>PHP MySQL Extension Check</h2>";
if (extension_loaded('mysqli')) {
    echo "<p style='color:green'>✓ MySQLi extension is loaded.</p>";
} else {
    echo "<p style='color:red'>✗ MySQLi extension is NOT loaded. Please enable it in your php.ini file.</p>";
}

if (extension_loaded('pdo_mysql')) {
    echo "<p style='color:green'>✓ PDO MySQL extension is loaded.</p>";
} else {
    echo "<p style='color:red'>✗ PDO MySQL extension is NOT loaded. Please enable it in your php.ini file.</p>";
}

// Test MySQL connection
echo "<h2>MySQL Connection Test</h2>";
try {
    // Try creating a connection
    $conn = new PDO("mysql:host=$host", $username, $password);
    
    // Set the PDO error mode to exception
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "<p style='color:green'>✓ Connection to MySQL server is working.</p>";
    
    // Check if database exists
    $stmt = $conn->query("SELECT COUNT(*) FROM INFORMATION_SCHEMA.SCHEMATA WHERE SCHEMA_NAME = '$dbname'");
    $dbExists = (bool) $stmt->fetchColumn();
    
    if ($dbExists) {
        echo "<p style='color:green'>✓ Database '$dbname' exists.</p>";
        
        // Try connecting to the specific database
        $conn2 = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
        echo "<p style='color:green'>✓ Connection to database '$dbname' is successful.</p>";
        
        // Check if users table exists
        $stmt = $conn2->query("SHOW TABLES LIKE 'users'");
        $tableExists = $stmt->rowCount() > 0;
        
        if ($tableExists) {
            echo "<p style='color:green'>✓ Table 'users' exists.</p>";
            
            // Check table structure
            $stmt = $conn2->query("DESCRIBE users");
            $columns = $stmt->fetchAll(PDO::FETCH_COLUMN);
            
            echo "<p>Table columns: " . implode(", ", $columns) . "</p>";
        } else {
            echo "<p style='color:red'>✗ Table 'users' does not exist. Please run the SQL setup script.</p>";
        }
    } else {
        echo "<p style='color:red'>✗ Database '$dbname' does not exist. Please create it by running the SQL setup script.</p>";
    }
} catch(PDOException $e) {
    echo "<p style='color:red'>✗ Connection failed: " . $e->getMessage() . "</p>";
    
    echo "<h2>Troubleshooting Steps</h2>";
    echo "<ol>";
    echo "<li>Check that MySQL server is running</li>";
    echo "<li>Verify username and password in config.php</li>";
    echo "<li>Make sure the database '$dbname' exists</li>";
    echo "<li>Check if your MySQL user has proper permissions</li>";
    echo "<li>Run the SQL setup script to create the required database and tables</li>";
    echo "</ol>";
}

// Connection info
echo "<h2>Connection Information</h2>";
echo "<ul>";
echo "<li>Host: $host</li>";
echo "<li>Database: $dbname</li>";
echo "<li>Username: $username</li>";
echo "<li>Password: " . (empty($password) ? "(empty)" : "****") . "</li>";
echo "</ul>";

// PHP info
echo "<h2>PHP Version Information</h2>";
echo "<p>PHP Version: " . phpversion() . "</p>";
echo "<p>PHP Configuration File (php.ini): " . php_ini_loaded_file() . "</p>";
?>