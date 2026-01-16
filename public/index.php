<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chat System</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        .emoji-picker { display: none; }
        .emoji-picker.show { display: block; }
    </style>
</head>
<body class="bg-gray-100">
    <div class="flex h-screen">
        <!-- Sidebar -->
        <div class="w-64 bg-white border-r flex flex-col">
            <div class="p-4 border-b">
                <div class="flex items-center space-x-3">
                    <img id="userAvatar" src="" alt="Avatar" class="w-10 h-10 rounded-full">
                    <div class="flex-1">
                        
                        <h2 class="font-bold" id="username"></h2>
                        <p class="text-xs text-gray-500">Online</p>
                    </div>
                </div>
                <button onclick="logout()" class="mt-2 text-sm text-red-500 hover:underline">Logout</button>
            </div>
            
            <div class="p-4 flex-1 overflow-y-auto">
                <button onclick="showCreateChannelModal()" 
                        class="w-full bg-blue-500 text-white py-2 rounded hover:bg-blue-600 mb-4">
                    + Create Channel
                </button>
                
                <h3 class="font-semibold mb-2">Channels</h3>
                <div id="channelList" class="space-y-2"></div>
            </div>
        </div>

        <!-- Main Chat Area -->
        <div class="flex-1 flex flex-col">
            <!-- Chat Header -->
            <div class="bg-white p-4 border-b flex justify-between items-center">
                <div>
                    <h3 class="font-bold text-lg" id="currentChannelName">Select a channel</h3>
                    <p class="text-sm text-gray-500" id="channelInfo"></p>
                </div>
                <div id="channelActions" class="hidden space-x-2">
                    <button onclick="deleteChannel()" class="px-3 py-1 bg-red-500 text-white rounded hover:bg-red-600">
                        Delete Channel
                    </button>
                </div>
            </div>

            <!-- Messages -->
            <div id="messagesArea" class="flex-1 overflow-y-auto p-4 space-y-3 bg-gray-50"></div>

            <!-- Message Input -->
            <div class="bg-white p-4 border-t">
                <div id="filePreview" class="hidden mb-2 p-2 bg-gray-100 rounded flex items-center justify-between">
                    <span id="fileName" class="text-sm"></span>
                    <button onclick="clearFileUpload()" class="text-red-500 text-sm">✕ Remove</button>
                </div>
                <form id="messageForm" class="flex space-x-2">
                    <input type="file" id="fileInput" class="hidden" onchange="handleFileSelect(event)" accept="image/*,.pdf,.doc,.docx,.txt">
                    <button type="button" onclick="document.getElementById('fileInput').click()" 
                            class="px-3 py-2 bg-gray-200 rounded hover:bg-gray-300" title="Attach file">
                        📎
                    </button>
                    <input type="text" id="messageInput" placeholder="Type a message..." 
                           class="flex-1 px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <button type="button" onclick="toggleEmojiPicker()" 
                            class="px-3 py-2 bg-gray-200 rounded hover:bg-gray-300" title="Add emoji">
                        😊
                    </button>
                    <button type="submit" class="px-6 py-2 bg-blue-500 text-white rounded-lg hover:bg-blue-600">
                        Send
                    </button>
                </form>
                
                <!-- Emoji Picker -->
                <div id="emojiPicker" class="emoji-picker absolute bottom-20 right-4 bg-white border rounded-lg shadow-lg p-4 z-50">
                    <div class="grid grid-cols-8 gap-2">
                        <button onclick="addEmoji('😊')" class="text-2xl hover:bg-gray-100 p-1 rounded">😊</button>
                        <button onclick="addEmoji('😂')" class="text-2xl hover:bg-gray-100 p-1 rounded">😂</button>
                        <button onclick="addEmoji('❤️')" class="text-2xl hover:bg-gray-100 p-1 rounded">❤️</button>
                        <button onclick="addEmoji('👍')" class="text-2xl hover:bg-gray-100 p-1 rounded">👍</button>
                        <button onclick="addEmoji('👎')" class="text-2xl hover:bg-gray-100 p-1 rounded">👎</button>
                        <button onclick="addEmoji('🎉')" class="text-2xl hover:bg-gray-100 p-1 rounded">🎉</button>
                        <button onclick="addEmoji('😢')" class="text-2xl hover:bg-gray-100 p-1 rounded">😢</button>
                        <button onclick="addEmoji('😡')" class="text-2xl hover:bg-gray-100 p-1 rounded">😡</button>
                        <button onclick="addEmoji('🔥')" class="text-2xl hover:bg-gray-100 p-1 rounded">🔥</button>
                        <button onclick="addEmoji('✅')" class="text-2xl hover:bg-gray-100 p-1 rounded">✅</button>
                        <button onclick="addEmoji('❌')" class="text-2xl hover:bg-gray-100 p-1 rounded">❌</button>
                        <button onclick="addEmoji('⭐')" class="text-2xl hover:bg-gray-100 p-1 rounded">⭐</button>
                        <button onclick="addEmoji('💡')" class="text-2xl hover:bg-gray-100 p-1 rounded">💡</button>
                        <button onclick="addEmoji('🎵')" class="text-2xl hover:bg-gray-100 p-1 rounded">🎵</button>
                        <button onclick="addEmoji('📷')" class="text-2xl hover:bg-gray-100 p-1 rounded">📷</button>
                        <button onclick="addEmoji('🎁')" class="text-2xl hover:bg-gray-100 p-1 rounded">🎁</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Create Channel Modal -->
    <div id="createChannelModal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
        <div class="bg-white p-6 rounded-lg w-96">
            <h3 class="text-xl font-bold mb-4">Create Channel</h3>
            <form id="createChannelForm">
                <div class="mb-4">
                    <label class="block mb-2">Channel Name</label>
                    <input type="text" id="channelName" required 
                           class="w-full px-3 py-2 border rounded focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                <div class="mb-4">
                    <label class="flex items-center">
                        <input type="checkbox" id="isPrivate" class="mr-2">
                        <span>Private Channel</span>
                    </label>
                </div>
                <div class="flex space-x-2">
                    <button type="submit" class="flex-1 bg-blue-500 text-white py-2 rounded hover:bg-blue-600">
                        Create
                    </button>
                    <button type="button" onclick="hideCreateChannelModal()" 
                            class="flex-1 bg-gray-300 py-2 rounded hover:bg-gray-400">
                        Cancel
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Image Viewer Modal -->
    <div id="imageViewerModal" class="hidden fixed inset-0 bg-black bg-opacity-90 flex items-center justify-center z-50" onclick="closeImageViewer()">
        <div class="relative max-w-7xl max-h-screen p-4">
            <button onclick="closeImageViewer()" class="absolute top-4 right-4 text-white text-4xl hover:text-gray-300 z-10">×</button>
            <img id="viewerImage" src="" class="max-w-full max-h-screen object-contain" onclick="event.stopPropagation();">
            <div class="absolute bottom-4 left-1/2 transform -translate-x-1/2 flex space-x-4">
                <a id="downloadImageBtn" href="" download class="px-4 py-2 bg-blue-500 text-white rounded hover:bg-blue-600">
                    ⬇️ Download
                </a>
                <a id="openImageBtn" href="" target="_blank" class="px-4 py-2 bg-gray-700 text-white rounded hover:bg-gray-600">
                    🔗 Open in New Tab
                </a>
            </div>
        </div>
    </div>

    <script>
        const API_URL = 'http://localhost/ChatApplication/api';
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
                const data = await response.json();
                
                if (data.success) {
                    const channelList = document.getElementById('channelList');
                    channelList.innerHTML = data.channels.map(channel => `
                        <div onclick="selectChannel('${channel.id}')" 
                             class="p-2 rounded hover:bg-gray-100 cursor-pointer ${currentChannel?.id === channel.id ? 'bg-blue-100' : ''}">
                            <div class="font-semibold">${channel.is_private ? '🔒' : '#'} ${channel.name}</div>
                        </div>
                    `).join('');
                }
            } catch (error) {
                console.error('Error loading channels:', error);
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

        // Load messages
        async function loadMessages(channelId) {
            try {
                const response = await fetch(`${API_URL}/messages.php?channel_id=${channelId}`, {
                    headers: { 'Authorization': `Bearer ${token}` }
                });
                const data = await response.json();
                
                console.log('Messages response:', data); // Debug log
                
                if (data.success) {
                    const messagesArea = document.getElementById('messagesArea');
                    messagesArea.innerHTML = data.messages.map(msg => {
                        console.log('Message:', msg); // Debug each message
                        
                        // Check if file_url exists and is not null/empty
                        const hasFile = msg.file_url && msg.file_url !== 'null' && msg.file_url.trim() !== '';
                        
                        let fileExt = '';
                        let isImage = false;
                        let isDocument = false;
                        
                        if (hasFile) {
                            console.log('File URL:', msg.file_url); // Debug file URL
                            fileExt = msg.file_url.split('.').pop().toLowerCase().split('?')[0];
                            isImage = ['jpg', 'jpeg', 'png', 'gif', 'webp'].includes(fileExt);
                            isDocument = ['pdf', 'doc', 'docx', 'txt', 'xls', 'xlsx'].includes(fileExt);
                        }
                        
                        let fileContent = '';
                        if (hasFile) {
                            if (isImage) {
                                // Show image preview
                                fileContent = `
                                    <div class="mb-2 rounded overflow-hidden">
                                        <img src="${msg.file_url}" 
                                             alt="Image" 
                                             class="max-w-full max-h-64 rounded cursor-pointer hover:opacity-90"
                                             onclick="openImageViewer('${msg.file_url}'); event.preventDefault();"
                                             onerror="console.error('Image load error:', this.src); this.style.display='none'; this.parentElement.innerHTML='<div class=\'text-red-500\'>❌ Image failed to load</div>';">
                                    </div>
                                `;
                            } else if (isDocument) {
                                // Show document download button
                                const fileName = msg.file_url.split('/').pop().split('?')[0];
                                const fileIcon = fileExt === 'pdf' ? '📄' : fileExt === 'doc' || fileExt === 'docx' ? '📝' : '📎';
                                fileContent = `
                                    <a href="${msg.file_url}" download target="_blank" 
                                       class="block mb-2 p-3 bg-white bg-opacity-20 rounded hover:bg-opacity-30 transition">
                                        <div class="flex items-center space-x-2">
                                            <span class="text-2xl">${fileIcon}</span>
                                            <div class="flex-1 min-w-0">
                                                <div class="text-sm font-medium truncate">${fileName}</div>
                                                <div class="text-xs opacity-75">.${fileExt.toUpperCase()} • Click to download</div>
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
                                    <div class="font-semibold text-sm mb-1 ${msg.user_id === user.id ? 'text-blue-100' : 'text-gray-700'}">${escapeHtml(msg.username)}</div>
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

        // Send message
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
                
                // Upload file if selected
                if (selectedFile) {
                    const formData = new FormData();
                    formData.append('file', selectedFile);
                    
                    console.log('Uploading file...'); // Debug
                    
                    const uploadResponse = await fetch(`${API_URL}/upload.php`, {
                        method: 'POST',
                        headers: {
                            'Authorization': `Bearer ${token}`
                        },
                        body: formData
                    });
                    
                    const uploadData = await uploadResponse.json();
                    console.log('Upload response:', uploadData); // Debug
                    
                    if (uploadData.success) {
                        fileUrl = uploadData.file_url;
                        console.log('File uploaded successfully:', fileUrl); // Debug
                    } else {
                        alert('Failed to upload file: ' + uploadData.message);
                        return;
                    }
                }
                
                // Send message
                const messagePayload = {
                    channel_id: currentChannel.id,
                    content: content || '📎 File attached',
                    file_url: fileUrl
                };
                
                console.log('Sending message:', messagePayload); // Debug
                
                const response = await fetch(`${API_URL}/messages.php`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Authorization': `Bearer ${token}`
                    },
                    body: JSON.stringify(messagePayload)
                });
                
                const data = await response.json();
                console.log('Message response:', data); // Debug
                
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
            document.body.style.overflow = 'hidden';
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
        setInterval(() => {
            if (currentChannel) {
                loadMessages(currentChannel.id);
            }
        }, 3000);

        console.log('Chat system loaded successfully!');
    </script>
</body>
</html>