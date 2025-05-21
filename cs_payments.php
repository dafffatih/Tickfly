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

// Mock payment data
$payments = [
    [
        'id' => 'PY123456',
        'bookingId' => 'BK123456',
        'user' => [
            'name' => 'Akmal Fadhurohman',
            'email' => 'akmal@gmail.com'
        ],
        'amount' => 2500000,
        'paymentMethod' => 'BNI',
        'status' => 'completed',
        'createdAt' => '2025-05-21',
        'proofImage' => '/placeholder.svg',
        'details' => 'Transfer from Akmal Fadhurohman, BNI, 21 May 2025'
    ],
    [
        'id' => 'PY789012',
        'bookingId' => 'BK789012',
        'user' => [
            'name' => 'John Doe',
            'email' => 'john@example.com'
        ],
        'amount' => 1800000,
        'paymentMethod' => 'BRI',
        'status' => 'pending',
        'createdAt' => '2025-05-21',
        'proofImage' => '/placeholder.svg',
        'details' => 'Transfer from John Doe, BRI, 21 May 2025'
    ],
    [
        'id' => 'PY345678',
        'bookingId' => 'BK345678',
        'user' => [
            'name' => 'Jane Smith',
            'email' => 'jane@example.com'
        ],
        'amount' => 3200000,
        'paymentMethod' => 'DANA',
        'status' => 'completed',
        'createdAt' => '2025-05-21',
        'proofImage' => '/placeholder.svg',
        'details' => 'Transfer from Jane Smith, DANA, 21 May 2025'
    ],
    [
        'id' => 'PY901234',
        'bookingId' => 'BK901234',
        'user' => [
            'name' => 'Akmal Fadhurohman',
            'email' => 'akmal@gmail.com'
        ],
        'amount' => 1500000,
        'paymentMethod' => 'GoPay',
        'status' => 'refunded',
        'createdAt' => '2025-05-21',
        'proofImage' => '/placeholder.svg',
        'details' => 'Transfer from Akmal Fadhurohman, GoPay, 21 May 2025'
    ],
    [
        'id' => 'PY567890',
        'bookingId' => 'BK567890',
        'user' => [
            'name' => 'John Doe',
            'email' => 'john@example.com'
        ],
        'amount' => 2100000,
        'paymentMethod' => 'BNI',
        'status' => 'completed',
        'createdAt' => '2025-05-21',
        'proofImage' => '/placeholder.svg',
        'details' => 'Transfer from John Doe, BNI, 21 May 2025'
    ]
];

// Handle payment verification and rejection
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['verify'])) {
        $paymentId = $_POST['verify'];
        // In a real application, you would update the database here
        // For this mock example, we'll update the array
        foreach ($payments as $key => $payment) {
            if ($payment['id'] === $paymentId) {
                $payments[$key]['status'] = 'completed';
                break;
            }
        }
    } elseif (isset($_POST['reject'])) {
        $paymentId = $_POST['reject'];
        // In a real application, you would update the database here
        foreach ($payments as $key => $payment) {
            if ($payment['id'] === $paymentId) {
                $payments[$key]['status'] = 'rejected';
                break;
            }
        }
    }
}

// Filter payments based on search and status
$statusFilter = $_GET['statusFilter'] ?? 'all';
$searchTerm = $_GET['search'] ?? '';

$filteredPayments = [];
foreach ($payments as $payment) {
    // Apply status filter
    if ($statusFilter !== 'all' && $payment['status'] !== $statusFilter) {
        continue;
    }
    
    // Apply search filter
    if (!empty($searchTerm) && 
        !stripos($payment['user']['name'], $searchTerm) && 
        !stripos($payment['user']['email'], $searchTerm) && 
        !stripos($payment['bookingId'], $searchTerm)) {
        continue;
    }
    
    $filteredPayments[] = $payment;
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
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Verification - Tickfly</title>
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
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            align-items: center;
            justify-content: center;
        }
        .modal-content {
            background-color: white;
            padding: 24px;
            border-radius: 8px;
            width: 100%;
            max-width: 672px;
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
                        <a href="cs_reschedules.php" class="sidebar-link flex items-center px-4 py-3 rounded-md transition-colors text-gray-700">
                            <i data-lucide="refresh-cw" class="mr-3 w-5 h-5"></i>
                            <span>Reschedule</span>
                        </a>
                    </li>
                    <li>
                        <a href="cs_payments.php" class="sidebar-link active flex items-center px-4 py-3 rounded-md transition-colors">
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
                    <h1 class="text-2xl font-bold mb-6">Payment Verification</h1>

                    <div class="bg-white rounded-lg shadow p-6 border border-gray-200">
                        <div class="flex flex-col md:flex-row justify-between items-center mb-6 gap-4">
                            <h2 class="text-lg font-medium">All Payments</h2>

                            <div class="flex flex-col md:flex-row gap-3">
                                <form method="GET" action="cs_payments.php" class="flex flex-col md:flex-row gap-3">
                                    <div class="relative">
                                        <i data-lucide="search" class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 w-4 h-4"></i>
                                        <input 
                                            type="text" 
                                            name="search" 
                                            placeholder="Search payments..." 
                                            value="<?php echo htmlspecialchars($searchTerm); ?>"
                                            class="pl-10 pr-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                                        >
                                    </div>

                                    <div class="flex items-center">
                                        <i data-lucide="filter" class="mr-2 text-gray-500 w-4 h-4"></i>
                                        <select 
                                            name="statusFilter" 
                                            class="p-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                                            onchange="this.form.submit()"
                                        >
                                            <option value="all" <?php echo $statusFilter === 'all' ? 'selected' : ''; ?>>All Status</option>
                                            <option value="pending" <?php echo $statusFilter === 'pending' ? 'selected' : ''; ?>>Pending</option>
                                            <option value="completed" <?php echo $statusFilter === 'completed' ? 'selected' : ''; ?>>Verified</option>
                                            <option value="rejected" <?php echo $statusFilter === 'rejected' ? 'selected' : ''; ?>>Rejected</option>
                                            <option value="refunded" <?php echo $statusFilter === 'refunded' ? 'selected' : ''; ?>>Refunded</option>
                                        </select>
                                    </div>
                                </form>
                            </div>
                        </div>

                        <?php if (empty($filteredPayments)): ?>
                            <div class="text-center py-8 text-gray-500">
                                <?php echo $searchTerm || $statusFilter !== "all" ? "No payments found matching your criteria" : "No payments found"; ?>
                            </div>
                        <?php else: ?>
                            <div class="overflow-x-auto">
                                <table class="w-full">
                                    <thead>
                                        <tr class="text-left border-b border-gray-200">
                                            <th class="pb-3 font-medium">Booking ID</th>
                                            <th class="pb-3 font-medium">User</th>
                                            <th class="pb-3 font-medium">Amount</th>
                                            <th class="pb-3 font-medium">Method</th>
                                            <th class="pb-3 font-medium">Date</th>
                                            <th class="pb-3 font-medium">Status</th>
                                            <th class="pb-3 font-medium">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($filteredPayments as $payment): ?>
                                            <tr class="border-b border-gray-100">
                                                <td class="py-3 font-medium"><?php echo $payment['bookingId']; ?></td>
                                                <td class="py-3">
                                                    <div>
                                                        <p><?php echo $payment['user']['name'] ?? 'Unknown'; ?></p>
                                                        <p class="text-xs text-gray-500"><?php echo $payment['user']['email'] ?? 'No email'; ?></p>
                                                    </div>
                                                </td>
                                                <td class="py-3 font-medium"><?php echo formatPrice($payment['amount']); ?></td>
                                                <td class="py-3"><?php echo $payment['paymentMethod']; ?></td>
                                                <td class="py-3"><?php echo $payment['createdAt']; ?></td>
                                                <td class="py-3">
                                                    <span
                                                        class="inline-block px-2 py-1 rounded-full text-xs <?php 
                                                            echo $payment['status'] === "completed" 
                                                                ? "bg-green-100 text-green-800" 
                                                                : ($payment['status'] === "pending" 
                                                                    ? "bg-yellow-100 text-yellow-800" 
                                                                    : ($payment['status'] === "refunded"
                                                                        ? "bg-blue-100 text-blue-800"
                                                                        : "bg-red-100 text-red-800")); 
                                                        ?>"
                                                    >
                                                        <?php echo strtoupper($payment['status']); ?>
                                                    </span>
                                                </td>
                                                <td class="py-3">
                                                    <div class="flex gap-2">
                                                        <button 
                                                            onclick="openModal('<?php echo $payment['id']; ?>')" 
                                                            class="p-1 text-blue-600 hover:text-blue-800" 
                                                            title="View proof"
                                                        >
                                                            <i data-lucide="eye" class="w-4 h-4"></i>
                                                        </button>
                                                        <?php if ($payment['status'] === 'pending'): ?>
                                                            <form method="POST" class="inline">
                                                                <button 
                                                                    type="submit" 
                                                                    name="verify" 
                                                                    value="<?php echo $payment['id']; ?>" 
                                                                    class="p-1 text-green-600 hover:text-green-800" 
                                                                    title="Verify payment"
                                                                >
                                                                    <i data-lucide="check" class="w-4 h-4"></i>
                                                                </button>
                                                            </form>
                                                            <form method="POST" class="inline">
                                                                <button 
                                                                    type="submit" 
                                                                    name="reject" 
                                                                    value="<?php echo $payment['id']; ?>" 
                                                                    class="p-1 text-red-600 hover:text-red-800" 
                                                                    title="Reject payment"
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

    <!-- Payment Proof Modal -->
    <div id="paymentProofModal" class="modal">
        <div class="modal-content">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-medium">Payment Proof</h3>
                <button onclick="closeModal()" class="text-gray-500 hover:text-gray-700 text-2xl">&times;</button>
            </div>

            <div id="modalContent" class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Modal content will be inserted here by JavaScript -->
            </div>
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

        // Payment modal functions
        function openModal(paymentId) {
            const modal = document.getElementById('paymentProofModal');
            const modalContent = document.getElementById('modalContent');
            modal.style.display = 'flex';
            
            // Find the payment data
            const payments = <?php echo json_encode($payments); ?>;
            const payment = payments.find(p => p.id === paymentId);
            
            if (payment) {
                modalContent.innerHTML = `
                    <div>
                        <img
                            src="${payment.proofImage || '/placeholder.svg'}"
                            alt="Payment proof"
                            class="w-full h-auto rounded-md border border-gray-200"
                        />
                    </div>

                    <div>
                        <div class="space-y-4">
                            <div>
                                <p class="text-sm text-gray-500">Booking ID</p>
                                <p class="font-medium">${payment.bookingId}</p>
                            </div>

                            <div>
                                <p class="text-sm text-gray-500">User</p>
                                <p class="font-medium">${payment.user.name || 'Unknown'}</p>
                                <p class="text-xs text-gray-500">${payment.user.email || 'No email'}</p>
                            </div>

                            <div>
                                <p class="text-sm text-gray-500">Amount</p>
                                <p class="font-medium">${formatPrice(payment.amount)}</p>
                            </div>

                            <div>
                                <p class="text-sm text-gray-500">Payment Method</p>
                                <p class="font-medium">${payment.paymentMethod}</p>
                            </div>

                            <div>
                                <p class="text-sm text-gray-500">Payment Details</p>
                                <p class="font-medium">${payment.details}</p>
                            </div>

                            <div>
                                <p class="text-sm text-gray-500">Status</p>
                                <span
                                    class="inline-block px-2 py-1 rounded-full text-xs ${
                                        payment.status === "completed"
                                            ? "bg-green-100 text-green-800"
                                            : payment.status === "pending"
                                                ? "bg-yellow-100 text-yellow-800"
                                                : payment.status === "refunded"
                                                    ? "bg-blue-100 text-blue-800"
                                                    : "bg-red-100 text-red-800"
                                    }"
                                >
                                    ${payment.status.toUpperCase()}
                                </span>
                            </div>
                        </div>

                        ${payment.status === 'pending' ? `
                            <div class="mt-6 flex gap-3">
                                <form method="POST">
                                    <button
                                        type="submit"
                                        name="reject"
                                        value="${payment.id}"
                                        class="px-4 py-2 border border-red-600 text-red-600 rounded-md hover:bg-red-50 transition"
                                    >
                                        Reject Payment
                                    </button>
                                </form>
                                <form method="POST">
                                    <button
                                        type="submit"
                                        name="verify"
                                        value="${payment.id}"
                                        class="px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700 transition"
                                    >
                                        Verify Payment
                                    </button>
                                </form>
                            </div>
                        ` : ''}
                    </div>
                `;
            } else {
                modalContent.innerHTML = '<p class="text-center">Payment details not found</p>';
            }
        }

        function closeModal() {
            const modal = document.getElementById('paymentProofModal');
            modal.style.display = 'none';
        }

        // Close modal if clicked outside
        window.onclick = function(event) {
            const modal = document.getElementById('paymentProofModal');
            if (event.target === modal) {
                closeModal();
            }
        }

        // Format price helper function for JavaScript
        function formatPrice(price) {
            return 'Rp ' + price.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
        }
    </script>
</body>
</html>