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

// Mock refund data
$refundsData = [
    [
        "id" => "RF123456",
        "bookingId" => "FLY-7890",
        "customerName" => "Jane Cooper",
        "flightDetails" => "Garuda: CGK → JFK (25 Jun)",
        "refundAmount" => 3500000,
        "status" => "pending",
        "email" => "jane@example.com",
        "phone" => "(205) 555-0100",
    ],
    [
        "id" => "RF789012",
        "bookingId" => "FLY-7891",
        "customerName" => "Floyd Miles",
        "flightDetails" => "Lion Air: CGK → SUB (27 Jun)",
        "refundAmount" => 2800000,
        "status" => "pending",
        "email" => "floyd@yahoo.com",
        "phone" => "(205) 555-0100",
    ],
    [
        "id" => "RF345678",
        "bookingId" => "FLY-7892",
        "customerName" => "Ronald Richards",
        "flightDetails" => "Batik Air: CGK → DPS (28 Jun)",
        "refundAmount" => 3200000,
        "status" => "pending",
        "email" => "ronald@adobe.com",
        "phone" => "(302) 555-0107",
    ],
    [
        "id" => "RF901234",
        "bookingId" => "FLY-7893",
        "customerName" => "Marvin McKinney",
        "flightDetails" => "Citilink: CGK → UPG (29 Jun)",
        "refundAmount" => 2500000,
        "status" => "approved",
        "email" => "marvin@tesla.com",
        "phone" => "(252) 555-0126",
    ],
    [
        "id" => "RF567890",
        "bookingId" => "FLY-7894",
        "customerName" => "Jerome Bell",
        "flightDetails" => "Garuda: CGK → PDG (30 Jun)",
        "refundAmount" => 3100000,
        "status" => "approved",
        "email" => "jerome@google.com",
        "phone" => "(629) 555-0129",
    ],
    [
        "id" => "RF123789",
        "bookingId" => "FLY-7895",
        "customerName" => "Kathryn Murphy",
        "flightDetails" => "Lion Air: CGK → BDO (01 Jul)",
        "refundAmount" => 2700000,
        "status" => "rejected",
        "email" => "kathryn@microsoft.com",
        "phone" => "(406) 555-0120",
    ],
    [
        "id" => "RF456012",
        "bookingId" => "FLY-7896",
        "customerName" => "Jacob Jones",
        "flightDetails" => "Batik Air: CGK → MLG (02 Jul)",
        "refundAmount" => 2900000,
        "status" => "approved",
        "email" => "jacob@yahoo.com",
        "phone" => "(208) 555-0112",
    ],
    [
        "id" => "RF890123",
        "bookingId" => "FLY-7897",
        "customerName" => "Kristin Watson",
        "flightDetails" => "Citilink: CGK → SOC (03 Jul)",
        "refundAmount" => 2600000,
        "status" => "rejected",
        "email" => "kristin@facebook.com",
        "phone" => "(704) 555-0127",
    ],
];

// Mock flight data for the detail view
$flightData = [
    "departure" => [
        "date" => "Mon, 14 Jun 2021",
        "class" => "Economy",
        "from" => [
            "code" => "JKT",
            "name" => "Jakarta",
        ],
        "to" => [
            "code" => "LPG",
            "name" => "Lampung",
        ],
        "airline" => "Sriwijaya Air",
        "departureTime" => "20:05",
        "arrivalTime" => "22:55",
        "duration" => "2h 50m",
        "transit" => "Transit",
    ],
];

// Function to format price to IDR
function formatPrice($price) {
    return "Rp " . number_format($price, 0, ',', '.');
}

// Handle refund approval/rejection if form submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action']) && isset($_POST['refund_id'])) {
        $refundId = $_POST['refund_id'];
        $action = $_POST['action'];
        
        // Update refund status in the array (in a real app, this would update a database)
        foreach ($refundsData as $key => $refund) {
            if ($refund['id'] === $refundId) {
                $refundsData[$key]['status'] = ($action === 'approve') ? 'approved' : 'rejected';
                break;
            }
        }
    }
}

// Get selected refund if ID is provided in URL
$selectedRefund = null;
$viewMode = 'list';
if (isset($_GET['id'])) {
    $refundId = $_GET['id'];
    foreach ($refundsData as $refund) {
        if ($refund['id'] === $refundId) {
            $selectedRefund = $refund;
            $viewMode = 'detail';
            break;
        }
    }
}

// Filter refunds based on search term and status
$searchTerm = $_GET['search'] ?? '';
$statusFilter = $_GET['status'] ?? 'all';

$filteredRefunds = array_filter($refundsData, function($refund) use ($searchTerm, $statusFilter) {
    $matchesSearch = empty($searchTerm) || 
        stripos($refund['customerName'], $searchTerm) !== false ||
        stripos($refund['bookingId'], $searchTerm) !== false ||
        stripos($refund['flightDetails'], $searchTerm) !== false ||
        stripos($refund['email'], $searchTerm) !== false;
    
    $matchesStatus = $statusFilter === 'all' || $refund['status'] === $statusFilter;
    
    return $matchesSearch && $matchesStatus;
});

// Logout functionality
if (isset($_GET['logout'])) {
    // Destroy session and redirect to login page
    session_destroy();
    header("location: login.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Customer Service Refunds - Tickfly</title>
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
                        <a href="cs_status.php" class="sidebar-link flex items-center px-4 py-3 rounded-md transition-colors text-gray-700">
                            <i data-lucide="user-check" class="mr-3 w-5 h-5"></i>
                            <span>Status Customer</span>
                        </a>
                    </li>
                    <li>
                        <a href="cs_refunds.php" class="sidebar-link active flex items-center px-4 py-3 rounded-md transition-colors">
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

            <main class="flex-1 overflow-y-auto">
                <?php if ($viewMode === 'list'): ?>
                <!-- List View -->
                <div class="p-6">
                    <h1 class="text-2xl font-bold mb-6">Customers</h1>

                    <div class="bg-white rounded-lg shadow-md p-6">
                        <div class="flex flex-col md:flex-row justify-between items-center mb-6 gap-4">
                            <div class="relative w-full md:w-auto">
                                <i data-lucide="search" class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 w-5 h-5"></i>
                                <form action="cs_refunds.php" method="get">
                                    <input 
                                        type="text" 
                                        name="search" 
                                        placeholder="Search..." 
                                        value="<?php echo htmlspecialchars($searchTerm); ?>"
                                        class="pl-10 pr-4 py-2 w-full md:w-64 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                                    >
                                </form>
                            </div>

                            <div class="flex items-center w-full md:w-auto">
                                <form action="cs_refunds.php" method="get">
                                    <select 
                                        name="status" 
                                        onchange="this.form.submit()" 
                                        class="p-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 w-full"
                                    >
                                        <option value="all" <?php echo $statusFilter === 'all' ? 'selected' : ''; ?>>All Status</option>
                                        <option value="pending" <?php echo $statusFilter === 'pending' ? 'selected' : ''; ?>>Pending</option>
                                        <option value="approved" <?php echo $statusFilter === 'approved' ? 'selected' : ''; ?>>Approved</option>
                                        <option value="rejected" <?php echo $statusFilter === 'rejected' ? 'selected' : ''; ?>>Rejected</option>
                                    </select>
                                </form>
                            </div>
                        </div>

                        <div class="overflow-x-auto">
                            <table class="w-full">
                                <thead>
                                    <tr class="text-left border-b border-gray-200">
                                        <th class="pb-3 font-medium">Booking ID</th>
                                        <th class="pb-3 font-medium">Customer Name</th>
                                        <th class="pb-3 font-medium">Flight Details</th>
                                        <th class="pb-3 font-medium">Refund Amount</th>
                                        <th class="pb-3 font-medium">Status</th>
                                        <th class="pb-3 font-medium">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($filteredRefunds as $refund): ?>
                                    <tr class="border-b border-gray-100 hover:bg-gray-50 cursor-pointer" onclick="window.location='cs_refunds.php?id=<?php echo $refund['id']; ?>'">
                                        <td class="py-3"><?php echo htmlspecialchars($refund['bookingId']); ?></td>
                                        <td class="py-3"><?php echo htmlspecialchars($refund['customerName']); ?></td>
                                        <td class="py-3"><?php echo htmlspecialchars($refund['flightDetails']); ?></td>
                                        <td class="py-3 font-medium"><?php echo formatPrice($refund['refundAmount']); ?></td>
                                        <td class="py-3">
                                            <span class="inline-block px-2 py-1 rounded-full text-xs <?php 
                                                if ($refund['status'] === 'approved') {
                                                    echo 'bg-green-100 text-green-800';
                                                } elseif ($refund['status'] === 'pending') {
                                                    echo 'bg-yellow-100 text-yellow-800';
                                                } else {
                                                    echo 'bg-red-100 text-red-800';
                                                }
                                            ?>">
                                                <?php 
                                                    if ($refund['status'] === 'approved') {
                                                        echo 'Approved';
                                                    } elseif ($refund['status'] === 'rejected') {
                                                        echo 'Rejected';
                                                    } else {
                                                        echo 'Pending';
                                                    }
                                                ?>
                                            </span>
                                        </td>
                                        <td class="py-3">
                                            <?php if ($refund['status'] === 'pending'): ?>
                                            <div class="flex gap-2" onclick="event.stopPropagation()">
                                                <form method="post" action="cs_refunds.php">
                                                    <input type="hidden" name="refund_id" value="<?php echo $refund['id']; ?>">
                                                    <input type="hidden" name="action" value="approve">
                                                    <button type="submit" class="p-1 text-green-600 hover:text-green-800" title="Approve">
                                                        <i data-lucide="check" class="w-4 h-4"></i>
                                                    </button>
                                                </form>
                                                <form method="post" action="cs_refunds.php">
                                                    <input type="hidden" name="refund_id" value="<?php echo $refund['id']; ?>">
                                                    <input type="hidden" name="action" value="reject">
                                                    <button type="submit" class="p-1 text-red-600 hover:text-red-800" title="Reject">
                                                        <i data-lucide="x" class="w-4 h-4"></i>
                                                    </button>
                                                </form>
                                            </div>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>

                        <div class="flex items-center justify-between mt-6">
                            <p class="text-sm text-gray-500">Showing data 1 to 8 of 256K entries</p>
                            <div class="flex items-center space-x-2">
                                <button class="w-8 h-8 flex items-center justify-center rounded-md bg-blue-600 text-white">1</button>
                                <button class="w-8 h-8 flex items-center justify-center rounded-md hover:bg-gray-100">2</button>
                                <button class="w-8 h-8 flex items-center justify-center rounded-md hover:bg-gray-100">3</button>
                                <button class="w-8 h-8 flex items-center justify-center rounded-md hover:bg-gray-100">4</button>
                                <span>...</span>
                                <button class="w-8 h-8 flex items-center justify-center rounded-md hover:bg-gray-100">40</button>
                            </div>
                        </div>
                    </div>
                </div>
                <?php else: ?>
                <!-- Detail View -->
                <div class="p-6 max-w-4xl mx-auto">
                    <a href="cs_refunds.php" class="flex items-center text-blue-600 mb-4 hover:underline">
                        <i data-lucide="arrow-left" class="w-4 h-4 mr-1"></i>
                        Kembali
                    </a>

                    <h1 class="text-2xl font-bold mb-6 text-center">REFUND</h1>

                    <div class="bg-white rounded-lg shadow-md p-6 mb-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <p class="text-gray-600">Nama</p>
                                <p class="font-medium">: <?php echo htmlspecialchars($selectedRefund['customerName']); ?></p>
                            </div>

                            <div>
                                <p class="text-gray-600">ID Booking</p>
                                <p class="font-medium">: <?php echo htmlspecialchars($selectedRefund['bookingId']); ?></p>
                            </div>

                            <div>
                                <p class="text-gray-600">Kontak</p>
                                <p class="font-medium">
                                    : <?php echo htmlspecialchars($selectedRefund['email']); ?> | <?php echo htmlspecialchars($selectedRefund['phone']); ?>
                                </p>
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
                                strokeWidth="1.5"
                                />
                                <path d="M20.5 9L13.5 4.5" stroke="black" strokeWidth="1.5" strokeLinecap="round" />
                                <path d="M10.5 7.5L3.5 12" stroke="black" strokeWidth="1.5" strokeLinecap="round" />
                                <path d="M11.5 13L7 10.5" stroke="black" strokeWidth="1.5" strokeLinecap="round" />
                            </svg>
                            <span class="font-medium">Departure Flight</span>
                        </div>

                        <div class="flex flex-col md:flex-row justify-between items-start md:items-center border-b pb-4 mb-4">
                            <div>
                                <p class="font-medium text-lg"><?php echo $flightData['departure']['date']; ?></p>
                                <p class="text-gray-600"><?php echo $flightData['departure']['class']; ?></p>
                            </div>

                            <div class="flex items-center mt-2 md:mt-0">
                                <img
                                    src="placeholder.svg"
                                    alt="<?php echo $flightData['departure']['airline']; ?>"
                                    class="w-8 h-8 mr-2"
                                />
                                <span><?php echo $flightData['departure']['airline']; ?></span>
                            </div>
                        </div>

                        <div class="flex flex-col md:flex-row justify-between items-start md:items-center">
                            <div class="text-center">
                                <p class="font-bold"><?php echo $flightData['departure']['departureTime']; ?></p>
                                <p class="text-sm text-gray-600"><?php echo $flightData['departure']['from']['code']; ?></p>
                                <p class="text-xs"><?php echo $flightData['departure']['from']['name']; ?></p>
                            </div>

                            <div class="my-4 md:my-0 flex flex-col items-center">
                                <p class="text-xs text-gray-500"><?php echo $flightData['departure']['duration']; ?></p>
                                <div class="relative w-32 md:w-48 h-0.5 bg-gray-300 my-2">
                                    <div class="absolute top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2 w-2 h-2 bg-gray-500 rounded-full"></div>
                                </div>
                                <p class="text-xs text-gray-500"><?php echo $flightData['departure']['transit']; ?></p>
                            </div>

                            <div class="text-center">
                                <p class="font-bold"><?php echo $flightData['departure']['arrivalTime']; ?></p>
                                <p class="text-sm text-gray-600"><?php echo $flightData['departure']['to']['code']; ?></p>
                                <p class="text-xs"><?php echo $flightData['departure']['to']['name']; ?></p>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white rounded-lg shadow-md p-6">
                        <div class="flex justify-between items-center">
                            <div>
                                <p class="text-sm text-gray-600"><?php echo $flightData['departure']['from']['name']; ?></p>
                                <p class="text-xs text-gray-500"><?php echo $flightData['departure']['from']['code']; ?></p>
                            </div>

                            <div class="relative w-32 md:w-48 h-0.5 bg-gray-300">
                                <div class="absolute top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2 w-2 h-2 bg-gray-500 rounded-full"></div>
                            </div>

                            <div>
                                <p class="text-sm text-gray-600"><?php echo $flightData['departure']['to']['name']; ?></p>
                                <p class="text-xs text-gray-500"><?php echo $flightData['departure']['to']['code']; ?></p>
                            </div>
                        </div>

                        <div class="mt-4 text-center py-2 rounded-md bg-green-100 text-green-800">REFUNDABLE</div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-6">
                        <div class="bg-white rounded-lg shadow-md p-6">
                            <h3 class="font-medium mb-2">Status Refund</h3>
                            <div class="inline-block px-4 py-2 rounded-md <?php 
                                if ($selectedRefund['status'] === 'approved') {
                                    echo 'bg-green-100 text-green-800';
                                } elseif ($selectedRefund['status'] === 'rejected') {
                                    echo 'bg-red-100 text-red-800';
                                } else {
                                    echo 'bg-yellow-100 text-yellow-800';
                                }
                            ?>">
                                <?php 
                                    if ($selectedRefund['status'] === 'approved') {
                                        echo 'Approved';
                                    } elseif ($selectedRefund['status'] === 'rejected') {
                                        echo 'Rejected';
                                    } else {
                                        echo 'Pending';
                                    }
                                ?>
                            </div>
                        </div>

                        <div class="bg-white rounded-lg shadow-md p-6">
                            <h3 class="font-medium mb-2">Refund Amount</h3>
                            <p class="text-xl font-bold"><?php echo formatPrice($selectedRefund['refundAmount']); ?></p>
                        </div>
                    </div>

                    <?php if ($selectedRefund['status'] === 'pending'): ?>
                    <div class="flex justify-end gap-4 mt-6">
                        <form method="post" action="cs_refunds.php">
                            <input type="hidden" name="refund_id" value="<?php echo $selectedRefund['id']; ?>">
                            <input type="hidden" name="action" value="reject">
                            <button type="submit" class="px-6 py-2 border border-red-600 text-red-600 font-medium rounded-md hover:bg-red-50 transition">
                                Reject
                            </button>
                        </form>
                        <form method="post" action="cs_refunds.php">
                            <input type="hidden" name="refund_id" value="<?php echo $selectedRefund['id']; ?>">
                            <input type="hidden" name="action" value="approve">
                            <button type="submit" class="px-6 py-2 bg-green-600 text-white font-medium rounded-md hover:bg-green-700 transition">
                                Approve
                            </button>
                        </form>
                    </div>
                    <?php endif; ?>
                </div>
                <?php endif; ?>
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