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

// Define destinations
$destinations = [
    [
        'id' => 1,
        'name' => 'Cimanggung, Jawa Barat',
        'price' => 'Rp3.500.000,00',
        'image' => 'images/cimanggung.jpg'
    ],
    [
        'id' => 2,
        'name' => 'Rajabasa, Bandar Lampung',
        'price' => 'Rp1.900.000,00',
        'image' => 'images/rajabasa.jpg'
    ],
    [
        'id' => 3,
        'name' => 'Kemiling, Bandar Lampung',
        'price' => 'Rp3.000.000,00',
        'image' => 'images/kemiling.jpg'
    ]
];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tickfly - Book Your Flight Tickets</title>
    <meta name="description" content="Find and book the best flight deals with Tickfly">
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
        .search-form-container {
            box-shadow: 0px 4px 12px rgba(0, 0, 0, 0.1);
            border-radius: 8px;
            background-color: white;
        }
        .hero-banner {
            background-image: url('airplane2.jpg');
            background-size: cover;
            background-position: center;
            position: relative;
        }
        .hero-banner::before {
            content: "";
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            z-index: 1;
        }
        .hero-content {
            position: relative;
            z-index: 2;
        }
    </style>
</head>
<body class="font-sans min-h-screen bg-gray-50">

<!-- Navbar -->
<nav class="bg-white shadow-sm py-3 px-4 sticky top-0 z-50">
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

<!-- Hero Banner Section -->
<div class="hero-banner py-16 text-center text-white">
    <div class="hero-content">
        <h1 class="text-4xl font-bold mb-4">Embark On Your Journey To Secure<br>The Ideal Getaway.</h1>
        <p class="text-sm max-w-2xl mx-auto">
            Tempora Facere Doloribus Id Aut. Ea Maiores Esse Accusantium Laboriosam, Quos 
            Commodi Non Assumenda Quam Illum. Id Omnis Saepe Corrupti Incidunt Qui Sed Debitis 
            Neque Minus Ducimus Ut Ratione Iste Quod Commodi Et.
        </p>
        <!-- Play button removed as requested -->
    </div>
</div>

<main>
    <div class="w-full p-8 bg-gray-50">
        <div class="mx-auto max-w-7xl">
                        <!-- Flight Search Form -->
            <div class="search-form-container px-8 py-6 mb-12 -mt-16 relative z-10">
                <div class="flex items-center mb-4">
                    <div class="bg-blue-100 text-blue-800 px-4 py-2 rounded-lg">
                        <span class="flex items-center">
                            ✈️ <span class="ml-2">Flights</span>
                        </span>
                    </div>
                </div>

                <form action="search.php" method="GET">
                    <div class="grid grid-cols-1 md:grid-cols-5 gap-4">
                        <div class="md:col-span-1">
                            <label class="block text-sm font-medium text-gray-700 mb-1">From</label>
                            <input 
                                type="text" 
                                name="from" 
                                placeholder="Jakarta" 
                                class="w-full p-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                                required
                            >
                        </div>
                        
                        <div class="md:col-span-1">
                            <label class="block text-sm font-medium text-gray-700 mb-1">To</label>
                            <input 
                                type="text" 
                                name="to" 
                                placeholder="Lampung" 
                                class="w-full p-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                                required
                            >
                        </div>
                        
                        <div class="md:col-span-1">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Depart</label>
                            <input 
                                type="date" 
                                name="depart_date" 
                                class="w-full p-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                                required
                            >
                        </div>
                        
                        <div class="md:col-span-1">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Travelers</label>
                            <select 
                                name="travelers" 
                                class="w-full p-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                            >
                                <option value="1">1 Adult</option>
                                <option value="2">2 Adults</option>
                                <option selected value="3">3 Adults</option>
                                <option value="4">4 Adults</option>
                            </select>
                        </div>
                        
                        <div class="md:col-span-1">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Cabin Class</label>
                            <select 
                                name="cabin_class" 
                                class="w-full p-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                            >
                                <option value="economy">Economy</option>
                                <option value="premium_economy">Premium Economy</option>
                                <option selected value="business">Business</option>
                                <option value="first">First Class</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="mt-4 text-right">
                        <button type="submit" class="px-6 py-2 bg-blue-800 text-white rounded-md hover:bg-blue-900 transition">
                            Search Flights
                        </button>
                    </div>
                </form>
            </div>

            <!-- Top Destinations -->
            <div class="mt-12">
                <h2 class="text-2xl font-bold mb-6">Top Destinations</h2>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <?php foreach ($destinations as $destination): ?>
                        <a href="search.php?to=<?php echo urlencode($destination['name']); ?>" class="block group">
                            <div class="bg-white rounded-lg overflow-hidden shadow-md transition transform group-hover:shadow-lg group-hover:-translate-y-1">
                                <div class="relative h-48 w-full bg-gray-200">
                                    <img 
                                        src="<?php echo $destination['image']; ?>" 
                                        alt="<?php echo $destination['name']; ?>" 
                                        class="h-full w-full object-cover"
                                        onerror="this.src='airplane.jpg'"
                                    >
                                </div>
                                <div class="p-4">
                                    <h3 class="font-medium text-lg"><?php echo $destination['name']; ?></h3>
                                    <p class="text-blue-800 font-bold"><?php echo $destination['price']; ?></p>
                                </div>
                            </div>
                        </a>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
</main>

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

</body>
</html>