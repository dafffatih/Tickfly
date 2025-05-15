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

// Process search if submitted
$searchTerm = '';
if (isset($_GET['search']) && !empty($_GET['search'])) {
    $searchTerm = strtolower(trim($_GET['search']));
}

// Filter airlines based on search term
$filteredAirlines = [];
if (!empty($searchTerm)) {
    foreach ($airlines as $airline) {
        if (strpos(strtolower($airline['name']), $searchTerm) !== false || 
            strpos(strtolower($airline['code']), $searchTerm) !== false) {
            $filteredAirlines[] = $airline;
        }
    }
} else {
    $filteredAirlines = $airlines;
}

// Process add airline form if submitted
if (isset($_POST['add_airline'])) {
    // In a real application, you would validate and save to database
    // For demo purpose, we'll just pretend it was successful
    $successMessage = "Airline added successfully!";
}

// Process edit airline form if submitted
if (isset($_POST['edit_airline'])) {
    // In a real application, you would validate and update the database
    // For demo purpose, we'll just pretend it was successful
    $successMessage = "Airline updated successfully!";
}

// Process delete airline if submitted
if (isset($_POST['delete_airline'])) {
    // In a real application, you would validate and delete from database
    // For demo purpose, we'll just pretend it was successful
    $successMessage = "Airline deleted successfully!";
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
        case 'search':
            return '<svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>';
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
    <title>Airline Management - Tickfly</title>
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
                        <a href="admin_airlines.php" class="flex items-center px-4 py-3 rounded-md transition-colors bg-blue-800 text-white">
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
                <h1 class="text-2xl font-bold mb-6">Airline Management</h1>

                <!-- Success Message -->
                <?php if (isset($successMessage)): ?>
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-6 flex justify-between items-center">
                    <span><?php echo $successMessage; ?></span>
                    <button type="button" class="close-alert">
                        <?php echo getIconSvg('close'); ?>
                    </button>
                </div>
                <?php endif; ?>

                <div class="bg-white rounded-lg shadow p-6 border border-gray-200">
                    <div class="flex flex-col md:flex-row justify-between items-center mb-6 gap-4">
                        <h2 class="text-lg font-medium">All Airlines</h2>

                        <div class="flex flex-col md:flex-row gap-3">
                            <div class="relative">
                                <form action="admin_airlines.php" method="GET" class="flex">
                                    <div class="relative">
                                        <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400">
                                            <?php echo getIconSvg('search'); ?>
                                        </span>
                                        <input 
                                            type="text" 
                                            name="search" 
                                            placeholder="Search airlines..." 
                                            value="<?php echo htmlspecialchars($searchTerm); ?>" 
                                            class="pl-10 pr-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                                        >
                                    </div>
                                    <button type="submit" class="sr-only">Search</button>
                                </form>
                            </div>

                            <button 
                                id="add-airline-btn" 
                                class="flex items-center gap-1 px-4 py-2 bg-blue-800 text-white rounded-md hover:bg-blue-900 transition"
                            >
                                <?php echo getIconSvg('plus'); ?>
                                <span>Add Airline</span>
                            </button>
                        </div>
                    </div>

                    <?php if (empty($filteredAirlines)): ?>
                    <div class="text-center py-8 text-gray-500">
                        <?php echo $searchTerm ? "No airlines found matching your search" : "No airlines found"; ?>
                    </div>
                    <?php else: ?>
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
                                <?php foreach ($filteredAirlines as $airline): ?>
                                <tr class="border-b border-gray-100">
                                    <td class="py-3"><?php echo htmlspecialchars($airline['name']); ?></td>
                                    <td class="py-3"><?php echo htmlspecialchars($airline['code']); ?></td>
                                    <td class="py-3">
                                        <span class="inline-block px-2 py-1 rounded-full text-xs <?php echo $airline['status'] === 'active' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'; ?>">
                                            <?php echo strtoupper($airline['status']); ?>
                                        </span>
                                    </td>
                                    <td class="py-3">
                                        <div class="flex gap-2">
                                            <button 
                                                class="edit-btn p-1 text-blue-600 hover:text-blue-800" 
                                                data-id="<?php echo $airline['id']; ?>"
                                                data-name="<?php echo htmlspecialchars($airline['name']); ?>"
                                                data-code="<?php echo htmlspecialchars($airline['code']); ?>"
                                                data-status="<?php echo htmlspecialchars($airline['status']); ?>"
                                            >
                                                <?php echo getIconSvg('edit'); ?>
                                            </button>
                                            <form action="admin_airlines.php" method="POST" class="inline" onsubmit="return confirm('Are you sure you want to delete this airline?');">
                                                <input type="hidden" name="delete_airline" value="1">
                                                <input type="hidden" name="airline_id" value="<?php echo $airline['id']; ?>">
                                                <button type="submit" class="p-1 text-red-600 hover:text-red-800">
                                                    <?php echo getIconSvg('trash'); ?>
                                                </button>
                                            </form>
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

    <!-- Add Airline Modal -->
    <div id="add-modal" class="fixed inset-0 z-50 hidden">
        <div class="fixed inset-0 bg-black bg-opacity-50"></div>
        <div class="flex items-center justify-center min-h-screen p-4">
            <div class="bg-white rounded-lg shadow-lg p-6 w-full max-w-md relative z-10">
                <h3 class="text-lg font-medium mb-4">Add New Airline</h3>
                
                <form action="admin_airlines.php" method="POST">
                    <input type="hidden" name="add_airline" value="1">
                    
                    <div class="space-y-4">
                        <div>
                            <label for="name" class="block text-sm font-medium text-gray-700 mb-1">Airline Name</label>
                            <input 
                                type="text" 
                                id="name" 
                                name="name" 
                                class="w-full p-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" 
                                placeholder="e.g. Garuda Indonesia"
                                required
                            >
                        </div>

                        <div>
                            <label for="code" class="block text-sm font-medium text-gray-700 mb-1">Airline Code</label>
                            <input 
                                type="text" 
                                id="code" 
                                name="code" 
                                class="w-full p-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" 
                                placeholder="e.g. GA"
                                required
                            >
                        </div>

                        <div>
                            <label for="status" class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                            <select 
                                id="status" 
                                name="status" 
                                class="w-full p-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                            >
                                <option value="active">Active</option>
                                <option value="inactive">Inactive</option>
                            </select>
                        </div>
                    </div>

                    <div class="flex justify-end gap-3 mt-6">
                        <button 
                            type="button" 
                            class="close-modal px-4 py-2 border border-gray-300 text-gray-700 rounded-md hover:bg-gray-50 transition"
                        >
                            Cancel
                        </button>

                        <button 
                            type="submit" 
                            class="px-4 py-2 bg-blue-800 text-white rounded-md hover:bg-blue-900 transition"
                        >
                            Add Airline
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Edit Airline Modal -->
    <div id="edit-modal" class="fixed inset-0 z-50 hidden">
        <div class="fixed inset-0 bg-black bg-opacity-50"></div>
        <div class="flex items-center justify-center min-h-screen p-4">
            <div class="bg-white rounded-lg shadow-lg p-6 w-full max-w-md relative z-10">
                <h3 class="text-lg font-medium mb-4">Edit Airline</h3>
                
                <form action="admin_airlines.php" method="POST">
                    <input type="hidden" name="edit_airline" value="1">
                    <input type="hidden" name="airline_id" id="edit-airline-id">
                    
                    <div class="space-y-4">
                        <div>
                            <label for="edit-name" class="block text-sm font-medium text-gray-700 mb-1">Airline Name</label>
                            <input 
                                type="text" 
                                id="edit-name" 
                                name="name" 
                                class="w-full p-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" 
                                required
                            >
                        </div>

                        <div>
                            <label for="edit-code" class="block text-sm font-medium text-gray-700 mb-1">Airline Code</label>
                            <input 
                                type="text" 
                                id="edit-code" 
                                name="code" 
                                class="w-full p-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" 
                                required
                            >
                        </div>

                        <div>
                            <label for="edit-status" class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                            <select 
                                id="edit-status" 
                                name="status" 
                                class="w-full p-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                            >
                                <option value="active">Active</option>
                                <option value="inactive">Inactive</option>
                            </select>
                        </div>
                    </div>

                    <div class="flex justify-end gap-3 mt-6">
                        <button 
                            type="button" 
                            class="close-modal px-4 py-2 border border-gray-300 text-gray-700 rounded-md hover:bg-gray-50 transition"
                        >
                            Cancel
                        </button>

                        <button 
                            type="submit" 
                            class="px-4 py-2 bg-blue-800 text-white rounded-md hover:bg-blue-900 transition"
                        >
                            Update Airline
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        // Sidebar toggle for mobile
        document.getElementById('show-sidebar').addEventListener('click', function() {
            document.getElementById('sidebar').classList.add('show');
        });

        // Modal functions
        const addModal = document.getElementById('add-modal');
        const editModal = document.getElementById('edit-modal');
        
        // Add airline modal toggle
        document.getElementById('add-airline-btn').addEventListener('click', function() {
            addModal.classList.remove('hidden');
        });
        
        // Edit airline modal toggle
        const editButtons = document.querySelectorAll('.edit-btn');
        editButtons.forEach(button => {
            button.addEventListener('click', function() {
                const id = this.getAttribute('data-id');
                const name = this.getAttribute('data-name');
                const code = this.getAttribute('data-code');
                const status = this.getAttribute('data-status');
                
                document.getElementById('edit-airline-id').value = id;
                document.getElementById('edit-name').value = name;
                document.getElementById('edit-code').value = code;
                document.getElementById('edit-status').value = status;
                
                editModal.classList.remove('hidden');
            });
        });
        
        // Close modals
        const closeButtons = document.querySelectorAll('.close-modal');
        closeButtons.forEach(button => {
            button.addEventListener('click', function() {
                addModal.classList.add('hidden');
                editModal.classList.add('hidden');
            });
        });
        
        // Close alerts
        const closeAlerts = document.querySelectorAll('.close-alert');
        closeAlerts.forEach(button => {
            button.addEventListener('click', function() {
                this.parentElement.remove();
            });
        });

        // Close modals when clicking outside
        window.addEventListener('click', function(e) {
            if (e.target.classList.contains('fixed')) {
                addModal.classList.add('hidden');
                editModal.classList.add('hidden');
            }
        });
    </script>
</body>
</html>
