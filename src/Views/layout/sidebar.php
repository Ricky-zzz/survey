<?php
// Helper function to check if current path matches
function isActive($path) {
    $current = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
    return strpos($current, $path) === 0;
}
?>
<!-- Sidebar -->
<div class="w-64 bg-gray-900 text-gray-100 flex flex-col shadow-lg">
    <div class="p-6 border-b border-gray-700">
        <h1 class="text-2xl font-bold text-white">Candidacy</h1>
        <p class="text-sm text-gray-400 mt-1">Management System</p>
    </div>
    
    <nav class="flex-1 px-4 py-6 space-y-2">
        <a href="/" class="block px-4 py-3 rounded-lg text-gray-100 hover:bg-gray-800 transition <?php echo isActive('/') && !isActive('/candidates') && !isActive('/parties') ? 'border-l-4 border-white bg-gray-800' : ''; ?>">
            <svg class="inline-block w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-3m0 0l7-4 7 4M5 9v10a1 1 0 001 1h12a1 1 0 001-1V9m-9 4v4m4-4v4"></path>
            </svg>
            Home
        </a>
        
        <div class="pt-2">
            <h2 class="px-4 py-2 text-xs font-semibold text-gray-400 uppercase tracking-wider">Management</h2>
            <a href="/candidates" class="block px-4 py-3 rounded-lg text-gray-100 hover:bg-gray-800 transition <?php echo isActive('/candidates') ? 'border-l-4 border-white bg-gray-800' : ''; ?>">
                <svg class="inline-block w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 12H9m6 0a6 6 0 11-12 0 6 6 0 0112 0z"></path>
                </svg>
                Candidates
            </a>
            <a href="/parties" class="block px-4 py-3 rounded-lg text-gray-100 hover:bg-gray-800 transition <?php echo isActive('/parties') ? 'border-l-4 border-white bg-gray-800' : ''; ?>">
                <svg class="inline-block w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.856-1.487M15 10h.01M11 10h.01M7 10h.01M21 13.25V16a2 2 0 01-2 2H5a2 2 0 01-2-2v-2.75m14-6.75l-2.414-2.414a2 2 0 00-2.828 0L9 7.172V5a2 2 0 012-2h6a2 2 0 012 2v7m-6-4h.01M7 20h10"></path>
                </svg>
                Political Parties
            </a>
        </div>
    </nav>
    
    <div class="p-4 border-t border-gray-700 text-xs text-gray-400">
        <p>© 2026 Candidacy System</p>
    </div>
</div>
