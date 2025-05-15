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
    header("location: login.php?redirect=my-ticket.php");
    exit;
}

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
    <title>My Tickets - Tickfly</title>
    <meta name="description" content="View and manage your Tickfly tickets">
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
        <h1 class="text-2xl font-bold mb-6">My Tickets</h1>

        <?php if (empty($bookings)): ?>
            <div class="bg-yellow-100 text-yellow-800 p-6 rounded-md text-center">
                <p class="mb-4">You don't have any bookings yet.</p>
                <a href="index.php" class="px-4 py-2 bg-blue-800 text-white rounded-md hover:bg-blue-900 transition">
                    Book a Flight
                </a>
            </div>
        <?php else: ?>
            <div class="space-y-6">
                <?php foreach ($bookings as $booking): ?>
                    <div class="bg-white rounded-lg shadow-md overflow-hidden border border-gray-200">
                        <div class="p-6">
                            <div class="flex flex-col md:flex-row justify-between">
                                <div class="mb-4 md:mb-0">
                                    <span class="inline-block px-3 py-1 rounded-full text-xs <?php echo $booking['status'] === 'CONFIRMED' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800'; ?>">
                                        <?php echo $booking['status']; ?>
                                    </span>
                                    <h2 class="text-lg font-medium mt-2">Booking #<?php echo $booking['id']; ?></h2>
                                </div>

                                <div class="flex gap-2">
                                    <a href="booking-detail.php?id=<?php echo $booking['id']; ?>" class="px-4 py-2 border border-blue-800 text-blue-800 rounded-md hover:bg-blue-50 transition">
                                        View Details
                                    </a>

                                    <?php if ($booking['status'] === 'CONFIRMED'): ?>
                                        <a href="refund.php?id=<?php echo $booking['id']; ?>" class="px-4 py-2 border border-red-600 text-red-600 rounded-md hover:bg-red-50 transition">
                                            Request Refund
                                        </a>
                                        <a href="reschedule.php?id=<?php echo $booking['id']; ?>" class="px-4 py-2 border border-orange-600 text-orange-600 rounded-md hover:bg-orange-50 transition">
                                            Reschedule
                                        </a>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <div class="mt-6 grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div class="space-y-4">
                                    <div class="flex items-start">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-blue-800 mr-3 mt-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                        </svg>
                                        <div>
                                            <p class="text-gray-500 text-sm">Flight</p>
                                            <p class="font-medium"><?php echo $booking['flight']['airline']; ?></p>
                                        </div>
                                    </div>

                                    <div class="flex items-start">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-blue-800 mr-3 mt-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                        </svg>
                                        <div>
                                            <p class="text-gray-500 text-sm">Date</p>
                                            <p class="font-medium"><?php echo $booking['flight']['date']; ?></p>
                                        </div>
                                    </div>

                                    <div class="flex items-start">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-blue-800 mr-3 mt-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                        <div>
                                            <p class="text-gray-500 text-sm">Time</p>
                                            <p class="font-medium">
                                                <?php echo $booking['flight']['departureTime']; ?> - <?php echo $booking['flight']['arrivalTime']; ?>
                                            </p>
                                        </div>
                                    </div>
                                </div>

                                <div class="space-y-4">
                                    <div class="flex items-start">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-blue-800 mr-3 mt-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                                        </svg>
                                        <div>
                                            <p class="text-gray-500 text-sm">Route</p>
                                            <p class="font-medium">
                                                <?php echo $booking['flight']['from']['name']; ?> (<?php echo $booking['flight']['from']['code']; ?>) → 
                                                <?php echo $booking['flight']['to']['name']; ?> (<?php echo $booking['flight']['to']['code']; ?>)
                                            </p>
                                        </div>
                                    </div>

                                    <div class="flex items-start">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-blue-800 mr-3 mt-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                        </svg>
                                        <div>
                                            <p class="text-gray-500 text-sm">Passenger</p>
                                            <p class="font-medium">
                                                <?php echo $booking['passenger']; ?> (Seat <?php echo $booking['seat']; ?>)
                                            </p>
                                        </div>
                                    </div>

                                    <div class="flex items-start">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-blue-800 mr-3 mt-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
                                        </svg>
                                        <div>
                                            <p class="text-gray-500 text-sm">Price</p>
                                            <p class="font-bold text-blue-800"><?php echo formatPrice($booking['price']); ?></p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
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