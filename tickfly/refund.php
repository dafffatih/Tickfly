<?php
// Initialize session
session_start();

// Check if user is logged in
$is_logged_in = isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true;
$username = $is_logged_in ? $_SESSION['username'] : '';

// User role (if applicable)
$user_role = isset($_SESSION['role']) ? $_SESSION['role'] : 'user';

// Redirect to login if not logged in
if (!$is_logged_in) {
    header("location: login.php?redirect=refund.php" . (isset($_GET['id']) ? "?id=" . $_GET['id'] : ""));
    exit;
}

// Check if ID parameter exists
if (!isset($_GET['id'])) {
    header("location: my-ticket.php");
    exit;
}

$booking_id = $_GET['id'];

// Mock bookings data - in a real app, you would fetch this from a database
$bookings = [
    [
        'id' => 'TF123456',
        'status' => 'CONFIRMED',
        'flight' => [
            'airline' => 'Garuda Indonesia',
            'from' => ['name' => 'Jakarta', 'code' => 'JKT'],
            'to' => ['name' => 'Bali', 'code' => 'DPS'],
            'departureTime' => '08:30',
            'arrivalTime' => '11:15',
            'date' => '2021-06-20'
        ],
        'passenger' => 'John Doe',
        'seat' => 'A4',
        'price' => 2500000
    ],
    [
        'id' => 'TF789012',
        'status' => 'PENDING',
        'flight' => [
            'airline' => 'Lion Air',
            'from' => ['name' => 'Jakarta', 'code' => 'JKT'],
            'to' => ['name' => 'Surabaya', 'code' => 'SUB'],
            'departureTime' => '14:45',
            'arrivalTime' => '16:30',
            'date' => '2021-07-05'
        ],
        'passenger' => 'John Doe',
        'seat' => 'C12',
        'price' => 1800000
    ]
];

// Find the booking by ID
$booking = null;
foreach ($bookings as $b) {
    if ($b['id'] === $booking_id) {
        $booking = $b;
        break;
    }
}

// If booking not found or not confirmed, redirect to my-ticket.php
if (!$booking || $booking['status'] !== 'CONFIRMED') {
    header("location: my-ticket.php");
    exit;
}

// Process refund request
$success_message = null;
$refund_id = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['reason'])) {
    $reason = trim($_POST['reason']);
    
    if (empty($reason)) {
        $error_message = "Please provide a reason for refund.";
    } else {
        // In a real application, you would save the refund request to the database
        // and initiate the refund process
        
        // Generate a random refund ID
        $refund_id = 'RF' . rand(100000, 999999);
        
        // Set success message
        $success_message = "Refund request has been submitted successfully";
    }
}

// Format price to IDR
function formatPrice($price) {
    return 'Rp ' . number_format($price, 0, ',', '.');
}

// Calculate refund amount (80% of ticket price)
$refund_amount = $booking['price'] * 0.8;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Request Refund - Tickfly</title>
    <meta name="description" content="Request a refund for your Tickfly booking">
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
<body class="font-sans min-h-screen bg-gray-50">

<!-- Navbar -->
<nav class="bg-white shadow-sm py-3 px-4 sticky top-0 z-50">
    <div class="container mx-auto flex justify-between items-center">
        <a href="index.php" class="flex items-center">
            <div class="relative h-8 w-24">
                <span class="text-xl font-bold">
                    <span class="text-gray-800">Tick</span>
                    <span class="text-blue-400">fly</span>
                </span>
            </div>
        </a>

        <div class="hidden md:flex space-x-6">
            <a href="index.php" class="px-3 py-2 text-gray-700 hover:text-blue-600 transition">Home</a>
            <a href="my-ticket.php" class="px-3 py-2 text-blue-600 font-medium transition">My Ticket</a>
            <a href="cancel-refund.php" class="px-3 py-2 text-gray-700 hover:text-blue-600 transition">Cancel & Refund</a>
            <a href="help-center.php" class="px-3 py-2 text-gray-700 hover:text-blue-600 transition">Help Center</a>
        </div>

        <div class="flex items-center space-x-2">
            <?php if ($is_logged_in): ?>
                <span class="text-sm text-gray-600 mr-2">
                    Hello, <?php echo $username; ?>
                </span>
                <a href="index.php?logout=1" class="px-4 py-2 border border-gray-300 text-gray-700 rounded-md hover:bg-gray-50 transition">
                    Logout
                </a>
            <?php else: ?>
                <a href="login.php" class="px-4 py-2 text-gray-700 hover:text-blue-600 rounded-md transition">
                    Sign In
                </a>
                <a href="register.php" class="px-4 py-2 bg-blue-800 text-white rounded-md hover:bg-blue-900 transition">
                    Sign Up
                </a>
            <?php endif; ?>
        </div>
    </div>
</nav>

<main>
    <div class="container mx-auto px-4 py-8">
        <?php if ($success_message): ?>
            <div class="max-w-2xl mx-auto bg-white rounded-lg shadow-lg p-8">
                <div class="bg-green-100 text-green-800 p-6 rounded-md mb-6">
                    <h3 class="font-bold text-lg mb-2"><?php echo $success_message; ?></h3>
                    <p>Refund ID: <?php echo $refund_id; ?></p>
                    <p class="mt-4">
                        Your refund request has been submitted and is being processed. You will receive an email with further details.
                    </p>
                </div>

                <div class="text-center">
                    <a href="my-ticket.php" class="px-6 py-3 bg-blue-800 text-white rounded-md hover:bg-blue-900 inline-block transition">
                        Back to My Tickets
                    </a>
                </div>
            </div>
        <?php else: ?>
            <div class="max-w-2xl mx-auto bg-white rounded-lg shadow-lg p-8">
                <h1 class="text-2xl font-bold mb-6">Request Refund</h1>

                <div class="mb-6 p-4 bg-blue-50 rounded-md border border-blue-100">
                    <div class="flex items-start">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-blue-800 mr-3 mt-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <div>
                            <p class="font-medium text-blue-800">Refund Policy</p>
                            <p class="text-sm text-blue-700 mt-1">
                                Please note that refunds are subject to our cancellation policy. A cancellation fee may apply
                                depending on how close to the departure date you are cancelling.
                            </p>
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                    <div>
                        <p class="text-gray-500 text-sm">Booking ID</p>
                        <p class="font-medium"><?php echo $booking['id']; ?></p>
                    </div>

                    <div>
                        <p class="text-gray-500 text-sm">Flight</p>
                        <p class="font-medium"><?php echo $booking['flight']['airline']; ?></p>
                    </div>

                    <div>
                        <p class="text-gray-500 text-sm">Route</p>
                        <p class="font-medium">
                            <?php echo $booking['flight']['from']['name']; ?> → <?php echo $booking['flight']['to']['name']; ?>
                        </p>
                    </div>

                    <div>
                        <p class="text-gray-500 text-sm">Date & Time</p>
                        <p class="font-medium">
                            <?php echo $booking['flight']['date']; ?> • <?php echo $booking['flight']['departureTime']; ?>
                        </p>
                    </div>

                    <div>
                        <p class="text-gray-500 text-sm">Passenger</p>
                        <p class="font-medium"><?php echo $booking['passenger']; ?></p>
                    </div>

                    <div>
                        <p class="text-gray-500 text-sm">Amount to be Refunded</p>
                        <p class="font-bold text-blue-800"><?php echo formatPrice($refund_amount); ?></p>
                        <p class="text-xs text-gray-500">(80% of <?php echo formatPrice($booking['price']); ?>)</p>
                    </div>
                </div>

                <?php if (isset($error_message)): ?>
                    <div class="mb-6 p-4 bg-red-100 text-red-700 rounded-md">
                        <?php echo $error_message; ?>
                    </div>
                <?php endif; ?>

                <form method="POST" action="">
                    <div class="mb-6">
                        <label for="reason" class="block text-sm font-medium text-gray-700 mb-2">
                            Reason for Refund
                        </label>
                        <textarea
                            id="reason"
                            name="reason"
                            rows="4"
                            class="w-full p-3 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                            placeholder="Please provide a reason for your refund request..."
                            required
                        ></textarea>
                    </div>

                    <div class="flex justify-end gap-3">
                        <a href="my-ticket.php" class="px-4 py-2 border border-gray-300 text-gray-700 rounded-md hover:bg-gray-50 transition">
                            Cancel
                        </a>

                        <button type="submit" class="px-4 py-2 bg-blue-800 text-white rounded-md hover:bg-blue-900 transition">
                            Submit Refund Request
                        </button>
                    </div>
                </form>
            </div>
        <?php endif; ?>
    </div>
</main>

<!-- Footer -->
<footer class="bg-gray-800 text-white py-8 mt-12">
    <div class="container mx-auto px-4">
        <div class="flex flex-col md:flex-row justify-between">
            <div class="mb-6 md:mb-0">
                <h2 class="text-xl font-bold mb-4">
                    <span class="text-white">Tick</span>
                    <span class="text-blue-400">fly</span>
                </h2>
                <p class="text-gray-400">Your trusted ticketing platform</p>
            </div>
            <div class="grid grid-cols-2 md:grid-cols-3 gap-8">
                <div>
                    <h3 class="text-lg font-semibold mb-3">Company</h3>
                    <ul class="space-y-2">
                        <li><a href="#" class="text-gray-400 hover:text-white">About Us</a></li>
                        <li><a href="#" class="text-gray-400 hover:text-white">Careers</a></li>
                        <li><a href="#" class="text-gray-400 hover:text-white">Contact</a></li>
                    </ul>
                </div>
                <div>
                    <h3 class="text-lg font-semibold mb-3">Support</h3>
                    <ul class="space-y-2">
                        <li><a href="#" class="text-gray-400 hover:text-white">Help Center</a></li>
                        <li><a href="#" class="text-gray-400 hover:text-white">Safety</a></li>
                        <li><a href="#" class="text-gray-400 hover:text-white">Terms of Service</a></li>
                    </ul>
                </div>
                <div class="col-span-2 md:col-span-1">
                    <h3 class="text-lg font-semibold mb-3">Connect With Us</h3>
                    <div class="flex space-x-4">
                        <a href="#" class="text-gray-400 hover:text-white">Facebook</a>
                        <a href="#" class="text-gray-400 hover:text-white">Twitter</a>
                        <a href="#" class="text-gray-400 hover:text-white">Instagram</a>
                    </div>
                </div>
            </div>
        </div>
        <div class="border-t border-gray-700 mt-8 pt-6 text-center text-gray-400">
            <p>&copy; <?php echo date('Y'); ?> Tickfly. All rights reserved.</p>
        </div>
    </div>
</footer>

</body>
</html>