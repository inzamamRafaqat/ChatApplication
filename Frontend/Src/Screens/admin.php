<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel - Chat System</title>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/tailwindcss/2.2.19/tailwind.min.js"></script>
</head>
<body class="bg-gray-100">
    <div class="min-h-screen">
        <!-- Header -->
        <div class="bg-white shadow">
            <div class="max-w-7xl mx-auto px-4 py-4 flex justify-between items-center">
                <h1 class="text-2xl font-bold">Admin Panel</h1>
                <div class="flex space-x-4">
                    <a href="index.html" class="text-blue-500 hover:underline">← Back to Chat</a>
                    <button onclick="logout()" class="text-red-500 hover:underline">Logout</button>
                </div>
            </div>
        </div>

        <!-- Stats -->
        <div class="max-w-7xl mx-auto px-4 py-6">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
                <div class="bg-white p-6 rounded-lg shadow">
                    <div class="text-gray-500 text-sm">Total Users</div>
                    <div class="text-3xl font-bold" id="totalUsers">0</div>
                </div>
                <div class="bg-white p-6 rounded-lg shadow">
                    <div class="text-gray-500 text-sm">Online Users</div>
                    <div class="text-3xl font-bold text-green-500" id="onlineUsers">0</div>
                </div>
                <div class="bg-white p-6 rounded-lg shadow">
                    <div class="text-gray-500 text-sm">Total Channels</div>
                    <div class="text-3xl font-bold" id="totalChannels">0</div>
                </div>
                <div class="bg-white p-6 rounded-lg shadow">
                    <div class="text-gray-500 text-sm">Total Messages</div>
                    <div class="text-3xl font-bold" id="totalMessages">0</div>
                </div>
            </div>

            <!-- Tabs -->
            <div class="bg-white rounded-lg shadow">
                <div class="border-b">
                    <nav class="flex">
                        <button onclick="showTab('users')" id="usersTab" 
                                class="px-6 py-3 font-medium border-b-2 border-blue-500">
                            Users
                        </button>
                        <button onclick="showTab('channels')" id="channelsTab" 
                                class="px-6 py-3 font-medium text-gray-500">
                            Channels
                        </button>
                        <button onclick="showTab('messages')" id="messagesTab" 
                                class="px-6 py-3 font-medium text-gray-500">
                            Recent Messages
                        </button>
                    </nav>
                </div>

                <!-- Users Tab -->
                <div id="usersContent" class="p-6">
                    <div class="mb-4">
                        <input type="text" id="userSearch" placeholder="Search users..." 
                               onkeyup="filterUsers(this.value)"
                               class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div class="overflow-x-auto">
                        <table class="min-w-full">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">User</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Email</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Role</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                                </tr>
                            </thead>
                            <tbody id="usersTable" class="bg-white divide-y divide-gray-200"></tbody>
                        </table>
                    </div>
                </div>

                <!-- Channels Tab -->
                <div id="channelsContent" class="hidden p-6">
                    <div class="overflow-x-auto">
                        <table class="min-w-full">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Channel Name</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Type</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Members</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Created</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                                </tr>
                            </thead>
                            <tbody id="channelsTable" class="bg-white divide-y divide-gray-200"></tbody>
                        </table>
                    </div>
                </div>

                <!-- Messages Tab -->
                <div id="messagesContent" class="hidden p-6">
                    <div class="space-y-4" id="recentMessages"></div>
                </div>
            </div>
        </div>
    </div>

    <script src="../Scripts/Admin.js">
       
    </script>
</body>
</html>