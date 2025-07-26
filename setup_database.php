<?php
/**
 * FitZone Database Setup
 * This file checks if the database exists and creates it if needed
 */

// Database configuration
$db_host = 'localhost';
$db_name = 'fitzone_db';
$db_user = 'root';
$db_pass = '';

// Check connection without database
try {
    $conn = new PDO("mysql:host=$db_host", $db_user, $db_pass);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "<p>Connected to MySQL server successfully.</p>";
    
    // Create database if it doesn't exist
    try {
        $conn->exec("CREATE DATABASE IF NOT EXISTS `$db_name` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
        echo "<p>Database '$db_name' created or already exists.</p>";
        
        // Select the database
        $conn->exec("USE `$db_name`");
        
        // Check if users table exists (as a proxy for whether the schema has been imported)
        $stmt = $conn->query("SHOW TABLES LIKE 'users'");
        $tableExists = $stmt->rowCount() > 0;
        
        if (!$tableExists) {
            echo "<p>Importing database schema...</p>";
            
            // Get the SQL file content
            $sql_file = file_get_contents('database/fitzone_db.sql');
            if (!$sql_file) {
                echo "<p style='color: red;'>Error: Could not read SQL file.</p>";
            } else {
                // Split SQL commands by semicolon
                $sql_commands = explode(';', $sql_file);
                
                // Execute each command
                foreach ($sql_commands as $command) {
                    $command = trim($command);
                    if (!empty($command)) {
                        try {
                            $conn->exec($command);
                        } catch (PDOException $e) {
                            echo "<p style='color: red;'>Error executing SQL: " . $e->getMessage() . "</p>";
                        }
                    }
                }
                
                echo "<p style='color: green;'>Database schema imported successfully!</p>";
                
                // Check if admin user exists, create if not
                $stmt = $conn->query("SELECT COUNT(*) FROM users WHERE role = 'admin'");
                $adminExists = $stmt->fetchColumn() > 0;
                
                if (!$adminExists) {
                    // Create admin user
                    $adminUsername = 'admin';
                    $adminPassword = password_hash('admin123', PASSWORD_DEFAULT);
                    $adminEmail = 'admin@fitzone.com';
                    
                    $stmt = $conn->prepare("INSERT INTO users (username, email, password, first_name, last_name, role, is_active, created_at) 
                                          VALUES (?, ?, ?, 'Admin', 'User', 'admin', 1, NOW())");
                    $stmt->execute([$adminUsername, $adminEmail, $adminPassword]);
                    
                    echo "<p style='color: green;'>Admin user created with username: 'admin' and password: 'admin123'</p>";
                }
            }
        } else {
            echo "<p>Database schema already imported.</p>";
        }
        
        echo "<p style='color: green;'>Database setup completed successfully!</p>";
        echo "<p>You can now <a href='index.php'>go to the homepage</a> or <a href='login.php'>login</a>.</p>";
        
    } catch(PDOException $e) {
        echo "<p style='color: red;'>Error creating database: " . $e->getMessage() . "</p>";
    }
    
} catch(PDOException $e) {
    echo "<p style='color: red;'>Connection failed: " . $e->getMessage() . "</p>";
}
?>
