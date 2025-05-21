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

// Mock data for reschedule requests
$reschedules = [
    [
        'id' => 'RS123456',
        'bookingId' => 'BK123456',
        'user' => [
            'name' => 'Akmal Fadhurohman',
            'email' => 'akmal@gmail.com'
        ],
        'flight' => [
            'route' => 'Jakarta → Bali',
            'date' => '2021-06-15',
            'airline' => 'Garuda Indonesia'
        ],
        'newFlightDate' => '2021-07-01',
        'fee' => 500000,
        'status' => 'pending',
        'reason' => 'Business meeting rescheduled to a later date.'
    ],
    [
        'id' => 'RS789012',
        'bookingId' => 'BK789012',
        'user' => [
            'name' => 'John Doe',
            'email' => 'john@example.com'
        ],
        'flight' => [
            'route' => 'Jakarta → Surabaya',
            'date' => '2021-06-14',
            'airline' => 'Lion Air'
        ],
        'newFlightDate' => '2021-06-20',
        'fee' => 300000,
        'status' => 'approved',
        'reason' => 'Family event date changed.'
    ],
    [
        'id' => 'RS345678',
        'bookingId' => 'BK345678',
        'user' => [
            'name' => 'Jane Smith',
            'email' => 'jane@example.com'
        ],
        'flight' => [
            'route' => 'Jakarta → Yogyakarta',
            'date' => '2021-06-13',
            'airline' => 'Batik Air'
        ],
        'newFlightDate' => '2021-06-18',
        'fee' => 400000,
        'status' => 'rejected',
        'reason' => 'Need to travel on a different date due to health issues.'
    ]
];

// Handle approve/reject actions
if (isset($_POST['action']) && isset($_POST['reschedule_id'])) {
    $action = $_POST['action'];
    $reschedule_id = $_POST['reschedule_id'];
    
    // Find the reschedule request and update its status
    foreach ($reschedules as $key => $reschedule) {
        if ($reschedule['id'] === $reschedule_id) {
            $reschedules[$key]['status'] = $action === 'approve' ? 'approved' : 'rejected';
            break;
        }
    }
}

// Search and filter functionality
$searchTerm = $_GET['search'] ?? '';
$statusFilter = $_GET['status'] ?? 'all';

// Filter the reschedules based on search term and status
$filteredReschedules = [];
foreach ($reschedules as $reschedule) {
    // Check if it matches the status filter
    if ($statusFilter !== 'all' && $reschedule['status'] !== $statusFilter) {
        continue;
    }
    
    // Check if it matches the search term
    if ($searchTerm !== '' &&
        stripos($reschedule['user']['name'], $searchTerm) === false &&
        stripos($reschedule['user']['email'], $searchTerm) === false &&
        stripos($reschedule['bookingId'], $searchTerm) === false) {
        continue;
    }
    
    $filteredReschedules[] = $reschedule;
}

// Format price to IDR
function formatPrice($price) {
    return 'Rp ' . number_format($price, 0, ',', '.');
}

// Logout functionality
if (isset($_GET['logout'])) {
    // Destroy session and redirect to login page
    session_destroy();
    header("location: login.php");
    exit;
}

// Get the reschedule detail if requested
$selectedReschedule = null;
if (isset($_GET['view']) && $_GET['view'] !== '') {
    foreach ($reschedules as $reschedule) {
        if ($reschedule['id'] === $_GET['view']) {
            $selectedReschedule = $reschedule;
            break;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reschedule Requests - Tickfly</title>
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
        .status-badge {
            padding: 2px 8px;
            border-radius: 9999px;
            font-size: 0.75rem;
            font-weight: 500;
            text-transform: uppercase;
        }
        .badge-pending {
            background-color: #fef3c7;
            color: #92400e;
        }
        .badge-approved {
            background-color: #d1fae5;
            color: #065f46;
        }
        .badge-rejected {
            background-color: #fee2e2;
            color: #b91c1c;
        }
        .modal {
            display: none;
            position: fixed;
            z-index: 50;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            overflow: auto;
            background-color: rgba(0, 0, 0, 0.5);
        }
        .modal-content {
            background-color: #fefefe;
            margin: 10% auto;
            padding: 20px;
            border-radius: 0.5rem;
            max-width: 36rem;
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
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
                        <a href="cs_refunds.php" class="sidebar-link flex items-center px-4 py-3 rounded-md transition-colors text-gray-700">
                            <i data-lucide="refresh-cw" class="mr-3 w-5 h-5"></i>
                            <span>Refund Ticket</span>
                        </a>
                    </li>
                    <li>
                        <a href="cs_reschedules.php" class="sidebar-link active flex items-center px-4 py-3 rounded-md transition-colors">
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
                <div class="max-w-6xl mx-auto">
                    <h1 class="text-2xl font-bold mb-6">Reschedule Requests</h1>

                    <div class="bg-white rounded-lg shadow-md p-6 border border-gray-200">
                        <div class="flex flex-col md:flex-row justify-between items-center mb-6 gap-4">
                            <h2 class="text-lg font-medium">All Reschedule Requests</h2>

                            <div class="flex flex-col md:flex-row gap-3">
                                <div class="relative">
                                    <form action="" method="GET" class="flex items-center">
                                        <div class="relative">
                                            <i data-lucide="search" class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 w-4 h-4"></i>
                                            <input
                                                type="text"
                                                name="search"
                                                placeholder="Search reschedules..."
                                                value="<?php echo htmlspecialchars($searchTerm); ?>"
                                                class="pl-10 pr-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                                            >
                                        </div>

                                        <div class="flex items-center ml-3">
                                            <i data-lucide="filter" class="mr-2 text-gray-500 w-4 h-4"></i>
                                            <select
                                                name="status"
                                                class="p-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                                                onchange="this.form.submit()"
                                            >
                                                <option value="all" <?php echo $statusFilter === 'all' ? 'selected' : ''; ?>>All Status</option>
                                                <option value="pending" <?php echo $statusFilter === 'pending' ? 'selected' : ''; ?>>Pending</option>
                                                <option value="approved" <?php echo $statusFilter === 'approved' ? 'selected' : ''; ?>>Approved</option>
                                                <option value="rejected" <?php echo $statusFilter === 'rejected' ? 'selected' : ''; ?>>Rejected</option>
                                            </select>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>

                        <?php if (empty($filteredReschedules)): ?>
                            <div class="text-center py-8 text-gray-500">
                                <?php if ($searchTerm || $statusFilter !== 'all'): ?>
                                    No reschedule requests found matching your criteria
                                <?php else: ?>
                                    No reschedule requests found
                                <?php endif; ?>
                            </div>
                        <?php else: ?>
                            <div class="overflow-x-auto">
                                <table class="w-full">
                                    <thead>
                                        <tr class="text-left border-b border-gray-200">
                                            <th class="pb-3 font-medium">Booking ID</th>
                                            <th class="pb-3 font-medium">User</th>
                                            <th class="pb-3 font-medium">Flight</th>
                                            <th class="pb-3 font-medium">Current Date</th>
                                            <th class="pb-3 font-medium">New Date</th>
                                            <th class="pb-3 font-medium">Fee</th>
                                            <th class="pb-3 font-medium">Status</th>
                                            <th class="pb-3 font-medium">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($filteredReschedules as $reschedule): ?>
                                            <tr class="border-b border-gray-100">
                                                <td class="py-3 font-medium"><?php echo htmlspecialchars($reschedule['bookingId']); ?></td>
                                                <td class="py-3">
                                                    <div>
                                                        <p><?php echo htmlspecialchars($reschedule['user']['name'] ?? 'Unknown'); ?></p>
                                                        <p class="text-xs text-gray-500"><?php echo htmlspecialchars($reschedule['user']['email'] ?? 'No email'); ?></p>
                                                    </div>
                                                </td>
                                                <td class="py-3"><?php echo htmlspecialchars($reschedule['flight']['route'] ?? 'Unknown'); ?></td>
                                                <td class="py-3"><?php echo htmlspecialchars($reschedule['flight']['date'] ?? 'Unknown'); ?></td>
                                                <td class="py-3"><?php echo htmlspecialchars($reschedule['newFlightDate']); ?></td>
                                                <td class="py-3 font-medium"><?php echo formatPrice($reschedule['fee']); ?></td>
                                                <td class="py-3">
                                                    <span class="status-badge badge-<?php echo $reschedule['status']; ?>">
                                                        <?php echo strtoupper($reschedule['status']); ?>
                                                    </span>
                                                </td>
                                                <td class="py-3">
                                                    <div class="flex gap-2">
                                                        <a 
                                                            href="cs_reschedules.php?view=<?php echo urlencode($reschedule['id']); ?>"
                                                            class="p-1 text-blue-600 hover:text-blue-800"
                                                            title="View details"
                                                        >
                                                            <i data-lucide="eye" class="w-4 h-4"></i>
                                                        </a>
                                                        
                                                        <?php if ($reschedule['status'] === 'pending'): ?>
                                                            <form method="POST" class="inline-block">
                                                                <input type="hidden" name="reschedule_id" value="<?php echo htmlspecialchars($reschedule['id']); ?>">
                                                                <input type="hidden" name="action" value="approve">
                                                                <button 
                                                                    type="submit" 
                                                                    class="p-1 text-green-600 hover:text-green-800"
                                                                    title="Approve reschedule"
                                                                >
                                                                    <i data-lucide="check" class="w-4 h-4"></i>
                                                                </button>
                                                            </form>
                                                            
                                                            <form method="POST" class="inline-block">
                                                                <input type="hidden" name="reschedule_id" value="<?php echo htmlspecialchars($reschedule['id']); ?>">
                                                                <input type="hidden" name="action" value="reject">
                                                                <button 
                                                                    type="submit" 
                                                                    class="p-1 text-red-600 hover:text-red-800"
                                                                    title="Reject reschedule"
                                                                >
                                                                    <i data-lucide="x" class="w-4 h-4"></i>
                                                                </button>
                                                            </form>
                                                        <?php endif; ?>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <!-- Reschedule Detail Modal -->
    <?php if ($selectedReschedule): ?>
    <div id="rescheduleModal" class="modal" style="display: block;">
        <div class="modal-content">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-medium">Reschedule Request Details</h3>
                <a href="cs_reschedules.php" class="text-gray-500 hover:text-gray-700 text-2xl">&times;</a>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <div class="space-y-4">
                        <div>
                            <p class="text-sm text-gray-500">Booking ID</p>
                            <p class="font-medium"><?php echo htmlspecialchars($selectedReschedule['bookingId']); ?></p>
                        </div>

                        <div>
                            <p class="text-sm text-gray-500">User</p>
                            <p class="font-medium"><?php echo htmlspecialchars($selectedReschedule['user']['name'] ?? 'Unknown'); ?></p>
                            <p class="text-xs text-gray-500"><?php echo htmlspecialchars($selectedReschedule['user']['email'] ?? 'No email'); ?></p>
                        </div>

                        <div>
                            <p class="text-sm text-gray-500">Flight</p>
                            <p class="font-medium"><?php echo htmlspecialchars($selectedReschedule['flight']['airline'] ?? 'Unknown'); ?></p>
                            <p class="text-sm"><?php echo htmlspecialchars($selectedReschedule['flight']['route'] ?? 'Unknown'); ?></p>
                        </div>

                        <div class="flex gap-6">
                            <div>
                                <p class="text-sm text-gray-500">Current Date</p>
                                <p class="font-medium"><?php echo htmlspecialchars($selectedReschedule['flight']['date'] ?? 'Unknown'); ?></p>
                            </div>

                            <div>
                                <p class="text-sm text-gray-500">New Date</p>
                                <div class="flex items-center">
                                    <i data-lucide="calendar" class="mr-1 text-blue-600 w-4 h-4"></i>
                                    <p class="font-medium"><?php echo htmlspecialchars($selectedReschedule['newFlightDate']); ?></p>
                                </div>
                            </div>
                        </div>

                        <div>
                            <p class="text-sm text-gray-500">Reschedule Fee</p>
                            <p class="font-medium"><?php echo formatPrice($selectedReschedule['fee']); ?></p>
                        </div>

                        <div>
                            <p class="text-sm text-gray-500">Status</p>
                            <span class="status-badge badge-<?php echo $selectedReschedule['status']; ?>">
                                <?php echo strtoupper($selectedReschedule['status']); ?>
                            </span>
                        </div>
                    </div>
                </div>

                <div>
                    <div>
                        <p class="text-sm text-gray-500 mb-2">Reason for Rescheduling</p>
                        <div class="p-3 bg-gray-50 rounded-md border border-gray-200 min-h-[100px]">
                            <?php echo htmlspecialchars($selectedReschedule['reason'] ?? 'No reason provided'); ?>
                        </div>
                    </div>

                    <?php if ($selectedReschedule['status'] === 'pending'): ?>
                        <div class="mt-6 flex gap-3">
                            <form method="POST" action="cs_reschedules.php">
                                <input type="hidden" name="reschedule_id" value="<?php echo htmlspecialchars($selectedReschedule['id']); ?>">
                                <input type="hidden" name="action" value="reject">
                                <button
                                    type="submit"
                                    class="px-4 py-2 border border-red-600 text-red-600 rounded-md hover:bg-red-50 transition"
                                >
                                    Reject Request
                                </button>
                            </form>
                            
                            <form method="POST" action="cs_reschedules.php">
                                <input type="hidden" name="reschedule_id" value="<?php echo htmlspecialchars($selectedReschedule['id']); ?>">
                                <input type="hidden" name="action" value="approve">
                                <button
                                    type="submit"
                                    class="px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700 transition"
                                >
                                    Approve Request
                                </button>
                            </form>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>

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