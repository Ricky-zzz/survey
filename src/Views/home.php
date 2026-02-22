<?php include __DIR__ . '/layout/head.php'; ?>

<?php include __DIR__ . '/layout/sidebar.php'; ?>

<?php include __DIR__ . '/layout/header.php'; ?>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <!-- Overview Cards -->
        <div class="bg-white rounded-lg shadow p-6 border-l-4 border-gray-800">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-500 text-sm">Total Candidates</p>
                    <p class="text-4xl font-bold text-gray-900 mt-2"><?php echo count($candidates ?? []); ?></p>
                </div>
                <svg class="w-16 h-16 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 12H9m6 0a6 6 0 11-12 0 6 6 0 0112 0z"></path>
                </svg>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow p-6 border-l-4 border-gray-600">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-500 text-sm">Total Parties</p>
                    <p class="text-4xl font-bold text-gray-900 mt-2"><?php echo count($parties); ?></p>
                </div>
                <svg class="w-16 h-16 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.856-1.487M15 10h.01M11 10h.01M7 10h.01M21 13.25V16a2 2 0 01-2 2H5a2 2 0 01-2-2v-2.75m14-6.75l-2.414-2.414a2 2 0 00-2.828 0L9 7.172V5a2 2 0 012-2h6a2 2 0 012 2v7m-6-4h.01M7 20h10"></path>
                </svg>
            </div>
        </div>
    </div>

    <div class="mt-8 grid grid-cols-1 md:grid-cols-2 gap-6">
        <!-- Quick Actions -->
        <div class="bg-white rounded-lg shadow p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Quick Actions</h3>
            <div class="space-y-2">
                <a href="/candidates/create" class="block px-4 py-2 bg-gray-900 text-white rounded-lg hover:bg-gray-800 transition text-center">
                    Add New Candidate
                </a>
                <a href="/parties/create" class="block px-4 py-2 bg-gray-700 text-white rounded-lg hover:bg-gray-600 transition text-center">
                    Add New Party
                </a>
            </div>
        </div>

        <!-- Info -->
        <div class="bg-white rounded-lg shadow p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">About</h3>
            <p class="text-gray-600 text-sm leading-relaxed">
                Manage candidates and political parties for the candidacy system. Create, edit, and delete candidate profiles with pictures, assign parties, and manage party information.
            </p>
        </div>
    </div>

<?php include __DIR__ . '/layout/footer.php'; ?>

