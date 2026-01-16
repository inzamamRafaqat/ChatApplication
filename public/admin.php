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

    <script>
        const BASE_PATH = window.location.pathname.split('/public')[0];
        const API_URL = BASE_PATH + 'http://localhost/ChatApplication/api';
        
        let token = localStorage.getItem('token');
        let user = JSON.parse(localStorage.getItem('user') || '{}');
        let allUsers = [];
        let allChannels = [];

        if (!token) window.location.href = 'login.html';

        // Check if admin
        checkAdmin();

        // Load data
        loadStats();
        loadUsers();
        loadChannels();

        async function checkAdmin() {
            try {
                const response = await fetch(`${API_URL}/auth.php?action=me`, {
                    headers: { 'Authorization': `Bearer ${token}` }
                });
                const data = await response.json();
                if (data.success && !data.user.is_admin) {
                    alert('Access denied. Admin only.');
                    window.location.href = 'index.html';
                }
            } catch (error) {
                window.location.href = 'login.html';
            }
        }

        async function loadStats() {
            try {
                // Get users
                const usersRes = await fetch(`${API_URL}/users.php`, {
                    headers: { 'Authorization': `Bearer ${token}` }
                });
                const usersData = await usersRes.json();
                
                // Get channels
                const channelsRes = await fetch(`${API_URL}/channels.php`, {
                    headers: { 'Authorization': `Bearer ${token}` }
                });
                const channelsData = await channelsRes.json();

                if (usersData.success) {
                    document.getElementById('totalUsers').textContent = usersData.users.length;
                    const online = usersData.users.filter(u => u.status === 'online').length;
                    document.getElementById('onlineUsers').textContent = online;
                }

                if (channelsData.success) {
                    document.getElementById('totalChannels').textContent = channelsData.channels.length;
                }
            } catch (error) {
                console.error('Error loading stats:', error);
            }
        }

        async function loadUsers() {
            try {
                const response = await fetch(`${API_URL}/users.php`, {
                    headers: { 'Authorization': `Bearer ${token}` }
                });
                const data = await response.json();
                
                if (data.success) {
                    allUsers = data.users;
                    displayUsers(allUsers);
                }
            } catch (error) {
                console.error('Error loading users:', error);
            }
        }

        function displayUsers(users) {
            const table = document.getElementById('usersTable');
            table.innerHTML = users.map(u => `
                <tr>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="flex items-center">
                            <img src="${u.avatar || `https://ui-avatars.com/api/?name=${u.username}`}" 
                                 class="h-10 w-10 rounded-full mr-3">
                            <div class="font-medium">${u.username}</div>
                        </div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">${u.email}</td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <span class="px-2 py-1 text-xs rounded-full ${
                            u.status === 'online' ? 'bg-green-100 text-green-800' : 
                            u.status === 'away' ? 'bg-yellow-100 text-yellow-800' : 
                            'bg-gray-100 text-gray-800'
                        }">
                            ${u.status || 'offline'}
                        </span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm">
                        ${u.is_admin ? '👑 Admin' : 'User'}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm space-x-2">
                        ${!u.is_admin ? `<button onclick="makeAdmin('${u.id}')" 
                                         class="text-blue-600 hover:underline">Make Admin</button>` : ''}
                        <button onclick="viewUser('${u.id}')" 
                                class="text-green-600 hover:underline">View</button>
                    </td>
                </tr>
            `).join('');
        }

        function filterUsers(query) {
            const filtered = allUsers.filter(u => 
                u.username.toLowerCase().includes(query.toLowerCase()) ||
                u.email.toLowerCase().includes(query.toLowerCase())
            );
            displayUsers(filtered);
        }

        async function loadChannels() {
            try {
                const response = await fetch(`${API_URL}/channels.php`, {
                    headers: { 'Authorization': `Bearer ${token}` }
                });
                const data = await response.json();
                
                if (data.success) {
                    allChannels = data.channels;
                    displayChannels(allChannels);
                }
            } catch (error) {
                console.error('Error loading channels:', error);
            }
        }

        function displayChannels(channels) {
            const table = document.getElementById('channelsTable');
            table.innerHTML = channels.map(c => `
                <tr>
                    <td class="px-6 py-4 whitespace-nowrap font-medium">${c.name}</td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        ${c.is_private ? '🔒 Private' : '🌐 Public'}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">${c.members.length} members</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                        ${new Date(c.created_at).toLocaleDateString()}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm">
                        <button onclick="deleteChannel('${c.id}')" 
                                class="text-red-600 hover:underline">Delete</button>
                    </td>
                </tr>
            `).join('');
        }

        async function makeAdmin(userId) {
            if (!confirm('Make this user an admin?')) return;
            
            try {
                const response = await fetch(`${API_URL}/users.php?action=make_admin`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Authorization': `Bearer ${token}`
                    },
                    body: JSON.stringify({ user_id: userId })
                });
                
                const data = await response.json();
                if (data.success) {
                    alert('User promoted to admin!');
                    loadUsers();
                } else {
                    alert(data.message);
                }
            } catch (error) {
                alert('Error: ' + error.message);
            }
        }

        async function deleteChannel(channelId) {
            if (!confirm('Delete this channel? This cannot be undone.')) return;
            
            try {
                const response = await fetch(`${API_URL}/channels.php?id=${channelId}`, {
                    method: 'DELETE',
                    headers: { 'Authorization': `Bearer ${token}` }
                });
                
                const data = await response.json();
                if (data.success) {
                    alert('Channel deleted!');
                    loadChannels();
                } else {
                    alert(data.message);
                }
            } catch (error) {
                alert('Error: ' + error.message);
            }
        }

        function viewUser(userId) {
            alert('User details view - Coming soon!');
        }

        function showTab(tab) {
            // Hide all
            document.getElementById('usersContent').classList.add('hidden');
            document.getElementById('channelsContent').classList.add('hidden');
            document.getElementById('messagesContent').classList.add('hidden');
            
            // Remove active styles
            document.getElementById('usersTab').classList.remove('border-blue-500');
            document.getElementById('usersTab').classList.add('text-gray-500');
            document.getElementById('channelsTab').classList.remove('border-blue-500');
            document.getElementById('channelsTab').classList.add('text-gray-500');
            document.getElementById('messagesTab').classList.remove('border-blue-500');
            document.getElementById('messagesTab').classList.add('text-gray-500');
            
            // Show selected
            document.getElementById(tab + 'Content').classList.remove('hidden');
            document.getElementById(tab + 'Tab').classList.add('border-blue-500');
            document.getElementById(tab + 'Tab').classList.remove('text-gray-500');
        }

        function logout() {
            localStorage.clear();
            window.location.href = 'login.php';
        }

        // Auto-refresh stats
        setInterval(loadStats, 10000);
    </script>
</body>
</html>