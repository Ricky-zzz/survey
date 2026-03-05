<?php include __DIR__ . '/../layout/head.php'; ?>

<div class="min-h-screen flex items-center justify-center bg-gray-50">
    <div class="bg-white rounded-lg shadow p-8 w-full max-w-md">
        <h1 class="text-2xl font-bold text-gray-900 mb-6 text-center">Admin Login</h1>
        
        <form method="POST" action="/admin/login" class="space-y-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Username</label>
                <input 
                    type="text" 
                    name="username" 
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                    required
                >
            </div>
            
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Password</label>
                <input 
                    type="password" 
                    name="password" 
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                    required
                >
            </div>
            
            <button 
                type="submit" 
                class="w-full bg-blue-600 text-white py-2 rounded-lg hover:bg-blue-700 transition font-medium"
            >
                Sign In
            </button>
        </form>
        
        <?php if (isset($error)): ?>
            <div class="mt-4 p-4 bg-red-50 border border-red-200 rounded-lg text-red-700 text-sm">
                <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>
    </div>
</div>

</body>
</html>
