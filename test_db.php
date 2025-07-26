<?php
/**
 * Database Connection Test
 * Tests the connection to the database
 */

// Show errors
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Database configuration
$db_host = 'localhost';
$db_name = 'fitzone_db';
$db_user = 'root';
$db_pass = '';

echo "<h1>Database Connection Test</h1>";

// Check connection without database
try {
    $conn = new PDO("mysql:host=$db_host", $db_user, $db_pass);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "<p>Connected to MySQL server successfully.</p>";
    
    // Check if database exists
    try {
        $dbExists = $conn->query("SELECT SCHEMA_NAME FROM INFORMATION_SCHEMA.SCHEMATA WHERE SCHEMA_NAME = '$db_name'")->rowCount() > 0;
        
        if ($dbExists) {
            echo "<p>Database '$db_name' exists.</p>";
            
            // Connect to the specific database
            try {
                $dbConn = new PDO("mysql:host=$db_host;dbname=$db_name;charset=utf8mb4", $db_user, $db_pass);
                $dbConn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                echo "<p>Connected to database '$db_name' successfully.</p>";
                
                // Test a query
                $tables = $dbConn->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
                
                if (count($tables) > 0) {
                    echo "<p>Tables found in database:</p>";
                    echo "<ul>";
                    foreach ($tables as $table) {
                        echo "<li>$table</li>";
                    }
                    echo "</ul>";
                } else {
                    echo "<p>No tables found in the database.</p>";
                }
                
            } catch (PDOException $e) {
                echo "<p style='color: red;'>Error connecting to database: " . $e->getMessage() . "</p>";
            }
        } else {
            echo "<p style='color: orange;'>Database '$db_name' does not exist. Run setup_database.php to create it.</p>";
        }
    } catch (PDOException $e) {
        echo "<p style='color: red;'>Error checking database: " . $e->getMessage() . "</p>";
    }
} catch (PDOException $e) {
    echo "<p style='color: red;'>Connection to MySQL server failed: " . $e->getMessage() . "</p>";
    echo "<p>Please make sure MySQL service is running in your WAMP server.</p>";
}

// PHP info
echo "<h2>PHP Information</h2>";
echo "<p>PHP Version: " . phpversion() . "</p>";
echo "<p>Loaded PHP Extensions:</p>";
echo "<ul>";
$extensions = get_loaded_extensions();
sort($extensions);
foreach ($extensions as $extension) {
    echo "<li>$extension</li>";
}
echo "</ul>";
?>
