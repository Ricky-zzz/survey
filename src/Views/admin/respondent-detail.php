<?php include __DIR__ . '/../layout/head.php'; ?>
<div class="flex h-screen">
    <?php include __DIR__ . '/../layout/app-layout.php'; ?>
    
    <!-- Respondent Details -->
    <div class="mb-8">
        <a href="/admin/surveys/<?php echo $survey['id']; ?>/respondents" class="text-blue-600 hover:text-blue-700 text-sm font-medium mb-4 inline-block">
            ← Back to Respondents
        </a>
    </div>

    <div class="space-y-6">
        <!-- Respondent Info -->
        <div class="bg-white rounded-lg shadow p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Respondent Information</h3>
            <div class="grid grid-cols-2 gap-6">
                <div>
                    <p class="text-sm text-gray-600">Email</p>
                    <p class="text-lg font-medium text-gray-900"><?php echo htmlspecialchars($respondent['email'] ?? 'N/A'); ?></p>
                </div>
                <div>
                    <p class="text-sm text-gray-600">Name</p>
                    <p class="text-lg font-medium text-gray-900"><?php echo htmlspecialchars($respondent['name'] ?? 'N/A'); ?></p>
                </div>
                <div>
                    <p class="text-sm text-gray-600">Submitted</p>
                    <p class="text-lg font-medium text-gray-900"><?php echo $respondent['submitted_at'] ? date('M d, Y H:i', strtotime($respondent['submitted_at'])) : 'Not submitted'; ?></p>
                </div>
                <div>
                    <p class="text-sm text-gray-600">Started</p>
                    <p class="text-lg font-medium text-gray-900"><?php echo date('M d, Y H:i', strtotime($respondent['created_at'])); ?></p>
                </div>
            </div>
        </div>

        <!-- Responses by Section -->
        <?php foreach ($sections as $section): ?>
            <div class="bg-white rounded-lg shadow p-6">
                <h4 class="text-lg font-semibold text-gray-900 mb-6"><?php echo htmlspecialchars($section['title']); ?></h4>
                
                <div class="space-y-6">
                    <?php foreach ($section['questions'] as $question): ?>
                        <div class="border-b border-gray-200 pb-6 last:border-b-0">
                            <p class="font-medium text-gray-900 mb-2"><?php echo htmlspecialchars($question['question_text']); ?></p>
                            
                            <?php 
                                $response = null;
                                if (!empty($responses)) {
                                    foreach ($responses as $r) {
                                        if ($r['question_id'] == $question['id']) {
                                            $response = $r;
                                            break;
                                        }
                                    }
                                }
                            ?>
                            
                            <?php if ($question['type'] === 'file_upload'): ?>
                                <?php 
                                    $questionFiles = array_filter($files ?? [], function($f) use ($question) {
                                        return $f['question_id'] == $question['id'];
                                    });
                                ?>
                                <?php if (!empty($questionFiles)): ?>
                                    <div class="space-y-2">
                                        <?php foreach ($questionFiles as $file): ?>
                                            <div class="flex items-center gap-3 p-3 bg-gray-50 rounded">
                                                <svg class="w-5 h-5 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
                                                </svg>
                                                <div class="flex-1">
                                                    <p class="text-sm font-medium text-gray-900"><?php echo htmlspecialchars($file['original_filename']); ?></p>
                                                    <p class="text-xs text-gray-500"><?php echo round($file['file_size'] / 1024, 2); ?> KB</p>
                                                </div>
                                                <a href="<?php echo $file['file_path']; ?>" class="text-blue-600 hover:text-blue-700 text-sm font-medium">Download</a>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                <?php else: ?>
                                    <p class="text-gray-500 text-sm">No file uploaded</p>
                                <?php endif; ?>
                            <?php else: ?>
                                <p class="text-gray-700 text-sm"><?php echo $response ? htmlspecialchars($response['answer_value']) : 'No response'; ?></p>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <?php include __DIR__ . '/../layout/footer.php'; ?>
</div>
