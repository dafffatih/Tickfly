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

// Mock users data
$users = [
    [
        "id" => 1,
        "username" => "admin",
        "name" => "Admin User",
        "email" => "admin@tickfly.com",
        "role" => "admin",
        "status" => "active",
        "createdAt" => "2025-05-20"
    ],
    [
        "id" => 2,
        "username" => "cs",
        "name" => "Customer Service",
        "email" => "cs@tickfly.com",
        "role" => "cs",
        "status" => "active",
        "createdAt" => "2025-05-20"
    ],
    [
        "id" => 3,
        "username" => "akmal",
        "name" => "Akmal Fadhurohman",
        "email" => "akmal@gmail.com",
        "role" => "user",
        "status" => "active",
        "createdAt" => "2025-05-20"
    ],
    [
        "id" => 4,
        "username" => "john",
        "name" => "John Doe",
        "email" => "john@example.com",
        "role" => "user",
        "status" => "active",
        "createdAt" => "2025-05-20"
    ],
    [
        "id" => 5,
        "username" => "jane",
        "name" => "Jane Smith",
        "email" => "jane@example.com",
        "role" => "user",
        "status" => "active",
        "createdAt" => "2025-05-20"
    ]
];

// Search functionality
$searchTerm = isset($_GET['search']) ? $_GET['search'] : '';
$filteredUsers = [];

if (!empty($searchTerm)) {
    foreach ($users as $user) {
        if (
            stripos($user['username'], $searchTerm) !== false ||
            stripos($user['email'], $searchTerm) !== false ||
            ($user['name'] && stripos($user['name'], $searchTerm) !== false)
        ) {
            $filteredUsers[] = $user;
        }
    }
} else {
    $filteredUsers = $users;
}

// Helper function for icon SVGs
function getIconSvg($icon) {
    switch ($icon) {
        case 'users':
            return '<svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="9" cy="7" r="4"></circle><path d="M23 21v-2a4 4 0 0 0-3-3.87"></path><path d="M16 3.13a4 4 0 0 1 0 7.75"></path></svg>';
        case 'user-plus':
            return '<svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="8.5" cy="7" r="4"></circle><line x1="20" y1="8" x2="20" y2="14"></line><line x1="23" y1="11" x2="17" y2="11"></line></svg>';
        case 'plane':
            return '<svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 2L11 13"></path><path d="M22 2l-7 20-4-9-9-4 20-7z"></path></svg>';
        case 'credit-card':
            return '<svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="1" y="4" width="22" height="16" rx="2" ry="2"></rect><line x1="1" y1="10" x2="23" y2="10"></line></svg>';
        case 'edit':
            return '<svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path></svg>';
        case 'trash':
            return '<svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path></svg>';
        case 'search':
            return '<svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>';
        case 'logout':
            return '<svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path><polyline points="16 17 21 12 16 7"></polyline><line x1="21" y1="12" x2="9" y2="12"></line></svg>';
        case 'menu':
            return '<svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="3" y1="12" x2="21" y2="12"></line><line x1="3" y1="6" x2="21" y2="6"></line><line x1="3" y1="18" x2="21" y2="18"></line></svg>';
        case 'close':
            return '<svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>';
        case 'home':
            return '<svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path><polyline points="9 22 9 12 15 12 15 22"></polyline></svg>';
        default:
            return '';
    }
}

// Process add user form if submitted
$newUserAdded = false;
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['addUser'])) {
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = trim($_POST['password'] ?? '');
    $name = trim($_POST['name'] ?? '');
    $role = $_POST['role'] ?? 'user';
    $status = $_POST['status'] ?? 'active';
    
    if (empty($username) || empty($email) || empty($password)) {
        $error = 'Please fill in all required fields';
    } else {
        // In a real application, you would insert the user into the database here
        // For this example, we'll just simulate success
        $newUserAdded = true;
        
        // Redirect to prevent form resubmission
        header("Location: admin_users.php?success=1");
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Management - Tickfly Admin</title>
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
        .modal {
            transition: opacity 0.3s ease;
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
                        <a href="admin_users.php" class="flex items-center px-4 py-3 rounded-md transition-colors bg-blue-800 text-white">
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
                    <h1 class="text-2xl font-bold">User Management</h1>

                    <button id="show-add-modal" class="flex items-center gap-1 px-4 py-2 bg-blue-800 text-white rounded-md hover:bg-blue-900 transition">
                        <?php echo getIconSvg('user-plus'); ?>
                        <span>Add User</span>
                    </button>
                </div>

                <div class="bg-white rounded-lg shadow-md p-6 border border-gray-200">
                    <div class="flex justify-between items-center mb-6">
                        <h2 class="text-lg font-medium">All Users</h2>

                        <div class="relative">
                            <form action="admin_users.php" method="GET">
                                <div class="relative">
                                    <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400">
                                        <?php echo getIconSvg('search'); ?>
                                    </span>
                                    <input 
                                        type="text" 
                                        name="search" 
                                        placeholder="Search users..." 
                                        value="<?php echo htmlspecialchars($searchTerm); ?>" 
                                        class="pl-10 pr-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                                    >
                                </div>
                            </form>
                        </div>
                    </div>

                    <?php if (isset($_GET['success']) && $_GET['success'] == 1): ?>
                        <div class="bg-green-100 text-green-700 p-4 rounded-md mb-6">
                            User added successfully.
                        </div>
                    <?php endif; ?>

                    <?php if (empty($filteredUsers)): ?>
                        <div class="text-center py-8 text-gray-500">
                            <?php echo $searchTerm ? "No users found matching your search" : "No users found"; ?>
                        </div>
                    <?php else: ?>
                        <div class="overflow-x-auto">
                            <table class="w-full">
                                <thead>
                                    <tr class="text-left border-b border-gray-200">
                                        <th class="pb-3 font-medium">ID</th>
                                        <th class="pb-3 font-medium">Username</th>
                                        <th class="pb-3 font-medium">Name</th>
                                        <th class="pb-3 font-medium">Email</th>
                                        <th class="pb-3 font-medium">Role</th>
                                        <th class="pb-3 font-medium">Created At</th>
                                        <th class="pb-3 font-medium">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($filteredUsers as $user): ?>
                                    <tr class="border-b border-gray-100">
                                        <td class="py-3"><?php echo $user['id']; ?></td>
                                        <td class="py-3"><?php echo $user['username']; ?></td>
                                        <td class="py-3"><?php echo $user['name'] ?? '-'; ?></td>
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
                                        <td class="py-3"><?php echo $user['createdAt']; ?></td>
                                        <td class="py-3">
                                            <div class="flex gap-2">
                                                <a href="edit_user.php?id=<?php echo $user['id']; ?>" class="p-1 text-blue-600 hover:text-blue-800">
                                                    <?php echo getIconSvg('edit'); ?>
                                                </a>
                                                <a href="delete_user.php?id=<?php echo $user['id']; ?>" 
                                                   onclick="return confirm('Are you sure you want to delete this user?')" 
                                                   class="p-1 text-red-600 hover:text-red-800">
                                                    <?php echo getIconSvg('trash'); ?>
                                                </a>
                                            </div>
                                        </td>
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

    <!-- Add User Modal -->
    <div id="add-user-modal" class="modal fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50 hidden">
        <div class="bg-white rounded-lg shadow-lg p-6 w-full max-w-md">
            <h3 class="text-lg font-medium mb-4">Add New User</h3>

            <?php if (!empty($error)): ?>
                <div class="bg-red-100 text-red-700 p-4 rounded-md mb-4">
                    <?php echo $error; ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="admin_users.php" class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Username*</label>
                    <input
                        type="text"
                        name="username"
                        class="w-full p-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                        placeholder="Enter username"
                        required
                    />
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Email*</label>
                    <input
                        type="email"
                        name="email"
                        class="w-full p-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                        placeholder="Enter email"
                        required
                    />
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Password*</label>
                    <input
                        type="password"
                        name="password"
                        class="w-full p-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                        placeholder="Enter password"
                        required
                    />
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Name</label>
                    <input
                        type="text"
                        name="name"
                        class="w-full p-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                        placeholder="Enter full name"
                    />
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Role</label>
                    <select
                        name="role"
                        class="w-full p-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                    >
                        <option value="user">User</option>
                        <option value="cs">Customer Service</option>
                        <option value="admin">Admin</option>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                    <select
                        name="status"
                        class="w-full p-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                    >
                        <option value="active">Active</option>
                        <option value="inactive">Inactive</option>
                    </select>
                </div>

                <div class="flex justify-end gap-3 mt-6">
                    <button
                        type="button"
                        id="cancel-add-user"
                        class="px-4 py-2 border border-gray-300 text-gray-700 rounded-md hover:bg-gray-50 transition"
                    >
                        Cancel
                    </button>

                    <button
                        type="submit"
                        name="addUser"
                        class="px-4 py-2 bg-blue-800 text-white rounded-md hover:bg-blue-900 transition"
                    >
                        Add User
                    </button>
                </div>
            </form>
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

        // Modal toggle
        document.getElementById('show-add-modal').addEventListener('click', function() {
            document.getElementById('add-user-modal').classList.remove('hidden');
        });

        document.getElementById('cancel-add-user').addEventListener('click', function() {
            document.getElementById('add-user-modal').classList.add('hidden');
        });

        // Close modal when clicking outside
        document.getElementById('add-user-modal').addEventListener('click', function(e) {
            if (e.target === this) {
                this.classList.add('hidden');
            }
        });
    </script>
</body>
</html>