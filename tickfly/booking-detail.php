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
    header("location: login.php?redirect=booking-detail.php?id=" . $_GET['id']);
    exit;
}

// Check if booking ID is provided
if (!isset($_GET['id'])) {
    header("location: my-ticket.php");
    exit;
}

$booking_id = $_GET['id'];

// Logout functionality
if (isset($_GET['logout'])) {
    // Destroy session and redirect to login page
    session_destroy();
    header("location: login.php");
    exit;
}

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
        'email' => 'john.doe@example.com',
        'phone' => '+62 812 3456 7890',
        'seat' => 'A4',
        'price' => 2500000,
        'payment_method' => 'DANA'
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
        'email' => 'john.doe@example.com',
        'phone' => '+62 812 3456 7890',
        'seat' => 'C12',
        'price' => 1800000,
        'payment_method' => 'Gopay'
    ]
];

// Find booking by ID
$booking = null;
foreach ($bookings as $b) {
    if ($b['id'] === $booking_id) {
        $booking = $b;
        break;
    }
}

// Redirect back to my-ticket if booking not found
if ($booking === null) {
    header("location: my-ticket.php");
    exit;
}

// Format price to IDR
function formatPrice($price) {
    return 'Rp ' . number_format($price, 0, ',', '.');
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Booking Details - Tickfly</title>
    <meta name="description" content="View your booking details on Tickfly">
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
        <div class="max-w-3xl mx-auto bg-white rounded-lg shadow-lg overflow-hidden">
            <div class="bg-blue-800 p-6 text-white">
                <h1 class="text-2xl font-bold">Booking Details</h1>
                <p class="text-blue-100">
                    <?php echo $booking['flight']['from']['name']; ?> to <?php echo $booking['flight']['to']['name']; ?>
                </p>
            </div>

            <div class="p-6">
                <div class="flex justify-between items-center mb-6">
                    <div>
                        <p class="text-gray-500">Booking Number</p>
                        <p class="text-xl font-bold"><?php echo $booking['id']; ?></p>
                    </div>

                    <div>
                        <span class="inline-block px-3 py-1 rounded-full text-xs <?php echo $booking['status'] === 'CONFIRMED' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800'; ?>">
                            <?php echo $booking['status']; ?>
                        </span>
                    </div>

                    <div class="flex gap-2">
                        <button class="flex items-center gap-1 px-3 py-2 border border-blue-800 text-blue-800 rounded-md hover:bg-blue-50">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                            </svg>
                            <span>Download</span>
                        </button>
                        <button class="flex items-center gap-1 px-3 py-2 border border-blue-800 text-blue-800 rounded-md hover:bg-blue-50">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.368 2.684 3 3 0 00-5.368-2.684z" />
                            </svg>
                            <span>Share</span>
                        </button>
                    </div>
                </div>

                <div class="border-t border-b border-gray-200 py-6 mb-6">
                    <h2 class="text-lg font-medium mb-4">Flight Details</h2>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <p class="text-gray-500 mb-1">From</p>
                            <p class="font-medium">
                                <?php echo $booking['flight']['from']['name']; ?> (<?php echo $booking['flight']['from']['code']; ?>)
                            </p>
                        </div>

                        <div>
                            <p class="text-gray-500 mb-1">To</p>
                            <p class="font-medium">
                                <?php echo $booking['flight']['to']['name']; ?> (<?php echo $booking['flight']['to']['code']; ?>)
                            </p>
                        </div>

                        <div>
                            <p class="text-gray-500 mb-1">Date</p>
                            <p class="font-medium"><?php echo $booking['flight']['date']; ?></p>
                        </div>

                        <div>
                            <p class="text-gray-500 mb-1">Flight Time</p>
                            <p class="font-medium">
                                <?php echo $booking['flight']['departureTime']; ?> - <?php echo $booking['flight']['arrivalTime']; ?>
                            </p>
                        </div>

                        <div>
                            <p class="text-gray-500 mb-1">Airline</p>
                            <p class="font-medium"><?php echo $booking['flight']['airline']; ?></p>
                        </div>

                        <div>
                            <p class="text-gray-500 mb-1">Seat</p>
                            <p class="font-medium"><?php echo $booking['seat']; ?></p>
                        </div>
                    </div>
                </div>

                <div class="border-b border-gray-200 py-6 mb-6">
                    <h2 class="text-lg font-medium mb-4">Passenger Details</h2>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <div>
                            <p class="text-gray-500 mb-1">Name</p>
                            <p class="font-medium"><?php echo $booking['passenger']; ?></p>
                        </div>

                        <div>
                            <p class="text-gray-500 mb-1">Email</p>
                            <p class="font-medium"><?php echo $booking['email']; ?></p>
                        </div>

                        <div>
                            <p class="text-gray-500 mb-1">Phone</p>
                            <p class="font-medium"><?php echo $booking['phone']; ?></p>
                        </div>
                    </div>
                </div>

                <div class="py-6 mb-6">
                    <h2 class="text-lg font-medium mb-4">Payment Details</h2>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <p class="text-gray-500 mb-1">Payment Method</p>
                            <p class="font-medium"><?php echo $booking['payment_method']; ?></p>
                        </div>

                        <div>
                            <p class="text-gray-500 mb-1">Total Amount</p>
                            <p class="font-bold text-blue-800"><?php echo formatPrice($booking['price']); ?></p>
                        </div>
                    </div>
                </div>

                <div class="flex justify-center gap-4">
                    <a href="my-ticket.php" class="px-6 py-3 bg-blue-800 text-white rounded-md hover:bg-blue-900 transition">
                        Back to My Tickets
                    </a>

                    <?php if ($booking['status'] === 'CONFIRMED'): ?>
                    <a href="refund.php?id=<?php echo $booking['id']; ?>" class="px-6 py-3 border border-red-600 text-red-600 rounded-md hover:bg-red-50 transition">
                        Request Refund
                    </a>

                    <a href="reschedule.php?id=<?php echo $booking['id']; ?>" class="px-6 py-3 border border-orange-600 text-orange-600 rounded-md hover:bg-orange-50 transition">
                        Reschedule
                    </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
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