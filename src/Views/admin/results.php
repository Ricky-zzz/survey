<?php include __DIR__ . '/../layout/head.php'; ?>
<div class="flex h-screen">
    <?php include __DIR__ . '/../layout/app-layout.php'; ?>
    
    <!-- Results Header -->
    <div class="mb-8 flex justify-between items-start">
        <div>
            <h3 class="text-lg font-semibold text-gray-900"><?php echo htmlspecialchars($survey['title']); ?></h3>
            <p class="text-gray-600 text-sm mt-1"><?php echo $responseCount ?? 0; ?> responses</p>
        </div>
        <div class="flex gap-2">
            <a href="/admin/surveys/<?php echo $survey['id']; ?>/respondents" class="text-blue-600 hover:text-blue-700 text-sm font-medium px-3 py-2">
                View All Respondents
            </a>
            <button onclick="exportResults('csv')" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition text-sm">
                Export CSV
            </button>
            <button onclick="exportResults('json')" class="bg-gray-600 text-white px-4 py-2 rounded-lg hover:bg-gray-700 transition text-sm">
                Export JSON
            </button>
        </div>
    </div>

    <!-- Results by Question -->
    <div class="space-y-6">
        <?php if (empty($questions)): ?>
            <div class="bg-white rounded-lg shadow p-8 text-center">
                <p class="text-gray-500">No responses yet.</p>
            </div>
        <?php else: ?>
            <?php foreach ($questions as $question): ?>
                <div class="bg-white rounded-lg shadow p-6">
                    <h4 class="font-semibold text-gray-900 mb-4"><?php echo htmlspecialchars($question['question_text']); ?></h4>
                    <p class="text-sm text-gray-500 mb-4">Type: <?php echo ucfirst(str_replace('_', ' ', $question['type'])); ?></p>
                    
                    <?php if ($question['type'] === 'text'): ?>
                        <!-- Text responses -->
                        <div class="space-y-2 max-h-48 overflow-y-auto">
                            <?php if (!empty($question['responses'])): ?>
                                <?php foreach ($question['responses'] as $resp): ?>
                                    <div class="p-3 bg-gray-50 rounded text-sm">
                                        <?php echo htmlspecialchars($resp['answer_value']); ?>
                                    </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <p class="text-gray-500 text-sm">No responses</p>
                            <?php endif; ?>
                        </div>

                    <?php elseif ($question['type'] === 'scale'): ?>
                        <!-- Scale chart -->
                        <div class="space-y-2">
                            <?php for ($i = 1; $i <= 5; $i++): ?>
                                <?php 
                                    $count = 0;
                                    $total = count($question['responses'] ?? []);
                                    if ($total > 0) {
                                        foreach ($question['responses'] as $resp) {
                                            if ($resp['answer_value'] == $i) $count++;
                                        }
                                    }
                                    $percent = $total > 0 ? ($count / $total) * 100 : 0;
                                ?>
                                <div class="flex items-center gap-2">
                                    <span class="w-8 text-sm font-medium"><?php echo $i; ?></span>
                                    <div class="flex-1 bg-gray-200 rounded-full h-2">
                                        <div class="bg-blue-600 h-2 rounded-full" style="width: <?php echo $percent; ?>%"></div>
                                    </div>
                                    <span class="w-12 text-right text-sm text-gray-600"><?php echo $count; ?> (<?php echo round($percent); ?>%)</span>
                                </div>
                            <?php endfor; ?>
                        </div>

                    <?php elseif ($question['type'] === 'yesno'): ?>
                        <!-- Yes/No chart -->
                        <div class="flex gap-8">
                            <?php 
                                $yes = $no = 0;
                                $total = count($question['responses'] ?? []);
                                if ($total > 0) {
                                    foreach ($question['responses'] as $resp) {
                                        if ($resp['answer_value'] === 'yes') $yes++;
                                        else $no++;
                                    }
                                }
                            ?>
                            <div>
                                <div class="text-sm font-medium text-gray-700">Yes</div>
                                <div class="text-2xl font-bold text-green-600"><?php echo $yes; ?></div>
                                <div class="text-xs text-gray-500"><?php echo round(($yes/$total)*100); ?>%</div>
                            </div>
                            <div>
                                <div class="text-sm font-medium text-gray-700">No</div>
                                <div class="text-2xl font-bold text-red-600"><?php echo $no; ?></div>
                                <div class="text-xs text-gray-500"><?php echo round(($no/$total)*100); ?>%</div>
                            </div>
                        </div>

                    <?php elseif ($question['type'] === 'multiple_choice'): ?>
                        <!-- Multiple choice chart -->
                        <div class="space-y-2">
                            <?php 
                                $counts = [];
                                $total = count($question['responses'] ?? []);
                                foreach ($question['responses'] as $resp) {
                                    $value = $resp['answer_value'];
                                    $counts[$value] = ($counts[$value] ?? 0) + 1;
                                }
                                arsort($counts);
                            ?>
                            <?php foreach ($counts as $option => $count): ?>
                                <?php $percent = ($count / $total) * 100; ?>
                                <div class="flex items-center gap-2">
                                    <div class="flex-1">
                                        <div class="flex justify-between mb-1">
                                            <span class="text-sm font-medium"><?php echo htmlspecialchars($option); ?></span>
                                            <span class="text-sm text-gray-600"><?php echo $count; ?> (<?php echo round($percent); ?>%)</span>
                                        </div>
                                        <div class="bg-gray-200 rounded-full h-2">
                                            <div class="bg-blue-600 h-2 rounded-full" style="width: <?php echo $percent; ?>%"></div>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>

                    <?php elseif ($question['type'] === 'file_upload'): ?>
                        <!-- File responses -->
                        <div class="space-y-2">
                            <?php if (!empty($question['files'])): ?>
                                <p class="text-sm text-gray-600 mb-3"><?php echo count($question['files']); ?> file(s) uploaded</p>
                                <?php foreach ($question['files'] as $file): ?>
                                    <div class="p-3 bg-gray-50 rounded flex justify-between items-center text-sm">
                                        <span><?php echo htmlspecialchars($file['original_filename']); ?></span>
                                        <a href="<?php echo $file['file_path']; ?>" class="text-blue-600 hover:text-blue-700">Download</a>
                                    </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <p class="text-gray-500 text-sm">No files uploaded</p>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <?php include __DIR__ . '/../layout/footer.php'; ?>
</div>

<script>
function exportResults(format) {
    window.location.href = `/admin/surveys/<?php echo $survey['id']; ?>/results?export=${format}`;
}
</script>
