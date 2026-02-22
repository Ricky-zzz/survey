<?php include __DIR__ . '/../layout/head.php'; ?>

<?php include __DIR__ . '/../layout/sidebar.php'; ?>

<?php include __DIR__ . '/../layout/header.php'; ?>

    <div class="max-w-2xl mx-auto">
        <div class="bg-white rounded-lg shadow p-8">
            <h2 class="text-2xl font-bold text-gray-900 mb-6">Edit Party</h2>

            <form method="POST" action="/parties/<?= $party['id'] ?>" class="space-y-6">
                <!-- Party Name -->
                <div>
                    <label for="name" class="block text-sm font-medium text-gray-900 mb-2">Party Name</label>
                    <input type="text" name="name" id="name" required value="<?= e($party['name']) ?>" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-gray-800 focus:border-transparent">
                </div>

                <!-- Submit -->
                <div class="flex gap-4 pt-6">
                    <button type="submit" class="flex-1 px-6 py-2 bg-gray-900 text-white rounded-lg hover:bg-gray-800 transition font-medium">
                        Update Party
                    </button>
                    <a href="/parties" class="flex-1 px-6 py-2 bg-gray-200 text-gray-900 rounded-lg hover:bg-gray-300 transition font-medium text-center">
                        Cancel
                    </a>
                </div>
            </form>
        </div>
    </div>

<?php include __DIR__ . '/../layout/footer.php'; ?>

