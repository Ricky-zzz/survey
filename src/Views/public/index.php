<?php include __DIR__ . '/../layout/head.php'; ?>

<div class="min-h-screen flex flex-col">
    <!-- Header -->
    <header class="bg-white border-b border-gray-200 shadow-sm">
        <div class="max-w-6xl mx-auto px-8 py-6">
            <h1 class="text-3xl font-bold text-gray-900">Surveys</h1>
            <p class="text-gray-600 mt-1">Select a survey to get started</p>
        </div>
    </header>

    <!-- Content -->
    <main class="flex-1">
        <div class="max-w-6xl mx-auto px-8 py-12">
            <?php if (empty($surveys)): ?>
                <div class="text-center py-12">
                    <p class="text-gray-500 text-lg">No surveys available at the moment.</p>
                </div>
            <?php else: ?>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <?php foreach ($surveys as $survey): ?>
                        <a href="/surveys/<?php echo $survey['id']; ?>/take" class="group block">
                            <div class="bg-white rounded-lg shadow hover:shadow-lg transition-shadow p-6 h-full flex flex-col">
                                <h3 class="text-xl font-semibold text-gray-900 mb-2 group-hover:text-blue-600">
                                    <?php echo htmlspecialchars($survey['title']); ?>
                                </h3>
                                <p class="text-gray-600 text-sm flex-1">
                                    <?php echo htmlspecialchars($survey['description'] ?? ''); ?>
                                </p>
                                <div class="mt-4 text-blue-600 font-medium text-sm group-hover:text-blue-700">
                                    Start →
                                </div>
                            </div>
                        </a>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </main>

    <!-- Footer -->
    <footer class="bg-white border-t border-gray-200 mt-12">
        <div class="max-w-6xl mx-auto px-8 py-6 text-center text-gray-600 text-sm">
            <p>© 2026 Survey Maker</p>
        </div>
    </footer>
</div>

</body>
</html>
