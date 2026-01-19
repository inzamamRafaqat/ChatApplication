const API_URL = 'http://localhost/ChatApplication/Backend/api';

console.log('Login.js loaded');

// Wait for DOM to load
document.addEventListener('DOMContentLoaded', function() {
    console.log('DOM Content Loaded');
    
    // Check if elements exist
    const loginForm = document.getElementById('loginForm');
    const forgotPasswordLink = document.getElementById('forgotPasswordLink');
    const cancelResetBtn = document.getElementById('cancelResetBtn');
    const forgotPasswordForm = document.getElementById('forgotPasswordForm');
    const forgotPasswordModal = document.getElementById('forgotPasswordModal');
    
    console.log('Elements found:', {
        loginForm: !!loginForm,
        forgotPasswordLink: !!forgotPasswordLink,
        cancelResetBtn: !!cancelResetBtn,
        forgotPasswordForm: !!forgotPasswordForm,
        forgotPasswordModal: !!forgotPasswordModal
    });
    
    // ================== LOGIN FORM HANDLER ==================
    if (loginForm) {
        loginForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            console.log('Login form submitted');
            
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
    }

    // ================== FORGOT PASSWORD MODAL ==================
    
    // Show Forgot Password Modal
    if (forgotPasswordLink) {
        forgotPasswordLink.addEventListener('click', function(e) {
            e.preventDefault();
            console.log('Forgot password link clicked');
            showForgotPasswordModal();
        });
        console.log('Forgot password event listener attached');
    } else {
        console.error('Forgot password link not found!');
    }

    // Hide Modal - Cancel Button
    if (cancelResetBtn) {
        cancelResetBtn.addEventListener('click', function() {
            console.log('Cancel button clicked');
            hideForgotPasswordModal();
        });
    }

    // Close modal when clicking outside
    if (forgotPasswordModal) {
        forgotPasswordModal.addEventListener('click', function(e) {
            if (e.target === this) {
                console.log('Modal background clicked');
                hideForgotPasswordModal();
            }
        });
    }

    // Close modal with Escape key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            const modal = document.getElementById('forgotPasswordModal');
            if (modal && !modal.classList.contains('hidden')) {
                console.log('Escape key pressed');
                hideForgotPasswordModal();
            }
        }
    });

    // ================== FORGOT PASSWORD FORM HANDLER ==================
    if (forgotPasswordForm) {
        forgotPasswordForm.addEventListener('submit', async function(e) {
            e.preventDefault();
            console.log('Forgot password form submitted');
            
            const email = document.getElementById('resetEmail').value;
            const resetMessageDiv = document.getElementById('resetMessage');
            const sendBtn = document.getElementById('sendResetBtn');
            
            console.log('Reset email:', email);
            
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
                console.log('Password reset response:', data);
                
                if (data.success) {
                    resetMessageDiv.className = 'mb-4 p-3 rounded bg-green-100 text-green-700 text-sm';
                    resetMessageDiv.textContent = '✅ ' + data.message;
                    resetMessageDiv.classList.remove('hidden');
                    
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
    }

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
    console.log('showForgotPasswordModal called');
    const modal = document.getElementById('forgotPasswordModal');
    if (modal) {
        modal.classList.remove('hidden');
        document.getElementById('resetEmail').value = '';
        const resetMsg = document.getElementById('resetMessage');
        if (resetMsg) {
            resetMsg.classList.add('hidden');
        }
        document.body.style.overflow = 'hidden';
        console.log('Modal shown successfully');
    } else {
        console.error('Modal element not found!');
    }
}

function hideForgotPasswordModal() {
    console.log('hideForgotPasswordModal called');
    const modal = document.getElementById('forgotPasswordModal');
    if (modal) {
        modal.classList.add('hidden');
        const resetMsg = document.getElementById('resetMessage');
        if (resetMsg) {
            resetMsg.classList.add('hidden');
        }
        document.body.style.overflow = 'auto';
        console.log('Modal hidden successfully');
    }
}