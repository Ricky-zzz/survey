<?php include __DIR__ . '/head.php'; ?>

<!-- Admin Sidebar -->
<div class="w-64 bg-gray-900 text-gray-100 flex flex-col">
    <div class="p-6 border-b border-gray-700">
        <h1 class="text-2xl font-bold">Survey Maker</h1>
        <p class="text-xs text-gray-400 mt-1">Admin</p>
    </div>
    
    <nav class="flex-1 px-4 py-6 space-y-2">
        <a href="/admin/surveys" class="block px-4 py-2 rounded-lg text-gray-100 hover:bg-gray-800 transition <?php echo strpos($_SERVER['REQUEST_URI'], '/admin/surveys') === 0 ? 'border-l-4 border-blue-500 bg-gray-800' : ''; ?>">
            <svg class="inline-block w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
            </svg>
            Surveys
        </a>
        <a href="/admin/logout" class="block px-4 py-2 rounded-lg text-gray-100 hover:bg-gray-800 transition">
            <svg class="inline-block w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path>
            </svg>
            Logout
        </a>
    </nav>
    
    <div class="p-4 border-t border-gray-700 text-xs text-gray-400">
        <p> Survey Maker</p>
    </div>
</div>

<!-- Main Content -->
<div class="flex-1 flex flex-col overflow-hidden">
    <!-- Header -->
    <header class="bg-white border-b border-gray-200 px-8 py-4 shadow-sm">
        <h2 class="text-2xl font-semibold text-gray-900"><?php echo $title ?? 'Dashboard'; ?></h2>
    </header>

    <!-- Content Area -->
    <main class="flex-1 overflow-auto bg-gray-50 p-8">
