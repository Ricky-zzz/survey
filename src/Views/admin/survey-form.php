<?php include __DIR__ . '/../layout/head.php'; ?>
<div class="flex h-screen">
    <?php include __DIR__ . '/../layout/app-layout.php'; ?>
    
    <form method="POST" action="<?php echo isset($survey) ? '/admin/surveys/' . $survey['id'] : '/admin/surveys'; ?>" class="space-y-6 max-w-4xl" x-data="surveyData" data-survey-id="<?php echo $survey['id'] ?? ''; ?>">
        <!-- Alerts -->
        <div x-show="error" class="bg-red-50 border border-red-200 rounded-lg p-4 text-red-700 text-sm" style="display: none;">
            <p x-text="error"></p>
        </div>
        
        <div x-show="successMessage" class="bg-green-50 border border-green-200 rounded-lg p-4 text-green-700 text-sm" style="display: none;">
            <p x-text="successMessage"></p>
        </div>

        <!-- Survey Details -->
        <div class="bg-white rounded-lg shadow p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Survey Details</h3>
            
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Title *</label>
                    <input 
                        type="text" 
                        name="name" 
                        value="<?php echo htmlspecialchars($survey['name'] ?? ''); ?>"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                        required
                    >
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Description</label>
                    <textarea 
                        name="description" 
                        rows="3"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                    ><?php echo htmlspecialchars($survey['description'] ?? ''); ?></textarea>
                </div>
                
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="flex items-center">
                            <input 
                                type="checkbox" 
                                name="is_public" 
                                value="1"
                                <?php echo (isset($survey) && $survey['is_public']) ? 'checked' : ''; ?>
                                class="rounded"
                                @change="isPublic = $el.checked"
                            >
                            <span class="ml-2 text-sm text-gray-700">Public Survey</span>
                        </label>
                    </div>
                    
                    <div x-show="!isPublic" class="transition-all">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Passcode (for private surveys)</label>
                        <input 
                            type="text" 
                            name="passkey" 
                            value="<?php echo htmlspecialchars($survey['passkey'] ?? ''); ?>"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                        >
                    </div>
                </div>
            </div>
        </div>

        <!-- Sections -->
        <?php if (isset($survey)): ?>
            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg font-semibold text-gray-900">Sections</h3>
                    <button 
                        type="button" 
                        class="bg-blue-600 text-white px-3 py-1 rounded text-sm hover:bg-blue-700 disabled:opacity-50" 
                        @click="addSection()"
                        :disabled="loading"
                    >
                        <span x-show="!loading">Add Section</span>
                        <span x-show="loading">Adding...</span>
                    </button>
                </div>
                
                <div id="sections" class="space-y-4">
                    <template x-for="section in sections" :key="section.id">
                        <div class="border border-gray-300 rounded p-4" :data-section-id="section.id">
                            <div class="flex justify-between items-start mb-4">
                                <div class="flex-1">
                                    <input 
                                        type="text" 
                                        :value="section.title"
                                        @change="updateSection(section.id, { title: $el.value })"
                                        class="w-full px-3 py-2 border border-gray-300 rounded mb-2 text-sm"
                                        placeholder="Section title"
                                    >
                                    <input 
                                        type="text" 
                                        :value="section.description || ''"
                                        @change="updateSection(section.id, { description: $el.value })"
                                        class="w-full px-3 py-2 border border-gray-300 rounded text-sm"
                                        placeholder="Section description (optional)"
                                    >
                                </div>
                                <template x-if="!section.is_respondent_info">
                                    <button 
                                        type="button" 
                                        class="text-red-600 hover:text-red-700 ml-4"
                                        @click="deleteSection(section.id)"
                                    >
                                        ✕
                                    </button>
                                </template>
                            </div>
                            
                            <!-- Questions -->
                            <div class="bg-gray-50 p-4 rounded space-y-3">
                                <div class="flex justify-between items-center">
                                    <p class="text-sm font-medium text-gray-700">Questions</p>
                                    <button 
                                        type="button" 
                                        class="text-blue-600 text-sm hover:text-blue-700"
                                        @click="addQuestion(section.id)"
                                    >
                                        + Add Question
                                    </button>
                                </div>
                                
                                <div class="questions space-y-2" :data-section="section.id">
                                    <template x-for="question in section.questions || []" :key="question.id">
                                        <div class="bg-white p-3 rounded border border-gray-200" :data-question-id="question.id">
                                            <div class="flex justify-between mb-2">
                                                <input 
                                                    type="text" 
                                                    :value="question.question_text"
                                                    @change="updateQuestion(question.id, { question_text: $el.value })"
                                                    class="flex-1 px-2 py-1 border border-gray-300 rounded text-sm mr-2"
                                                    placeholder="Question text"
                                                >
                                                <button 
                                                    type="button" 
                                                    class="text-red-600 hover:text-red-700 text-sm"
                                                    @click="deleteQuestion(question.id)"
                                                >
                                                    ✕
                                                </button>
                                            </div>
                                            
                                            <select 
                                                :value="question.type"
                                                @change="updateQuestion(question.id, { type: $el.value })"
                                                class="w-full px-2 py-1 border border-gray-300 rounded text-sm mb-2"
                                            >
                                                <option value="text">Short text</option>
                                                <option value="yesno">Yes/No</option>
                                                <option value="scale">Scale (1-5)</option>
                                                <option value="multiple_choice">Multiple choice</option>
                                                <option value="file_upload">File upload</option>
                                            </select>
                                            
                                            <label class="flex items-center text-sm">
                                                <input 
                                                    type="checkbox" 
                                                    :checked="question.required"
                                                    @change="updateQuestion(question.id, { required: $el.checked })"
                                                    class="mr-2"
                                                >
                                                Required
                                            </label>
                                        </div>
                                    </template>
                                </div>
                            </div>
                        </div>
                    </template>
                </div>
            </div>
        <?php endif; ?>

        <!-- Actions -->
        <div class="flex gap-4">
            <a href="/admin/surveys" class="flex-1 text-center px-6 py-3 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition font-medium">
                Cancel
            </a>
            <button 
                type="submit" 
                class="flex-1 bg-blue-600 text-white px-6 py-3 rounded-lg hover:bg-blue-700 transition font-medium disabled:opacity-50"
                :disabled="loading"
            >
                <span x-show="!loading"><?php echo isset($survey) ? 'Update Survey' : 'Create Survey'; ?></span>
                <span x-show="loading">Saving...</span>
            </button>
        </div>
    </form>

    <?php include __DIR__ . '/../layout/footer.php'; ?>
</div>

<script type="module">
    import surveyData from './js/survey-manager.js';
    
    document.addEventListener('alpine:init', () => {
        Alpine.data('surveyData', surveyData);
    });
</script>
