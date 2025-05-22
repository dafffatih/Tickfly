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

// Get flight parameters from URL
$flight_id = isset($_GET['flightId']) ? $_GET['flightId'] : null;
$from = isset($_GET['from']) ? $_GET['from'] : 'Jakarta';
$to = isset($_GET['to']) ? $_GET['to'] : 'Lampung';
$date = isset($_GET['date']) ? $_GET['date'] : date('Y-m-d');
$travelers = isset($_GET['travelers']) ? $_GET['travelers'] : '1';
$cabin_class = isset($_GET['class']) ? $_GET['class'] : 'economy';

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

// Generate mock seat data
function generateSeats() {
    $rows = 10;
    $cols = 6;
    $seats = [];
    
    // These seats are already booked
    $bookedSeats = ["A1", "B1", "C3", "D3", "E5", "F5", "A7", "B7", "C7", "D8", "E8", "F8"];
    
    for ($row = 1; $row <= $rows; $row++) {
        for ($col = 0; $col < $cols; $col++) {
            $seatLetter = chr(65 + $col); // A, B, C, D, E, F
            $seatId = $seatLetter . $row;
            
            $seats[] = [
                'id' => $seatId,
                'row' => $row,
                'col' => $seatLetter,
                'status' => in_array($seatId, $bookedSeats) ? 'booked' : 'available'
            ];
        }
    }
    
    return $seats;
}

$seats = generateSeats();

// Handle form submission
$errors = [];
$passengerInfo = [
    'title' => isset($_POST['title']) ? $_POST['title'] : 'Mr',
    'firstName' => isset($_POST['firstName']) ? $_POST['firstName'] : '',
    'lastName' => isset($_POST['lastName']) ? $_POST['lastName'] : '',
    'email' => isset($_POST['email']) ? $_POST['email'] : '',
    'phone' => isset($_POST['phone']) ? $_POST['phone'] : '',
];

$selectedSeats = isset($_POST['selectedSeat']) ? (array)$_POST['selectedSeat'] : [];

$additionalServices = [
    'meals' => isset($_POST['meals']) && $_POST['meals'] === 'on',
    'baggage' => isset($_POST['baggage']) && $_POST['baggage'] === 'on',
];

// Calculate service fees
$baseFare = $flight ? $flight['price'] * $travelers : 0;
$fees = 100000; // Standard fees and surcharges
$mealsCost = $additionalServices['meals'] ? 50000 : 0; // 50k if meals are selected
$baggageCost = $additionalServices['baggage'] ? 100000 : 0; // 100k if baggage is selected

$totalAddOns = $mealsCost + $baggageCost;
$totalPrice = $baseFare + $fees + $totalAddOns;

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['continue'])) {
    // Validation
    if (empty($passengerInfo['firstName'])) {
        $errors[] = "First name is required";
    }
    
    if (empty($passengerInfo['lastName'])) {
        $errors[] = "Last name is required";
    }
    
    if (empty($passengerInfo['email']) || !filter_var($passengerInfo['email'], FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Valid email is required";
    }
    
    if (empty($passengerInfo['phone'])) {
        $errors[] = "Phone number is required";
    }
    
    if (count($selectedSeats) < intval($travelers)) {
        $errors[] = "Please select " . intval($travelers) . " seat(s)";
    }
    
    // If no errors, proceed to payment page
    if (empty($errors)) {
        $queryParams = http_build_query([
            'flightId' => $flight_id,
            'seat' => $selectedSeats, 
            'from' => $from,
            'to' => $to,
            'date' => $date,
            'travelers' => $travelers,
            'class' => $cabin_class,
            'firstName' => $passengerInfo['firstName'],
            'lastName' => $passengerInfo['lastName'],
            'email' => $passengerInfo['email'],
            'phone' => $passengerInfo['phone'],
            'meals' => $additionalServices['meals'] ? '1' : '0',
            'baggage' => $additionalServices['baggage'] ? '1' : '0'
        ]);
        
        header("Location: payment.php?" . $queryParams);
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Flight Booking - Tickfly</title>
    <meta name="description" content="Book your flight with Tickfly">
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

            <a href="index.php?from=<?php echo urlencode($from); ?>&to=<?php echo urlencode($to); ?>&depart_date=<?php echo urlencode($date); ?>&travelers=<?php echo urlencode($travelers); ?>&cabin_class=<?php echo urlencode($cabin_class); ?>" class="px-4 py-2 bg-blue-800 text-white rounded-md hover:bg-blue-900 transition">
                Search Another Ticket
            </a>
        </div>
    </div>
</div>

<?php if (!$flight): ?>
<!-- Flight Not Found Error -->
<div class="container mx-auto px-4 py-12">
    <div class="bg-yellow-100 text-yellow-800 p-4 rounded-md">
        Flight not found. Please go back to search and select a flight.
    </div>
</div>
<?php else: ?>

<!-- Main Content -->
<div class="container mx-auto px-4 py-6">
    <!-- Display validation errors if any -->
    <?php if (!empty($errors)): ?>
        <div class="bg-red-100 text-red-700 p-4 rounded-md mb-6">
            <ul class="list-disc pl-5">
                <?php foreach ($errors as $error): ?>
                    <li><?php echo $error; ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>
    
    <form method="POST" action="">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div class="md:col-span-2">
                <!-- Passenger Form Section -->
                <div class="bg-white p-6 rounded-lg shadow border border-gray-200">
                    <h2 class="text-lg font-medium mb-4">Passenger Detail</h2>
                    
                    <div class="mb-6">
                        <h3 class="text-base font-medium mb-3">Passenger Info</h3>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div>
                                <label for="title" class="block text-sm text-gray-600 mb-1">Title</label>
                                <select name="title" id="title" class="w-full p-2 border border-gray-300 rounded-md">
                                    <option value="Mr" <?php echo $passengerInfo['title'] === 'Mr' ? 'selected' : ''; ?>>Mr</option>
                                    <option value="Mrs" <?php echo $passengerInfo['title'] === 'Mrs' ? 'selected' : ''; ?>>Mrs</option>
                                    <option value="Ms" <?php echo $passengerInfo['title'] === 'Ms' ? 'selected' : ''; ?>>Ms</option>
                                </select>
                            </div>
                            <div>
                                <label for="firstName" class="block text-sm text-gray-600 mb-1">First Name</label>
                                <input
                                    type="text"
                                    id="firstName"
                                    name="firstName"
                                    value="<?php echo htmlspecialchars($passengerInfo['firstName']); ?>"
                                    class="w-full p-2 border border-gray-300 rounded-md"
                                    placeholder="First Name"
                                >
                            </div>
                            <div>
                                <label for="lastName" class="block text-sm text-gray-600 mb-1">Last Name</label>
                                <input
                                    type="text"
                                    id="lastName"
                                    name="lastName"
                                    value="<?php echo htmlspecialchars($passengerInfo['lastName']); ?>"
                                    class="w-full p-2 border border-gray-300 rounded-md"
                                    placeholder="Last Name"
                                >
                            </div>
                        </div>
                    </div>
                    
                    <div>
                        <h3 class="text-base font-medium mb-3">Contact Detail</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label for="email" class="block text-sm text-gray-600 mb-1">Email</label>
                                <input
                                    type="email"
                                    id="email"
                                    name="email"
                                    value="<?php echo htmlspecialchars($passengerInfo['email']); ?>"
                                    class="w-full p-2 border border-gray-300 rounded-md"
                                    placeholder="your@email.com"
                                >
                            </div>
                            <div>
                                <label for="phone" class="block text-sm text-gray-600 mb-1">Mobile Phone</label>
                                <input
                                    type="tel"
                                    id="phone"
                                    name="phone"
                                    value="<?php echo htmlspecialchars($passengerInfo['phone']); ?>"
                                    class="w-full p-2 border border-gray-300 rounded-md"
                                    placeholder="Enter Your Phone Number"
                                >
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Additional Services Section -->
                <div class="mt-6">
                    <div class="bg-white p-6 rounded-lg shadow border border-gray-200">
                        <h3 class="text-lg font-medium mb-4">Additional Services (Optional)</h3>
                        <div class="flex flex-wrap gap-4">
                            <div class="flex items-center">
                                <input
                                    type="checkbox"
                                    id="meals"
                                    name="meals"
                                    <?php echo $additionalServices['meals'] ? 'checked' : ''; ?>
                                    class="h-4 w-4 text-blue-600"
                                >
                                <label for="meals" class="ml-2 text-sm text-gray-700">
                                    Meals
                                </label>
                            </div>

                            <div class="flex items-center">
                                <input
                                    type="checkbox"
                                    id="baggage"
                                    name="baggage"
                                    <?php echo $additionalServices['baggage'] ? 'checked' : ''; ?>
                                    class="h-4 w-4 text-blue-600"
                                >
                                <label for="baggage" class="ml-2 text-sm text-gray-700">
                                    Baggage
                                </label>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Seat Selection Section -->
                <div class="mt-6">
                    <div class="bg-white p-6 rounded-lg shadow border border-blue-100">
                        <div class="flex justify-between items-center mb-4">
                            <h2 class="text-lg font-medium">FLIGHT SEAT PLAN</h2>
                            <div id="seatStatus">
                                <?php if ($selectedSeats): ?>
                                <span class="text-green-600">Selected Seat: <strong><?php 
                                foreach ($selectedSeats as $i) {
                                    echo "{$i},FDS";
                                }
                                ?></strong></span>
                                <?php endif; ?>
                            </div>
                        </div>

                        <div class="overflow-x-auto">
                            <div class="grid grid-cols-6 gap-2 mb-6">
                                <?php foreach ($seats as $seat): ?>
                                <button
                                    type="button"
                                    data-seat-id="<?php echo $seat['id']; ?>"
                                    class="seat-btn flex items-center justify-center w-10 h-10 rounded-md transition
                                    <?php if ($seat['status'] === 'booked'): ?>
                                        bg-gray-800 text-white cursor-not-allowed
                                    <?php elseif ($selectedSeats === $seat['id']): ?>
                                        bg-blue-800 text-white
                                    <?php else: ?>
                                        bg-blue-100 text-blue-800 hover:bg-blue-200
                                    <?php endif; ?>"
                                    <?php echo $seat['status'] === 'booked' ? 'disabled' : ''; ?>
                                >
                                    <?php echo $seat['id']; ?>
                                </button>
                                <?php endforeach; ?>
                            </div>
                            
                            <?php foreach ($selectedSeats as $seat): ?>
                                <input type="hidden" name="selectedSeat[]" value="<?php echo htmlspecialchars($seat); ?>">
                            <?php endforeach; ?>

                            <div class="flex justify-center gap-8">
                                <div class="flex items-center">
                                    <div class="w-4 h-4 bg-gray-800 rounded-sm mr-2"></div>
                                    <span class="text-sm">Booked</span>
                                </div>

                                <div class="flex items-center">
                                    <div class="w-4 h-4 bg-blue-100 rounded-sm mr-2"></div>
                                    <span class="text-sm">Available</span>
                                </div>

                                <div class="flex items-center">
                                    <div class="w-4 h-4 bg-blue-800 rounded-sm mr-2"></div>
                                    <span class="text-sm">Choosed</span>
                                </div>

                                <?php if (!empty($selectedSeats)): ?>
                                <div class="flex items-center">
                                    <div class="w-4 h-4 bg-blue-800 rounded-sm mr-2"></div>
                                    <span class="text-sm">Your Seats (<?php echo implode(', ', $selectedSeats); ?>)</span>
                                </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Payment Summary Section -->
            <div class="md:col-span-1">
                <div class="bg-white p-6 rounded-lg shadow border border-gray-200 sticky top-24">
                    <h2 class="text-lg font-medium mb-4">Payment Detail</h2>

                    <div class="border-b pb-4">
                        <div class="flex justify-between mb-2">
                            <span class="text-gray-600">Base Fare</span>
                            <span><?php echo formatPriceIDR($baseFare); ?></span>
                        </div>
                        <div class="flex justify-between mb-2">
                            <span class="text-gray-600">Fee & Surcharges</span>
                            <span><?php echo formatPriceIDR($fees); ?></span>
                        </div>
                        <div class="flex justify-between mb-2">
                            <span class="text-gray-600">Booking Fee</span>
                            <span>Rp 0</span>
                        </div>
                        <div class="flex justify-between mb-2">
                            <span class="text-gray-600">PPN</span>
                            <span>Rp 0</span>
                        </div>
                    </div>

                    <div class="border-b py-4">
                        <div class="flex justify-between mb-2">
                            <span class="text-gray-600">Total Fare</span>
                            <span><?php echo formatPriceIDR($baseFare + $fees); ?></span>
                        </div>
                        <div class="flex justify-between mb-2">
                            <span class="text-gray-600">Add Ons</span>
                            <span class="add-ons-display"><?php echo formatPriceIDR($totalAddOns); ?></span>
                        </div>
                    </div>

                    <div class="pt-4">
                        <div class="flex justify-between mb-4">
                            <span class="font-bold">Price You Pay</span>
                            <span class="font-bold text-blue-800 total-price-display"><?php echo formatPriceIDR($totalPrice); ?></span>
                        </div>

                        <button
                            type="submit"
                            name="continue"
                            class="w-full py-3 bg-blue-800 text-white rounded-md hover:bg-blue-900 transition font-medium"
                        >
                            CONTINUE
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </form>
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

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Seat selection functionality
    const seatButtons = document.querySelectorAll('.seat-btn');
    const seatStatusDiv = document.getElementById('seatStatus');
    const travelers = <?php echo intval($travelers); ?>;
    
    // Initialize selected seats from PHP
    let selectedSeats = <?php echo json_encode($selectedSeats); ?>;
    
    function updateSelectedSeatsDisplay() {
        if (selectedSeats.length > 0) {
            seatStatusDiv.innerHTML = '<span class="text-green-600">Selected Seats: <strong>' + 
                selectedSeats.join(', ') + '</strong></span>';
        } else {
            seatStatusDiv.innerHTML = '';
        }
    }
    
    function updateHiddenInputs() {
        // Remove all existing hidden inputs
        document.querySelectorAll('input[name="selectedSeat[]"]').forEach(el => el.remove());
        
        // Add new hidden inputs for each selected seat
        selectedSeats.forEach(seat => {
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'selectedSeat[]';
            input.value = seat;
            document.querySelector('form').appendChild(input);
        });
    }
    
    // Initialize display
    updateSelectedSeatsDisplay();
    updateHiddenInputs();
    
    seatButtons.forEach(button => {
        if (!button.disabled) {
            button.addEventListener('click', function() {
                const seatId = this.getAttribute('data-seat-id');
                
                // Check if seat is already selected
                const seatIndex = selectedSeats.indexOf(seatId);
                
                if (seatIndex > -1) {
                    // Seat is already selected - deselect it
                    selectedSeats.splice(seatIndex, 1);
                    this.classList.remove('bg-blue-800', 'text-white');
                    this.classList.add('bg-blue-100', 'text-blue-800', 'hover:bg-blue-200');
                } else {
                    // Check if we can select more seats
                    if (selectedSeats.length < travelers) {
                        // Select new seat
                        selectedSeats.push(seatId);
                        this.classList.remove('bg-blue-100', 'text-blue-800', 'hover:bg-blue-200');
                        this.classList.add('bg-blue-800', 'text-white');
                    } else {
                        // Show error that maximum seats reached
                        return;
                    }
                }
                
                // Update display and hidden inputs
                updateSelectedSeatsDisplay();
                updateHiddenInputs();
            });
            
            // Set initial state of buttons
            if (selectedSeats.includes(button.getAttribute('data-seat-id'))) {
                button.classList.remove('bg-blue-100', 'text-blue-800', 'hover:bg-blue-200');
                button.classList.add('bg-blue-800', 'text-white');
            }
        }
    });
    
    // Update payment summary based on additional services
    const mealsCheckbox = document.getElementById('meals');
    const baggageCheckbox = document.getElementById('baggage');
    const addOnsDisplay = document.querySelector('.add-ons-display');
    const totalPriceDisplay = document.querySelector('.total-price-display');
    
    // Base values
    const baseFare = <?php echo $flight ? $flight['price'] * $travelers : 0; ?>; // Base fare amount
    const fees = 100000;      // Fees amount
    const mealsCost = 50000;  // Cost for meals
    const baggageCost = 100000; // Cost for baggage
    
    function formatPriceIDR(price) {
        return 'Rp ' + price.toLocaleString('id-ID');
    }
    
    function updatePaymentSummary() {
        let totalAddOns = 0;
        
        if (mealsCheckbox.checked) {
            totalAddOns += mealsCost;
        }
        
        if (baggageCheckbox.checked) {
            totalAddOns += baggageCost;
        }
        
        // Update the add-ons display
        addOnsDisplay.textContent = formatPriceIDR(totalAddOns);
        
        // Update the total price display
        const totalPrice = baseFare + fees + totalAddOns;
        totalPriceDisplay.textContent = formatPriceIDR(totalPrice);
    }
    
    // Add event listeners to checkboxes
    mealsCheckbox.addEventListener('change', updatePaymentSummary);
    baggageCheckbox.addEventListener('change', updatePaymentSummary);
    
    // Initialize on page load
    updatePaymentSummary();
});
</script>

</body>
</html>
