 const BASE_PATH = window.location.pathname.split('/public')[0];
        const API_URL = BASE_PATH + 'http://localhost/ChatApplication/Backend/api';
        
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