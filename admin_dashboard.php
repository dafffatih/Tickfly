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

// Mock data for the dashboard
$stats = [
    [
        "title" => "Total Users",
        "value" => "2,845",
        "change" => "+12.5%",
        "icon" => "users",
        "color" => "blue"
    ],
    [
        "title" => "Active Flights",
        "value" => "156",
        "change" => "+3.2%",
        "icon" => "plane",
        "color" => "green"
    ],
    [
        "title" => "Revenue",
        "value" => "Rp 245,000,000",
        "change" => "+18.7%",
        "icon" => "credit-card",
        "color" => "purple"
    ],
    [
        "title" => "Bookings",
        "value" => "1,245",
        "change" => "+5.9%",
        "icon" => "calendar",
        "color" => "orange"
    ]
];

// Mock airlines data
$airlines = [
    [
        "id" => 1,
        "name" => "Garuda Indonesia",
        "code" => "GA",
        "status" => "active"
    ],
    [
        "id" => 2,
        "name" => "Lion Air",
        "code" => "JT",
        "status" => "active"
    ],
    [
        "id" => 3,
        "name" => "Sriwijaya Air",
        "code" => "SJ",
        "status" => "active"
    ],
    [
        "id" => 4,
        "name" => "Citilink",
        "code" => "QG",
        "status" => "inactive"
    ],
    [
        "id" => 5,
        "name" => "Batik Air",
        "code" => "ID",
        "status" => "active"
    ]
];

// Mock users data
$users = [
    [
        "id" => 1,
        "name" => "John Doe",
        "email" => "john@example.com",
        "role" => "user",
        "status" => "active"
    ],
    [
        "id" => 2,
        "name" => "Jane Smith",
        "email" => "jane@example.com",
        "role" => "user",
        "status" => "active"
    ],
    [
        "id" => 3,
        "name" => "Admin User",
        "email" => "admin@tickfly.com",
        "role" => "admin",
        "status" => "active"
    ],
    [
        "id" => 4,
        "name" => "CS Agent",
        "email" => "cs@tickfly.com",
        "role" => "cs",
        "status" => "active"
    ],
    [
        "id" => 5,
        "name" => "Bob Johnson",
        "email" => "bob@example.com",
        "role" => "user",
        "status" => "inactive"
    ]
];

// Mock transactions data
$transactions = [
    [
        "id" => "PY123456",
        "user" => [
            "name" => "Akmal Fadhurohman",
            "email" => "akmal@gmail.com"
        ],
        "flight" => "Jakarta → Bali",
        "amount" => "Rp 2,500,000",
        "status" => "completed",
        "date" => "2021-06-15",
        "payment" => "BNI"
    ],
    [
        "id" => "PY789012",
        "user" => [
            "name" => "John Doe",
            "email" => "john@example.com"
        ],
        "flight" => "Jakarta → Surabaya",
        "amount" => "Rp 1,800,000",
        "status" => "pending",
        "date" => "2021-06-14",
        "payment" => "BRI"
    ],
    [
        "id" => "PY345678",
        "user" => [
            "name" => "Jane Smith",
            "email" => "jane@example.com"
        ],
        "flight" => "Jakarta → Yogyakarta",
        "amount" => "Rp 3,200,000",
        "status" => "completed",
        "date" => "2021-06-13",
        "payment" => "DANA"
    ]
];

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
        case 'user-plus':
            return '<svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="8.5" cy="7" r="4"></circle><line x1="20" y1="8" x2="20" y2="14"></line><line x1="23" y1="11" x2="17" y2="11"></line></svg>';
        case 'home':
            return '<svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path><polyline points="9 22 9 12 15 12 15 22"></polyline></svg>';
        default:
            return '';
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Tickfly</title>
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
                        <a href="admin_dashboard.php" class="flex items-center px-4 py-3 rounded-md transition-colors bg-blue-800 text-white">
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
                        <a href="admin_transactions.php" class="flex items-center px-4 py-3 rounded-md transition-colors text-blue-100 hover:bg-blue-800 hover:text-white">
                            <?php echo getIconSvg('credit-card'); ?>
                            <span class="ml-3">Transactions</span>
                        </a>
                    </li>
                </ul>
            </nav>

            <div class="absolute bottom-0 w-full p-4">
                <a href="logout.php" class="flex items-center w-full px-4 py-3 text-blue-100 rounded-md hover:bg-blue-800 hover:text-white transition-colors">
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
                                    <?php echo strtoupper(substr($_SESSION['username'], 0, 1)); ?>
                                </div>
                                <div>
                                    <p class="text-sm font-medium"><?php echo $_SESSION['username']; ?></p>
                                    <p class="text-xs text-gray-500"><?php echo $_SESSION['email'] ?? 'admin@tickfly.com'; ?></p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </header>

            <main class="flex-1 overflow-y-auto p-6">
                <h1 class="text-2xl font-bold mb-6">Admin Dashboard</h1>

                <!-- Stats -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                    <?php foreach ($stats as $stat): ?>
                    <div class="bg-white rounded-lg shadow p-6 border border-gray-200">
                        <div class="flex items-center justify-between mb-4">
                            <div class="p-3 rounded-full bg-<?php echo $stat['color']; ?>-100 text-<?php echo $stat['color']; ?>-800">
                                <?php echo getIconSvg($stat['icon']); ?>
                            </div>
                            <span class="text-sm font-medium text-green-600"><?php echo $stat['change']; ?></span>
                        </div>
                        <h3 class="text-lg font-medium text-gray-500"><?php echo $stat['title']; ?></h3>
                        <p class="text-2xl font-bold"><?php echo $stat['value']; ?></p>
                    </div>
                    <?php endforeach; ?>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mt-6">
                    <!-- Airlines -->
                    <div class="bg-white rounded-lg shadow p-6 border border-gray-200">
                        <div class="flex justify-between items-center mb-6">
                            <h2 class="text-lg font-medium">Airlines</h2>
                            <button class="flex items-center gap-1 px-3 py-2 bg-blue-800 text-white rounded-md hover:bg-blue-900 transition">
                                <?php echo getIconSvg('plus'); ?>
                                <span>Add Airline</span>
                            </button>
                        </div>

                        <div class="overflow-x-auto">
                            <table class="w-full">
                                <thead>
                                    <tr class="text-left border-b border-gray-200">
                                        <th class="pb-3 font-medium">Name</th>
                                        <th class="pb-3 font-medium">Code</th>
                                        <th class="pb-3 font-medium">Status</th>
                                        <th class="pb-3 font-medium">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($airlines as $airline): ?>
                                    <tr class="border-b border-gray-100">
                                        <td class="py-3"><?php echo $airline['name']; ?></td>
                                        <td class="py-3"><?php echo $airline['code']; ?></td>
                                        <td class="py-3">
                                            <span class="inline-block px-2 py-1 rounded-full text-xs <?php echo $airline['status'] === 'active' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'; ?>">
                                                <?php echo strtoupper($airline['status']); ?>
                                            </span>
                                        </td>
                                        <td class="py-3">
                                            <div class="flex gap-2">
                                                <button class="p-1 text-blue-600 hover:text-blue-800">
                                                    <?php echo getIconSvg('edit'); ?>
                                                </button>
                                                <button class="p-1 text-red-600 hover:text-red-800">
                                                    <?php echo getIconSvg('trash'); ?>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Users -->
                    <div class="bg-white rounded-lg shadow p-6 border border-gray-200">
                        <div class="flex justify-between items-center mb-6">
                            <h2 class="text-lg font-medium">Users</h2>
                            <button class="flex items-center gap-1 px-3 py-2 bg-blue-800 text-white rounded-md hover:bg-blue-900 transition">
                                <?php echo getIconSvg('user-plus'); ?>
                                <span>Add User</span>
                            </button>
                        </div>

                        <div class="overflow-x-auto">
                            <table class="w-full">
                                <thead>
                                    <tr class="text-left border-b border-gray-200">
                                        <th class="pb-3 font-medium">Name</th>
                                        <th class="pb-3 font-medium">Email</th>
                                        <th class="pb-3 font-medium">Role</th>
                                        <th class="pb-3 font-medium">Status</th>
                                        <th class="pb-3 font-medium">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($users as $user): ?>
                                    <tr class="border-b border-gray-100">
                                        <td class="py-3"><?php echo $user['name']; ?></td>
                                        <td class="py-3"><?php echo $user['email']; ?></td>
                                        <td class="py-3">
                                            <span class="inline-block px-2 py-1 rounded-full text-xs 
                                                <?php if ($user['role'] === 'admin'): ?> bg-purple-100 text-purple-800
                                                <?php elseif ($user['role'] === 'cs'): ?> bg-blue-100 text-blue-800
                                                <?php else: ?> bg-gray-100 text-gray-800
                                                <?php endif; ?>">
                                                <?php echo strtoupper($user['role']); ?>
                                            </span>
                                        </td>
                                        <td class="py-3">
                                            <span class="inline-block px-2 py-1 rounded-full text-xs <?php echo $user['status'] === 'active' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'; ?>">
                                                <?php echo strtoupper($user['status']); ?>
                                            </span>
                                        </td>
                                        <td class="py-3">
                                            <div class="flex gap-2">
                                                <button class="p-1 text-blue-600 hover:text-blue-800">
                                                    <?php echo getIconSvg('edit'); ?>
                                                </button>
                                                <button class="p-1 text-red-600 hover:text-red-800">
                                                    <?php echo getIconSvg('trash'); ?>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Transactions -->
                <div class="mt-6">
                    <div class="bg-white rounded-lg shadow p-6 border border-gray-200">
                        <div class="flex justify-between items-center mb-6">
                            <h2 class="text-lg font-medium">Recent Transactions</h2>
                            <div class="flex items-center gap-3">
                                <select class="px-3 py-2 border border-gray-300 rounded-md">
                                    <option>All Status</option>
                                    <option>Completed</option>
                                    <option>Pending</option>
                                    <option>Failed</option>
                                </select>
                                <button class="flex items-center gap-1 px-3 py-2 bg-blue-800 text-white rounded-md hover:bg-blue-900 transition">
                                    <?php echo getIconSvg('export'); ?>
                                    <span>Export</span>
                                </button>
                            </div>
                        </div>

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
                                    <?php foreach ($transactions as $transaction): ?>
                                    <tr class="border-b border-gray-100">
                                        <td class="py-3"><?php echo $transaction['id']; ?></td>
                                        <td class="py-3">
                                            <div>
                                                <p class="font-medium"><?php echo $transaction['user']['name']; ?></p>
                                                <p class="text-xs text-gray-500"><?php echo $transaction['user']['email']; ?></p>
                                            </div>
                                        </td>
                                        <td class="py-3"><?php echo $transaction['flight']; ?></td>
                                        <td class="py-3"><?php echo $transaction['amount']; ?></td>
                                        <td class="py-3">
                                            <span class="inline-block px-2 py-1 rounded-full text-xs 
                                                <?php if ($transaction['status'] === 'completed'): ?> bg-green-100 text-green-800
                                                <?php elseif ($transaction['status'] === 'pending'): ?> bg-yellow-100 text-yellow-800
                                                <?php else: ?> bg-red-100 text-red-800
                                                <?php endif; ?>">
                                                <?php echo strtoupper($transaction['status']); ?>
                                            </span>
                                        </td>
                                        <td class="py-3"><?php echo $transaction['date']; ?></td>
                                        <td class="py-3"><?php echo $transaction['payment']; ?></td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
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