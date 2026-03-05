<?php include __DIR__ . '/../layout/head.php'; ?>

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
            <form method="POST" action="/surveys/<?= $survey['id'] ?>/submit" enctype="multipart/form-data" @submit="handleSubmit" x-ref="surveyForm">
                
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
                            <!-- Render questions, grouping matrix questions into tables -->
                            <template x-for="(item, itemIndex) in getDisplayItems(section.questions)" :key="item.id || item.groupId">
                                <!-- Individual Question (non-matrix) -->
                                <div x-show="item.type === 'single'" class="border-b border-gray-100 pb-8 last:border-b-0 last:pb-0">
                                    <div class="mb-4">
                                        <label class="block text-lg font-medium text-gray-900 leading-tight">
                                            <span x-text="item.question.question_text"></span>
                                            <span class="text-red-500 ml-1" x-show="item.question.required">*</span>
                                        </label>
                                        <p class="text-gray-600 text-sm mt-2" x-text="item.question.description" x-show="item.question.description"></p>
                                    </div>

                                    <!-- Text Input -->
                                    <div x-show="item.question.type === 'text'">
                                        <input 
                                            type="text" 
                                            :name="`responses[${item.question.id}]`"
                                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-shadow"
                                            :required="item.question.required"
                                            placeholder="Your answer..."
                                        >
                                    </div>

                                    <!-- Yes/No -->
                                    <div x-show="item.question.type === 'yesno'" class="space-y-3">
                                        <label class="flex items-center p-3 border border-gray-200 rounded-lg hover:bg-gray-50 cursor-pointer transition-colors">
                                            <input 
                                                type="radio" 
                                                :name="`responses[${item.question.id}]`" 
                                                value="yes" 
                                                class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300"
                                                :required="item.question.required"
                                            >
                                            <span class="ml-3 text-gray-700 font-medium">Yes</span>
                                        </label>
                                        <label class="flex items-center p-3 border border-gray-200 rounded-lg hover:bg-gray-50 cursor-pointer transition-colors">
                                            <input 
                                                type="radio" 
                                                :name="`responses[${item.question.id}]`" 
                                                value="no" 
                                                class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300"
                                                :required="item.question.required"
                                            >
                                            <span class="ml-3 text-gray-700 font-medium">No</span>
                                        </label>
                                    </div>

                                    <!-- Scale (single, non-matrix) -->
                                    <div x-show="item.question.type === 'scale'">
                                        <div class="flex items-start justify-between gap-2">
                                            <template x-for="(option, idx) in (item.question.options || [])" :key="option.id">
                                                <label class="flex flex-col items-center cursor-pointer group flex-1">
                                                    <input 
                                                        type="radio" 
                                                        :name="`responses[${item.question.id}]`" 
                                                        :value="option.value" 
                                                        class="sr-only"
                                                        :required="item.question.required"
                                                        @change="scaleAnswers[item.question.id] = option.value"
                                                    >
                                                    <div 
                                                        class="w-12 h-12 flex items-center justify-center border-2 rounded-full font-medium transition-all duration-200"
                                                        :class="scaleAnswers[item.question.id] === option.value 
                                                            ? 'bg-blue-600 text-white border-blue-600 shadow-md' 
                                                            : 'border-gray-300 text-gray-700 group-hover:border-blue-400 group-hover:bg-blue-50'"
                                                    >
                                                        <span x-text="option.value"></span>
                                                    </div>
                                                    <span 
                                                        class="text-xs mt-1 text-center leading-tight max-w-[80px]"
                                                        :class="scaleAnswers[item.question.id] === option.value ? 'text-blue-600 font-medium' : 'text-gray-400'"
                                                        x-text="option.option_text"
                                                    ></span>
                                                </label>
                                            </template>
                                        </div>
                                    </div>

                                    <!-- Multiple Choice -->
                                    <div x-show="item.question.type === 'multiple_choice'" class="space-y-3">
                                        <template x-for="option in item.question.options || []" :key="option.id">
                                            <label class="flex items-center p-3 border border-gray-200 rounded-lg hover:bg-gray-50 cursor-pointer transition-colors">
                                                <input 
                                                    type="radio" 
                                                    :name="`responses[${item.question.id}]`" 
                                                    :value="option.value || option.option_text" 
                                                    class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300"
                                                    :required="item.question.required"
                                                >
                                                <span class="ml-3 text-gray-700" x-text="option.option_text || option.value"></span>
                                            </label>
                                        </template>
                                    </div>

                                    <!-- File Upload -->
                                    <div x-show="item.question.type === 'file_upload'">
                                        <input 
                                            type="file" 
                                            :name="`files[${item.question.id}]`"
                                            accept=".pdf"
                                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-medium file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100"
                                            :required="item.question.required"
                                        >
                                        <p class="text-xs text-gray-500 mt-2">PDF files only, max 5MB</p>
                                    </div>
                                </div>

                                <!-- Matrix Question (grouped scales) -->
                                <div x-show="item.type === 'matrix'" class="bg-white rounded-lg border border-gray-200 p-6 border-b-0 last:border-b">
                                    <h3 class="text-lg font-semibold text-gray-900 mb-2">
                                        <span x-text="item.matrixTitle"></span>
                                        <span class="text-red-500 ml-1">*</span>
                                    </h3>
                                    
                                    <!-- Matrix Table -->
                                    <div class="overflow-x-auto">
                                        <table class="w-full text-sm">
                                            <!-- Header -->
                                            <thead>
                                                <tr class="border-b-2 border-gray-300">
                                                    <th class="text-left py-3 px-4 font-semibold text-gray-700 w-1/3">Aspect</th>
                                                    <template x-for="(option, idx) in (item.questions[0]?.options || [])" :key="option.id">
                                                        <th class="text-center py-3 px-2 font-semibold text-gray-600">
                                                            <div class="text-xs" x-text="option.option_text"></div>
                                                        </th>
                                                    </template>
                                                </tr>
                                            </thead>
                                            <!-- Rows -->
                                            <tbody>
                                                <template x-for="question in item.questions" :key="question.id">
                                                    <tr class="border-b border-gray-200 hover:bg-blue-50 transition-colors">
                                                        <!-- Question -->
                                                        <td class="py-4 px-4 font-medium text-gray-800">
                                                            <span x-text="question.question_text"></span>
                                                        </td>
                                                        <!-- Options -->
                                                        <template x-for="(option, idx) in (question.options || [])" :key="option.id">
                                                            <td class="py-4 px-2 text-center">
                                                                <label class="inline-flex">
                                                                    <input 
                                                                        type="radio" 
                                                                        :name="`responses[${question.id}]`" 
                                                                        :value="option.value" 
                                                                        class="h-5 w-5 text-blue-600 focus:ring-blue-500 cursor-pointer"
                                                                        :required="question.required"
                                                                        @change="scaleAnswers[question.id] = option.value"
                                                                    >
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

                        <!-- Navigation -->
                        <div class="px-8 py-6 bg-gray-50 border-t border-gray-200 flex justify-between items-center">
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
                            
                            <!-- Next/Submit Button -->
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
                </template>
            </form>
        </main>
    <?php endif; ?>

    <!-- Alpine.js Survey Component -->
    <script>
        function surveyForm() {
            return {
                sections: <?= json_encode($sections ?? []) ?>,
                currentSection: 0,
                totalSections: 0,
                progress: 0,
                scaleAnswers: {},

                init() {
                    this.totalSections = this.sections.length;
                    this.updateProgress();
                },

                /**
                 * Group questions, separating matrix groups from individual questions
                 */
                getDisplayItems(questions) {
                    if (!questions) return [];
                    
                    const items = [];
                    const processedIds = new Set();
                    const matrixGroups = {};

                    // First pass: identify and group matrix questions
                    questions.forEach(q => {
                        if (q.matrix_group_id && !matrixGroups[q.matrix_group_id]) {
                            matrixGroups[q.matrix_group_id] = [];
                        }
                        if (q.matrix_group_id) {
                            matrixGroups[q.matrix_group_id].push(q);
                            processedIds.add(q.id);
                        }
                    });

                    // Second pass: add items in order
                    questions.forEach(q => {
                        if (q.matrix_group_id && !processedIds.has(-q.id)) {
                            // Add matrix group once
                            const groupId = q.matrix_group_id;
                            items.push({
                                type: 'matrix',
                                groupId: groupId,
                                matrixTitle: groupId.split('-').map(w => w.charAt(0).toUpperCase() + w.slice(1)).join(' '),
                                questions: matrixGroups[groupId].sort((a, b) => a.order_sequence - b.order_sequence)
                            });
                            processedIds.add(-q.id); // Mark as processed using negative ID
                            matrixGroups[groupId].forEach(mq => processedIds.delete(mq.id)); // Remove from individual processing
                        }
                    });

                    // Third pass: add individual questions
                    questions.forEach(q => {
                        if (!q.matrix_group_id) {
                            items.push({
                                type: 'single',
                                id: q.id,
                                question: q
                            });
                        }
                    });

                    return items;
                },

                nextSection() {
                    if (this.validateCurrentSection()) {
                        if (this.currentSection < this.totalSections - 1) {
                            this.currentSection++;
                            this.updateProgress();
                            this.scrollToTop();
                        }
                    }
                },

                previousSection() {
                    if (this.currentSection > 0) {
                        this.currentSection--;
                        this.updateProgress();
                        this.scrollToTop();
                    }
                },

                validateCurrentSection() {
                    const currentSectionElement = this.$refs.surveyForm.querySelector('[x-show="currentSection === ' + this.currentSection + '"]');
                    const requiredFields = currentSectionElement?.querySelectorAll('[required]');
                    
                    if (requiredFields) {
                        for (let field of requiredFields) {
                            if (!field.value.trim() && field.type !== 'radio' && field.type !== 'checkbox') {
                                field.focus();
                                field.classList.add('border-red-500', 'ring-red-500');
                                setTimeout(() => {
                                    field.classList.remove('border-red-500', 'ring-red-500');
                                }, 3000);
                                return false;
                            }
                            
                            // Validate radio groups
                            if (field.type === 'radio') {
                                const radioGroup = currentSectionElement.querySelectorAll(`input[name="${field.name}"]`);
                                const isChecked = Array.from(radioGroup).some(radio => radio.checked);
                                if (!isChecked) {
                                    radioGroup[0].focus();
                                    return false;
                                }
                            }
                        }
                    }
                    return true;
                },

                updateProgress() {
                    this.progress = ((this.currentSection + 1) / this.totalSections) * 100;
                },

                scrollToTop() {
                    window.scrollTo({ top: 0, behavior: 'smooth' });
                },

                handleSubmit(event) {
                    if (!this.validateCurrentSection()) {
                        event.preventDefault();
                        return false;
                    }
                }
            }
        }
    </script>
</div>

</body>
</html>