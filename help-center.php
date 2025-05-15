<?php
// Initialize session
session_start();


// Check if user is logged in
$is_logged_in = isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true;
$username = $is_logged_in ? $_SESSION['username'] : '';

// User role (if applicable)
$user_role = isset($_SESSION['role']) ? $_SESSION['role'] : 'user';

// Redirect to login if not logged in
if (!$is_logged_in) {
    header("location: login.php?redirect=my-ticket.php");
    exit;
}

// Logout functionality
if (isset($_GET['logout'])) {
    // Destroy session and redirect to login page
    session_destroy();
    header("location: login.php");
    exit;
}


// Include database configuration
require_once 'config.php';
?>



<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Help Center - Tickfly</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/lucide-icons@latest/dist/umd/lucide.min.js" rel="stylesheet">
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
    </style>
    <script src="https://cdn.jsdelivr.net/npm/lucide@latest/dist/umd/lucide.min.js"></script>
</head>
<body class="font-sans">

<!-- Navbar -->
<nav class="bg-white/90 backdrop-blur-sm shadow-sm py-3 px-4 sticky top-0 z-50">
    <div class="container mx-auto flex justify-between items-center">
        <a href="index.php" class="flex items-center">
            <div class="relative h-8 w-24">
                <span class="text-xl font-bold">
                    <span class="text-gray-800">Tick</span>
                    <span class="text-blue-400">fly</span>
                </span>
            </div>
        </a>

        <div class="flex space-x-6">
            <a href="index.php" class="text-gray-700 hover:text-blue-600">Home</a>
            <a href="my-ticket.php" class="text-gray-700 hover:text-blue-600">My Ticket</a>
            <a href="cancel-refund.php" class="text-gray-700 hover:text-blue-600">Cancel & Refund</a>
            <a href="help-center.php" class="text-blue-600 hover:text-blue-700">Help Center</a>
        </div>

        <div class="flex items-center space-x-2">
            <?php if ($is_logged_in): ?>
                <span class="text-sm text-gray-600 mr-2">
                    Hello, <?php echo htmlspecialchars($_SESSION['username']); ?>
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

<!-- Main Content -->
<div class="container mx-auto px-4 py-8">
    <h1 class="text-2xl font-bold mb-6">Help Center</h1>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <!-- Left Column (FAQs + Contact) -->
        <div class="md:col-span-2">
            <!-- FAQs Section -->
            <div class="bg-white rounded-lg shadow-md p-6 border border-gray-200 mb-6">
                <h2 class="text-xl font-medium mb-4">Frequently Asked Questions</h2>

                <div class="space-y-4">
                    <!-- FAQ Item 1 -->
                    <div class="border border-gray-200 rounded-md overflow-hidden">
                        <button class="w-full flex justify-between items-center p-4 text-left bg-gray-50 hover:bg-gray-100 transition faq-toggle" data-target="faq-1">
                            <span class="font-medium">How do I cancel my booking?</span>
                            <span class="faq-icon">
                                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="chevron-down"><path d="m6 9 6 6 6-6"/></svg>
                            </span>
                        </button>
                        <div id="faq-1" class="p-4 bg-white hidden faq-content">
                            <p class="text-gray-700">You can cancel your booking by going to the "Cancel & Refund" page from the navigation menu. Select the booking you wish to cancel, provide a reason, and submit your request. Please note that cancellation fees may apply depending on how close to the departure date you are cancelling.</p>
                        </div>
                    </div>

                    <!-- FAQ Item 2 -->
                    <div class="border border-gray-200 rounded-md overflow-hidden">
                        <button class="w-full flex justify-between items-center p-4 text-left bg-gray-50 hover:bg-gray-100 transition faq-toggle" data-target="faq-2">
                            <span class="font-medium">What is the baggage allowance?</span>
                            <span class="faq-icon">
                                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="chevron-down"><path d="m6 9 6 6 6-6"/></svg>
                            </span>
                        </button>
                        <div id="faq-2" class="p-4 bg-white hidden faq-content">
                            <p class="text-gray-700">Baggage allowance varies by airline and ticket class. Generally, economy class tickets include 20kg checked baggage and 7kg cabin baggage. Business and first class tickets typically include higher allowances. You can view the specific baggage allowance for your booking in your ticket details.</p>
                        </div>
                    </div>

                    <!-- FAQ Item 3 -->
                    <div class="border border-gray-200 rounded-md overflow-hidden">
                        <button class="w-full flex justify-between items-center p-4 text-left bg-gray-50 hover:bg-gray-100 transition faq-toggle" data-target="faq-3">
                            <span class="font-medium">How can I change my flight date?</span>
                            <span class="faq-icon">
                                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="chevron-down"><path d="m6 9 6 6 6-6"/></svg>
                            </span>
                        </button>
                        <div id="faq-3" class="p-4 bg-white hidden faq-content">
                            <p class="text-gray-700">To change your flight date, go to the "My Ticket" page, find your booking, and click on the "Reschedule" button. Select a new date and follow the instructions. Please note that date change fees may apply, and the new flight might have a different price.</p>
                        </div>
                    </div>

                    <!-- FAQ Item 4 -->
                    <div class="border border-gray-200 rounded-md overflow-hidden">
                        <button class="w-full flex justify-between items-center p-4 text-left bg-gray-50 hover:bg-gray-100 transition faq-toggle" data-target="faq-4">
                            <span class="font-medium">Do I need to print my ticket?</span>
                            <span class="faq-icon">
                                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="chevron-down"><path d="m6 9 6 6 6-6"/></svg>
                            </span>
                        </button>
                        <div id="faq-4" class="p-4 bg-white hidden faq-content">
                            <p class="text-gray-700">No, you don't need to print your ticket. You can show your e-ticket on your mobile device at the check-in counter. However, some airports or countries might require a printed copy, so it's always good to check the requirements of your destination.</p>
                        </div>
                    </div>

                    <!-- FAQ Item 5 -->
                    <div class="border border-gray-200 rounded-md overflow-hidden">
                        <button class="w-full flex justify-between items-center p-4 text-left bg-gray-50 hover:bg-gray-100 transition faq-toggle" data-target="faq-5">
                            <span class="font-medium">How early should I arrive at the airport?</span>
                            <span class="faq-icon">
                                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="chevron-down"><path d="m6 9 6 6 6-6"/></svg>
                            </span>
                        </button>
                        <div id="faq-5" class="p-4 bg-white hidden faq-content">
                            <p class="text-gray-700">For domestic flights, we recommend arriving at the airport at least 2 hours before departure. For international flights, please arrive at least 3 hours before departure to allow time for check-in, security, and immigration procedures.</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Contact Us Section -->
            <div class="bg-white rounded-lg shadow-md p-6 border border-gray-200">
                <h2 class="text-xl font-medium mb-4">Contact Us</h2>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Phone Contact -->
                    <div class="p-4 border border-gray-200 rounded-md text-center">
                        <div class="mx-auto mb-3 text-blue-800 w-6 h-6">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="phone"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"/></svg>
                        </div>
                        <h3 class="font-medium mb-2">Phone</h3>
                        <p class="text-gray-600">+62 812 3456 7890</p>
                        <p class="text-sm text-gray-500 mt-1">Mon-Fri, 8am-8pm</p>
                    </div>

                    <!-- Email Contact -->
                    <div class="p-4 border border-gray-200 rounded-md text-center">
                        <div class="mx-auto mb-3 text-blue-800 w-6 h-6">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="mail"><rect width="20" height="16" x="2" y="4" rx="2"/><path d="m22 7-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7"/></svg>
                        </div>
                        <h3 class="font-medium mb-2">Email</h3>
                        <p class="text-gray-600">support@tickfly.com</p>
                        <p class="text-sm text-gray-500 mt-1">24/7 Support</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right Column (Live Chat) -->
        <div class="md:col-span-1">
            <div class="bg-white rounded-lg shadow-md border border-gray-200 overflow-hidden h-96 md:h-full flex flex-col">
                <div class="bg-blue-800 text-white p-4">
                    <h2 class="font-medium flex items-center">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="mr-2"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
                        Live Customer Support
                    </h2>
                </div>

                <div id="chat-container" class="flex-1 overflow-y-auto p-4 space-y-4">
                    <!-- Initial state: Chat not started -->
                    <div id="chat-initial" class="h-full flex flex-col items-center justify-center text-center">
                        <div class="text-blue-800 mb-4">
                            <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"/><path d="M12 17h.01"/></svg>
                        </div>
                        <h3 class="font-medium text-lg mb-2">Need Help?</h3>
                        <p class="text-gray-600 mb-4">Our customer service team is here to help you</p>
                        <button id="start-chat-btn" class="px-4 py-2 bg-blue-800 text-white rounded-md hover:bg-blue-900 transition">
                            Start Chat
                        </button>
                    </div>

                    <!-- Chat started - welcome message -->
                    <div id="chat-welcome" class="bg-blue-50 p-4 rounded-md hidden">
                        <p class="text-blue-800">
                            Hello! How can we help you today? Please describe your issue and a customer service representative
                            will assist you.
                        </p>
                    </div>

                    <!-- Chat messages will be displayed here -->
                    <div id="chat-messages" class="space-y-4">
                        <!-- Messages will be added here dynamically -->
                    </div>
                </div>

                <!-- Chat input - initially hidden -->
                <div id="chat-input-container" class="p-4 border-t border-gray-200 hidden">
                    <form id="chat-form" class="flex gap-2">
                        <input
                            type="text"
                            id="chat-message"
                            placeholder="Type your message..."
                            class="flex-1 p-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                        />
                        <button
                            type="submit"
                            class="px-4 py-2 bg-blue-800 text-white rounded-md hover:bg-blue-900 transition"
                        >
                            Send
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    // FAQ Toggle functionality
    document.addEventListener('DOMContentLoaded', function() {
        const faqToggles = document.querySelectorAll('.faq-toggle');
        
        faqToggles.forEach(toggle => {
            toggle.addEventListener('click', function() {
                const targetId = this.getAttribute('data-target');
                const targetContent = document.getElementById(targetId);
                
                // Toggle visibility
                if (targetContent.classList.contains('hidden')) {
                    targetContent.classList.remove('hidden');
                    // Change icon to chevron-up
                    this.querySelector('.faq-icon').innerHTML = '<svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="chevron-up"><path d="m18 15-6-6-6 6"/></svg>';
                } else {
                    targetContent.classList.add('hidden');
                    // Change icon back to chevron-down
                    this.querySelector('.faq-icon').innerHTML = '<svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="chevron-down"><path d="m6 9 6 6 6-6"/></svg>';
                }
            });
        });

        // Chat functionality
        const startChatBtn = document.getElementById('start-chat-btn');
        const chatInitial = document.getElementById('chat-initial');
        const chatWelcome = document.getElementById('chat-welcome');
        const chatInputContainer = document.getElementById('chat-input-container');
        const chatForm = document.getElementById('chat-form');
        const chatMessage = document.getElementById('chat-message');
        const chatMessages = document.getElementById('chat-messages');

        // Start chat button click
        startChatBtn.addEventListener('click', function() {
            chatInitial.classList.add('hidden');
            chatWelcome.classList.remove('hidden');
            chatInputContainer.classList.remove('hidden');
        });

        // Send message
        chatForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const messageText = chatMessage.value.trim();
            if (messageText === '') return;
            
            // Add user message
            addMessage('user', messageText);
            
            // Clear input
            chatMessage.value = '';
            
            // Simulate CS response
            setTimeout(() => {
                addMessage('cs', 'Thank you for your message. A customer service representative will get back to you shortly.');
            }, 1000);
        });

        // Function to add a message to the chat
        function addMessage(sender, text) {
            chatWelcome.classList.add('hidden');
            
            const messageDiv = document.createElement('div');
            messageDiv.className = `p-3 rounded-lg max-w-[80%] ${
                sender === 'user' ? 'bg-blue-100 text-blue-800 ml-auto' : 'bg-gray-100 text-gray-800'
            }`;
            messageDiv.textContent = text;
            
            chatMessages.appendChild(messageDiv);
            
            // Scroll to bottom
            const chatContainer = document.getElementById('chat-container');
            chatContainer.scrollTop = chatContainer.scrollHeight;
        }
    });
</script>

</body>
</html>