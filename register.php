<?php
// Initialize session
session_start();

// Include database configuration
require_once 'config.php';

// Initialize variables
$error_message = "";
$success_message = "";

// Check if form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get form data
    $username = $_POST['username'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];
    $password = $_POST['password'];
    $remember_me = isset($_POST['rememberMe']) ? 1 : 0;
    
    // Check if connection is established
    if (!isset($conn) || $conn === null) {
        $error_message = "Database connection failed. Please check your configuration.";
    } else {
        try {
            // Check if username already exists
            $stmt = $conn->prepare("SELECT id FROM users WHERE username = :username");
            $stmt->bindParam(':username', $username);
            $stmt->execute();
        
        if ($stmt->rowCount() > 0) {
            $error_message = "Username already exists";
        } else {
            // Check if email already exists
            $stmt = $conn->prepare("SELECT id FROM users WHERE email = :email");
            $stmt->bindParam(':email', $email);
            $stmt->execute();
            
            if ($stmt->rowCount() > 0) {
                $error_message = "Email already exists";
            } else {
                // Hash password
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                
                // Insert new user
                $stmt = $conn->prepare("INSERT INTO users (username, email, phone, password, remember_me) VALUES (:username, :email, :phone, :password, :remember_me)");
                $stmt->bindParam(':username', $username);
                $stmt->bindParam(':email', $email);
                $stmt->bindParam(':phone', $phone);
                $stmt->bindParam(':password', $hashed_password);
                $stmt->bindParam(':remember_me', $remember_me);
                $stmt->execute();
                
                $success_message = "Registration successful! You can now login.";
                
                // Redirect to login page after 2 seconds
                header("refresh:2;url=login.php");
            }
        }
    } catch(PDOException $e) {
        $error_message = "Error: " . $e->getMessage();
    }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign Up - Tickfly</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f5f7fa;
        }
        .tickfly-blue {
            color: #1e3a8a;
        }
        .tickfly-blue-bg {
            background-color: #1e3a8a;
        }
        .tickfly-light-blue {
            color: #60a5fa;
        }
        .tickfly-light-blue-bg {
            background-color: #60a5fa;
        }
    </style>
</head>
<body class="font-sans">

<!-- Navbar -->
<nav class="bg-white/90 backdrop-blur-sm shadow-sm py-3 px-4 sticky top-0 z-50">
    <div class="container mx-auto flex justify-between items-center">
        <a href="index.php" class="flex items-center">
            <div class="relative h-8 w-24">
                <span class="text-xl font-bold">
                    <span class="text-gray-800">Tick</span>
                    <span class="text-blue-400">fly</span>
                </span>
            </div>
        </a>

        <div class="flex space-x-6">
            <a href="index.php" class="text-gray-700 hover:text-blue-600">Home</a>
            <a href="my-ticket.php" class="text-gray-700 hover:text-blue-600">My Ticket</a>
            <a href="cancel-refund.php" class="text-gray-700 hover:text-blue-600">Cancel & Refund</a>
            <a href="help-center.php" class="text-gray-700 hover:text-blue-600">Help Center</a>
        </div>

        <div class="flex items-center space-x-2">
            <a href="login.php" class="text-gray-700 hover:text-blue-600">Sign In</a>
            <a href="register.php" class="px-4 py-2 bg-blue-800 text-white rounded-md hover:bg-blue-900">Sign Up</a>
        </div>
    </div>
</nav>

<!-- Sign-Up Form -->
<div class="container mx-auto px-4 py-12">
    <div class="max-w-md mx-auto bg-white rounded-lg shadow-md p-8">
        <h1 class="text-2xl font-bold text-center mb-4">Create your account!</h1>
        <p class="text-gray-600 text-center mb-6">Enter your Full Details</p>

        <!-- Error Message (only show if there is an error) -->
        <?php if (!empty($error_message)): ?>
        <div class="bg-red-100 text-red-700 p-3 rounded-md mb-4">
            <?php echo $error_message; ?>
        </div>
        <?php endif; ?>

        <!-- Success Message (only show if there is a success message) -->
        <?php if (!empty($success_message)): ?>
        <div class="bg-green-100 text-green-700 p-3 rounded-md mb-4">
            <?php echo $success_message; ?>
        </div>
        <?php endif; ?>

        <!-- Sign-Up Form -->
        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="POST" class="space-y-6">
            <div>
                <input
                    type="text"
                    name="username"
                    placeholder="Username"
                    class="w-full pl-4 pr-4 py-3 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                    required
                />
            </div>

            <div>
                <input
                    type="email"
                    name="email"
                    placeholder="Email"
                    class="w-full pl-4 pr-4 py-3 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                    required
                />
            </div>

            <div>
                <input
                    type="tel"
                    name="phone"
                    placeholder="Phone"
                    class="w-full pl-4 pr-4 py-3 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                    required
                />
            </div>

            <div>
                <input
                    type="password"
                    name="password"
                    placeholder="Password"
                    class="w-full pl-4 pr-4 py-3 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                    required
                />
            </div>

            <div class="flex items-center">
                <input
                    type="checkbox"
                    name="rememberMe"
                    id="rememberMe"
                    class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded"
                />
                <label for="rememberMe" class="ml-2 block text-sm text-gray-700">Remember me</label>
            </div>

            <button type="submit" class="w-full py-3 bg-blue-800 text-white rounded-md hover:bg-blue-900 transition">
                Sign Up
            </button>
        </form>

        <div class="text-center mt-6">
            <p>
                Have an account?
                <a href="login.php" class="text-blue-600 hover:underline">Sign in</a>
            </p>
        </div>
    </div>
</div>

</body>
</html>