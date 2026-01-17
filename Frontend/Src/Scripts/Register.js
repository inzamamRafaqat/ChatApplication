 const API_URL = 'http://localhost/ChatApplication/Backend/api';
        
        document.getElementById('registerForm').addEventListener('submit', async (e) => {
            e.preventDefault();
            
            const username = document.getElementById('username').value;
            const email = document.getElementById('email').value;
            const password = document.getElementById('password').value;
            const messageDiv = document.getElementById('message');
            
            try {
                const response = await fetch(`${API_URL}/auth.php?action=register`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({ username, email, password })
                });
                
                const data = await response.json();
                
                if (data.success) {
                    messageDiv.className = 'mt-4 p-3 rounded bg-green-100 text-green-700';
                    messageDiv.textContent = 'Registration successful! Redirecting to login...';
                    messageDiv.classList.remove('hidden');
                    
                    setTimeout(() => {
                        window.location.href = 'login.php';
                    }, 1500);
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