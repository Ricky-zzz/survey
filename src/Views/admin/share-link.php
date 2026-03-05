<?php include __DIR__ . '/../layout/head.php'; ?>
<body class="bg-gray-50">
    <div id="app">
        <?php include __DIR__ . '/../layout/app-layout.php'; ?>
        
        <main class="max-w-4xl mx-auto py-6 sm:px-6 lg:px-8">
            <div class="px-4 py-6 sm:px-0">
                <div class="bg-white shadow rounded-lg p-6">
                    <h1 class="text-2xl font-bold text-gray-900 mb-6">Share Survey</h1>
                    
                    <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-6">
                        <h2 class="text-lg font-semibold text-blue-900"><?= htmlspecialchars($shareInfo['name']) ?></h2>
                        <div class="mt-2 flex items-center space-x-2">
                            <?php if ($shareInfo['is_public']): ?>
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                    🌍 Public Survey
                                </span>
                            <?php else: ?>
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-orange-100 text-orange-800">
                                    🔒 Private Survey
                                </span>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Shareable Link -->
                    <div class="mb-6">
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Shareable Link
                        </label>
                        <div class="flex items-center space-x-2">
                            <input 
                                type="text" 
                                readonly
                                value="<?= htmlspecialchars($shareInfo['shareable_link']) ?>"
                                class="flex-1 px-3 py-2 border border-gray-300 rounded-md bg-gray-50 text-sm"
                                id="shareableLink"
                            >
                            <button 
                                onclick="copyLink()"
                                class="px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
                            >
                                Copy Link
                            </button>
                        </div>
                    </div>

                    <!-- Instructions -->
                    <div class="bg-gray-50 rounded-lg p-4">
                        <h3 class="text-sm font-medium text-gray-800 mb-2">Instructions</h3>
                        <p class="text-sm text-gray-600"><?= htmlspecialchars($shareInfo['instructions']) ?></p>
                        
                        <?php if (!$shareInfo['is_public']): ?>
                            <div class="mt-3 p-3 bg-yellow-50 border border-yellow-200 rounded-md">
                                <p class="text-sm text-yellow-800">
                                    <strong>⚠️ Security Note:</strong> This link contains the passkey. Anyone with this link can access the survey. Share it securely.
                                </p>
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- Actions -->
                    <div class="mt-6 flex items-center space-x-4">
                        <a 
                            href="/admin/surveys" 
                            class="px-4 py-2 bg-gray-600 text-white text-sm font-medium rounded-md hover:bg-gray-700"
                        >
                            Back to Surveys
                        </a>
                        <a 
                            href="<?= htmlspecialchars($shareInfo['shareable_link']) ?>" 
                            target="_blank"
                            class="px-4 py-2 bg-green-600 text-white text-sm font-medium rounded-md hover:bg-green-700"
                        >
                            Test Link
                        </a>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <script>
        function copyLink() {
            const linkInput = document.getElementById('shareableLink');
            linkInput.select();
            linkInput.setSelectionRange(0, 99999); // For mobile devices
            
            try {
                document.execCommand('copy');
                // Show success feedback
                const button = event.target;
                const originalText = button.textContent;
                button.textContent = 'Copied!';
                button.classList.remove('bg-blue-600', 'hover:bg-blue-700');
                button.classList.add('bg-green-600');
                
                setTimeout(() => {
                    button.textContent = originalText;
                    button.classList.remove('bg-green-600');
                    button.classList.add('bg-blue-600', 'hover:bg-blue-700');
                }, 2000);
            } catch (err) {
                console.error('Failed to copy: ', err);
                alert('Could not copy link to clipboard');
            }
        }
    </script>

    <?php include __DIR__ . '/../layout/footer.php'; ?>
</body>
</html>