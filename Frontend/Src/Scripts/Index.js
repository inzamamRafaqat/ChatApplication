
        const API_URL = 'http://localhost/ChatApplication/Backend/api';
        let token = localStorage.getItem('token');
        let user = JSON.parse(localStorage.getItem('user') || '{}');
        let currentChannel = null;
        let selectedFile = null;

        // Check authentication
        if (!token) {
            window.location.href = 'login.php';
        }

        // Initialize
        document.getElementById('username').textContent = user.username;
        document.getElementById('userAvatar').src = user.avatar || `https://ui-avatars.com/api/?name=${encodeURIComponent(user.username)}`;
        loadChannels();

        // Logout
        function logout() {
            localStorage.removeItem('token');
            localStorage.removeItem('user');
            window.location.href = 'login.php';
        }

        // Load channels
       async function loadChannels() {
    try {
        const response = await fetch(`${API_URL}/channels.php`, {
            headers: { 'Authorization': `Bearer ${token}` }
        });
        
        // Check if response is OK
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        
        // Get raw text first to debug
        const text = await response.text();
        console.log('Raw response:', text); // Debug log
        
        // Try to parse JSON
        const data = JSON.parse(text);
        
        if (data.success) {
            const channelList = document.getElementById('channelList');
            channelList.innerHTML = data.channels.map(channel => `
                <div onclick="selectChannel('${channel.id}')" 
                     class="p-2 rounded hover:bg-gray-100 cursor-pointer ${currentChannel?.id === channel.id ? 'bg-blue-100' : ''}">
                    <div class="font-semibold">${channel.is_private ? '🔒' : '#'} ${channel.name}</div>
                </div>
            `).join('');
        } else {
            console.error('API Error:', data.message);
            alert('Error: ' + data.message);
        }
    } catch (error) {
        console.error('Error loading channels:', error);
        alert('Failed to load channels. Check console for details.');
    }
}

        // Select channel
        async function selectChannel(channelId) {
            try {
                const response = await fetch(`${API_URL}/channels.php?id=${channelId}`, {
                    headers: { 'Authorization': `Bearer ${token}` }
                });
                const data = await response.json();
                
                if (data.success) {
                    currentChannel = data.channel;
                    document.getElementById('currentChannelName').textContent = data.channel.name;
                    document.getElementById('channelInfo').textContent = 
                        data.channel.is_private ? 'Private Channel' : 'Public Channel';
                    
                    if (data.channel.created_by === user.id) {
                        document.getElementById('channelActions').classList.remove('hidden');
                    } else {
                        document.getElementById('channelActions').classList.add('hidden');
                    }
                    
                    loadMessages(channelId);
                    loadChannels();
                }
            } catch (error) {
                console.error('Error selecting channel:', error);
            }
        }

  // Updated loadMessages function to handle GridFS URLs
async function loadMessages(channelId) {
    try {
        const response = await fetch(`${API_URL}/messages.php?channel_id=${channelId}`, {
            headers: { 'Authorization': `Bearer ${token}` }
        });
        const data = await response.json();
        
        if (data.success) {
            const messagesArea = document.getElementById('messagesArea');
            messagesArea.innerHTML = data.messages.map(msg => {
                const hasFile = msg.file_url && msg.file_url !== 'null' && msg.file_url !== '';
                
                // For GridFS files, the URL will be like: /ChatApplication/Backend/api/file.php?id=...
                // Extract file type from the URL or message metadata
                let fileExt = '';
                let isImage = false;
                let isDocument = false;
                
                if (hasFile) {
                    // Use file_type from message metadata if available
                    if (msg.file_type) {
                        isImage = msg.file_type === 'image';
                        isDocument = msg.file_type === 'document';
                    } else {
                        // Fallback to extension-based detection
                        fileExt = msg.file_url.split('.').pop().toLowerCase();
                        isImage = ['jpg', 'jpeg', 'png', 'gif', 'webp'].includes(fileExt);
                        isDocument = ['pdf', 'doc', 'docx', 'txt', 'xls', 'xlsx'].includes(fileExt);
                    }
                    
                    // Get filename from metadata or URL
                    const displayFileName = msg.file_name || msg.file_url.split('/').pop();
                }
                
                let fileContent = '';
                if (hasFile) {
                    // Make the file URL absolute
                    const fullFileUrl = msg.file_url.startsWith('http') 
                        ? msg.file_url 
                        : `http://localhost${msg.file_url}`;
                    
                    if (isImage) {
                        // Show image preview
                        fileContent = `
                            <div class="mb-2 rounded overflow-hidden">
                                <a href="${fullFileUrl}" target="_blank">
                                    <img src="${fullFileUrl}" alt="Image" 
                                         class="max-w-full max-h-64 rounded cursor-pointer hover:opacity-90"
                                         onerror="this.onerror=null; this.src='data:image/svg+xml,<svg xmlns=%22http://www.w3.org/2000/svg%22 width=%22200%22 height=%22200%22><text x=%2250%25%22 y=%2250%25%22 text-anchor=%22middle%22>Image Error</text></svg>';"
                                         onclick="openImageViewer('${fullFileUrl}'); event.preventDefault();">
                                </a>
                            </div>
                        `;
                    } else if (isDocument || !isImage) {
                        // Show document download button
                        const displayFileName = msg.file_name || msg.file_url.split('/').pop();
                        const fileIcon = fileExt === 'pdf' ? '📄' : fileExt === 'doc' || fileExt === 'docx' ? '📝' : '📎';
                        fileContent = `
                            <a href="${fullFileUrl}" download class="block mb-2 p-3 bg-white bg-opacity-20 rounded hover:bg-opacity-30 transition">
                                <div class="flex items-center space-x-2">
                                    <span class="text-2xl">${fileIcon}</span>
                                    <div class="flex-1 min-w-0">
                                        <div class="text-sm font-medium truncate">${displayFileName}</div>
                                        <div class="text-xs opacity-75">${fileExt ? '.' + fileExt.toUpperCase() + ' • ' : ''}Click to download</div>
                                    </div>
                                    <span class="text-lg">⬇️</span>
                                </div>
                            </a>
                        `;
                    }
                }
                
                return `
                    <div class="flex ${msg.user_id === user.id ? 'justify-end' : 'justify-start'}">
                        <div class="max-w-md ${msg.user_id === user.id ? 'bg-blue-500 text-white' : 'bg-white'} rounded-lg p-3 shadow">
                            <div class="font-semibold text-sm mb-1 ${msg.user_id === user.id ? 'text-blue-100' : 'text-gray-700'}">${msg.username}</div>
                            ${fileContent}
                            ${msg.content && msg.content !== '📎 File attached' ? `<div class="break-words">${escapeHtml(msg.content)}</div>` : ''}
                            <div class="text-xs opacity-75 mt-1 text-right">${new Date(msg.created_at).toLocaleTimeString()}</div>
                        </div>
                    </div>
                `;
            }).join('');
            
            messagesArea.scrollTop = messagesArea.scrollHeight;
        }
    } catch (error) {
        console.error('Error loading messages:', error);
    }
}
// Updated message sending with file metadata
document.getElementById('messageForm').addEventListener('submit', async (e) => {
    e.preventDefault();
    
    if (!currentChannel) {
        alert('Please select a channel first');
        return;
    }
    
    const content = document.getElementById('messageInput').value.trim();
    
    if (!content && !selectedFile) {
        return;
    }
    
    try {
        let fileUrl = null;
        let fileType = null;
        let fileName = null;
        
        // Upload file if selected
        if (selectedFile) {
            const formData = new FormData();
            formData.append('file', selectedFile);
            
            const uploadResponse = await fetch(`${API_URL}/upload.php`, {
                method: 'POST',
                headers: {
                    'Authorization': `Bearer ${token}`
                },
                body: formData
            });
            
            const uploadData = await uploadResponse.json();
            console.log('Upload response:', uploadData); // Debug log
            
            if (uploadData.success) {
                fileUrl = uploadData.file_url;
                fileType = uploadData.file_type; // image or document
                fileName = uploadData.original_name;
            } else {
                alert('File upload failed: ' + uploadData.message);
                return;
            }
        }
        
        // Send message with file metadata
        const response = await fetch(`${API_URL}/messages.php`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Authorization': `Bearer ${token}`
            },
            body: JSON.stringify({
                channel_id: currentChannel.id,
                content: content || '📎 File attached',
                file_url: fileUrl,
                file_type: fileType,    // Include file type
                file_name: fileName     // Include file name
            })
        });
        
        const data = await response.json();
        
        if (data.success) {
            document.getElementById('messageInput').value = '';
            clearFileUpload();
            loadMessages(currentChannel.id);
        } else {
            alert('Failed to send message: ' + data.message);
        }
    } catch (error) {
        console.error('Error sending message:', error);
        alert('Error sending message');
    }
});
        // Add after the loadChannels function

async function showInviteModal() {
    if (!currentChannel || !currentChannel.is_private) {
        alert('You can only invite users to private channels');
        return;
    }
    
    if (currentChannel.created_by !== user.id) {
        alert('Only channel creator can invite users');
        return;
    }
    
    const email = prompt('Enter email address to invite:');
    if (!email) return;
    
    // Validate email
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    if (!emailRegex.test(email)) {
        alert('Please enter a valid email address');
        return;
    }
    
    try {
        const response = await fetch(`${API_URL}/Invitation.php`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Authorization': `Bearer ${token}`
            },
            body: JSON.stringify({
                channel_id: currentChannel.id,
                email: email
            })
        });
        
        const data = await response.json();
        
        if (data.success) {
            alert('✅ ' + data.message);
        } else {
            alert('❌ ' + data.message);
        }
    } catch (error) {
        console.error('Error sending invitation:', error);
        alert('Failed to send invitation');
    }
}
   // Manage Members Modal Functions
async function showManageMembersModal() {
    if (!currentChannel) {
        alert('Please select a channel first');
        return;
    }
    
    if (!currentChannel.is_private) {
        alert('Member management is only for private channels');
        return;
    }
    
    if (currentChannel.created_by !== user.id) {
        alert('Only channel creator can manage members');
        return;
    }
    
    // Create modal if it doesn't exist
    let modal = document.getElementById('manageMembersModal');
    if (!modal) {
        modal = createManageMembersModal();
        document.body.appendChild(modal);
    }
    
    modal.classList.remove('hidden');
    loadMembers();
}

function hideManageMembersModal() {
    document.getElementById('manageMembersModal').classList.add('hidden');
}

function createManageMembersModal() {
    const modal = document.createElement('div');
    modal.id = 'manageMembersModal';
    modal.className = 'hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50';
    modal.innerHTML = `
        <div class="bg-white p-6 rounded-lg w-[600px] max-h-[80vh] flex flex-col">
            <h3 class="text-xl font-bold mb-4">Manage Members</h3>
            
            <!-- Add Member Section -->
            <div class="mb-4 pb-4 border-b">
                <h4 class="font-semibold mb-2">Add New Member</h4>
                <div class="flex space-x-2">
                    <input type="text" id="searchUserInput" placeholder="Search users by username or email..." 
                           class="flex-1 px-3 py-2 border rounded focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <button onclick="searchUsersToAdd()" class="px-4 py-2 bg-blue-500 text-white rounded hover:bg-blue-600">
                        Search
                    </button>
                </div>
                <div id="searchResults" class="mt-2 max-h-40 overflow-y-auto"></div>
            </div>
            
            <!-- Current Members List -->
            <div class="flex-1 overflow-y-auto">
                <h4 class="font-semibold mb-2">Current Members</h4>
                <div id="membersList" class="space-y-2"></div>
            </div>
            
            <button onclick="hideManageMembersModal()" 
                    class="mt-4 w-full bg-gray-300 py-2 rounded hover:bg-gray-400">
                Close
            </button>
        </div>
    `;
    return modal;
}
async function loadMembers() {
    try {
        const response = await fetch(`${API_URL}/channels.php?id=${currentChannel.id}&action=members`, {
            headers: { 'Authorization': `Bearer ${token}` }
        });
        
        // Check if response is OK
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        
        // Get raw text first to debug
        const text = await response.text();
        console.log('Members API Raw response:', text);
        
        // Try to parse JSON
        const data = JSON.parse(text);
        
        const membersList = document.getElementById('membersList');
        
        // Handle different response structures
        // Check if members are in data.members OR data.channel.members
        const members = data.members || (data.channel && data.channel.members) || [];
        
        if (data.success && Array.isArray(members)) {
            if (members.length === 0) {
                membersList.innerHTML = '<p class="text-gray-500 text-center py-4">No members yet</p>';
                return;
            }
            
            membersList.innerHTML = members.map(member => `
                <div class="flex items-center justify-between p-3 bg-gray-50 rounded hover:bg-gray-100">
                    <div class="flex items-center space-x-3">
                        <img src="${member.avatar || `https://ui-avatars.com/api/?name=${encodeURIComponent(member.username)}`}" 
                             class="w-10 h-10 rounded-full">
                        <div>
                            <div class="font-medium">${member.username}</div>
                            <div class="text-sm text-gray-500">${member.email}</div>
                        </div>
                    </div>
                    ${member.id !== currentChannel.created_by ? `
                        <button onclick="removeMember('${member.id}')" 
                                class="px-3 py-1 bg-red-500 text-white rounded hover:bg-red-600 text-sm">
                            Remove
                        </button>
                    ` : `
                        <span class="px-3 py-1 bg-yellow-100 text-yellow-800 rounded text-sm">Creator</span>
                    `}
                </div>
            `).join('');
        } else {
            // Handle error or unexpected response
            console.error('API Error:', data);
            membersList.innerHTML = '<p class="text-red-500 text-center py-4">Failed to load members. Check console for details.</p>';
            alert('Error loading members: ' + (data.message || 'Unknown error'));
        }
    } catch (error) {
        console.error('Error loading members:', error);
        const membersList = document.getElementById('membersList');
        if (membersList) {
            membersList.innerHTML = '<p class="text-red-500 text-center py-4">Failed to load members</p>';
        }
        alert('Failed to load members. Check console for details.');
    }
}

async function searchUsersToAdd() {
    const query = document.getElementById('searchUserInput').value.trim();
    if (!query) {
        alert('Please enter a search term');
        return;
    }
    
    try {
        const response = await fetch(`${API_URL}/users.php?action=search&q=${encodeURIComponent(query)}`, {
            headers: { 'Authorization': `Bearer ${token}` }
        });
        const data = await response.json();
        
        if (data.success) {
            const searchResults = document.getElementById('searchResults');
            
            if (data.users.length === 0) {
                searchResults.innerHTML = '<p class="text-gray-500 text-sm">No users found</p>';
                return;
            }
            
            // Filter out users already in channel
            const currentMembers = await getCurrentMemberIds();
            const availableUsers = data.users.filter(u => !currentMembers.includes(u.id));
            
            if (availableUsers.length === 0) {
                searchResults.innerHTML = '<p class="text-gray-500 text-sm">All found users are already members</p>';
                return;
            }
            
            searchResults.innerHTML = availableUsers.map(u => `
                <div class="flex items-center justify-between p-2 bg-white border rounded hover:bg-gray-50">
                    <div class="flex items-center space-x-2">
                        <img src="${u.avatar || `https://ui-avatars.com/api/?name=${encodeURIComponent(u.username)}`}" 
                             class="w-8 h-8 rounded-full">
                        <div>
                            <div class="font-medium text-sm">${u.username}</div>
                            <div class="text-xs text-gray-500">${u.email}</div>
                        </div>
                    </div>
                    <button onclick="addMemberToChannel('${u.id}')" 
                            class="px-3 py-1 bg-green-500 text-white rounded hover:bg-green-600 text-sm">
                        Add
                    </button>
                </div>
            `).join('');
        }
    } catch (error) {
        console.error('Error searching users:', error);
        alert('Failed to search users');
    }
}

async function getCurrentMemberIds() {
    try {
        const response = await fetch(`${API_URL}/channels.php?id=${currentChannel.id}&action=members`, {
            headers: { 'Authorization': `Bearer ${token}` }
        });
        const data = await response.json();
        return data.success ? data.members.map(m => m.id) : [];
    } catch (error) {
        return [];
    }
}

async function addMemberToChannel(userId) {
    if (!confirm('Add this user to the channel?')) return;
    
    try {
        const response = await fetch(`${API_URL}/channels.php?id=${currentChannel.id}&action=add_member`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Authorization': `Bearer ${token}`
            },
            body: JSON.stringify({ user_id: userId })
        });
        
        const data = await response.json();
        
        if (data.success) {
            alert('✅ Member added successfully!');
            document.getElementById('searchUserInput').value = '';
            document.getElementById('searchResults').innerHTML = '';
            loadMembers();
        } else {
            alert('❌ ' + data.message);
        }
    } catch (error) {
        console.error('Error adding member:', error);
        alert('Failed to add member');
    }
}

async function removeMember(userId) {
    if (!confirm('Remove this user from the channel?')) return;
    
    try {
        const response = await fetch(`${API_URL}/channels.php?id=${currentChannel.id}&action=remove_member`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Authorization': `Bearer ${token}`
            },
            body: JSON.stringify({ user_id: userId })
        });
        
        const data = await response.json();
        
        if (data.success) {
            alert('✅ Member removed successfully!');
            loadMembers();
        } else {
            alert('❌ ' + data.message);
        }
    } catch (error) {
        console.error('Error removing member:', error);
        alert('Failed to remove member');
    }
}

        // File upload handling
        function handleFileSelect(event) {
            const file = event.target.files[0];
            if (file) {
                selectedFile = file;
                const fileExt = file.name.split('.').pop().toLowerCase();
                const isImage = ['jpg', 'jpeg', 'png', 'gif', 'webp'].includes(fileExt);
                
                document.getElementById('filePreview').classList.remove('hidden');
                
                if (isImage) {
                    // Show image preview
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        document.getElementById('fileName').innerHTML = `
                            <div class="flex items-center space-x-3">
                                <img src="${e.target.result}" class="h-16 w-16 object-cover rounded">
                                <div>
                                    <div class="font-medium">${file.name}</div>
                                    <div class="text-xs text-gray-500">${(file.size / 1024).toFixed(1)} KB</div>
                                </div>
                            </div>
                        `;
                    };
                    reader.readAsDataURL(file);
                } else {
                    // Show document info
                    const fileIcon = fileExt === 'pdf' ? '📄' : fileExt === 'doc' || fileExt === 'docx' ? '📝' : '📎';
                    document.getElementById('fileName').innerHTML = `
                        <div class="flex items-center space-x-3">
                            <span class="text-4xl">${fileIcon}</span>
                            <div>
                                <div class="font-medium">${file.name}</div>
                                <div class="text-xs text-gray-500">${(file.size / 1024).toFixed(1)} KB • ${fileExt.toUpperCase()}</div>
                            </div>
                        </div>
                    `;
                }
            }
        }

        function clearFileUpload() {
            selectedFile = null;
            document.getElementById('fileInput').value = '';
            document.getElementById('filePreview').classList.add('hidden');
            document.getElementById('fileName').textContent = '';
        }

        // Emoji picker
        function toggleEmojiPicker() {
            const picker = document.getElementById('emojiPicker');
            picker.classList.toggle('show');
        }

        function addEmoji(emoji) {
            const input = document.getElementById('messageInput');
            input.value += emoji;
            input.focus();
            document.getElementById('emojiPicker').classList.remove('show');
        }

        // Close emoji picker when clicking outside
        document.addEventListener('click', function(event) {
            const picker = document.getElementById('emojiPicker');
            const emojiButton = event.target.closest('button[onclick*="toggleEmojiPicker"]');
            const insidePicker = event.target.closest('#emojiPicker');
            
            if (!emojiButton && !insidePicker && picker.classList.contains('show')) {
                picker.classList.remove('show');
            }
        });

        // Create channel modal
        function showCreateChannelModal() {
            document.getElementById('createChannelModal').classList.remove('hidden');
        }

        function hideCreateChannelModal() {
            document.getElementById('createChannelModal').classList.add('hidden');
        }

        document.getElementById('createChannelForm').addEventListener('submit', async (e) => {
            e.preventDefault();
            
            const name = document.getElementById('channelName').value;
            const isPrivate = document.getElementById('isPrivate').checked;
            
            try {
                const response = await fetch(`${API_URL}/channels.php`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Authorization': `Bearer ${token}`
                    },
                    body: JSON.stringify({
                        name: name,
                        is_private: isPrivate
                    })
                });
                
                const data = await response.json();
                
                if (data.success) {
                    hideCreateChannelModal();
                    document.getElementById('channelName').value = '';
                    document.getElementById('isPrivate').checked = false;
                    loadChannels();
                    alert('Channel created successfully!');
                } else {
                    alert('Failed to create channel: ' + data.message);
                }
            } catch (error) {
                console.error('Error creating channel:', error);
                alert('Error creating channel');
            }
        });

        // Delete channel
        async function deleteChannel() {
            if (!confirm('Are you sure you want to delete this channel?')) return;
            
            try {
                const response = await fetch(`${API_URL}/channels.php?id=${currentChannel.id}`, {
                    method: 'DELETE',
                    headers: { 'Authorization': `Bearer ${token}` }
                });
                
                const data = await response.json();
                
                if (data.success) {
                    currentChannel = null;
                    document.getElementById('currentChannelName').textContent = 'Select a channel';
                    document.getElementById('channelInfo').textContent = '';
                    document.getElementById('messagesArea').innerHTML = '';
                    document.getElementById('channelActions').classList.add('hidden');
                    loadChannels();
                    alert('Channel deleted successfully!');
                } else {
                    alert('Failed to delete channel: ' + data.message);
                }
            } catch (error) {
                console.error('Error deleting channel:', error);
                alert('Error deleting channel');
            }
        }

        // Utility function to escape HTML
        function escapeHtml(text) {
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        }

        // Image viewer functions
        function openImageViewer(imageUrl) {
            document.getElementById('imageViewerModal').classList.remove('hidden');
            document.getElementById('viewerImage').src = imageUrl;
            document.getElementById('downloadImageBtn').href = imageUrl;
            document.getElementById('openImageBtn').href = imageUrl;
            document.body.style.overflow = 'hidden'; // Prevent background scrolling
        }

        function closeImageViewer() {
            document.getElementById('imageViewerModal').classList.add('hidden');
            document.body.style.overflow = 'auto';
        }

        // Close viewer with Escape key
        document.addEventListener('keydown', function(event) {
            if (event.key === 'Escape') {
                closeImageViewer();
            }
        });

        // Auto-refresh messages every 3 seconds
        let lastMessageCount = 0;
        setInterval(() => {
            if (currentChannel) {
                // Only reload if there might be new messages
                fetch(`${API_URL}/messages.php?channel_id=${currentChannel.id}`, {
                    headers: { 'Authorization': `Bearer ${token}` }
                })
                .then(res => res.json())
                .then(data => {
                    if (data.success && data.messages.length !== lastMessageCount) {
                        lastMessageCount = data.messages.length;
                        loadMessages(currentChannel.id);
                    }
                })
                .catch(err => console.error('Auto-refresh error:', err));
            }
        }, 3000);

        console.log('Chat system loaded successfully!');
    