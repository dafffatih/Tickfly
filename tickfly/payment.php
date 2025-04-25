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
$firstName = isset($_GET['firstName']) ? $_GET['firstName'] : '';
$lastName = isset($_GET['lastName']) ? $_GET['lastName'] : '';
$email = isset($_GET['email']) ? $_GET['email'] : '';
$phone = isset($_GET['phone']) ? $_GET['phone'] : '';
$title = isset($_GET['title']) ? $_GET['title'] : 'Mr';
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

// Initialize payment variables
$selectedPaymentMethod = isset($_POST['paymentMethod']) ? $_POST['paymentMethod'] : 'BNI';
$paymentMethods = [
    'BNI' => [
        'name' => 'BNI',
        'type' => 'Bank Transfer',
        'account' => '0123456789'
    ],
    'BRI' => [
        'name' => 'BRI',
        'type' => 'Bank Transfer',
        'account' => '9876543210'
    ],
    'DANA' => [
        'name' => 'DANA',
        'type' => 'E-Wallet',
        'account' => '081234567890'
    ],
    'GoPay' => [
        'name' => 'GoPay',
        'type' => 'E-Wallet',
        'account' => '089876543210'
    ]
];

// Handle form submission for payment confirmation
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submitPayment'])) {
    // In a real application, you would:
    // 1. Validate the payment details
    // 2. Store the payment information in a database
    // 3. Upload and store the payment proof
    // 4. Create a booking record
    // 5. Send confirmation emails
    
    // For the demo, we'll redirect to booking-confirmation.php after a delay
    $queryParams = http_build_query([
        'flightId' => $flight_id,
        'seat' => $selectedSeat,
        'from' => $from,
        'to' => $to,
        'date' => $date,
        'travelers' => $travelers,
        'class' => $cabin_class,
        'success' => 'true'
    ]);
    
    // We'll use JavaScript to redirect after a "processing" delay
    $redirect = true;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment - Tickfly</title>
    <meta name="description" content="Complete your flight booking payment with Tickfly">
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
        .payment-option {
            transition: all 0.3s ease;
        }
        .payment-option:hover {
            border-color: #60a5fa;
        }
        .payment-option.selected {
            border-color: #1e3a8a;
            background-color: #eff6ff;
        }
        .overlay {
            background-color: rgba(0, 0, 0, 0.5);
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 50;
        }
        .loading-spinner {
            border: 4px solid rgba(255, 255, 255, 0.3);
            border-radius: 50%;
            border-top: 4px solid #ffffff;
            width: 40px;
            height: 40px;
            animation: spin 1s linear infinite;
        }
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
    </style>
    
    <!-- JavaScript Functions -->
    <script>
        // Force modal to be hidden as soon as page loads
        document.addEventListener('DOMContentLoaded', function() {
            var modal = document.getElementById('payment-confirmation-modal');
            if (modal) {
                modal.style.display = 'none';
                modal.classList.add('hidden');
            }
        });

        // Function to preview uploaded image
        function previewImage(input) {
            if (input.files && input.files[0]) {
                var reader = new FileReader();
                
                reader.onload = function(e) {
                    document.getElementById('image-preview').src = e.target.result;
                    document.getElementById('preview-container').classList.remove('hidden');
                    document.getElementById('upload-prompt').classList.add('hidden');
                }
                
                reader.readAsDataURL(input.files[0]);
            }
        }
        
        // Function to select payment method
        function selectPaymentMethod(element) {
            // Remove selected class from all options
            const options = document.querySelectorAll('.payment-option');
            options.forEach(opt => {
                opt.classList.remove('selected');
            });
            
            // Add selected class to clicked option
            element.classList.add('selected');
            
            // Update hidden input value
            const paymentMethod = element.getAttribute('data-payment');
            const paymentMethodInput = document.getElementById('payment-method-input');
            if (paymentMethodInput) {
                paymentMethodInput.value = paymentMethod;
            }
            
            // Payment methods data
            const paymentMethods = {
                'BNI': {
                    'name': 'BNI',
                    'account': '0123456789'
                },
                'BRI': {
                    'name': 'BRI',
                    'account': '9876543210'
                },
                'DANA': {
                    'name': 'DANA',
                    'account': '081234567890'
                },
                'GoPay': {
                    'name': 'GoPay',
                    'account': '089876543210'
                }
            };
            
            // Update account details display
            const bankNameDisplay = document.getElementById('bank-name');
            const accountNumberDisplay = document.getElementById('account-number');
            
            if (bankNameDisplay) {
                bankNameDisplay.textContent = paymentMethods[paymentMethod].name;
            }
            
            if (accountNumberDisplay) {
                accountNumberDisplay.textContent = paymentMethods[paymentMethod].account;
            }
            
            // Update copy button data
            const copyButtons = document.querySelectorAll('.copy-button');
            if (copyButtons.length > 0) {
                copyButtons.forEach(button => {
                    if (button.closest('.flex') && button.closest('.flex').querySelector('#account-number')) {
                        button.setAttribute('data-copy', paymentMethods[paymentMethod].account);
                    }
                });
            }
        }
        
        // Function to show payment modal
        function showPaymentModal() {
            const paymentModal = document.getElementById('payment-confirmation-modal');
            
            // Check if a payment method is selected
            const selectedPayment = document.querySelector('.payment-option.selected');
            if (!selectedPayment) {
                alert('Please select a payment method first');
                return;
            }
            
            // Show the modal
            if (paymentModal) {
                paymentModal.style.display = 'flex';
                paymentModal.classList.remove('hidden');
            }
        }
        
        // Function to hide payment modal
        function hidePaymentModal() {
            const paymentModal = document.getElementById('payment-confirmation-modal');
            if (paymentModal) {
                paymentModal.style.display = 'none';
                paymentModal.classList.add('hidden');
            }
        }
        
        // Function to copy text to clipboard
        function copyToClipboard(text) {
            const tempInput = document.createElement('input');
            tempInput.value = text;
            document.body.appendChild(tempInput);
            tempInput.select();
            document.execCommand('copy');
            document.body.removeChild(tempInput);
            alert('Copied to clipboard: ' + text);
        }
    </script>
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

            <a href="search.php?from=<?php echo urlencode($from); ?>&to=<?php echo urlencode($to); ?>&depart_date=<?php echo urlencode($date); ?>&travelers=<?php echo urlencode($travelers); ?>&cabin_class=<?php echo urlencode($cabin_class); ?>" class="px-4 py-2 bg-blue-800 text-white rounded-md hover:bg-blue-900 transition">
                Back to Search
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
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div class="md:col-span-2">
            <div class="bg-white p-6 rounded-lg shadow border border-gray-200">
                <h2 class="text-xl font-medium mb-6">Payment</h2>
                
                <!-- Payment Method Selection -->
                <div class="mb-6">
                    <h3 class="text-lg font-medium mb-4">Select Payment Method</h3>
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                        <div class="payment-option border rounded-md p-4 text-center cursor-pointer selected" data-payment="BNI" onclick="selectPaymentMethod(this)">
                            <div class="font-medium">BNI</div>
                            <div class="text-sm text-gray-500">Bank Transfer</div>
                        </div>
                        <div class="payment-option border rounded-md p-4 text-center cursor-pointer" data-payment="BRI" onclick="selectPaymentMethod(this)">
                            <div class="font-medium">BRI</div>
                            <div class="text-sm text-gray-500">Bank Transfer</div>
                        </div>
                        <div class="payment-option border rounded-md p-4 text-center cursor-pointer" data-payment="DANA" onclick="selectPaymentMethod(this)">
                            <div class="font-medium">DANA</div>
                            <div class="text-sm text-gray-500">E-Wallet</div>
                        </div>
                        <div class="payment-option border rounded-md p-4 text-center cursor-pointer" data-payment="GoPay" onclick="selectPaymentMethod(this)">
                            <div class="font-medium">GoPay</div>
                            <div class="text-sm text-gray-500">E-Wallet</div>
                        </div>
                    </div>
                </div>
                
                <!-- Payment Instructions -->
                <div class="mb-6">
                    <h3 class="text-lg font-medium mb-4">Payment Instructions</h3>
                    <div class="bg-blue-50 p-4 rounded-md">
                        <div class="mb-4">
                            <div class="font-medium mb-2">Account Details:</div>
                            <div class="grid grid-cols-3 gap-4">
                                <div>
                                    <div class="text-sm text-gray-500">Bank Name</div>
                                    <div id="bank-name"><?php echo $paymentMethods[$selectedPaymentMethod]['name']; ?></div>
                                </div>
                                <div>
                                    <div class="text-sm text-gray-500">Account Name</div>
                                    <div>PT Tickfly Indonesia</div>
                                </div>
                                <div>
                                    <div class="text-sm text-gray-500">Account Number</div>
                                    <div class="flex items-center">
                                        <span id="account-number"><?php echo $paymentMethods[$selectedPaymentMethod]['account']; ?></span>
                                        <button class="ml-2 text-blue-600 hover:text-blue-800 copy-button" 
                                               onclick="copyToClipboard('<?php echo $paymentMethods[$selectedPaymentMethod]['account']; ?>')"
                                               data-copy="<?php echo $paymentMethods[$selectedPaymentMethod]['account']; ?>">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z" />
                                            </svg>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="mb-4">
                            <div class="font-medium mb-2">Amount to Pay:</div>
                            <div class="text-xl font-bold text-blue-800"><?php echo formatPriceIDR($totalPrice); ?></div>
                        </div>
                        
                        <div>
                            <div class="font-medium mb-2">Steps:</div>
                            <ol class="list-decimal list-inside space-y-1">
                                <li>Transfer the exact amount to the account details above</li>
                                <li>Keep your payment receipt</li>
                                <li>Click the "Confirm Payment" button below</li>
                                <li>Upload your payment receipt and provide your payment details</li>
                                <li>Wait for our team to verify your payment</li>
                            </ol>
                        </div>
                    </div>
                </div>
                
                <!-- Confirm Payment Button -->
                <div class="text-center">
                    <button type="button" id="confirm-payment-button" onclick="showPaymentModal()" class="px-6 py-3 bg-blue-800 text-white rounded-md hover:bg-blue-900 transition">
                        Confirm Payment
                    </button>
                </div>
            </div>
        </div>
        
        <!-- Payment Summary Section -->
        <div class="md:col-span-1">
            <div class="bg-white p-6 rounded-lg shadow border border-gray-200 sticky top-24">
                <h2 class="text-lg font-medium mb-4">Payment Detail</h2>

                <div class="space-y-3 mb-6">
                    <div class="flex justify-between">
                        <span class="text-gray-600">Base Fare</span>
                        <span class="font-medium"><?php echo formatPriceIDR($baseFare); ?></span>
                    </div>
                    
                    <div class="flex justify-between">
                        <span class="text-gray-600">Fee & Surcharges</span>
                        <span class="font-medium"><?php echo formatPriceIDR($fees); ?></span>
                    </div>
                    
                    <div class="flex justify-between">
                        <span class="text-gray-600">Booking Fee</span>
                        <span class="font-medium">Rp 0</span>
                    </div>
                    
                    <div class="flex justify-between">
                        <span class="text-gray-600">PPN</span>
                        <span class="font-medium">Rp 0</span>
                    </div>
                </div>

                <div class="border-t border-gray-200 pt-3 space-y-3">
                    <div class="flex justify-between">
                        <span class="text-gray-600">Total Fare</span>
                        <span class="font-medium"><?php echo formatPriceIDR($baseFare + $fees); ?></span>
                    </div>
                    
                    <div class="flex justify-between">
                        <span class="text-gray-600">Add Ons</span>
                        <span class="font-medium"><?php echo formatPriceIDR($totalAddOns); ?></span>
                    </div>
                </div>

                <div class="border-t border-gray-200 pt-3 mt-3">
                    <div class="flex justify-between">
                        <span class="text-gray-700 font-bold">Price You Pay</span>
                        <span class="text-blue-800 font-bold"><?php echo formatPriceIDR($totalPrice); ?></span>
                    </div>
                </div>
                
                <div class="mt-6 flex items-center text-sm text-blue-600 cursor-pointer">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z" />
                    </svg>
                    Need help? Chat with us
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Payment Confirmation Modal - IMPORTANT: This starts hidden and only shows when "Confirm Payment" is clicked -->
<div id="payment-confirmation-modal" class="hidden overlay">
    <div class="bg-white rounded-lg p-6 max-w-md w-full">
        <h3 class="text-lg font-medium mb-4">Payment Confirmation</h3>
        
        <form id="payment-form" method="POST" enctype="multipart/form-data">
            <input type="hidden" name="paymentMethod" id="payment-method-input" value="<?php echo $selectedPaymentMethod; ?>">
            
            <div class="mb-4">
                <p class="text-gray-600 mb-4">Please provide your payment details and upload your payment receipt.</p>
                
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Payment Details</label>
                    <textarea 
                        name="paymentDetails" 
                        placeholder="Please provide: 1. Amount transferred 2. Sender's name 3. Bank/e-wallet used 4. Transfer date and time"
                        class="w-full p-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                        rows="4"
                        required
                    ></textarea>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Upload Payment Receipt</label>
                    <div 
                        id="upload-area"
                        class="border-2 border-dashed border-gray-300 rounded-md p-4 text-center cursor-pointer hover:border-blue-500"
                        onclick="document.getElementById('payment-proof').click();"
                    >
                        <div id="preview-container" class="hidden mb-2">
                            <img id="image-preview" src="" alt="Payment Proof" class="max-h-40 mx-auto">
                        </div>
                        
                        <div id="upload-prompt">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10 mx-auto text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
                            </svg>
                            <p class="mt-2 text-sm text-gray-500">Click to upload or drag and drop</p>
                            <p class="text-xs text-gray-500">PNG, JPG, JPEG up to 5MB</p>
                        </div>
                        
                        <input 
                            type="file" 
                            id="payment-proof" 
                            name="paymentProof"
                            accept="image/*"
                            class="hidden"
                            required
                            onchange="previewImage(this);"
                        >
                    </div>
                </div>
            </div>
            
            <div class="flex justify-end space-x-3">
                <button 
                    type="button"
                    id="cancel-button"
                    onclick="hidePaymentModal()"
                    class="px-4 py-2 border border-gray-300 rounded-md hover:bg-gray-50 transition"
                >
                    Cancel
                </button>
                <button 
                    type="submit"
                    name="submitPayment"
                    class="px-4 py-2 bg-blue-800 text-white rounded-md hover:bg-blue-900 transition"
                >
                    Submit
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Loading Overlay -->
<?php if (isset($redirect) && $redirect): ?>
<div id="loading-overlay" class="overlay">
    <div class="text-center text-white">
        <div class="loading-spinner mx-auto mb-4"></div>
        <p>Processing your payment...</p>
    </div>
</div>
<?php endif; ?>

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
    // Form submission with loading overlay
    const paymentForm = document.getElementById('payment-form');
    const paymentProofInput = document.getElementById('payment-proof');
    
    if (paymentForm) {
        paymentForm.addEventListener('submit', function(e) {
            // Check if required fields are filled
            if (paymentProofInput && !paymentProofInput.files.length) {
                alert('Please upload your payment receipt');
                e.preventDefault();
                return;
            }
            
            // Show loading overlay
            const loadingOverlay = document.createElement('div');
            loadingOverlay.id = 'loading-overlay';
            loadingOverlay.className = 'overlay';
            loadingOverlay.innerHTML = `
                <div class="text-center text-white">
                    <div class="loading-spinner mx-auto mb-4"></div>
                    <p>Processing your payment...</p>
                </div>
            `;
            document.body.appendChild(loadingOverlay);
            
            // Redirect with all necessary parameters
            setTimeout(function() {
                window.location.href = 'booking-confirmation.php?flightId=<?php echo $flight_id; ?>&seat=<?php echo $selectedSeat; ?>&from=<?php echo urlencode($from); ?>&to=<?php echo urlencode($to); ?>&date=<?php echo urlencode($date); ?>&travelers=<?php echo urlencode($travelers); ?>&class=<?php echo urlencode($cabin_class); ?>&firstName=<?php echo urlencode($firstName); ?>&lastName=<?php echo urlencode($lastName); ?>&email=<?php echo urlencode($email); ?>&phone=<?php echo urlencode($phone); ?>&title=<?php echo urlencode($title); ?>&paymentMethod=' + document.getElementById('payment-method-input').value + '&meals=<?php echo $meals ? "1" : "0"; ?>&baggage=<?php echo $baggage ? "1" : "0"; ?>&success=true';
            }, 10000);
            
            e.preventDefault(); // Prevent actual form submission for this demo
        });
    }
    
    // Handle the case where we have a redirection after form submission
    <?php if (isset($redirect) && $redirect): ?>
    setTimeout(function() {
        window.location.href = 'booking-confirmation.php?flightId=<?php echo $flight_id; ?>&seat=<?php echo $selectedSeat; ?>&from=<?php echo urlencode($from); ?>&to=<?php echo urlencode($to); ?>&date=<?php echo urlencode($date); ?>&travelers=<?php echo urlencode($travelers); ?>&class=<?php echo urlencode($cabin_class); ?>&firstName=<?php echo urlencode($firstName); ?>&lastName=<?php echo urlencode($lastName); ?>&email=<?php echo urlencode($email); ?>&phone=<?php echo urlencode($phone); ?>&title=<?php echo urlencode($title); ?>&paymentMethod=<?php echo $selectedPaymentMethod; ?>&meals=<?php echo $meals ? "1" : "0"; ?>&baggage=<?php echo $baggage ? "1" : "0"; ?>&success=true';
    }, 10000);
    <?php endif; ?>
});
</script>

</body>
</html>