
const API_URL = 'http://localhost/ChatApplication/Backend/api';

       // Show Forgot Password Modal
        document.getElementById('forgotPasswordLink').addEventListener('click', (e) => {
            e.preventDefault();
            document.getElementById('forgotPasswordModal').classList.remove('hidden');
            document.getElementById('resetEmail').value = '';
            document.getElementById('resetMessage').classList.add('hidden');
        });

        // Hide Forgot Password Modal
        function hideForgotPasswordModal() {
            document.getElementById('forgotPasswordModal').classList.add('hidden');
        }

        document.getElementById('cancelResetBtn').addEventListener('click', hideForgotPasswordModal);

        // Handle Forgot Password Form
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
                
                const data = await response.json();
                
                if (data.success) {
                    resetMessageDiv.className = 'mb-4 p-3 rounded bg-green-100 text-green-700 text-sm';
                    resetMessageDiv.textContent = data.message;
                    resetMessageDiv.classList.remove('hidden');
                    
                    // Clear form and close modal after 3 seconds
                    setTimeout(() => {
                        hideForgotPasswordModal();
                    }, 3000);
                } else {
                    resetMessageDiv.className = 'mb-4 p-3 rounded bg-red-100 text-red-700 text-sm';
                    resetMessageDiv.textContent = data.message;
                    resetMessageDiv.classList.remove('hidden');
                }
            } catch (error) {
                console.error('Password reset error:', error);
                resetMessageDiv.className = 'mb-4 p-3 rounded bg-red-100 text-red-700 text-sm';
                resetMessageDiv.textContent = 'An error occurred. Please try again.';
                resetMessageDiv.classList.remove('hidden');
            } finally {
                // Re-enable button
                sendBtn.disabled = false;
                sendBtn.textContent = 'Send Reset Email';
            }
        });

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

  // Login Form Handler
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
                messageDiv.className = 'mt-4 p-3 rounded bg-red-100 text-red-700';
                messageDiv.textContent = 'An error occurred. Please try again.';
                messageDiv.classList.remove('hidden');
            }
        });