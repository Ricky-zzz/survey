<?php include __DIR__ . '/../layout/head.php'; ?>

<script>
    // Set sections data IMMEDIATELY so Alpine can use it when x-data initializes
    window.surveyData = <?= json_encode($sections ?? []) ?>;
    console.log('PHP sections count:', <?= count($sections ?? []) ?>);
    console.log('window.surveyData NOW set to:', window.surveyData);
</script>

<div class="min-h-screen bg-gray-50" x-data="surveyForm()" x-init="init()">
    <?php if (isset($showPasskeyForm) && $showPasskeyForm): ?>
        <!-- Passkey Form for Private Surveys -->
        <div class="flex items-center justify-center min-h-screen">
            <div class="bg-white rounded-lg shadow p-8 max-w-md w-full mx-4">
                <h2 class="text-xl font-semibold text-gray-900 mb-6">This survey is private</h2>
                
                <?php if (isset($error) && $error): ?>
                    <div class="bg-red-50 border border-red-200 rounded-md p-4 mb-4">
                        <p class="text-red-800 text-sm"><?= htmlspecialchars($error) ?></p>
                    </div>
                <?php endif; ?>
                
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
                        class="w-full bg-blue-600 text-white py-3 px-4 rounded-lg hover:bg-blue-700 transition font-medium"
                    >
                        Access Survey
                    </button>
                </form>
            </div>
        </div>
    <?php else: ?>
        <!-- Progress Header -->
        <div class="bg-white shadow-sm sticky top-0 z-10">
            <div class="max-w-4xl mx-auto px-6 py-4">
                <div class="flex items-center justify-between mb-3">
                    <h1 class="text-xl font-semibold text-gray-900">
                        <?= htmlspecialchars($survey['name']) ?>
                    </h1>
                    <span class="text-sm text-gray-500" x-text="`Section ${currentSection + 1} of ${totalSections}`"></span>
                </div>
                
                <!-- Progress bar -->
                <div class="w-full bg-gray-200 rounded-full h-3">
                    <div 
                        class="bg-blue-600 h-3 rounded-full transition-all duration-500 ease-out" 
                        :style="`width: ${progress}%`"
                    ></div>
                </div>
                
                <!-- Section indicators -->
                <div class="flex justify-between mt-2 text-xs text-gray-400" x-show="totalSections > 1">
                    <template x-for="(section, index) in sections" :key="index">
                        <span 
                            :class="index === currentSection ? 'text-blue-600 font-medium' : index < currentSection ? 'text-green-600' : 'text-gray-400'"
                            x-text="section.title || `Section ${index + 1}`"
                            class="truncate max-w-[120px]"
                        ></span>
                    </template>
                </div>
            </div>
        </div>

        <!-- Survey Content -->
        <main class="max-w-3xl mx-auto py-8 px-6">
            <form id="surveyForm" method="POST" action="/surveys/<?= $survey['id'] ?>/submit" enctype="multipart/form-data" @submit="handleSubmit" x-ref="surveyForm">
                
                <!-- Section Content -->
                <template x-for="(section, sectionIndex) in sections" :key="section.id">
                    <div 
                        class="bg-white rounded-xl shadow-lg border border-gray-200 overflow-hidden"
                        x-show="currentSection === sectionIndex"
                        x-transition:enter="transform transition ease-out duration-300"
                        x-transition:enter-start="translate-x-8 opacity-0 scale-95"
                        x-transition:enter-end="translate-x-0 opacity-100 scale-100"
                        x-transition:leave="transform transition ease-in duration-200"
                        x-transition:leave-start="translate-x-0 opacity-100 scale-100"
                        x-transition:leave-end="-translate-x-8 opacity-0 scale-95"
                    >
                        <!-- Section Header -->
                        <div class="px-8 py-6 bg-gradient-to-r from-blue-50 to-indigo-50 border-b border-gray-200">
                            <h2 class="text-2xl font-bold text-gray-800" x-text="section.title"></h2>
                            <p class="text-gray-600 mt-2" x-text="section.description" x-show="section.description"></p>
                        </div>

                        <!-- Questions -->
                        <div class="px-8 py-8 space-y-8">
                            <div x-show="!(section.questions && section.questions.length > 0)" class="text-center py-8 text-gray-500">
                                No questions in this section (DEBUG: <span x-text="section.questions ? section.questions.length : 'questions is null'"></span>)
                            </div>
                            <!-- Render questions, grouping matrix questions into tables -->
                            <template x-for="(item, itemIndex) in getDisplayItems(section.questions)" :key="item.id || item.groupId">
                                <div>
                                <!-- Individual Question (non-matrix) -->
                                <template x-if="item.type === 'single'">
                                    <div class="border-b border-gray-100 pb-8 last:border-b-0 last:pb-0">
                                    <div class="mb-4">
                                        <label class="block text-lg font-medium text-gray-900 leading-tight">
                                            <span x-text="item.question?.question_text"></span>
                                            <span class="text-red-500 ml-1" x-show="item.question?.required">*</span>
                                        </label>
                                        <p class="text-gray-600 text-sm mt-2" x-text="item.question?.description" x-show="item.question?.description"></p>
                                    </div>

                                    <!-- Text Input -->
                                    <div x-show="item.type === 'single' && item.question?.type === 'text'">
                                        <input 
                                            type="text" 
                                            :name="`responses[${item.question?.id}]`"
                                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-shadow"
                                            :required="item.question?.required"
                                            placeholder="Your answer..."
                                        >
                                    </div>

                                    <!-- Yes/No -->
                                    <div x-show="item.type === 'single' && item.question?.type === 'yesno'" class="space-y-3">
                                        <label class="flex items-center p-3 border border-gray-200 rounded-lg hover:bg-gray-50 cursor-pointer transition-colors">
                                            <input 
                                                type="radio" 
                                                :name="`responses[${item.question?.id}]`" 
                                                value="yes" 
                                                class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300"
                                                :required="item.question?.required"
                                            >
                                            <span class="ml-3 text-gray-700 font-medium">Yes</span>
                                        </label>
                                        <label class="flex items-center p-3 border border-gray-200 rounded-lg hover:bg-gray-50 cursor-pointer transition-colors">
                                            <input 
                                                type="radio" 
                                                :name="`responses[${item.question?.id}]`" 
                                                value="no" 
                                                class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300"
                                                :required="item.question?.required"
                                            >
                                            <span class="ml-3 text-gray-700 font-medium">No</span>
                                        </label>
                                    </div>

                                    <!-- Scale (single, non-matrix) -->
                                    <div x-show="item.type === 'single' && item.question?.type === 'scale'">
                                        <div class="flex items-start justify-between gap-2">
                                            <template x-for="(option, idx) in (item.question?.options || [])" :key="option.id">
                                                <label class="flex flex-col items-center cursor-pointer group flex-1">
                                                    <input 
                                                        type="radio" 
                                                        :name="`responses[${item.question?.id}]`" 
                                                        :value="option.value" 
                                                        class="sr-only"
                                                        :required="item.question?.required"
                                                        @change="scaleAnswers[item.question?.id] = option.value"
                                                    >
                                                    <div 
                                                        class="w-12 h-12 flex items-center justify-center border-2 rounded-full font-medium transition-all duration-200"
                                                        :class="scaleAnswers[item.question?.id] === option.value 
                                                            ? 'bg-blue-600 text-white border-blue-600 shadow-md' 
                                                            : 'border-gray-300 text-gray-700 group-hover:border-blue-400 group-hover:bg-blue-50'"
                                                    >
                                                        <span x-text="option.value"></span>
                                                    </div>
                                                    <span 
                                                        class="text-xs mt-1 text-center leading-tight max-w-[80px]"
                                                        :class="scaleAnswers[item.question?.id] === option.value ? 'text-blue-600 font-medium' : 'text-gray-400'"
                                                        x-text="option.option_text"
                                                    ></span>
                                                </label>
                                            </template>
                                        </div>
                                    </div>

                                    <!-- Multiple Choice -->
                                    <div x-show="item.type === 'single' && item.question?.type === 'multiple_choice'" class="space-y-3">
                                        <template x-for="option in item.question?.options || []" :key="option.id">
                                            <label class="flex items-center p-3 border border-gray-200 rounded-lg hover:bg-gray-50 cursor-pointer transition-colors">
                                                <input 
                                                    type="radio" 
                                                    :name="`responses[${item.question?.id}]`" 
                                                    :value="option.value || option.option_text" 
                                                    class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300"
                                                    :required="item.question?.required"
                                                >
                                                <span class="ml-3 text-gray-700" x-text="option.option_text || option.value"></span>
                                            </label>
                                        </template>
                                    </div>

                                    <!-- File Upload -->
                                    <div x-show="item.type === 'single' && item.question?.type === 'file_upload'">
                                        <input 
                                            type="file" 
                                            :name="`files[${item.question?.id}]`"
                                            accept=".pdf"
                                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-medium file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100"
                                            :required="item.question?.required"
                                        >
                                        <p class="text-xs text-gray-500 mt-2">PDF files only, max 5MB</p>
                                    </div>
                                </div>
                                </template>

                                <!-- Matrix Question (grouped scales) -->
                                <template x-if="item.type === 'matrix'">
                                    <div class="bg-white rounded-lg border border-gray-300 p-6">
                                        <h3 class="text-lg font-semibold text-gray-900 mb-6">
                                            <span x-text="item.matrixTitle"></span>
                                            <span class="text-red-500 ml-1">*</span>
                                        </h3>
                                        
                                        <!-- Matrix Table -->
                                        <div class="overflow-x-auto border border-gray-200 rounded-lg">
                                            <table class="w-full text-sm">
                                                <!-- Header Row -->
                                                <thead>
                                                    <tr class="bg-gray-100 border-b border-gray-300">
                                                        <th class="text-left py-3 px-4 font-semibold text-gray-700 w-2/5 border-r border-gray-300">Aspect</th>
                                                        <template x-for="(option, idx) in (item.questions[0]?.options || [])" :key="`header-${idx}`">
                                                            <th class="text-center py-3 px-3 font-semibold text-gray-700 border-r border-gray-300 last:border-r-0">
                                                                <span class="block font-bold" x-text="option.value"></span>
                                                                <span class="block text-xs text-gray-600 mt-1" x-text="option.option_text"></span>
                                                            </th>
                                                        </template>
                                                    </tr>
                                                </thead>
                                                <!-- Body Rows -->
                                                <tbody>
                                                    <template x-for="(question, qIdx) in item.questions" :key="`row-${question.id}`">
                                                        <tr class="border-b border-gray-200" :class="qIdx % 2 === 0 ? 'bg-white' : 'bg-gray-50'">
                                                            <td class="py-4 px-4 font-medium text-gray-800 border-r border-gray-300">
                                                                <span x-text="question.question_text"></span>
                                                            </td>
                                                            <template x-for="(option, optIdx) in (item.questions[0]?.options || [])" :key="`cell-${question.id}-${optIdx}`">
                                                                <td class="py-4 px-3 text-center border-r border-gray-300 last:border-r-0">
                                                                    <label class="inline-flex items-center justify-center cursor-pointer">
                                                                        <input 
                                                                            type="radio" 
                                                                            :name="`responses[${question.id}]`" 
                                                                            :value="option.value" 
                                                                            class="sr-only"
                                                                            @change="scaleAnswers[question.id] = option.value"
                                                                        >
                                                                        <div 
                                                                            class="w-8 h-8 border-2 rounded-full flex items-center justify-center cursor-pointer transition-all duration-200"
                                                                            :class="scaleAnswers[question.id] === option.value 
                                                                                ? 'bg-blue-600 border-blue-600 shadow-md' 
                                                                                : 'border-gray-400 hover:border-blue-500 hover:shadow-sm'"
                                                                        >
                                                                            <div 
                                                                                class="w-3 h-3 rounded-full bg-current transition-colors"
                                                                                :class="scaleAnswers[question.id] === option.value ? 'text-white' : 'hidden'"
                                                                            ></div>
                                                                        </div>
                                                                    </label>
                                                                </td>
                                                            </template>
                                                        </tr>
                                                    </template>
                                                </tbody>
                                            </table>
                                        </div>
                                </div>
                            </template>
                                </div>
                            </template>
                        </div>
                    </div>
                </template>
            </form>

            <!-- Navigation Bar - Outside form, always visible -->
            <div class="px-8 py-6 bg-gray-50 border-t border-gray-200 flex justify-between items-center mt-8 rounded-b-lg">
                <div class="text-sm text-gray-600">Section <span x-text="currentSection + 1"></span> of <span x-text="totalSections"></span></div>
                
                <!-- Previous Button -->
                <button 
                    type="button"
                    @click="previousSection()"
                    x-show="currentSection > 0"
                    class="flex items-center px-6 py-3 text-gray-600 border border-gray-300 rounded-lg hover:bg-white hover:text-gray-800 transition-all duration-200 font-medium"
                >
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                    </svg>
                    Previous
                </button>
                
                <div class="flex gap-4">
                    <!-- Next Button -->
                    <button 
                        type="button"
                        @click="nextSection()"
                        x-show="currentSection < totalSections - 1"
                        class="flex items-center px-8 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-all duration-200 font-medium shadow-md"
                    >
                        Next
                        <svg class="w-4 h-4 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                        </svg>
                    </button>
                    
                    <!-- Submit Button -->
                    <button 
                        form="surveyForm"
                        type="submit"
                        x-show="currentSection === totalSections - 1"
                        class="flex items-center px-8 py-3 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-all duration-200 font-medium shadow-md"
                    >
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                        Submit Survey
                    </button>
                </div>
            </div>
        </main>
    <?php endif; ?>

</div>

</body>
</html>