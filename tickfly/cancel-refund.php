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
    header("location: login.php?redirect=cancel-refund.php");
    exit;
}

// Logout functionality
if (isset($_GET['logout'])) {
    // Destroy session and redirect to login page
    session_destroy();
    header("location: login.php");
    exit;
}


// Mock data for bookings (in real application, this would come from a database)
$bookings = [
    [
        'id' => 'TF123456',
        'flight' => [
            'airline' => ['name' => 'Garuda Indonesia', 'logo' => '/placeholder.svg?height=32&width=32'],
            'from' => ['name' => 'Jakarta', 'code' => 'JKT'],
            'to' => ['name' => 'Bali', 'code' => 'DPS'],
            'departureTime' => '08:30',
            'arrivalTime' => '11:15',
            'date' => '2021-06-20',
        ],
        'status' => 'confirmed',
        'passenger' => 'John Doe',
        'seat' => 'A4',
        'price' => 2500000,
    ]
];

// Initialize variables
$selectedBooking = null;
$refundSuccess = false;
$refundMessage = '';
$refundId = '';

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['submit_refund'])) {
    $bookingId = $_POST['booking_id'] ?? '';
    $refundReason = $_POST['refund_reason'] ?? '';
    
    if (!empty($bookingId) && !empty($refundReason)) {
        // Process refund (in a real app, this would interact with a payment API)
        $refundSuccess = true;
        $refundId = 'RF' . str_pad(rand(0, 999999), 6, '0', STR_PAD_LEFT);
        $refundMessage = 'Your refund request has been submitted and is being processed. You will receive an email with further details.';
    }
}

// Handle booking selection
if (isset($_GET['booking_id'])) {
    $bookingId = $_GET['booking_id'];
    foreach ($bookings as $booking) {
        if ($booking['id'] == $bookingId) {
            $selectedBooking = $booking;
            break;
        }
    }
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
    <title>Cancel & Refund - Tickfly</title>
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
            <a href="cancel-refund.php" class="text-blue-600 border-b-2 border-blue-600">Cancel & Refund</a>
            <a href="help-center.php" class="text-gray-700 hover:text-blue-600">Help Center</a>
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

<div class="container mx-auto px-4 py-8">
    <h1 class="text-2xl font-bold mb-6">Cancel & Refund</h1>

    <?php if ($refundSuccess): ?>
    <!-- Success Message -->
    <div class="bg-green-100 text-green-800 p-6 rounded-md mb-6">
        <h3 class="font-bold text-lg mb-2">Refund request has been submitted successfully</h3>
        <p>Refund ID: <?php echo $refundId; ?></p>
        <p class="mt-4"><?php echo $refundMessage; ?></p>
    </div>
    <?php endif; ?>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <!-- Booking Selection Column -->
        <div class="md:col-span-1">
            <div class="bg-white rounded-lg shadow-md p-6 border border-gray-200">
                <h2 class="text-lg font-medium mb-4">Select a Booking</h2>

                <div class="space-y-4">
                    <?php foreach ($bookings as $booking): ?>
                        <a href="?booking_id=<?php echo $booking['id']; ?>" 
                           class="block w-full text-left p-4 rounded-md border transition 
                           <?php echo ($selectedBooking && $selectedBooking['id'] == $booking['id']) 
                                 ? 'border-blue-500 bg-blue-50' 
                                 : 'border-gray-200 hover:border-blue-300 hover:bg-blue-50'; ?>">
                            <p class="font-medium">
                                <?php echo $booking['flight']['from']['code']; ?> → <?php echo $booking['flight']['to']['code']; ?>
                            </p>
                            <p class="text-sm text-gray-600">
                                <?php echo $booking['flight']['date']; ?> • <?php echo $booking['flight']['departureTime']; ?>
                            </p>
                            <p class="text-sm text-gray-600 mt-1">Booking #<?php echo $booking['id']; ?></p>
                        </a>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <!-- Refund Details Column -->
        <div class="md:col-span-2">
            <?php if ($selectedBooking): ?>
                <div class="bg-white rounded-lg shadow-md p-6 border border-gray-200">
                    <h2 class="text-lg font-medium mb-4">Refund Details</h2>

                    <div class="mb-6 p-4 bg-blue-50 rounded-md border border-blue-100">
                        <div class="flex items-start">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-blue-800 mr-3 mt-1" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2h-1V9a1 1 0 00-1-1z" clip-rule="evenodd" />
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
                            <p class="font-medium"><?php echo $selectedBooking['id']; ?></p>
                        </div>

                        <div>
                            <p class="text-gray-500 text-sm">Flight</p>
                            <p class="font-medium"><?php echo $selectedBooking['flight']['airline']['name']; ?></p>
                        </div>

                        <div>
                            <p class="text-gray-500 text-sm">Route</p>
                            <p class="font-medium">
                                <?php echo $selectedBooking['flight']['from']['name']; ?> → 
                                <?php echo $selectedBooking['flight']['to']['name']; ?>
                            </p>
                        </div>

                        <div>
                            <p class="text-gray-500 text-sm">Date & Time</p>
                            <p class="font-medium">
                                <?php echo $selectedBooking['flight']['date']; ?> • 
                                <?php echo $selectedBooking['flight']['departureTime']; ?>
                            </p>
                        </div>

                        <div>
                            <p class="text-gray-500 text-sm">Passenger</p>
                            <p class="font-medium"><?php echo $selectedBooking['passenger']; ?></p>
                        </div>

                        <div>
                            <p class="text-gray-500 text-sm">Amount to be Refunded</p>
                            <p class="font-bold text-blue-800"><?php echo formatPrice($selectedBooking['price'] * 0.8); ?></p>
                            <p class="text-xs text-gray-500">(80% of <?php echo formatPrice($selectedBooking['price']); ?>)</p>
                        </div>
                    </div>

                    <form method="POST" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
                        <input type="hidden" name="booking_id" value="<?php echo $selectedBooking['id']; ?>">
                        
                        <div class="mb-6">
                            <label for="refundReason" class="block text-sm font-medium text-gray-700 mb-2">
                                Reason for Refund
                            </label>
                            <textarea
                                id="refundReason"
                                name="refund_reason"
                                rows="4"
                                class="w-full p-3 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                                placeholder="Please provide a reason for your refund request..."
                                required
                            ></textarea>
                        </div>

                        <div class="flex justify-end gap-3">
                            <a href="cancel-refund.php" 
                               class="px-4 py-2 border border-gray-300 text-gray-700 rounded-md hover:bg-gray-50 transition">
                                Cancel
                            </a>

                            <button
                                type="submit"
                                name="submit_refund"
                                class="px-4 py-2 bg-blue-800 text-white rounded-md hover:bg-blue-900 transition"
                            >
                                Submit Refund Request
                            </button>
                        </div>
                    </form>
                </div>
            <?php else: ?>
                <div class="bg-gray-100 rounded-lg p-6 border border-gray-200 h-full flex items-center justify-center">
                    <p class="text-gray-500 text-center">Select a booking from the list to request a refund</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

</body>
</html>