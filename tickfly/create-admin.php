<?php
// Include database configuration
require_once 'config.php';

// Check if connection is established
if (!isset($conn) || $conn === null) {
    die("Database connection failed. Please check your configuration.<br>Error: " . ($db_error ?? "Unknown error"));
}

try {
    // Admin user details
    $username = 'admin';
    $email = 'admin@tickfly.com';
    $password = 'admin';
    $role = 'admin';
    
    // Hash the password
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    
    // Check if username already exists
    $check = $conn->prepare("SELECT id FROM users WHERE username = :username");
    $check->bindParam(':username', $username);
    $check->execute();
    
    if ($check->rowCount() > 0) {
        echo "User 'admin' already exists. Please use a different username or delete the existing admin user first.";
    } else {
        // Prepare SQL statement to insert new admin user
        $stmt = $conn->prepare("INSERT INTO users (username, email, password, role) VALUES (:username, :email, :password, :role)");
        
        // Bind parameters
        $stmt->bindParam(':username', $username);
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':password', $hashed_password);
        $stmt->bindParam(':role', $role);
        
        // Execute the query
        $stmt->execute();
        
        echo "Admin user has been created successfully!<br>";
        echo "Username: admin<br>";
        echo "Password: admin<br>";
        echo "Role: admin<br>";
        echo "<br><a href='login.php'>Go to Login Page</a>";
    }
} catch(PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>