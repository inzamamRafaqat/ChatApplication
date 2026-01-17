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
    <button onclick="showInviteModal()" class="px-3 py-1 bg-green-500 text-white rounded hover:bg-green-600">
        📧 Invite User
    </button>
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

    <script src="../Scripts/Index.js">
       
    </script>
</body>
</html>