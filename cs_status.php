<?php
// Initialize session
session_start();

// Check if user is logged in and has the 'cs' role
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true || $_SESSION['role'] !== 'cs') {
    // Redirect to login page if not logged in or not a CS
    header("location: login.php");
    exit;
}

// User information
$username = $_SESSION['username'] ?? '';
$name = $username; // Use username as name if no name available
$email = "cs@tickfly.com"; // Default email
$cs_id = "NY-125";
$location = "NYC, New York, USA";
$mobile = "+1 675 346 23 10";

// CS Stats (hardcoded for this example)
$stats = [
    'totalRefundRequests' => 76,
    'ticketsRefundedThisMonth' => 1893,
    'notifications' => 18,
];

// Initialize variables for search
$searchId = '';
$searchTransaction = '';
$error = '';
$customerData = null;
$searchSubmitted = false;

// Handle search form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $searchId = trim($_POST['userId'] ?? '');
    $searchTransaction = trim($_POST['transactionId'] ?? '');
    $searchSubmitted = true;

    if (empty($searchId) && empty($searchTransaction)) {
        $error = "Silakan masukkan ID Pengguna atau Nomor Transaksi";
        $searchSubmitted = false;
    } else {
        // Simulate database search based on React component logic
        // In a real application, this would query an actual database
        
        // Search by booking ID
        if (!empty($searchId)) {
            // For demo purposes, let's simulate finding data for BK123456
            if ($searchId === "BK123456") {
                $customerData = [
                    'name' => "Akmal Fadhurohman",
                    'id' => "BK123456",
                    'contact' => "akmal@gmail.com | 1234567890",
                    'flight' => [
                        'departure' => [
                            'date' => "2021-06-15",
                            'class' => "Economy",
                            'from' => [
                                'code' => "JKT",
                                'name' => "Jakarta"
                            ],
                            'to' => [
                                'code' => "DPS",
                                'name' => "Bali"
                            ],
                            'airline' => "Garuda Indonesia",
                            'departureTime' => "08:00",
                            'arrivalTime' => "10:30",
                            'duration' => "2h 30m",
                            'transit' => "Direct",
                        ]
                    ],
                    'status' => "Menunggu Keberangkatan",
                    'isRefundable' => true,
                ];
            } else {
                $error = "Data tidak ditemukan";
            }
        }
        // Search by transaction ID
        else if (!empty($searchTransaction)) {
            // For demo purposes, let's simulate finding data for PY123456
            if ($searchTransaction === "PY123456") {
                $customerData = [
                    'name' => "Akmal Fadhurohman",
                    'id' => "BK123456",
                    'contact' => "akmal@gmail.com | 1234567890",
                    'flight' => [
                        'departure' => [
                            'date' => "2021-06-15",
                            'class' => "Economy",
                            'from' => [
                                'code' => "JKT",
                                'name' => "Jakarta"
                            ],
                            'to' => [
                                'code' => "DPS",
                                'name' => "Bali"
                            ],
                            'airline' => "Garuda Indonesia",
                            'departureTime' => "08:00",
                            'arrivalTime' => "10:30",
                            'duration' => "2h 30m",
                            'transit' => "Direct",
                        ]
                    ],
                    'status' => "Menunggu Keberangkatan",
                    'isRefundable' => true,
                ];
            } else {
                $error = "Data transaksi tidak ditemukan";
            }
        }
    }
}

// Logout functionality
if (isset($_GET['logout'])) {
    // Destroy session and redirect to login page
    session_destroy();
    header("location: login.php");
    exit;
}

// Helper function to calculate flight duration (similar to React component)
function calculateDuration($departureTime, $arrivalTime) {
    $depParts = explode(":", $departureTime);
    $arrParts = explode(":", $arrivalTime);
    
    $depHours = (int)$depParts[0];
    $depMinutes = (int)$depParts[1];
    $arrHours = (int)$arrParts[0];
    $arrMinutes = (int)$arrParts[1];
    
    $hoursDiff = $arrHours - $depHours;
    $minutesDiff = $arrMinutes - $depMinutes;
    
    if ($minutesDiff < 0) {
        $minutesDiff += 60;
        $hoursDiff -= 1;
    }
    
    if ($hoursDiff < 0) {
        $hoursDiff += 24;
    }
    
    return $hoursDiff . "h " . $minutesDiff . "m";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Status Customer - Tickfly</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <!-- Lucide Icons alternative for PHP -->
    <script src="https://unpkg.com/lucide@latest"></script>
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
        .sidebar-link.active {
            background-color: #ebf5ff;
            color: #2563eb;
        }
        .sidebar-link:hover:not(.active) {
            background-color: #f3f4f6;
        }
        .mobile-menu-overlay {
            background-color: rgba(0, 0, 0, 0.5);
            transition: opacity 300ms ease-in-out;
        }
        .sidebar {
            transition: transform 300ms ease-in-out;
        }
    </style>
</head>
<body class="font-sans min-h-screen bg-gray-100">
    <div class="flex h-screen bg-gray-100">
        <!-- Mobile sidebar backdrop (hidden by default) -->
        <div id="mobile-backdrop" class="fixed inset-0 z-20 bg-black bg-opacity-50 lg:hidden hidden" onclick="toggleSidebar()"></div>

        <!-- Sidebar -->
        <div id="sidebar" class="fixed inset-y-0 left-0 z-30 w-48 bg-white shadow-md transform -translate-x-full lg:translate-x-0 transition-transform duration-300 ease-in-out lg:static lg:inset-auto lg:z-auto">
            <div class="flex items-center justify-between h-16 px-4 border-b border-gray-200">
                <a href="index.php" class="flex items-center">
                    <span class="text-xl font-bold">
                        <span class="text-black">Tick</span>
                        <span class="text-blue-500">fly</span>
                        <span class="text-sm ml-1 text-gray-500">Support</span>
                    </span>
                </a>
                <button class="lg:hidden text-gray-500" onclick="toggleSidebar()">
                    <i data-lucide="x" class="w-5 h-5"></i>
                </button>
            </div>

            <nav class="mt-6 px-2">
                <ul class="space-y-1">
                    <li>
                        <a href="cs_dashboard.php" class="sidebar-link flex items-center px-4 py-3 rounded-md transition-colors text-gray-700">
                            <i data-lucide="home" class="mr-3 w-5 h-5"></i>
                            <span>Home</span>
                        </a>
                    </li>
                    <li>
                        <a href="cs_chats.php" class="sidebar-link flex items-center px-4 py-3 rounded-md transition-colors text-gray-700">
                            <i data-lucide="message-square" class="mr-3 w-5 h-5"></i>
                            <span>Chat User</span>
                        </a>
                    </li>
                    <li>
                        <a href="cs_status.php" class="sidebar-link active flex items-center px-4 py-3 rounded-md transition-colors">
                            <i data-lucide="user-check" class="mr-3 w-5 h-5"></i>
                            <span>Status Customer</span>
                        </a>
                    </li>
                    <li>
                        <a href="cs_refunds.php" class="sidebar-link flex items-center px-4 py-3 rounded-md transition-colors text-gray-700">
                            <i data-lucide="refresh-cw" class="mr-3 w-5 h-5"></i>
                            <span>Refund Ticket</span>
                        </a>
                    </li>
                    <li>
                        <a href="cs_reschedules.php" class="sidebar-link flex items-center px-4 py-3 rounded-md transition-colors text-gray-700">
                            <i data-lucide="refresh-cw" class="mr-3 w-5 h-5"></i>
                            <span>Reschedule</span>
                        </a>
                    </li>
                    <li>
                        <a href="cs_payments.php" class="sidebar-link flex items-center px-4 py-3 rounded-md transition-colors text-gray-700">
                            <i data-lucide="dollar-sign" class="mr-3 w-5 h-5"></i>
                            <span>Payment Verification</span>
                        </a>
                    </li>
                </ul>
            </nav>

            <div class="absolute bottom-0 w-full p-4 border-t border-gray-200">
                <a href="cs_dashboard.php?logout=1" class="flex items-center w-full px-4 py-3 text-gray-700 rounded-md hover:bg-gray-100 transition-colors">
                    <i data-lucide="log-out" class="mr-3 w-5 h-5"></i>
                    <span>Logout</span>
                </a>
            </div>
        </div>

        <!-- Main content -->
        <div class="flex-1 flex flex-col overflow-hidden">
            <header class="bg-white shadow-sm z-10">
                <div class="flex items-center justify-between h-16 px-6">
                    <button class="lg:hidden text-gray-600" onclick="toggleSidebar()">
                        <i data-lucide="menu" class="w-5 h-5"></i>
                    </button>

                    <div class="flex items-center">
                        <div class="relative">
                            <div class="flex items-center space-x-2">
                                <div class="w-8 h-8 rounded-full bg-gray-200 flex items-center justify-center text-gray-700">
                                    <?php echo strtoupper(substr($username, 0, 1)); ?>
                                </div>
                                <div>
                                    <p class="text-sm font-medium"><?php echo $username; ?></p>
                                    <p class="text-xs text-gray-500"><?php echo $email; ?></p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </header>

            <main class="flex-1 overflow-y-auto bg-gray-100 p-6">
                <div class="max-w-4xl mx-auto">
                    <?php if (!$searchSubmitted || !$customerData): ?>
                        <div class="bg-white rounded-lg shadow-md p-6">
                            <h2 class="text-xl font-bold mb-6">Cari Pengguna</h2>

                            <form method="POST" action="cs_status.php" class="space-y-6">
                                <div>
                                    <label for="userId" class="block text-sm font-medium text-gray-700 mb-1">
                                        ID Pengguna
                                    </label>
                                    <input
                                        type="text"
                                        id="userId"
                                        name="userId"
                                        value="<?php echo htmlspecialchars($searchId); ?>"
                                        placeholder="BK123456"
                                        class="w-full p-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                                    />
                                </div>

                                <div>
                                    <label for="transactionId" class="block text-sm font-medium text-gray-700 mb-1">
                                        Nomor Transaksi
                                    </label>
                                    <input
                                        type="text"
                                        id="transactionId"
                                        name="transactionId"
                                        value="<?php echo htmlspecialchars($searchTransaction); ?>"
                                        placeholder="PY123456"
                                        class="w-full p-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                                    />
                                </div>

                                <?php if (!empty($error)): ?>
                                    <div class="text-red-500 text-sm"><?php echo $error; ?></div>
                                <?php endif; ?>

                                <button
                                    type="submit"
                                    class="w-full py-2 px-4 bg-green-500 text-white font-semibold rounded-md hover:bg-green-600 transition"
                                >
                                    Cari
                                </button>
                            </form>
                        </div>
                    <?php else: ?>
                        <div>
                            <button
                                onclick="window.location.href='cs_status.php'"
                                class="flex items-center text-blue-600 mb-4 hover:underline"
                            >
                                <i data-lucide="arrow-left" class="mr-1 w-4 h-4"></i>
                                Kembali
                            </button>

                            <h1 class="text-2xl font-bold mb-6 text-center">STATUS CUSTOMER</h1>

                            <div class="bg-white rounded-lg shadow-md p-6 mb-6">
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div>
                                        <p class="text-gray-600">Nama</p>
                                        <p class="font-medium">: <?php echo htmlspecialchars($customerData['name']); ?></p>
                                    </div>

                                    <div class="md:text-right">
                                        <p class="text-gray-600">Status Penerbangan</p>
                                        <span class="inline-block px-4 py-2 bg-green-100 text-green-800 rounded-md">
                                            <?php echo htmlspecialchars($customerData['status']); ?>
                                        </span>
                                    </div>

                                    <div>
                                        <p class="text-gray-600">ID Booking</p>
                                        <p class="font-medium">: <?php echo htmlspecialchars($customerData['id']); ?></p>
                                    </div>

                                    <div>
                                        <p class="text-gray-600">Kontak</p>
                                        <p class="font-medium">: <?php echo htmlspecialchars($customerData['contact']); ?></p>
                                    </div>
                                </div>
                            </div>

                            <div class="bg-white rounded-lg shadow-md p-6 mb-6">
                                <div class="flex items-center mb-2">
                                    <svg
                                        width="20"
                                        height="20"
                                        viewBox="0 0 24 24"
                                        fill="none"
                                        xmlns="http://www.w3.org/2000/svg"
                                        class="mr-2"
                                    >
                                        <path
                                            d="M22 16.5H2C1.72386 16.5 1.5 16.7239 1.5 17V18C1.5 18.2761 1.72386 18.5 2 18.5H22C22.2761 18.5 22.5 18.2761 22.5 18V17C22.5 16.7239 22.2761 16.5 22 16.5Z"
                                            stroke="black"
                                            stroke-width="1.5"
                                        />
                                        <path d="M20.5 9L13.5 4.5" stroke="black" stroke-width="1.5" stroke-linecap="round" />
                                        <path d="M10.5 7.5L3.5 12" stroke="black" stroke-width="1.5" stroke-linecap="round" />
                                        <path d="M11.5 13L7 10.5" stroke="black" stroke-width="1.5" stroke-linecap="round" />
                                    </svg>
                                    <span class="font-medium">Departure Flight</span>
                                </div>

                                <div class="flex flex-col md:flex-row justify-between items-start md:items-center border-b pb-4 mb-4">
                                    <div>
                                        <p class="font-medium text-lg"><?php echo htmlspecialchars($customerData['flight']['departure']['date']); ?></p>
                                        <p class="text-gray-600"><?php echo htmlspecialchars($customerData['flight']['departure']['class']); ?></p>
                                    </div>

                                    <div class="flex items-center mt-2 md:mt-0">
                                        <img
                                            src="placeholder.svg"
                                            alt="<?php echo htmlspecialchars($customerData['flight']['departure']['airline']); ?>"
                                            class="w-8 h-8 mr-2"
                                        />
                                        <span><?php echo htmlspecialchars($customerData['flight']['departure']['airline']); ?></span>
                                    </div>
                                </div>

                                <div class="flex flex-col md:flex-row justify-between items-start md:items-center">
                                    <div class="text-center">
                                        <p class="font-bold"><?php echo htmlspecialchars($customerData['flight']['departure']['departureTime']); ?></p>
                                        <p class="text-sm text-gray-600"><?php echo htmlspecialchars($customerData['flight']['departure']['from']['code']); ?></p>
                                        <p class="text-xs"><?php echo htmlspecialchars($customerData['flight']['departure']['from']['name']); ?></p>
                                    </div>

                                    <div class="my-4 md:my-0 flex flex-col items-center">
                                        <p class="text-xs text-gray-500"><?php echo htmlspecialchars($customerData['flight']['departure']['duration']); ?></p>
                                        <div class="relative w-32 md:w-48 h-0.5 bg-gray-300 my-2">
                                            <div class="absolute top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2 w-2 h-2 bg-gray-500 rounded-full"></div>
                                        </div>
                                        <p class="text-xs text-gray-500"><?php echo htmlspecialchars($customerData['flight']['departure']['transit']); ?></p>
                                    </div>

                                    <div class="text-center">
                                        <p class="font-bold"><?php echo htmlspecialchars($customerData['flight']['departure']['arrivalTime']); ?></p>
                                        <p class="text-sm text-gray-600"><?php echo htmlspecialchars($customerData['flight']['departure']['to']['code']); ?></p>
                                        <p class="text-xs"><?php echo htmlspecialchars($customerData['flight']['departure']['to']['name']); ?></p>
                                    </div>
                                </div>
                            </div>

                            <div class="bg-white rounded-lg shadow-md p-6">
                                <div class="flex justify-between items-center">
                                    <div>
                                        <p class="text-sm text-gray-600"><?php echo htmlspecialchars($customerData['flight']['departure']['from']['name']); ?></p>
                                        <p class="text-xs text-gray-500"><?php echo htmlspecialchars($customerData['flight']['departure']['from']['code']); ?></p>
                                    </div>

                                    <div class="relative w-32 md:w-48 h-0.5 bg-gray-300">
                                        <div class="absolute top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2 w-2 h-2 bg-gray-500 rounded-full"></div>
                                    </div>

                                    <div>
                                        <p class="text-sm text-gray-600"><?php echo htmlspecialchars($customerData['flight']['departure']['to']['name']); ?></p>
                                        <p class="text-xs text-gray-500"><?php echo htmlspecialchars($customerData['flight']['departure']['to']['code']); ?></p>
                                    </div>
                                </div>

                                <div
                                    class="mt-4 text-center py-2 rounded-md <?php echo $customerData['isRefundable'] ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'; ?>"
                                >
                                    <?php echo $customerData['isRefundable'] ? "REFUNDABLE" : "UNREFUNDABLE"; ?>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </main>
        </div>
    </div>

    <script>
        // Initialize Lucide icons
        lucide.createIcons();
        
        // Toggle sidebar function for mobile view
        function toggleSidebar() {
            const sidebar = document.getElementById('sidebar');
            const backdrop = document.getElementById('mobile-backdrop');
            
            if (sidebar.classList.contains('-translate-x-full')) {
                sidebar.classList.remove('-translate-x-full');
                backdrop.classList.remove('hidden');
            } else {
                sidebar.classList.add('-translate-x-full');
                backdrop.classList.add('hidden');
            }
        }
    </script>
</body>
</html>