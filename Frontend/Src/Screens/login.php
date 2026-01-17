<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Chat System</title>
   <script  src="https://cdn.tailwindcss.com" ></script>
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
                
                <div class="mb-6">
                    <label class="block text-gray-700 mb-2">Password</label>
                    <input type="password" id="password" required 
                           class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
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

    <script src="../Scripts/Login.js">
      
    </script>
     <script src="https://cdnjs.cloudflare.com/ajax/libs/tailwindcss/2.2.19/tailwind.min.js"></script>
</body>
</html>