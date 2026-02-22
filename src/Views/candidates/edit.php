<?php include __DIR__ . '/../layout/head.php'; ?>

<?php include __DIR__ . '/../layout/sidebar.php'; ?>

<?php include __DIR__ . '/../layout/header.php'; ?>

    <div class="max-w-2xl mx-auto">
        <div class="bg-white rounded-lg shadow p-8">
            <h2 class="text-2xl font-bold text-gray-900 mb-6">Edit Candidate</h2>

            <form method="POST" action="/candidates/<?= $candidate['id'] ?>" enctype="multipart/form-data" class="space-y-6">
                <!-- Candidate Code -->
                <div>
                    <label for="candidate_code" class="block text-sm font-medium text-gray-900 mb-2">Candidate Code</label>
                    <input type="text" name="candidate_code" id="candidate_code" required value="<?= e($candidate['candidate_code']) ?>" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-gray-800 focus:border-transparent">
                </div>

                <!-- First Name -->
                <div>
                    <label for="firstname" class="block text-sm font-medium text-gray-900 mb-2">First Name</label>
                    <input type="text" name="firstname" id="firstname" required value="<?= e($candidate['firstname']) ?>" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-gray-800 focus:border-transparent">
                </div>

                <!-- Middle Name -->
                <div>
                    <label for="middlename" class="block text-sm font-medium text-gray-900 mb-2">Middle Name</label>
                    <input type="text" name="middlename" id="middlename" value="<?= e($candidate['middlename'] ?? '') ?>" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-gray-800 focus:border-transparent">
                </div>

                <!-- Last Name -->
                <div>
                    <label for="lastname" class="block text-sm font-medium text-gray-900 mb-2">Last Name</label>
                    <input type="text" name="lastname" id="lastname" required value="<?= e($candidate['lastname']) ?>" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-gray-800 focus:border-transparent">
                </div>

                <!-- Gender -->
                <div>
                    <label class="block text-sm font-medium text-gray-900 mb-3">Gender</label>
                    <div class="space-y-2">
                        <label class="flex items-center">
                            <input type="radio" name="gender" value="Male" <?= $candidate['gender'] === 'Male' ? 'checked' : '' ?> required class="w-4 h-4 text-gray-800 bg-white border-gray-300 rounded-full focus:ring-2 focus:ring-gray-800">
                            <span class="ml-3 text-gray-700">Male</span>
                        </label>
                        <label class="flex items-center">
                            <input type="radio" name="gender" value="Female" <?= $candidate['gender'] === 'Female' ? 'checked' : '' ?> required class="w-4 h-4 text-gray-800 bg-white border-gray-300 rounded-full focus:ring-2 focus:ring-gray-800">
                            <span class="ml-3 text-gray-700">Female</span>
                        </label>
                    </div>
                </div>

                <!-- Party -->
                <div>
                    <label for="party_id" class="block text-sm font-medium text-gray-900 mb-2">Political Party</label>
                    <select name="party_id" id="party_id" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-gray-800 focus:border-transparent bg-white">
                        <?php foreach ($parties as $p): ?>
                            <option value="<?= $p['id'] ?>" <?= $p['id'] == $candidate['party_id'] ? 'selected' : '' ?>><?= e($p['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Picture -->
                <div>
                    <label class="block text-sm font-medium text-gray-900 mb-2">Picture</label>
                    <?php if ($candidate['picture']): ?>
                        <div class="mb-4 flex items-center gap-4">
                            <img src="/uploads/<?= e($candidate['picture']) ?>" alt="Candidate picture" class="w-20 h-20 rounded-lg object-cover border border-gray-200">
                            <p class="text-sm text-gray-600">Current picture</p>
                        </div>
                    <?php endif; ?>
                    <input type="file" name="picture" id="picture" accept="image/png,image/jpeg,image/gif" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-gray-800 focus:border-transparent">
                    <p class="text-xs text-gray-500 mt-1">JPG, PNG or GIF (max 2MB) - Leave empty to keep current picture</p>
                </div>

                <!-- Submit -->
                <div class="flex gap-4 pt-6">
                    <button type="submit" class="flex-1 px-6 py-2 bg-gray-900 text-white rounded-lg hover:bg-gray-800 transition font-medium">
                        Update Candidate
                    </button>
                    <a href="/candidates" class="flex-1 px-6 py-2 bg-gray-200 text-gray-900 rounded-lg hover:bg-gray-300 transition font-medium text-center">
                        Cancel
                    </a>
                </div>
            </form>
        </div>
    </div>

<?php include __DIR__ . '/../layout/footer.php'; ?>

