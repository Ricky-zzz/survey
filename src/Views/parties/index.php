<?php include __DIR__ . '/../layout/head.php'; ?>

<?php include __DIR__ . '/../layout/sidebar.php'; ?>

<?php include __DIR__ . '/../layout/header.php'; ?>

    <div class="space-y-6">
        <div class="flex justify-between items-center">
            <h2 class="text-3xl font-bold text-gray-900">Political Parties</h2>
            <a href="/parties/create" class="px-6 py-2 bg-gray-900 text-white rounded-lg hover:bg-gray-800 transition font-medium">
                + Add Party
            </a>
        </div>

        <?php if (empty($parties)): ?>
            <div class="bg-white rounded-lg shadow p-12 text-center">
                <svg class="w-16 h-16 text-gray-300 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.856-1.487M15 10h.01M11 10h.01M7 10h.01M21 13.25V16a2 2 0 01-2 2H5a2 2 0 01-2-2v-2.75m14-6.75l-2.414-2.414a2 2 0 00-2.828 0L9 7.172V5a2 2 0 012-2h6a2 2 0 012 2v7m-6-4h.01M7 20h10"></path>
                </svg>
                <p class="text-gray-500 text-lg">No parties found</p>
                <p class="text-gray-400 text-sm mt-1">Create your first party to get started</p>
                <a href="/parties/create" class="mt-4 inline-block px-4 py-2 bg-gray-900 text-white rounded-lg hover:bg-gray-800 transition">
                    Create Party
                </a>
            </div>
        <?php else: ?>
            <div class="bg-white rounded-lg shadow overflow-hidden">
                <table class="w-full">
                    <thead>
                        <tr class="bg-gray-100 border-b border-gray-200">
                            <th class="px-6 py-4 text-left text-sm font-semibold text-gray-900">Party Name</th>
                            <th class="px-6 py-4 text-center text-sm font-semibold text-gray-900">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($parties as $party): ?>
                            <tr class="border-b border-gray-200 hover:bg-gray-50 transition">
                                <td class="px-6 py-4 text-sm text-gray-900 font-medium"><?= e($party['name']) ?></td>
                                <td class="px-6 py-4 text-center text-sm space-x-2">
                                    <a href="/parties/<?= $party['id'] ?>/edit" class="inline-block px-3 py-1 bg-blue-100 text-blue-700 rounded hover:bg-blue-200 transition">Edit</a>
                                    <form method="POST" action="/parties/<?= $party['id'] ?>/delete" class="inline-block" onsubmit="return confirm('Are you sure?');">
                                        <button type="submit" class="px-3 py-1 bg-red-100 text-red-700 rounded hover:bg-red-200 transition">Delete</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>

<?php include __DIR__ . '/../layout/footer.php'; ?>

