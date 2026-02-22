<?php include __DIR__ . '/../layout/head.php'; ?>

<?php include __DIR__ . '/../layout/sidebar.php'; ?>

<?php include __DIR__ . '/../layout/header.php'; ?>

    <div class="space-y-6">
        <div class="flex justify-between items-center">
            <h2 class="text-3xl font-bold text-gray-900">Candidates</h2>
            <a href="/candidates/create" class="px-6 py-2 bg-gray-900 text-white rounded-lg hover:bg-gray-800 transition font-medium">
                + Add Candidate
            </a>
        </div>

        <?php if (empty($candidates)): ?>
            <div class="bg-white rounded-lg shadow p-12 text-center">
                <svg class="w-16 h-16 text-gray-300 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 12H9m6 0a6 6 0 11-12 0 6 6 0 0112 0z"></path>
                </svg>
                <p class="text-gray-500 text-lg">No candidates found</p>
                <p class="text-gray-400 text-sm mt-1">Create your first candidate to get started</p>
                <a href="/candidates/create" class="mt-4 inline-block px-4 py-2 bg-gray-900 text-white rounded-lg hover:bg-gray-800 transition">
                    Create Candidate
                </a>
            </div>
        <?php else: ?>
            <div class="bg-white rounded-lg shadow overflow-hidden">
                <table class="w-full">
                    <thead>
                        <tr class="bg-gray-100 border-b border-gray-200">
                            <th class="px-6 py-4 text-left text-sm font-semibold text-gray-900">Picture</th>
                            <th class="px-6 py-4 text-left text-sm font-semibold text-gray-900">Code</th>
                            <th class="px-6 py-4 text-left text-sm font-semibold text-gray-900">Name</th>
                            <th class="px-6 py-4 text-left text-sm font-semibold text-gray-900">Party</th>
                            <th class="px-6 py-4 text-left text-sm font-semibold text-gray-900">Gender</th>
                            <th class="px-6 py-4 text-center text-sm font-semibold text-gray-900">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($candidates as $cand): ?>
                            <?php
                                $fullName = trim($cand['firstname'] . ' ' . ($cand['middlename'] ?? '') . ' ' . $cand['lastname']);
                                $pictureUrl = $cand['picture'] ? '/uploads/' . $cand['picture'] : 'data:image/svg+xml,%3Csvg xmlns=%22http://www.w3.org/2000/svg%22 fill=%22%23d1d5db%22 viewBox=%220 0 24 24%22%3E%3Cpath d=%22M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm0 3c1.66 0 3 1.34 3 3s-1.34 3-3 3-3-1.34-3-3 1.34-3 3-3zm0 14.2c-2.5 0-4.71-1.28-6-3.22.03-1.99 4-3.08 6-3.08 1.99 0 5.97 1.09 6 3.08-1.29 1.94-3.5 3.22-6 3.22z%22/%3E%3C/svg%3E';
                            ?>
                            <tr class="border-b border-gray-200 hover:bg-gray-50 transition">
                                <td class="px-6 py-4">
                                    <img src="<?= $pictureUrl ?>"
alt="<?= e($fullName) ?>" class="w-10 h-10 rounded-full bg-gray-200 object-cover">
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-700"><?= e($cand['candidate_code']) ?></td>
                                <td class="px-6 py-4 text-sm text-gray-900 font-medium"><?= e($fullName) ?></td>
                                <td class="px-6 py-4 text-sm text-gray-700"><?= e($cand['party_name']) ?></td>
                                <td class="px-6 py-4 text-sm text-gray-700"><?= e($cand['gender']) ?></td>
                                <td class="px-6 py-4 text-center text-sm space-x-2">
                                    <a href="/candidates/<?= $cand['id'] ?>/edit" class="inline-block px-3 py-1 bg-blue-100 text-blue-700 rounded hover:bg-blue-200 transition">Edit</a>
                                    <form method="POST" action="/candidates/<?= $cand['id'] ?>/delete" class="inline-block" onsubmit="return confirm('Are you sure?');">
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

