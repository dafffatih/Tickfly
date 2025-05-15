<?php
// Initialize session
session_start();

// Check if user is logged in
$is_logged_in = isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true;
$username = $is_logged_in ? $_SESSION['username'] : '';

// User role (if applicable)
$user_role = isset($_SESSION['role']) ? $_SESSION['role'] : 'user';

// Logout functionality
if (isset($_GET['logout'])) {
    // Destroy session and redirect to login page
    session_destroy();
    header("location: login.php");
    exit;
}

// Get parameters from URL
$flight_id = isset($_GET['flightId']) ? $_GET['flightId'] : null;
$selectedSeat = isset($_GET['seat']) ? $_GET['seat'] : null;
$from = isset($_GET['from']) ? $_GET['from'] : 'Jakarta';
$to = isset($_GET['to']) ? $_GET['to'] : 'Lampung';
$date = isset($_GET['date']) ? $_GET['date'] : date('Y-m-d');
$travelers = isset($_GET['travelers']) ? $_GET['travelers'] : '1';
$cabin_class = isset($_GET['class']) ? $_GET['class'] : 'economy';

// Get passenger details from URL parameters
$firstName = isset($_GET['firstName']) ? $_GET['firstName'] : 'John';
$lastName = isset($_GET['lastName']) ? $_GET['lastName'] : 'Doe';
$email = isset($_GET['email']) ? $_GET['email'] : 'john.doe@example.com';
$phone = isset($_GET['phone']) ? $_GET['phone'] : '+62 812 3456 7890';
$title = isset($_GET['title']) ? $_GET['title'] : 'Mr';

// Get payment method from URL parameter
$paymentMethod = isset($_GET['paymentMethod']) ? $_GET['paymentMethod'] : 'BNI';

// Get additional services from URL
$meals = isset($_GET['meals']) && $_GET['meals'] === '1';
$baggage = isset($_GET['baggage']) && $_GET['baggage'] === '1';

// Format date
$formatted_date = date('D, d M Y', strtotime($date));

// Function to get flight details
function getFlightById($flight_id) {
    // In a real application, this would fetch from a database
    // For now, we'll use mock data based on the flight ID
    $airlines = [
        ['id' => 1, 'name' => 'Garuda Indonesia', 'logo' => 'placeholder.svg'],
        ['id' => 2, 'name' => 'Lion Air', 'logo' => 'placeholder.svg'],
        ['id' => 3, 'name' => 'Sriwijaya Air', 'logo' => 'placeholder.svg']
    ];
    
    $cabinClasses = ['ClassCP', 'ClassH', 'ClassL', 'ClassBC'];
    
    // Since this is mock data, we'll return a flight based on the ID
    $airline_index = ($flight_id - 1) % count($airlines);
    $class_index = ($flight_id - 1) % count($cabinClasses);
    
    return [
        'id' => $flight_id,
        'airline' => $airlines[$airline_index],
        'from' => ['name' => 'Jakarta', 'code' => 'JAK'],
        'to' => ['name' => 'Lampung', 'code' => 'LAM'],
        'departureTime' => '15:00',
        'arrivalTime' => '17:30',
        'durationMinutes' => 150,
        'price' => 1000000 + ($flight_id * 100000),
        'cabinClass' => $cabinClasses[$class_index],
        'seatsLeft' => 3 + ($flight_id * 2),
        'refundable' => ($flight_id % 2 === 0)
    ];
}

// Get flight details if flight_id is provided
$flight = null;
if ($flight_id) {
    $flight = getFlightById($flight_id);
}

// Helper function to format price in IDR
function formatPriceIDR($price) {
    return 'Rp ' . number_format($price, 0, ',', '.');
}

// Calculate service fees
$baseFare = $flight ? $flight['price'] : 0;
$fees = 100000; // Standard fees and surcharges
$mealsCost = $meals ? 50000 : 0; // 50k if meals are selected
$baggageCost = $baggage ? 100000 : 0; // 100k if baggage is selected

$totalAddOns = $mealsCost + $baggageCost;
$totalPrice = $baseFare + $fees + $totalAddOns;

// Generate booking number
$booking_number = 'TF' . sprintf('%06d', rand(100000, 999999));

// Format flight duration
$duration_hours = floor($flight['durationMinutes'] / 60);
$duration_minutes = $flight['durationMinutes'] % 60;
$duration_formatted = "{$duration_hours}h {$duration_minutes}m";

// Map payment method code to display name
$paymentMethodDisplay = [
    'BNI' => 'BNI Bank Transfer',
    'BRI' => 'BRI Bank Transfer',
    'DANA' => 'DANA E-Wallet',
    'GoPay' => 'GoPay E-Wallet'
];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Booking Confirmation - Tickfly</title>
    <meta name="description" content="Your booking confirmation with Tickfly">
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
<nav class="bg-white shadow-sm py-3 px-4 sticky top-0 z-40">
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
            <a href="index.php" class="px-3 py-2 text-blue-600 font-medium transition">Home</a>
            <a href="my-ticket.php" class="px-3 py-2 text-gray-700 hover:text-blue-600 transition">My Ticket</a>
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

<!-- Main Content - Confirmation Section -->
<div class="container max-w-4xl mx-auto px-4 py-8">
    <div class="bg-white rounded-lg shadow-lg overflow-hidden">
        <!-- Success Header -->
        <div class="bg-green-100 p-6 text-center">
            <div class="inline-flex items-center justify-center w-16 h-16 bg-green-500 rounded-full mb-4">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                </svg>
            </div>
            <h1 class="text-2xl font-bold text-green-800">Booking Confirmed!</h1>
            <p class="text-green-700 mt-2">Your booking has been confirmed and your ticket is ready.</p>
        </div>

        <div class="p-6">
            <!-- Booking Number and Action Buttons -->
            <div class="flex justify-between items-center mb-6">
                <div>
                    <p class="text-gray-500">Booking Number</p>
                    <p class="text-xl font-bold"><?php echo $booking_number; ?></p>
                </div>

                <div class="flex gap-2">
                    <!-- <button class="flex items-center gap-1 px-3 py-2 border border-blue-800 text-blue-800 rounded-md hover:bg-blue-50">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                        </svg>
                        <span>Download</span>
                    </button>
                    <button class="flex items-center gap-1 px-3 py-2 border border-blue-800 text-blue-800 rounded-md hover:bg-blue-50">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.368 2.684 3 3 0 00-5.368-2.684z" />
                        </svg>
                        <span>Share</span> -->
                    </button>
                </div>
            </div>

            <!-- Flight Details Section -->
            <div class="border-t border-b border-gray-200 py-6 mb-6">
                <h2 class="text-lg font-medium mb-4">Flight Details</h2>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <p class="text-gray-500 mb-1">From</p>
                        <p class="font-medium">
                            <?php echo $from; ?> (<?php echo substr($from, 0, 3); ?>)
                        </p>
                    </div>

                    <div>
                        <p class="text-gray-500 mb-1">To</p>
                        <p class="font-medium">
                            <?php echo $to; ?> (<?php echo substr($to, 0, 3); ?>)
                        </p>
                    </div>

                    <div>
                        <p class="text-gray-500 mb-1">Date</p>
                        <p class="font-medium"><?php echo $formatted_date; ?></p>
                    </div>

                    <div>
                        <p class="text-gray-500 mb-1">Flight Time</p>
                        <p class="font-medium">
                            <?php echo $flight['departureTime']; ?> - <?php echo $flight['arrivalTime']; ?> (<?php echo $duration_formatted; ?>)
                        </p>
                    </div>

                    <div>
                        <p class="text-gray-500 mb-1">Airline</p>
                        <p class="font-medium"><?php echo $flight['airline']['name']; ?></p>
                    </div>

                    <div>
                        <p class="text-gray-500 mb-1">Seat</p>
                        <p class="font-medium"><?php echo $selectedSeat; ?></p>
                    </div>
                </div>
            </div>

            <!-- Passenger Details Section -->
            <div class="border-b border-gray-200 py-6 mb-6">
                <h2 class="text-lg font-medium mb-4">Passenger Details</h2>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div>
                        <p class="text-gray-500 mb-1">Name</p>
                        <p class="font-medium"><?php echo $title . ' ' . $firstName . ' ' . $lastName; ?></p>
                    </div>

                    <div>
                        <p class="text-gray-500 mb-1">Email</p>
                        <p class="font-medium"><?php echo $email; ?></p>
                    </div>

                    <div>
                        <p class="text-gray-500 mb-1">Phone</p>
                        <p class="font-medium"><?php echo $phone; ?></p>
                    </div>
                </div>
            </div>

            <!-- Payment Details Section -->
            <div class="py-6 mb-6">
                <h2 class="text-lg font-medium mb-4">Payment Details</h2>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <p class="text-gray-500 mb-1">Payment Method</p>
                        <p class="font-medium"><?php echo isset($paymentMethodDisplay[$paymentMethod]) ? $paymentMethodDisplay[$paymentMethod] : $paymentMethod; ?></p>
                    </div>

                    <div>
                        <p class="text-gray-500 mb-1">Total Amount</p>
                        <p class="font-bold text-blue-800"><?php echo formatPriceIDR($totalPrice); ?></p>
                    </div>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="flex justify-center gap-4">
                <a href="index.php" class="px-6 py-3 bg-blue-800 text-white rounded-md hover:bg-blue-900 transition">
                    Back to Home
                </a>

                <a href="my-ticket.php" class="px-6 py-3 border border-blue-800 text-blue-800 rounded-md hover:bg-blue-50 transition">
                    View My Tickets
                </a>
            </div>
        </div>
    </div>
</div>

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