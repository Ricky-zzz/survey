<?php include __DIR__ . '/../layout/head.php'; ?>

<div class="min-h-screen flex flex-col bg-gray-50">
    <!-- Header -->
    <header class="bg-white border-b border-gray-200 shadow-sm">
        <div class="max-w-2xl mx-auto px-8 py-6">
            <h1 class="text-3xl font-bold text-gray-900"><?php echo htmlspecialchars($survey['name']); ?></h1>
            <?php if (!empty($survey['description'])): ?>
                <p class="text-gray-600 mt-2"><?php echo htmlspecialchars($survey['description']); ?></p>
            <?php endif; ?>
        </div>
    </header>

    <!-- Content -->
    <main class="flex-1">
        <div class="max-w-2xl mx-auto px-8 py-12">
            <?php if (isset($showPasskeyForm) && $showPasskeyForm): ?>
                <!-- Passkey Form for Private Surveys -->
                <div class="bg-white rounded-lg shadow p-8">
                    <h2 class="text-xl font-semibold text-gray-900 mb-6">This survey is private</h2>
                    <form method="POST" action="/surveys/<?php echo $survey['id']; ?>/take" class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Enter Passcode</label>
                            <input 
                                type="password" 
                                name="passkey" 
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                                required
                            >
                        </div>
                        <button 
                            type="submit" 
                            class="w-full bg-blue-600 text-white py-2 rounded-lg hover:bg-blue-700 transition font-medium"
                        >
                            Continue
                        </button>
                    </form>
                    <?php if (isset($error)): ?>
                        <div class="mt-4 p-4 bg-red-50 border border-red-200 rounded-lg text-red-700 text-sm">
                            <?php echo htmlspecialchars($error); ?>
                        </div>
                    <?php endif; ?>
                </div>
            <?php else: ?>
                <!-- Survey Form -->
                <form method="POST" action="/surveys/<?php echo $survey['id']; ?>/submit" enctype="multipart/form-data" class="space-y-8">
                    <?php foreach ($sections as $section): ?>
                        <div class="bg-white rounded-lg shadow p-8">
                            <?php if (!$section['is_respondent_info']): ?>
                                <h2 class="text-2xl font-semibold text-gray-900 mb-2"><?php echo htmlspecialchars($section['title']); ?></h2>
                                <?php if (!empty($section['description'])): ?>
                                    <p class="text-gray-600 mb-6"><?php echo htmlspecialchars($section['description']); ?></p>
                                <?php endif; ?>
                            <?php else: ?>
                                <h2 class="text-lg font-semibold text-gray-900 mb-6">Your Information</h2>
                            <?php endif; ?>

                            <div class="space-y-6">
                                <?php foreach ($section['questions'] as $question): ?>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-900 mb-2">
                                            <?php echo htmlspecialchars($question['question_text']); ?>
                                            <?php if ($question['required']): ?>
                                                <span class="text-red-500">*</span>
                                            <?php endif; ?>
                                        </label>

                                        <?php if ($question['type'] === 'text'): ?>
                                            <input 
                                                type="text" 
                                                name="responses[<?php echo $question['id']; ?>]"
                                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                                                <?php echo $question['required'] ? 'required' : ''; ?>
                                            >

                                        <?php elseif ($question['type'] === 'yesno'): ?>
                                            <div class="space-y-2">
                                                <label class="flex items-center">
                                                    <input type="radio" name="responses[<?php echo $question['id']; ?>]" value="yes" class="mr-2" <?php echo $question['required'] ? 'required' : ''; ?>>
                                                    <span class="text-gray-700">Yes</span>
                                                </label>
                                                <label class="flex items-center">
                                                    <input type="radio" name="responses[<?php echo $question['id']; ?>]" value="no" class="mr-2">
                                                    <span class="text-gray-700">No</span>
                                                </label>
                                            </div>

                                        <?php elseif ($question['type'] === 'scale'): ?>
                                            <div class="flex gap-2">
                                                <?php for ($i = 1; $i <= 5; $i++): ?>
                                                    <label class="flex items-center cursor-pointer">
                                                        <input 
                                                            type="radio" 
                                                            name="responses[<?php echo $question['id']; ?>]" 
                                                            value="<?php echo $i; ?>" 
                                                            class="mr-1"
                                                            <?php echo $question['required'] ? 'required' : ''; ?>
                                                        >
                                                        <span class="w-10 h-10 flex items-center justify-center border border-gray-300 rounded hover:border-blue-500 hover:bg-blue-50">
                                                            <?php echo $i; ?>
                                                        </span>
                                                    </label>
                                                <?php endfor; ?>
                                            </div>

                                        <?php elseif ($question['type'] === 'multiple_choice'): ?>
                                            <div class="space-y-2">
                                                <?php foreach ($question['options'] as $option): ?>
                                                    <label class="flex items-center">
                                                        <input 
                                                            type="<?php echo $question['allow_multiple_files'] ? 'checkbox' : 'radio'; ?>" 
                                                            name="responses[<?php echo $question['id']; ?>]<?php echo $question['allow_multiple_files'] ? '[]' : ''; ?>" 
                                                            value="<?php echo htmlspecialchars($option['option_value']); ?>"
                                                            class="mr-2"
                                                            <?php echo $question['required'] ? 'required' : ''; ?>
                                                        >
                                                        <span class="text-gray-700"><?php echo htmlspecialchars($option['option_value']); ?></span>
                                                    </label>
                                                <?php endforeach; ?>
                                            </div>

                                        <?php elseif ($question['type'] === 'file_upload'): ?>
                                            <input 
                                                type="file" 
                                                name="files[<?php echo $question['id']; ?>]"
                                                accept=".pdf"
                                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                                                <?php echo $question['required'] ? 'required' : ''; ?>
                                            >
                                            <p class="text-xs text-gray-500 mt-1">PDF files only, max 5MB</p>
                                        <?php endif; ?>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>

                    <!-- Submit Button -->
                    <div class="flex gap-4">
                        <a href="/" class="flex-1 text-center px-6 py-3 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition font-medium">
                            Cancel
                        </a>
                        <button 
                            type="submit" 
                            class="flex-1 bg-blue-600 text-white px-6 py-3 rounded-lg hover:bg-blue-700 transition font-medium"
                        >
                            Submit
                        </button>
                    </div>
                </form>
            <?php endif; ?>
        </div>
    </main>
</div>

</body>
</html>
