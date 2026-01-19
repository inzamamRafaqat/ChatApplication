<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Chat System</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100">
    <div class="min-h-screen flex items-center justify-center">
        <div class="bg-white p-8 rounded-lg shadow-md w-96">
            <h1 class="text-2xl font-bold mb-6 text-center">Login</h1>
            
            <form id="loginForm">
                <div class="mb-4">
                    <label class="block text-gray-700 mb-2">Email</label>
                    <input type="email" id="email" required 
                           class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                
                <div class="mb-4">
                    <label class="block text-gray-700 mb-2">Password</label>
                    <input type="password" id="password" required 
                           class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                
                <div class="mb-6 text-right">
                    <a href="#" id="forgotPasswordLink" 
                       class="text-sm text-blue-500 hover:underline">
                        Forgot Password?
                    </a>
                </div>
                
                <button type="submit" 
                        class="w-full bg-blue-500 text-white py-2 rounded-lg hover:bg-blue-600 transition">
                    Login
                </button>
            </form>
            
            <p class="mt-4 text-center text-gray-600">
                Don't have an account? 
                <a href="register.php" class="text-blue-500 hover:underline">Register</a>
            </p>
            
            <div id="message" class="mt-4 p-3 rounded hidden"></div>
        </div>
    </div>

    <!-- Forgot Password Modal -->
    <div id="forgotPasswordModal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
        <div class="bg-white p-8 rounded-lg w-96">
            <h3 class="text-xl font-bold mb-4">Reset Password</h3>
            <p class="text-gray-600 mb-4 text-sm">
                Enter your email address and we'll send you a temporary password.
            </p>
            
            <form id="forgotPasswordForm">
                <div class="mb-4">
                    <label class="block text-gray-700 mb-2">Email Address</label>
                    <input type="email" id="resetEmail" required 
                           class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                           placeholder="your@email.com">
                </div>
                
                <div id="resetMessage" class="mb-4 p-3 rounded hidden"></div>
                
                <div class="flex space-x-3">
                    <button type="button" id="cancelResetBtn" 
                            class="flex-1 bg-gray-300 py-2 rounded-lg hover:bg-gray-400">
                        Cancel
                    </button>
                    <button type="submit" id="sendResetBtn"
                            class="flex-1 bg-blue-500 text-white py-2 rounded-lg hover:bg-blue-600">
                        Send Reset Email
                    </button>
                </div>
            </form>
        </div>
    </div>
   
    <script>
        const API_URL = 'http://localhost/ChatApplication/Backend/api';

        // Wait for DOM to be ready
        document.addEventListener('DOMContentLoaded', function() {
            
            // ================== LOGIN FORM HANDLER ==================
            document.getElementById('loginForm').addEventListener('submit', async (e) => {
                e.preventDefault();
                
                const email = document.getElementById('email').value;
                const password = document.getElementById('password').value;
                const messageDiv = document.getElementById('message');
                
                try {
                    const response = await fetch(`${API_URL}/auth.php?action=login`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify({ email, password })
                    });
                    
                    const data = await response.json();
                    
                    if (data.success) {
                        localStorage.setItem('token', data.token);
                        localStorage.setItem('user', JSON.stringify(data.user));
                        
                        messageDiv.className = 'mt-4 p-3 rounded bg-green-100 text-green-700';
                        messageDiv.textContent = 'Login successful! Redirecting...';
                        messageDiv.classList.remove('hidden');
                        
                        // Check if redirecting from invitation
                        const urlParams = new URLSearchParams(window.location.search);
                        const channelId = urlParams.get('channel_id');
                        
                        setTimeout(() => {
                            if (channelId) {
                                window.location.href = 'index.php?channel_id=' + channelId;
                            } else {
                                window.location.href = 'index.php';
                            }
                        }, 1000);
                    } else {
                        messageDiv.className = 'mt-4 p-3 rounded bg-red-100 text-red-700';
                        messageDiv.textContent = data.message;
                        messageDiv.classList.remove('hidden');
                    }
                } catch (error) {
                    console.error('Login error:', error);
                    messageDiv.className = 'mt-4 p-3 rounded bg-red-100 text-red-700';
                    messageDiv.textContent = 'An error occurred. Please try again.';
                    messageDiv.classList.remove('hidden');
                }
            });

            // ================== FORGOT PASSWORD MODAL ==================
            
            // Show Modal
            document.getElementById('forgotPasswordLink').addEventListener('click', (e) => {
                e.preventDefault();
                showForgotPasswordModal();
            });

            // Hide Modal
            document.getElementById('cancelResetBtn').addEventListener('click', hideForgotPasswordModal);

            // Close modal when clicking outside
            document.getElementById('forgotPasswordModal').addEventListener('click', function(e) {
                if (e.target === this) {
                    hideForgotPasswordModal();
                }
            });

            // Close modal with Escape key
            document.addEventListener('keydown', function(e) {
                if (e.key === 'Escape') {
                    const modal = document.getElementById('forgotPasswordModal');
                    if (!modal.classList.contains('hidden')) {
                        hideForgotPasswordModal();
                    }
                }
            });

            // ================== FORGOT PASSWORD FORM HANDLER ==================
            document.getElementById('forgotPasswordForm').addEventListener('submit', async (e) => {
                e.preventDefault();
                
                const email = document.getElementById('resetEmail').value;
                const resetMessageDiv = document.getElementById('resetMessage');
                const sendBtn = document.getElementById('sendResetBtn');
                
                // Disable button and show loading
                sendBtn.disabled = true;
                sendBtn.textContent = 'Sending...';
                
                try {
                    const response = await fetch(`${API_URL}/password_reset.php`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify({ email })
                    });
                    
                    const text = await response.text();
                    console.log('Raw response:', text);
                    
                    const data = JSON.parse(text);
                    console.log('Parsed data:', data);
                    
                    if (data.success) {
                        resetMessageDiv.className = 'mb-4 p-3 rounded bg-green-100 text-green-700 text-sm';
                        resetMessageDiv.textContent = '✅ ' + data.message;
                        resetMessageDiv.classList.remove('hidden');
                        
                        // Show debug info if available (for development)
                        if (data.debug_info) {
                            console.log('Debug Info:', data.debug_info);
                            console.log('Temp Password:', data.debug_info.temp_password);
                            console.log('Verify Link:', data.debug_info.verify_link);
                        }
                        
                        // Clear email field
                        document.getElementById('resetEmail').value = '';
                        
                        // Close modal after 3 seconds
                        setTimeout(() => {
                            hideForgotPasswordModal();
                        }, 3000);
                    } else {
                        resetMessageDiv.className = 'mb-4 p-3 rounded bg-red-100 text-red-700 text-sm';
                        resetMessageDiv.textContent = '❌ ' + data.message;
                        resetMessageDiv.classList.remove('hidden');
                    }
                } catch (error) {
                    console.error('Password reset error:', error);
                    resetMessageDiv.className = 'mb-4 p-3 rounded bg-red-100 text-red-700 text-sm';
                    resetMessageDiv.textContent = '❌ An error occurred. Please try again.';
                    resetMessageDiv.classList.remove('hidden');
                } finally {
                    // Re-enable button
                    sendBtn.disabled = false;
                    sendBtn.textContent = 'Send Reset Email';
                }
            });

            // ================== CHECK FOR SUCCESS MESSAGES ==================
            const urlParams = new URLSearchParams(window.location.search);
            const success = urlParams.get('success');
            
            if (success === 'password_reset') {
                const messageDiv = document.getElementById('message');
                messageDiv.className = 'mt-4 p-3 rounded bg-green-100 text-green-700';
                messageDiv.textContent = '✅ Password reset successful! Please login with your new password.';
                messageDiv.classList.remove('hidden');
            }
        });

        // ================== MODAL FUNCTIONS ==================
        
        function showForgotPasswordModal() {
            document.getElementById('forgotPasswordModal').classList.remove('hidden');
            document.getElementById('resetEmail').value = '';
            document.getElementById('resetMessage').classList.add('hidden');
            document.body.style.overflow = 'hidden';
        }

        function hideForgotPasswordModal() {
            document.getElementById('forgotPasswordModal').classList.add('hidden');
            document.getElementById('resetMessage').classList.add('hidden');
            document.body.style.overflow = 'auto';
        }
    </script>
</body>
</html>