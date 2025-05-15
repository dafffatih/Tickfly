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

// Get search parameters from URL
$from = isset($_GET['from']) ? $_GET['from'] : 'Jakarta';
$to = isset($_GET['to']) ? $_GET['to'] : 'Lampung';
$depart_date = isset($_GET['depart_date']) ? $_GET['depart_date'] : date('Y-m-d');
$travelers = isset($_GET['travelers']) ? $_GET['travelers'] : '1';
$cabin_class = isset($_GET['cabin_class']) ? $_GET['cabin_class'] : 'economy';

// Format date
$formatted_date = date('D, d M Y', strtotime($depart_date));

// Filter settings (default values)
$min_price = isset($_GET['min_price']) ? intval($_GET['min_price']) : 500000;
$max_price = isset($_GET['max_price']) ? intval($_GET['max_price']) : 2000000;
$departure_time = isset($_GET['departure_time']) ? $_GET['departure_time'] : 'all';
$stops = isset($_GET['stops']) ? $_GET['stops'] : 'all';

// Mock flight data generator function
function generateMockFlights($from, $to, $date) {
    $airlines = [
        ['id' => 1, 'name' => 'Garuda Indonesia', 'logo' => 'placeholder.svg'],
        ['id' => 2, 'name' => 'Lion Air', 'logo' => 'placeholder.svg'],
        ['id' => 3, 'name' => 'Sriwijaya Air', 'logo' => 'placeholder.svg']
    ];

    $cabinClasses = ['ClassCP', 'ClassH', 'ClassL', 'ClassBC'];

    $fromCity = ['name' => $from, 'code' => strtoupper(substr($from, 0, 3))];
    $toCity = ['name' => $to, 'code' => strtoupper(substr($to, 0, 3))];

    $flights = [];

    // Generate 6 flights
    for ($i = 0; $i < 6; $i++) {
        $airline = $airlines[$i % count($airlines)];
        $departureHour = 15 + floor($i / 2);
        $departureMinutes = ($i % 2) * 45;
        $durationMinutes = 150 + $i * 10;
        $arrivalHour = ($departureHour + floor($durationMinutes / 60)) % 24;
        $arrivalMinutes = ($departureMinutes + ($durationMinutes % 60)) % 60;

        $flights[] = [
            'id' => $i + 1,
            'airline' => $airline,
            'from' => $fromCity,
            'to' => $toCity,
            'departureTime' => sprintf('%02d:%02d', $departureHour, $departureMinutes),
            'arrivalTime' => sprintf('%02d:%02d', $arrivalHour, $arrivalMinutes),
            'durationMinutes' => $durationMinutes,
            'price' => 1000000 + $i * 200000,
            'cabinClass' => $cabinClasses[$i % count($cabinClasses)],
            'seatsLeft' => 3 + $i * 2,
            'refundable' => $i % 2 === 0,
            'stops' => min($i % 3, 2)
        ];
    }

    return $flights;
}

// Generate mock flights
$flights = generateMockFlights($from, $to, $depart_date);

// Apply filters to flights
$filtered_flights = [];
foreach ($flights as $flight) {
    // Price filter
    if ($flight['price'] < $min_price || $flight['price'] > $max_price) {
        continue;
    }

    // Departure time filter
    if ($departure_time !== 'all') {
        $hour = intval(explode(':', $flight['departureTime'])[0]);
        
        if ($departure_time === 'morning' && ($hour < 6 || $hour >= 12)) {
            continue;
        } else if ($departure_time === 'afternoon' && ($hour < 12 || $hour >= 18)) {
            continue;
        } else if ($departure_time === 'evening' && ($hour < 18 || $hour >= 24)) {
            continue;
        }
    }

    // Stops filter
    if ($stops !== 'all' && $flight['stops'] !== intval($stops)) {
        continue;
    }

    $filtered_flights[] = $flight;
}

// Helper function to format price in IDR
function formatPriceIDR($price) {
    return 'Rp ' . number_format($price, 0, ',', '.');
}

// Helper function to format duration
function formatDuration($minutes) {
    $hours = floor($minutes / 60);
    $mins = $minutes % 60;
    return "{$hours}h {$mins}m";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Search Results - Tickfly</title>
    <meta name="description" content="Find and book the best flight deals with Tickfly">
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
        .search-form-container {
            box-shadow: 0px 4px 12px rgba(0, 0, 0, 0.1);
            border-radius: 8px;
            background-color: white;
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

<!-- Search Header -->
<div class="bg-gray-100 border-b">
    <div class="container mx-auto px-4 py-4">
        <div class="flex flex-wrap items-center justify-between gap-4">
            <div class="flex items-center gap-4">
                <div class="flex items-center gap-2">
                    <span class="text-gray-600">✈️</span>
                    <div>
                        <p class="font-medium"><?php echo htmlspecialchars($from); ?></p>
                    </div>
                </div>

                <div class="text-gray-400">→</div>

                <div class="flex items-center gap-2">
                    <span class="text-gray-600">🏢</span>
                    <div>
                        <p class="font-medium"><?php echo htmlspecialchars($to); ?></p>
                    </div>
                </div>
            </div>

            <div class="flex items-center gap-2">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-gray-600">
                    <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
                    <line x1="16" y1="2" x2="16" y2="6"></line>
                    <line x1="8" y1="2" x2="8" y2="6"></line>
                    <line x1="3" y1="10" x2="21" y2="10"></line>
                </svg>
                <div>
                    <p class="font-medium"><?php echo $formatted_date; ?></p>
                </div>
            </div>

            <div class="flex items-center gap-2">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-gray-600">
                    <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                    <circle cx="9" cy="7" r="4"></circle>
                    <path d="M23 21v-2a4 4 0 0 0-3-3.87"></path>
                    <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
                </svg>
                <div>
                    <p class="font-medium">
                        <?php echo $travelers; ?> passenger<?php echo intval($travelers) > 1 ? 's' : ''; ?> | <?php echo htmlspecialchars($cabin_class); ?>
                    </p>
                </div>
            </div>

            <a href="index.php" class="px-4 py-2 bg-blue-800 text-white rounded-md hover:bg-blue-900 transition">
                Search Another Tickets
            </a>
        </div>
    </div>
</div>

<!-- Main Content -->
<div class="container mx-auto px-4 py-6">
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
        <!-- Sidebar Filters -->
        <div class="md:col-span-1">
            <div class="bg-white rounded-lg shadow p-4 border border-blue-100">
                <h2 class="text-lg font-medium mb-4 text-center text-blue-800">Filter</h2>

                <form id="filterForm" action="search.php" method="GET">
                    <!-- Hidden fields to preserve search parameters -->
                    <input type="hidden" name="from" value="<?php echo htmlspecialchars($from); ?>">
                    <input type="hidden" name="to" value="<?php echo htmlspecialchars($to); ?>">
                    <input type="hidden" name="depart_date" value="<?php echo htmlspecialchars($depart_date); ?>">
                    <input type="hidden" name="travelers" value="<?php echo htmlspecialchars($travelers); ?>">
                    <input type="hidden" name="cabin_class" value="<?php echo htmlspecialchars($cabin_class); ?>">
                    
                    <!-- Price Filter -->
                    <div class="mb-6">
                        <h3 class="font-medium mb-3">Price</h3>
                        <div class="flex justify-between mb-2">
                            <span id="minPriceDisplay" class="text-sm text-gray-600"><?php echo formatPriceIDR($min_price); ?></span>
                            <span id="maxPriceDisplay" class="text-sm text-gray-600"><?php echo formatPriceIDR($max_price); ?></span>
                        </div>
                        <input
                            type="range"
                            id="min_price"
                            name="min_price"
                            min="500000"
                            max="2000000"
                            step="100000"
                            value="<?php echo $min_price; ?>"
                            class="w-full mb-2 filter-control"
                        >
                        <input
                            type="range"
                            id="max_price"
                            name="max_price"
                            min="500000"
                            max="2000000"
                            step="100000"
                            value="<?php echo $max_price; ?>"
                            class="w-full filter-control"
                        >
                    </div>

                    <!-- Departure Time Filter -->
                    <div class="mb-6">
                        <h3 class="font-medium mb-3">Departure Time</h3>
                        <div class="space-y-2">
                            <div class="flex items-center">
                                <input
                                    type="radio"
                                    id="all_times"
                                    name="departure_time"
                                    value="all"
                                    <?php echo $departure_time === 'all' ? 'checked' : ''; ?>
                                    class="h-4 w-4 text-blue-600 filter-control"
                                >
                                <label for="all_times" class="ml-2 text-sm text-gray-700">
                                    All Times
                                </label>
                            </div>

                            <div class="flex items-center">
                                <input
                                    type="radio"
                                    id="morning"
                                    name="departure_time"
                                    value="morning"
                                    <?php echo $departure_time === 'morning' ? 'checked' : ''; ?>
                                    class="h-4 w-4 text-blue-600 filter-control"
                                >
                                <label for="morning" class="ml-2 text-sm text-gray-700">
                                    Morning (06:00 - 12:00)
                                </label>
                            </div>

                            <div class="flex items-center">
                                <input
                                    type="radio"
                                    id="afternoon"
                                    name="departure_time"
                                    value="afternoon"
                                    <?php echo $departure_time === 'afternoon' ? 'checked' : ''; ?>
                                    class="h-4 w-4 text-blue-600 filter-control"
                                >
                                <label for="afternoon" class="ml-2 text-sm text-gray-700">
                                    Afternoon (12:00 - 18:00)
                                </label>
                            </div>

                            <div class="flex items-center">
                                <input
                                    type="radio"
                                    id="evening"
                                    name="departure_time"
                                    value="evening"
                                    <?php echo $departure_time === 'evening' ? 'checked' : ''; ?>
                                    class="h-4 w-4 text-blue-600 filter-control"
                                >
                                <label for="evening" class="ml-2 text-sm text-gray-700">
                                    Evening (18:00 - 24:00)
                                </label>
                            </div>
                        </div>
                    </div>

                    <!-- Stops Filter -->
                    <div>
                        <h3 class="font-medium mb-3">Stops</h3>
                        <div class="space-y-2">
                            <div class="flex items-center">
                                <input
                                    type="radio"
                                    id="all_stops"
                                    name="stops"
                                    value="all"
                                    <?php echo $stops === 'all' ? 'checked' : ''; ?>
                                    class="h-4 w-4 text-blue-600 filter-control"
                                >
                                <label for="all_stops" class="ml-2 text-sm text-gray-700">
                                    All Flights
                                </label>
                            </div>

                            <div class="flex items-center">
                                <input
                                    type="radio"
                                    id="non_stop"
                                    name="stops"
                                    value="0"
                                    <?php echo $stops === '0' ? 'checked' : ''; ?>
                                    class="h-4 w-4 text-blue-600 filter-control"
                                >
                                <label for="non_stop" class="ml-2 text-sm text-gray-700">
                                    Non Stop
                                </label>
                            </div>

                            <div class="flex items-center">
                                <input
                                    type="radio"
                                    id="one_stop"
                                    name="stops"
                                    value="1"
                                    <?php echo $stops === '1' ? 'checked' : ''; ?>
                                    class="h-4 w-4 text-blue-600 filter-control"
                                >
                                <label for="one_stop" class="ml-2 text-sm text-gray-700">
                                    1 Stop
                                </label>
                            </div>

                            <div class="flex items-center">
                                <input
                                    type="radio"
                                    id="two_stops"
                                    name="stops"
                                    value="2"
                                    <?php echo $stops === '2' ? 'checked' : ''; ?>
                                    class="h-4 w-4 text-blue-600 filter-control"
                                >
                                <label for="two_stops" class="ml-2 text-sm text-gray-700">
                                    2 Stops
                                </label>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Apply Filters button is removed as requested -->
                </form>
            </div>
        </div>

        <!-- Flight Results -->
        <div class="md:col-span-3">
            <div class="bg-blue-100 p-4 rounded-md mb-4">
                <h2 class="text-center font-medium">Flight Found</h2>
            </div>

            <div id="flight-results">
                <?php if (empty($filtered_flights)): ?>
                    <div class="bg-yellow-100 text-yellow-800 p-4 rounded-md">
                        No flights found matching your criteria. Try adjusting your filters.
                    </div>
                <?php else: ?>
                    <div class="space-y-4">
                        <?php foreach ($filtered_flights as $flight): ?>
                            <div class="bg-white rounded-lg shadow border border-gray-200 overflow-hidden">
                                <div class="p-4">
                                    <div class="flex flex-col md:flex-row md:items-center justify-between">
                                        <div class="flex items-center mb-4 md:mb-0">
                                            <div class="w-8 h-8 mr-3">
                                                <img 
                                                    src="<?php echo $flight['airline']['logo']; ?>" 
                                                    alt="<?php echo $flight['airline']['name']; ?>"
                                                    width="32"
                                                    height="32"
                                                    onerror="this.src='placeholder.svg'"
                                                />
                                            </div>
                                            <div>
                                                <p class="font-medium"><?php echo $flight['airline']['name']; ?></p>
                                                <p class="text-sm text-gray-600"><?php echo $flight['cabinClass']; ?></p>
                                            </div>
                                        </div>

                                        <div class="flex flex-col md:flex-row items-start md:items-center gap-4 md:gap-8">
                                            <div class="text-center">
                                                <p class="font-medium"><?php echo $flight['departureTime']; ?></p>
                                                <p class="text-sm text-gray-600"><?php echo $flight['from']['code']; ?></p>
                                            </div>

                                            <div class="hidden md:block text-center">
                                                <div class="relative w-24 h-6">
                                                    <div class="absolute top-1/2 left-0 right-0 border-t border-gray-300"></div>
                                                    <div class="absolute top-1/2 right-0 w-2 h-2 border-t border-r border-gray-300 transform rotate-45 -translate-y-1/2"></div>
                                                </div>
                                                <p class="text-xs text-gray-500"><?php echo formatDuration($flight['durationMinutes']); ?></p>
                                            </div>

                                            <div class="text-center">
                                                <p class="font-medium"><?php echo $flight['arrivalTime']; ?></p>
                                                <p class="text-sm text-gray-600"><?php echo $flight['to']['code']; ?></p>
                                            </div>

                                            <div class="md:ml-4">
                                                <p class="font-bold text-blue-800"><?php echo formatPriceIDR($flight['price']); ?></p>
                                                <p class="text-xs text-gray-500"><?php echo $flight['seatsLeft']; ?> seats left</p>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="mt-4 flex flex-col md:flex-row justify-between items-center">
                                        <div class="mb-3 md:mb-0">
                                            <span class="inline-block px-3 py-1 rounded-full text-xs <?php echo $flight['refundable'] ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'; ?>">
                                                <?php echo $flight['refundable'] ? 'REFUNDABLE' : 'NON-REFUNDABLE'; ?>
                                            </span>
                                        </div>

                                        <div class="flex gap-2">
                                        <a href="flight-detail.php?flightId=<?php echo $flight['id']; ?>" class="px-4 py-2 border border-blue-800 text-blue-800 rounded-md hover:bg-blue-100 transition">
                                            Flight Detail
                                        </a>

                                            <a href="booking.php?flightId=<?php echo $flight['id']; ?>&from=<?php echo urlencode($from); ?>&to=<?php echo urlencode($to); ?>&date=<?php echo urlencode($depart_date); ?>&travelers=<?php echo urlencode($travelers); ?>&class=<?php echo urlencode($cabin_class); ?>" class="px-4 py-2 text-sm text-white bg-blue-800 rounded hover:bg-blue-900 transition">
                                                CHOOSE
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
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

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const minPriceInput = document.getElementById('min_price');
        const maxPriceInput = document.getElementById('max_price');
        const minPriceDisplay = document.getElementById('minPriceDisplay');
        const maxPriceDisplay = document.getElementById('maxPriceDisplay');
        const filterControls = document.querySelectorAll('.filter-control');
        
        // Function to format price in IDR
        const formatPrice = (price) => {
            return new Intl.NumberFormat('id-ID', {
                style: 'currency',
                currency: 'IDR',
                minimumFractionDigits: 0,
                maximumFractionDigits: 0
            }).format(price).replace('IDR', 'Rp');
        };
        
        // Update price displays when sliders change
        const updatePriceDisplay = function() {
            const minPrice = parseInt(minPriceInput.value);
            const maxPrice = parseInt(maxPriceInput.value);
            
            minPriceDisplay.textContent = formatPrice(minPrice);
            maxPriceDisplay.textContent = formatPrice(maxPrice);
        };
        
        // Add event listeners to all filter controls to submit form when changed
        filterControls.forEach(control => {
            control.addEventListener('input', function() {
                if (this.id === 'min_price' || this.id === 'max_price') {
                    updatePriceDisplay();
                }
                
                // Short delay for range sliders to avoid too many requests while dragging
                if (this.type === 'range') {
                    clearTimeout(this.timer);
                    this.timer = setTimeout(() => {
                        document.getElementById('filterForm').submit();
                    }, 500);
                } else {
                    document.getElementById('filterForm').submit();
                }
            });
        });
    });
</script>

</body>
</html>