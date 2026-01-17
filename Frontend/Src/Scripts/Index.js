
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
                
                if (data.success) {
                    const messagesArea = document.getElementById('messagesArea');
                    messagesArea.innerHTML = data.messages.map(msg => {
                        const hasFile = msg.file_url && msg.file_url !== 'null';
                        const fileExt = hasFile ? msg.file_url.split('.').pop().toLowerCase() : '';
                        const isImage = hasFile && ['jpg', 'jpeg', 'png', 'gif', 'webp'].includes(fileExt);
                        const isDocument = hasFile && ['pdf', 'doc', 'docx', 'txt', 'xls', 'xlsx'].includes(fileExt);
                        
                        let fileContent = '';
                        if (hasFile) {
                            if (isImage) {
                                // Show image preview
                                fileContent = `
                                    <div class="mb-2 rounded overflow-hidden">
                                        <a href="${msg.file_url}" target="_blank">
                                            <img src="${msg.file_url}" alt="Image" 
                                                 class="max-w-full max-h-64 rounded cursor-pointer hover:opacity-90"
                                                 onclick="openImageViewer('${msg.file_url}'); event.preventDefault();">
                                        </a>
                                    </div>
                                `;
                            } else if (isDocument) {
                                // Show document download button
                                const fileName = msg.file_url.split('/').pop();
                                const fileIcon = fileExt === 'pdf' ? '📄' : fileExt === 'doc' || fileExt === 'docx' ? '📝' : '📎';
                                fileContent = `
                                    <a href="${msg.file_url}" download class="block mb-2 p-3 bg-white bg-opacity-20 rounded hover:bg-opacity-30 transition">
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
                    
                    const uploadResponse = await fetch(`${API_URL}/upload.php`, {
                        method: 'POST',
                        headers: {
                            'Authorization': `Bearer ${token}`
                        },
                        body: formData
                    });
                    
                    const uploadData = await uploadResponse.json();
                    if (uploadData.success) {
                        fileUrl = uploadData.file_url;
                    }
                }
                
                // Send message
                const response = await fetch(`${API_URL}/messages.php`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Authorization': `Bearer ${token}`
                    },
                    body: JSON.stringify({
                        channel_id: currentChannel.id,
                        content: content || '📎 File attached',
                        file_url: fileUrl
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
    