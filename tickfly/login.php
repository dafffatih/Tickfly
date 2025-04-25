<?php
// Initialize session
session_start();

// Include database configuration
require_once 'config.php';

// Initialize error message variable
$error_message = "";

// Check if form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get username and password from form
    $username = $_POST['username'];
    $password = $_POST['password'];
    
    // Check if connection is established
    if (!isset($conn) || $conn === null) {
        $error_message = "Database connection failed. Please check your configuration.";
    } else {
        try {
            // Prepare SQL statement to prevent SQL injection
            $stmt = $conn->prepare("SELECT id, username, password, role FROM users WHERE username = :username");
            $stmt->bindParam(':username', $username);
            $stmt->execute();
        
            // Check if user exists
            if ($stmt->rowCount() > 0) {
                $row = $stmt->fetch(PDO::FETCH_ASSOC);
                
                // Verify password
                if (password_verify($password, $row['password'])) {
                    // Password is correct, create session variables
                    $_SESSION['loggedin'] = true;
                    $_SESSION['id'] = $row['id'];
                    $_SESSION['username'] = $row['username'];
                    // Instead of hardcoding the role, use the one from the database
                    $_SESSION['role'] = $row['role'];

                    // Then redirect based on role
                    if ($_SESSION['role'] === 'admin') {
                        header("location: admin_dashboard.php");
                    } elseif ($_SESSION['role'] === 'cs') {
                        header("location: cs_dashboard.php");
                    } else {
                        header("location: index.php");
                    }
                    exit;
                } else {
                    // Password is incorrect
                    $error_message = "Invalid username or password";
                }
            } else {
                // Username doesn't exist
                $error_message = "Invalid username or password";
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
    <title>Login - Tickfly</title>
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

<!-- Login Form -->
<div class="container mx-auto px-4 py-12">
    <div class="max-w-md mx-auto bg-white rounded-lg shadow-md p-8">
        <h1 class="text-2xl font-bold text-center mb-6">Login your account!</h1>

        <!-- Error Message (only show if there is an error) -->
        <?php if (!empty($error_message)): ?>
        <div class="bg-red-100 text-red-700 p-3 rounded-md mb-4">
            <?php echo $error_message; ?>
        </div>
        <?php endif; ?>

        <!-- Login Form -->
        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="POST" class="space-y-6">
            <div>
                <input
                    type="text"
                    name="username"
                    placeholder="Username"
                    class="w-full pl-4 py-3 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                    required
                />
            </div>

            <div>
                <input
                    type="password"
                    name="password"
                    placeholder="Password"
                    class="w-full pl-4 py-3 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                    required
                />
                <div class="text-right mt-2">
                    <a href="forgot-password.php" class="text-sm text-blue-600 hover:underline">
                        Forgot password?
                    </a>
                </div>
            </div>

            <button type="submit" class="w-full py-3 bg-blue-800 text-white rounded-md hover:bg-blue-900 transition">
                Sign In
            </button>
        </form>

        <!-- Sign Up Link -->
        <div class="text-center mt-6">
            <p>
                Don't have an account?
                <a href="register.php" class="text-blue-600 hover:underline">
                    Sign up
                </a>
            </p>
        </div>
    </div>
</div>

</body>
</html>