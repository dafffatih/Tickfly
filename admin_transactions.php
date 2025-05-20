<?php
// Initialize session
session_start();

// Include database configuration
require_once 'config.php';

// Check if user is logged in
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("location: login.php");
    exit;
}

// Check if user has admin role
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    // Redirect based on role
    if ($_SESSION['role'] === 'user') {
        header("location: index.php");
    } elseif ($_SESSION['role'] === 'cs') {
        header("location: cs_dashboard.php");
    } else {
        header("location: login.php");
    }
    exit;
}

// Mock transactions data
$transactions = [
    [
        "id" => "TF123456",
        "user" => [
            "name" => "John Doe",
            "email" => "john@example.com"
        ],
        "flight" => "Jakarta → Bali",
        "amount" => "Rp 2.500.000",
        "status" => "completed",
        "date" => "2021-06-15",
        "payment" => "BNI"
    ],
    [
        "id" => "TF789012",
        "user" => [
            "name" => "Jane Smith",
            "email" => "jane@example.com"
        ],
        "flight" => "Jakarta → Surabaya",
        "amount" => "Rp 1.800.000",
        "status" => "pending",
        "date" => "2021-06-14",
        "payment" => "BRI"
    ],
    [
        "id" => "TF345678",
        "user" => [
            "name" => "Bob Johnson",
            "email" => "bob@example.com"
        ],
        "flight" => "Jakarta → Yogyakarta",
        "amount" => "Rp 3.200.000",
        "status" => "completed",
        "date" => "2021-06-13",
        "payment" => "GoPay"
    ],
    [
        "id" => "TF901234",
        "user" => [
            "name" => "Alice Brown",
            "email" => "alice@example.com"
        ],
        "flight" => "Jakarta → Bandung",
        "amount" => "Rp 1.500.000",
        "status" => "refunded",
        "date" => "2021-06-12",
        "payment" => "BNI"
    ],
    [
        "id" => "TF567890",
        "user" => [
            "name" => "Charlie Davis",
            "email" => "charlie@example.com"
        ],
        "flight" => "Jakarta → Medan",
        "amount" => "Rp 2.100.000",
        "status" => "completed",
        "date" => "2021-06-11",
        "payment" => "DANA"
    ]
];

// Filter transactions by status if status filter is set
$statusFilter = isset($_GET['status']) ? $_GET['status'] : 'all';
$searchTerm = isset($_GET['search']) ? $_GET['search'] : '';

$filteredTransactions = [];
foreach ($transactions as $transaction) {
    // Filter by status if not 'all'
    if ($statusFilter !== 'all' && $transaction['status'] !== $statusFilter) {
        continue;
    }
    
    // Filter by search term if provided
    if ($searchTerm !== '') {
        $searchTermLower = strtolower($searchTerm);
        $idFound = strpos(strtolower($transaction['id']), $searchTermLower) !== false;
        $nameFound = strpos(strtolower($transaction['user']['name']), $searchTermLower) !== false;
        $emailFound = strpos(strtolower($transaction['user']['email']), $searchTermLower) !== false;
        $flightFound = strpos(strtolower($transaction['flight']), $searchTermLower) !== false;
        
        if (!$idFound && !$nameFound && !$emailFound && !$flightFound) {
            continue;
        }
    }
    
    $filteredTransactions[] = $transaction;
}

// Helper function for icon SVGs
function getIconSvg($icon) {
    switch ($icon) {
        case 'users':
            return '<svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="9" cy="7" r="4"></circle><path d="M23 21v-2a4 4 0 0 0-3-3.87"></path><path d="M16 3.13a4 4 0 0 1 0 7.75"></path></svg>';
        case 'plane':
            return '<svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 2L11 13"></path><path d="M22 2l-7 20-4-9-9-4 20-7z"></path></svg>';
        case 'credit-card':
            return '<svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="1" y="4" width="22" height="16" rx="2" ry="2"></rect><line x1="1" y1="10" x2="23" y2="10"></line></svg>';
        case 'calendar':
            return '<svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect><line x1="16" y1="2" x2="16" y2="6"></line><line x1="8" y1="2" x2="8" y2="6"></line><line x1="3" y1="10" x2="21" y2="10"></line></svg>';
        case 'edit':
            return '<svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path></svg>';
        case 'trash':
            return '<svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path></svg>';
        case 'plus':
            return '<svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>';
        case 'export':
            return '<svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path><polyline points="7 10 12 15 17 10"></polyline><line x1="12" y1="15" x2="12" y2="3"></line></svg>';
        case 'logout':
            return '<svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path><polyline points="16 17 21 12 16 7"></polyline><line x1="21" y1="12" x2="9" y2="12"></line></svg>';
        case 'menu':
            return '<svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="3" y1="12" x2="21" y2="12"></line><line x1="3" y1="6" x2="21" y2="6"></line><line x1="3" y1="18" x2="21" y2="18"></line></svg>';
        case 'close':
            return '<svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>';
        case 'search':
            return '<svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>';
        case 'filter':
            return '<svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3"></polygon></svg>';
        case 'download':
            return '<svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path><polyline points="7 10 12 15 17 10"></polyline><line x1="12" y1="15" x2="12" y2="3"></line></svg>';
        case 'home':
            return '<svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path><polyline points="9 22 9 12 15 12 15 22"></polyline></svg>';
        default:
            return '';
    }
}

// Function to convert PHP array to CSV and trigger download
function exportTransactionsToCSV($transactions) {
    // Set headers to force download
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="transactions.csv"');
    
    // Open output stream
    $output = fopen('php://output', 'w');
    
    // Add BOM for UTF-8 compatibility with Excel
    fprintf($output, "\xEF\xBB\xBF");
    
    // Add CSV header row
    fputcsv($output, ['ID', 'User', 'Email', 'Flight', 'Amount', 'Status', 'Date', 'Payment']);
    
    // Add data rows
    foreach ($transactions as $transaction) {
        fputcsv($output, [
            $transaction['id'],
            $transaction['user']['name'],
            $transaction['user']['email'],
            $transaction['flight'],
            $transaction['amount'],
            strtoupper($transaction['status']),
            $transaction['date'],
            $transaction['payment']
        ]);
    }
    
    // Close the output stream
    fclose($output);
    exit;
}

// Check if export action is requested
if (isset($_GET['export']) && $_GET['export'] === 'true') {
    exportTransactionsToCSV($filteredTransactions);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Transactions - Tickfly Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f3f4f6;
        }
        .sidebar {
            width: 16rem;
            transition: transform 0.3s ease;
        }
        @media (max-width: 1024px) {
            .sidebar {
                transform: translateX(-100%);
            }
            .sidebar.show {
                transform: translateX(0);
            }
        }
    </style>
</head>
<body class="font-sans">
    <div class="flex h-screen">
        <!-- Sidebar -->
        <div id="sidebar" class="sidebar fixed inset-y-0 left-0 z-30 bg-blue-900 text-white lg:relative">
            <div class="flex items-center justify-between h-16 px-6 border-b border-blue-800">
                <a href="admin_dashboard.php" class="flex items-center">
                    <span class="text-xl font-bold">
                        <span class="text-white">Tick</span>
                        <span class="text-blue-300">fly</span>
                        <span class="text-sm ml-1 text-blue-300">Admin</span>
                    </span>
                </a>
                <button id="close-sidebar" class="lg:hidden text-white">
                    <?php echo getIconSvg('close'); ?>
                </button>
            </div>

            <nav class="mt-6 px-4">
                <ul class="space-y-2">
                    <li>
                        <a href="admin_dashboard.php" class="flex items-center px-4 py-3 rounded-md transition-colors text-blue-100 hover:bg-blue-800 hover:text-white">
                            <?php echo getIconSvg('home'); ?>
                            <span class="ml-3">Dashboard</span>
                        </a>
                    </li>
                    <li>
                        <a href="admin_airlines.php" class="flex items-center px-4 py-3 rounded-md transition-colors text-blue-100 hover:bg-blue-800 hover:text-white">
                            <?php echo getIconSvg('plane'); ?>
                            <span class="ml-3">Airlines</span>
                        </a>
                    </li>
                    <li>
                        <a href="admin_users.php" class="flex items-center px-4 py-3 rounded-md transition-colors text-blue-100 hover:bg-blue-800 hover:text-white">
                            <?php echo getIconSvg('users'); ?>
                            <span class="ml-3">Users</span>
                        </a>
                    </li>
                    <li>
                        <a href="admin_transactions.php" class="flex items-center px-4 py-3 rounded-md transition-colors bg-blue-800 text-white">
                            <?php echo getIconSvg('credit-card'); ?>
                            <span class="ml-3">Transactions</span>
                        </a>
                    </li>
                </ul>
            </nav>

            <div class="absolute bottom-0 w-full p-4">
                <a href="login.php" class="flex items-center w-full px-4 py-3 text-blue-100 rounded-md hover:bg-blue-800 hover:text-white transition-colors">
                    <?php echo getIconSvg('logout'); ?>
                    <span class="ml-3">Logout</span>
                </a>
            </div>
        </div>

        <!-- Main content -->
        <div class="flex-1 flex flex-col overflow-hidden">
            <header class="bg-white shadow-sm z-10">
                <div class="flex items-center justify-between h-16 px-6">
                    <button id="show-sidebar" class="lg:hidden text-gray-600">
                        <?php echo getIconSvg('menu'); ?>
                    </button>

                    <div class="flex items-center">
                        <div class="relative">
                            <div class="flex items-center space-x-2">
                                <div class="w-8 h-8 rounded-full bg-blue-800 flex items-center justify-center text-white">
                                    <?php echo strtoupper(substr($_SESSION['username'] ?? 'A', 0, 1)); ?>
                                </div>
                                <div>
                                    <p class="text-sm font-medium"><?php echo $_SESSION['username'] ?? 'Admin User'; ?></p>
                                    <p class="text-xs text-gray-500"><?php echo $_SESSION['email'] ?? 'admin@tickfly.com'; ?></p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </header>

            <main class="flex-1 overflow-y-auto p-6">
                <div class="flex justify-between items-center mb-6">
                    <h1 class="text-2xl font-bold">Transactions</h1>

                    <a href="?export=true<?php echo $statusFilter !== 'all' ? '&status=' . $statusFilter : ''; echo $searchTerm !== '' ? '&search=' . urlencode($searchTerm) : ''; ?>" class="flex items-center gap-1 px-4 py-2 bg-blue-800 text-white rounded-md hover:bg-blue-900 transition">
                        <?php echo getIconSvg('download'); ?>
                        <span>Export</span>
                    </a>
                </div>

                <div class="bg-white rounded-lg shadow-md p-6 border border-gray-200">
                    <div class="flex flex-col md:flex-row justify-between items-center mb-6 gap-4">
                        <h2 class="text-lg font-medium">Recent Transactions</h2>

                        <div class="flex flex-col md:flex-row gap-3">
                            <form action="" method="get" class="flex flex-col md:flex-row gap-3">
                                <div class="relative">
                                    <div class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400">
                                        <?php echo getIconSvg('search'); ?>
                                    </div>
                                    <input 
                                        type="text" 
                                        name="search" 
                                        placeholder="Search transactions..." 
                                        value="<?php echo htmlspecialchars($searchTerm); ?>" 
                                        class="pl-10 pr-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                                    >
                                </div>

                                <div class="flex items-center">
                                    <div class="mr-2 text-gray-500">
                                        <?php echo getIconSvg('filter'); ?>
                                    </div>
                                    <select 
                                        name="status" 
                                        onchange="this.form.submit()" 
                                        class="p-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                                    >
                                        <option value="all" <?php echo $statusFilter === 'all' ? 'selected' : ''; ?>>All Status</option>
                                        <option value="pending" <?php echo $statusFilter === 'pending' ? 'selected' : ''; ?>>Pending</option>
                                        <option value="completed" <?php echo $statusFilter === 'completed' ? 'selected' : ''; ?>>Completed</option>
                                        <option value="refunded" <?php echo $statusFilter === 'refunded' ? 'selected' : ''; ?>>Refunded</option>
                                    </select>
                                </div>
                            </form>
                        </div>
                    </div>

                    <?php if (empty($filteredTransactions)): ?>
                    <div class="text-center py-8 text-gray-500">
                        <?php echo ($searchTerm || $statusFilter !== 'all') ? "No transactions found matching your criteria" : "No transactions found"; ?>
                    </div>
                    <?php else: ?>
                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead>
                                <tr class="text-left border-b border-gray-200">
                                    <th class="pb-3 font-medium">ID</th>
                                    <th class="pb-3 font-medium">User</th>
                                    <th class="pb-3 font-medium">Flight</th>
                                    <th class="pb-3 font-medium">Amount</th>
                                    <th class="pb-3 font-medium">Status</th>
                                    <th class="pb-3 font-medium">Date</th>
                                    <th class="pb-3 font-medium">Payment</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($filteredTransactions as $transaction): ?>
                                <tr class="border-b border-gray-100">
                                    <td class="py-3 font-medium"><?php echo htmlspecialchars($transaction['id']); ?></td>
                                    <td class="py-3">
                                        <div>
                                            <p><?php echo htmlspecialchars($transaction['user']['name']); ?></p>
                                            <p class="text-xs text-gray-500"><?php echo htmlspecialchars($transaction['user']['email']); ?></p>
                                        </div>
                                    </td>
                                    <td class="py-3"><?php echo htmlspecialchars($transaction['flight']); ?></td>
                                    <td class="py-3 font-medium"><?php echo htmlspecialchars($transaction['amount']); ?></td>
                                    <td class="py-3">
                                        <span class="inline-block px-2 py-1 rounded-full text-xs 
                                            <?php if ($transaction['status'] === 'completed'): ?> bg-green-100 text-green-800
                                            <?php elseif ($transaction['status'] === 'pending'): ?> bg-yellow-100 text-yellow-800
                                            <?php else: ?> bg-red-100 text-red-800
                                            <?php endif; ?>">
                                            <?php echo strtoupper($transaction['status']); ?>
                                        </span>
                                    </td>
                                    <td class="py-3"><?php echo htmlspecialchars($transaction['date']); ?></td>
                                    <td class="py-3"><?php echo htmlspecialchars($transaction['payment']); ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <?php endif; ?>
                </div>
            </main>
        </div>
    </div>

    <script>
        // Simple sidebar toggle for mobile
        document.getElementById('show-sidebar').addEventListener('click', function() {
            document.getElementById('sidebar').classList.add('show');
        });

        document.getElementById('close-sidebar').addEventListener('click', function() {
            document.getElementById('sidebar').classList.remove('show');
        });
    </script>
</body>
</html>