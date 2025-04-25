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

// Get flight ID from URL parameter
$flight_id = isset($_GET['flightId']) ? $_GET['flightId'] : null;

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
        'date' => 'Mon, 14 Jun 2021',
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

// Helper function to format duration
function formatDuration($minutes) {
    $hours = floor($minutes / 60);
    $mins = $minutes % 60;
    return $hours . 'h ' . $mins . 'm';
}

// Calculate base fare and fees
$baseFare = $flight ? $flight['price'] - 100000 : 0; // Base fare is total minus fees
$fees = 100000; // Standard fees and surcharges
$totalPrice = $flight ? $flight['price'] : 0;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Flight Details - Tickfly</title>
    <meta name="description" content="View flight details on Tickfly">
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

<?php if (!$flight): ?>
<!-- Flight Not Found Error -->
<div class="container mx-auto px-4 py-12">
    <div class="bg-yellow-100 text-yellow-800 p-4 rounded-md">
        Flight not found. Please go back to search and select a flight.
    </div>
</div>
<?php else: ?>

<!-- Main Content -->
<div class="container mx-auto px-4 py-8">
    <div class="bg-white rounded-lg shadow-lg overflow-hidden">
        <!-- Header -->
        <div class="bg-blue-800 p-6 text-white">
            <h1 class="text-2xl font-bold">Flight Details</h1>
            <p class="text-blue-100">
                <?php echo $flight['from']['name']; ?> to <?php echo $flight['to']['name']; ?>
            </p>
        </div>

        <!-- Flight Information -->
        <div class="p-6">
            <!-- Airline Information -->
            <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-8 border-b border-gray-200 pb-6">
                <div class="flex items-center mb-4 md:mb-0">
                    <div class="w-16 h-16 bg-gray-200 rounded-full flex items-center justify-center mr-4">
                        <!-- Airplane Icon -->
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-blue-800" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6l8 3-8 3-8-3 8-3z" />
                        </svg>
                    </div>
                    <div>
                        <h2 class="text-xl font-bold"><?php echo $flight['airline']['name']; ?></h2>
                        <p class="text-gray-600">Flight #<?php echo $flight['id']; ?></p>
                    </div>
                </div>

                <div class="bg-blue-100 text-blue-800 px-4 py-2 rounded-md font-bold">
                    <?php echo formatPriceIDR($flight['price']); ?>
                </div>
            </div>

            <!-- Detailed Flight Information -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-8 mb-8">
                <!-- Left Column -->
                <div>
                    <h3 class="text-lg font-medium mb-4">Flight Information</h3>
                    <div class="space-y-4">
                        <!-- Date -->
                        <div class="flex items-start">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-blue-800 mr-3 mt-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                            <div>
                                <p class="text-gray-500 text-sm">Date</p>
                                <p class="font-medium"><?php echo $flight['date']; ?></p>
                            </div>
                        </div>

                        <!-- Duration -->
                        <div class="flex items-start">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-blue-800 mr-3 mt-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <div>
                                <p class="text-gray-500 text-sm">Duration</p>
                                <p class="font-medium"><?php echo formatDuration($flight['durationMinutes']); ?></p>
                            </div>
                        </div>

                        <!-- Route -->
                        <div class="flex items-start">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-blue-800 mr-3 mt-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                            </svg>
                            <div>
                                <p class="text-gray-500 text-sm">Route</p>
                                <p class="font-medium">
                                    <?php echo $flight['from']['name']; ?> (<?php echo $flight['from']['code']; ?>) → 
                                    <?php echo $flight['to']['name']; ?> (<?php echo $flight['to']['code']; ?>)
                                </p>
                            </div>
                        </div>

                        <!-- Cabin Class -->
                        <div class="flex items-start">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-blue-800 mr-3 mt-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                            </svg>
                            <div>
                                <p class="text-gray-500 text-sm">Cabin Class</p>
                                <p class="font-medium"><?php echo $flight['cabinClass']; ?></p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Right Column -->
                <div>
                    <h3 class="text-lg font-medium mb-4">Departure & Arrival</h3>
                    <div class="flex flex-col space-y-8">
                        <!-- Timeline -->
                        <div class="flex">
                            <div class="w-24 text-center">
                                <div class="text-xl font-bold"><?php echo $flight['departureTime']; ?></div>
                                <div class="text-sm text-gray-500"><?php echo $flight['from']['code']; ?></div>
                            </div>
                            <div class="flex-1 px-4">
                                <div class="relative h-6">
                                    <div class="absolute top-1/2 left-0 right-0 border-t-2 border-gray-300"></div>
                                    <div class="absolute top-1/2 right-0 w-3 h-3 border-t-2 border-r-2 border-gray-300 transform rotate-45 -translate-y-1/2"></div>
                                </div>
                            </div>
                            <div class="w-24 text-center">
                                <div class="text-xl font-bold"><?php echo $flight['arrivalTime']; ?></div>
                                <div class="text-sm text-gray-500"><?php echo $flight['to']['code']; ?></div>
                            </div>
                        </div>

                        <!-- Airport Info -->
                        <div class="space-y-4">
                            <div>
                                <p class="text-gray-500 text-sm">Departure Airport</p>
                                <p class="font-medium"><?php echo $flight['from']['name']; ?> International Airport</p>
                            </div>

                            <div>
                                <p class="text-gray-500 text-sm">Arrival Airport</p>
                                <p class="font-medium"><?php echo $flight['to']['name']; ?> International Airport</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Price Details -->
            <div class="border-t border-gray-200 pt-6">
                <h3 class="text-lg font-medium mb-4">Price Details</h3>
                <div class="space-y-3">
                    <div class="flex justify-between">
                        <span class="text-gray-600">Base Fare</span>
                        <span class="font-medium"><?php echo formatPriceIDR($baseFare); ?></span>
                    </div>

                    <div class="flex justify-between">
                        <span class="text-gray-600">Fee & Surcharges</span>
                        <span class="font-medium"><?php echo formatPriceIDR($fees); ?></span>
                    </div>

                    <div class="border-t border-gray-200 pt-3 mt-3">
                        <div class="flex justify-between">
                            <span class="text-gray-700 font-bold">Total Price</span>
                            <span class="text-blue-800 font-bold"><?php echo formatPriceIDR($flight['price']); ?></span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="mt-8 flex justify-center">
                <a href="javascript:history.back()" class="px-6 py-3 bg-gray-200 text-gray-800 rounded-md hover:bg-gray-300 transition mr-4">
                    Back
                </a>
                <a href="booking.php?flightId=<?php echo $flight['id']; ?>&from=<?php echo urlencode($flight['from']['name']); ?>&to=<?php echo urlencode($flight['to']['name']); ?>&date=2021-06-14" class="px-6 py-3 bg-blue-800 text-white rounded-md hover:bg-blue-900 transition">
                    Book This Flight
                </a>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

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