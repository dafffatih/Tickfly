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

// Mock chat data
$mockChats = [
    [
        "id" => 1,
        "user" => [
            "id" => 101,
            "name" => "John Doe",
            "email" => "john@example.com",
            "avatar" => "/placeholder.svg?height=40&width=40",
        ],
        "lastMessage" => [
            "text" => "I need help with my booking",
            "timestamp" => "10:30 AM",
            "isRead" => true,
        ],
        "unreadCount" => 0,
    ],
    [
        "id" => 2,
        "user" => [
            "id" => 102,
            "name" => "Jane Smith",
            "email" => "jane@example.com",
            "avatar" => "/placeholder.svg?height=40&width=40",
        ],
        "lastMessage" => [
            "text" => "When will my refund be processed?",
            "timestamp" => "Yesterday",
            "isRead" => false,
        ],
        "unreadCount" => 3,
    ],
    [
        "id" => 3,
        "user" => [
            "id" => 103,
            "name" => "Bob Johnson",
            "email" => "bob@example.com",
            "avatar" => "/placeholder.svg?height=40&width=40",
        ],
        "lastMessage" => [
            "text" => "Thanks for your help!",
            "timestamp" => "Yesterday",
            "isRead" => true,
        ],
        "unreadCount" => 0,
    ],
    [
        "id" => 4,
        "user" => [
            "id" => 104,
            "name" => "Alice Brown",
            "email" => "alice@example.com",
            "avatar" => "/placeholder.svg?height=40&width=40",
        ],
        "lastMessage" => [
            "text" => "I've sent the payment proof",
            "timestamp" => "Monday",
            "isRead" => true,
        ],
        "unreadCount" => 0,
    ],
];

// Mock messages for a specific chat
$mockMessages = [
    [
        "id" => 1,
        "senderId" => 101,
        "text" => "Hello, I need help with my booking",
        "timestamp" => "10:25 AM",
        "isRead" => true,
    ],
    [
        "id" => 2,
        "senderId" => "cs",
        "text" => "Hi John, I'd be happy to help. Could you please provide your booking reference number?",
        "timestamp" => "10:27 AM",
        "isRead" => true,
    ],
    [
        "id" => 3,
        "senderId" => 101,
        "text" => "Sure, it's TF123456",
        "timestamp" => "10:28 AM",
        "isRead" => true,
    ],
    [
        "id" => 4,
        "senderId" => "cs",
        "text" => "Thank you. I can see your booking for Jakarta to Bali on June 15th. What do you need help with?",
        "timestamp" => "10:29 AM",
        "isRead" => true,
    ],
    [
        "id" => 5,
        "senderId" => 101,
        "text" => "I need to change my seat selection",
        "timestamp" => "10:30 AM",
        "isRead" => true,
    ],
];

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
    <title>Customer Service Chats - Tickfly</title>
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
        .message-user {
            background-color: #ffffff;
            border-radius: 0.5rem;
            border-bottom-left-radius: 0;
        }
        .message-cs {
            background-color: #2563eb;
            color: white;
            border-radius: 0.5rem;
            border-bottom-right-radius: 0;
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
                        <a href="cs_chats.php" class="sidebar-link active flex items-center px-4 py-3 rounded-md transition-colors">
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

            <main class="flex-1 overflow-hidden">
                <div class="flex h-full">
                    <!-- Chat list -->
                    <div class="w-80 border-r border-gray-200 bg-white flex flex-col">
                        <div class="p-4 border-b border-gray-200">
                            <div class="relative">
                                <i data-lucide="search" class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 w-4 h-4"></i>
                                <input 
                                    type="text" 
                                    id="searchInput"
                                    placeholder="Search users..." 
                                    class="pl-10 pr-4 py-2 w-full border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                                >
                            </div>
                        </div>

                        <div class="flex-1 overflow-y-auto" id="chatList">
                            <?php foreach ($mockChats as $chat): ?>
                            <div 
                                data-chat-id="<?php echo $chat['id']; ?>"
                                data-user-name="<?php echo $chat['user']['name']; ?>"
                                data-user-email="<?php echo $chat['user']['email']; ?>"
                                data-user-avatar="<?php echo $chat['user']['avatar']; ?>"
                                class="chat-item p-4 border-b border-gray-100 hover:bg-gray-50 cursor-pointer"
                            >
                                <div class="flex items-center">
                                    <div class="relative">
                                        <img 
                                            src="<?php echo $chat['user']['avatar'] ?: '/placeholder.svg'; ?>" 
                                            alt="<?php echo $chat['user']['name']; ?>" 
                                            class="w-10 h-10 rounded-full object-cover"
                                        >
                                        <?php if ($chat['unreadCount'] > 0): ?>
                                        <div class="absolute -top-1 -right-1 w-5 h-5 bg-red-500 text-white rounded-full flex items-center justify-center text-xs">
                                            <?php echo $chat['unreadCount']; ?>
                                        </div>
                                        <?php endif; ?>
                                    </div>
                                    <div class="ml-3 flex-1">
                                        <div class="flex justify-between items-center">
                                            <p class="font-medium"><?php echo $chat['user']['name']; ?></p>
                                            <p class="text-xs text-gray-500"><?php echo $chat['lastMessage']['timestamp']; ?></p>
                                        </div>
                                        <div class="flex justify-between items-center">
                                            <p class="text-sm truncate <?php echo $chat['unreadCount'] > 0 ? 'font-medium' : 'text-gray-500'; ?>">
                                                <?php echo $chat['lastMessage']['text']; ?>
                                            </p>
                                            <?php if (!$chat['lastMessage']['isRead']): ?>
                                            <div class="w-2 h-2 bg-blue-600 rounded-full"></div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <!-- Chat area -->
                    <div class="flex-1 flex flex-col bg-gray-50">
                        <!-- Default state (no chat selected) -->
                        <div id="noChatSelected" class="flex-1 flex items-center justify-center">
                            <div class="text-center">
                                <i data-lucide="message-square" class="mx-auto text-gray-400 mb-4 w-12 h-12"></i>
                                <p class="text-gray-500">Select a chat to start messaging</p>
                            </div>
                        </div>

                        <!-- Chat view (hidden initially) -->
                        <div id="chatView" class="hidden flex-1 flex flex-col">
                            <!-- Chat header -->
                            <div class="p-4 border-b border-gray-200 bg-white flex items-center">
                                <img id="selectedChatAvatar" src="/placeholder.svg" alt="" class="w-10 h-10 rounded-full object-cover">
                                <div class="ml-3">
                                    <p id="selectedChatName" class="font-medium"></p>
                                    <p id="selectedChatEmail" class="text-xs text-gray-500"></p>
                                </div>
                            </div>

                            <!-- Messages -->
                            <div id="messagesContainer" class="flex-1 overflow-y-auto p-4">
                                <!-- Messages will be populated by JavaScript -->
                            </div>

                            <!-- Message input -->
                            <div class="p-4 border-t border-gray-200 bg-white">
                                <div id="imagePreviewContainer" class="mb-2 hidden relative inline-block">
                                    <img id="imagePreview" src="" alt="Preview" class="h-20 rounded">
                                    <button
                                        id="removeImageBtn"
                                        class="absolute top-1 right-1 bg-red-600 text-white rounded-full p-1 w-5 h-5 flex items-center justify-center text-xs"
                                    >
                                        &times;
                                    </button>
                                </div>
                                <form id="messageForm" class="flex items-center">
                                    <button
                                        type="button"
                                        id="attachImageBtn"
                                        class="p-2 text-gray-500 hover:text-gray-700 focus:outline-none"
                                        title="Attach image"
                                    >
                                        <i data-lucide="image" class="w-5 h-5"></i>
                                        <input
                                            type="file"
                                            id="imageInput"
                                            accept="image/*"
                                            class="hidden"
                                        >
                                    </button>
                                    <input
                                        type="text"
                                        id="messageInput"
                                        placeholder="Type a message..."
                                        class="flex-1 mx-2 p-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                                    >
                                    <button
                                        type="submit"
                                        id="sendMessageBtn"
                                        class="p-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 focus:outline-none"
                                        disabled
                                    >
                                        <i data-lucide="send" class="w-5 h-5"></i>
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
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

        // Chat functionality
        document.addEventListener('DOMContentLoaded', function() {
            // Elements
            const chatItems = document.querySelectorAll('.chat-item');
            const noChatSelected = document.getElementById('noChatSelected');
            const chatView = document.getElementById('chatView');
            const selectedChatName = document.getElementById('selectedChatName');
            const selectedChatEmail = document.getElementById('selectedChatEmail');
            const selectedChatAvatar = document.getElementById('selectedChatAvatar');
            const messagesContainer = document.getElementById('messagesContainer');
            const messageForm = document.getElementById('messageForm');
            const messageInput = document.getElementById('messageInput');
            const sendMessageBtn = document.getElementById('sendMessageBtn');
            const searchInput = document.getElementById('searchInput');
            const attachImageBtn = document.getElementById('attachImageBtn');
            const imageInput = document.getElementById('imageInput');
            const imagePreviewContainer = document.getElementById('imagePreviewContainer');
            const imagePreview = document.getElementById('imagePreview');
            const removeImageBtn = document.getElementById('removeImageBtn');

            // Messages data (from PHP)
            const mockMessages = <?php echo json_encode($mockMessages); ?>;
            let currentSelectedChatId = null;
            let imageFile = null;

            // Handle chat item selection
            chatItems.forEach(item => {
                item.addEventListener('click', function() {
                    // Update active chat
                    chatItems.forEach(ci => ci.classList.remove('bg-blue-50'));
                    this.classList.add('bg-blue-50');

                    // Get chat data
                    const chatId = this.dataset.chatId;
                    const userName = this.dataset.userName;
                    const userEmail = this.dataset.userEmail;
                    const userAvatar = this.dataset.userAvatar;

                    // Update selected chat info
                    selectedChatName.textContent = userName;
                    selectedChatEmail.textContent = userEmail;
                    selectedChatAvatar.src = userAvatar || '/placeholder.svg';
                    selectedChatAvatar.alt = userName;

                    // Show chat view
                    noChatSelected.classList.add('hidden');
                    chatView.classList.remove('hidden');
                    chatView.classList.add('flex');

                    // Load messages
                    loadMessages(chatId);
                    currentSelectedChatId = chatId;

                    // Remove unread indicator if present
                    const unreadIndicator = this.querySelector('.bg-red-500');
                    if (unreadIndicator) {
                        unreadIndicator.remove();
                    }
                });
            });

            // Load messages function
            function loadMessages(chatId) {
                messagesContainer.innerHTML = '';
                
                mockMessages.forEach(message => {
                    const isCs = message.senderId === 'cs';
                    const messageElement = document.createElement('div');
                    messageElement.className = `mb-4 flex ${isCs ? 'justify-end' : 'justify-start'}`;
                    
                    messageElement.innerHTML = `
                        <div class="${isCs ? 'message-cs' : 'message-user'} max-w-xs md:max-w-md lg:max-w-lg xl:max-w-xl p-3">
                            <p>${message.text}</p>
                            <p class="text-xs mt-1 text-right ${isCs ? 'text-blue-100' : 'text-gray-500'}">${message.timestamp}</p>
                        </div>
                    `;
                    
                    messagesContainer.appendChild(messageElement);
                });
                
                // Scroll to bottom
                messagesContainer.scrollTop = messagesContainer.scrollHeight;
            }

            // Handle send message
            messageForm.addEventListener('submit', function(e) {
                e.preventDefault();
                
                const messageText = messageInput.value.trim();
                if ((!messageText && !imageFile) || !currentSelectedChatId) return;
                
                // Create new message
                const now = new Date();
                const timeString = now.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
                
                const messageElement = document.createElement('div');
                messageElement.className = 'mb-4 flex justify-end';
                
                let messageContent = '';
                if (messageText) {
                    messageContent += `<p>${messageText}</p>`;
                }
                if (imageFile) {
                    messageContent += `
                        <div class="mt-2">
                            <img src="${imageFile}" alt="Attachment" class="max-w-full rounded-md">
                        </div>
                    `;
                }
                
                messageElement.innerHTML = `
                    <div class="message-cs max-w-xs md:max-w-md lg:max-w-lg xl:max-w-xl p-3">
                        ${messageContent}
                        <p class="text-xs mt-1 text-right text-blue-100">${timeString}</p>
                    </div>
                `;
                
                messagesContainer.appendChild(messageElement);
                
                // Update the chat in the list
                const chatItem = document.querySelector(`.chat-item[data-chat-id="${currentSelectedChatId}"]`);
                const lastMessageElement = chatItem.querySelector('.text-sm');
                lastMessageElement.textContent = messageText || 'Sent an image';
                
                const timestampElement = chatItem.querySelector('.text-xs.text-gray-500');
                timestampElement.textContent = 'Just now';
                
                // Clear input
                messageInput.value = '';
                imageFile = null;
                imagePreviewContainer.classList.add('hidden');
                sendMessageBtn.disabled = true;
                
                // Scroll to bottom
                messagesContainer.scrollTop = messagesContainer.scrollHeight;
            });
            
            // Enable/disable send button based on input
            messageInput.addEventListener('input', function() {
                sendMessageBtn.disabled = !this.value.trim() && !imageFile;
            });
            
            // Search functionality
            searchInput.addEventListener('input', function() {
                const searchTerm = this.value.toLowerCase();
                
                chatItems.forEach(item => {
                    const userName = item.dataset.userName.toLowerCase();
                    const userEmail = item.dataset.userEmail.toLowerCase();
                    
                    if (userName.includes(searchTerm) || userEmail.includes(searchTerm)) {
                        item.style.display = 'block';
                    } else {
                        item.style.display = 'none';
                    }
                });
            });
            
            // Image upload functionality
            attachImageBtn.addEventListener('click', function() {
                imageInput.click();
            });
            
            imageInput.addEventListener('change', function(e) {
                const file = e.target.files[0];
                if (file) {
                    const reader = new FileReader();
                    reader.onloadend = function() {
                        imageFile = reader.result;
                        imagePreview.src = imageFile;
                        imagePreviewContainer.classList.remove('hidden');
                        sendMessageBtn.disabled = false;
                    };
                    reader.readAsDataURL(file);
                }
            });
            
            removeImageBtn.addEventListener('click', function() {
                imageFile = null;
                imagePreviewContainer.classList.add('hidden');
                imageInput.value = '';
                sendMessageBtn.disabled = !messageInput.value.trim();
            });
        });
    </script>
</body>
</html>