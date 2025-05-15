<?php
// Initialize session
session_start();

// Include database configuration
require_once 'config.php';

// Initialize variables
$error_message = "";
$success_message = "";
$email_verified = false;
$user_id = null;
$user_email = "";

// Step 1: User enters email
// Step 2: If email exists, show password reset form

// Check if form for email verification is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['verify_email'])) {
    // Get email from form
    $email = $_POST['email'];
    $user_email = $email;
    
    // Check if connection is established
    if (!isset($conn) || $conn === null) {
        $error_message = "Koneksi database gagal. Silakan periksa konfigurasi Anda.";
    } else {
        try {
            // Check if email exists
            $stmt = $conn->prepare("SELECT id, username FROM users WHERE email = :email");
            $stmt->bindParam(':email', $email);
            $stmt->execute();
            
            if ($stmt->rowCount() > 0) {
                $row = $stmt->fetch(PDO::FETCH_ASSOC);
                $user_id = $row['id'];
                $username = $row['username'];
                
                // Email is verified, show password reset form
                $email_verified = true;
            } else {
                // Email doesn't exist
                $error_message = "Email tidak ditemukan dalam sistem kami.";
            }
        } catch(PDOException $e) {
            $error_message = "Error: " . $e->getMessage();
        }
    }
}

// Check if form for password reset is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['reset_password'])) {
    // Get form data
    $email = $_POST['email'];
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    
    // Validate password
    if ($password !== $confirm_password) {
        $error_message = "Password tidak cocok.";
        $email_verified = true; // Keep form open
        $user_email = $email;
    } elseif (strlen($password) < 8) {
        $error_message = "Password harus minimal 8 karakter.";
        $email_verified = true; // Keep form open
        $user_email = $email;
    } else {
        try {
            // Check if email exists and get user_id
            $stmt = $conn->prepare("SELECT id FROM users WHERE email = :email");
            $stmt->bindParam(':email', $email);
            $stmt->execute();
            
            if ($stmt->rowCount() > 0) {
                $row = $stmt->fetch(PDO::FETCH_ASSOC);
                $user_id = $row['id'];
                
                // Hash the new password
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                
                // Update user password
                $stmt = $conn->prepare("UPDATE users SET password = :password WHERE id = :user_id");
                $stmt->bindParam(':password', $hashed_password);
                $stmt->bindParam(':user_id', $user_id);
                $stmt->execute();
                
                $success_message = "Password Anda telah berhasil diperbarui. Anda sekarang dapat masuk dengan password baru Anda.";
                
                // Redirect to login page after 3 seconds
                header("refresh:3;url=login.php");
            } else {
                $error_message = "Email tidak ditemukan dalam sistem kami.";
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
    <title>Lupa Password - Tickfly</title>
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

<!-- Forgot Password Form -->
<div class="container mx-auto px-4 py-12">
    <div class="max-w-md mx-auto bg-white rounded-lg shadow-md p-8">
        <h1 class="text-2xl font-bold text-center mb-6">Lupa Password</h1>

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
        <div class="text-center mt-6">
            <p>Mengalihkan ke halaman login...</p>
            <a href="login.php" class="text-blue-600 hover:underline">Klik di sini jika Anda tidak dialihkan secara otomatis</a>
        </div>
        
        <!-- If success, don't show any form -->
        <?php else: ?>
        
            <?php if (!$email_verified): ?>
            <!-- Email Verification Form -->
            <p class="text-gray-600 text-center mb-6">Masukkan alamat email Anda untuk memperbarui password.</p>
            <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="POST" class="space-y-6">
                <div>
                    <input
                        type="email"
                        name="email"
                        placeholder="Email"
                        class="w-full pl-4 pr-4 py-3 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                        required
                    />
                </div>

                <button type="submit" name="verify_email" class="w-full py-3 bg-blue-800 text-white rounded-md hover:bg-blue-900 transition">
                    Lanjutkan
                </button>
            </form>
            <?php else: ?>
            <!-- Password Reset Form -->
            <p class="text-gray-600 text-center mb-6">Silakan masukkan password baru Anda.</p>
            <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="POST" class="space-y-6">
                <input type="hidden" name="email" value="<?php echo htmlspecialchars($user_email); ?>" />
                
                <div>
                    <input
                        type="password"
                        name="password"
                        placeholder="Password Baru"
                        class="w-full pl-4 pr-4 py-3 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                        required
                        minlength="8"
                    />
                    <p class="text-xs text-gray-500 mt-1">Password harus minimal 8 karakter</p>
                </div>

                <div>
                    <input
                        type="password"
                        name="confirm_password"
                        placeholder="Konfirmasi Password Baru"
                        class="w-full pl-4 pr-4 py-3 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                        required
                        minlength="8"
                    />
                </div>

                <button type="submit" name="reset_password" class="w-full py-3 bg-blue-800 text-white rounded-md hover:bg-blue-900 transition">
                    Perbarui Password
                </button>
            </form>
            <?php endif; ?>
            
            <div class="text-center mt-6">
                <p>
                    Ingat password Anda?
                    <a href="login.php" class="text-blue-600 hover:underline">Kembali ke Login</a>
                </p>
            </div>
        <?php endif; ?>
    </div>
</div>

</body>
</html>