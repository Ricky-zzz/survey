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
                            <template x-for="(question, questionIndex) in section.questions" :key="question.id">
                                <div class="border-b border-gray-100 pb-8 last:border-b-0 last:pb-0">
                                    <div class="mb-4">
                                        <label class="block text-lg font-medium text-gray-900 leading-tight">
                                            <span x-text="question.question_text"></span>
                                            <span class="text-red-500 ml-1" x-show="question.required">*</span>
                                        </label>
                                        <p class="text-gray-600 text-sm mt-2" x-text="question.description" x-show="question.description"></p>
                                    </div>

                                    <!-- Text Input -->
                                    <div x-show="question.type === 'text'">
                                        <input 
                                            type="text" 
                                            :name="`responses[${question.id}]`"
                                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-shadow"
                                            :required="question.required"
                                            placeholder="Your answer..."
                                        >
                                    </div>

                                    <!-- Yes/No -->
                                    <div x-show="question.type === 'yesno'" class="space-y-3">
                                        <label class="flex items-center p-3 border border-gray-200 rounded-lg hover:bg-gray-50 cursor-pointer transition-colors">
                                            <input 
                                                type="radio" 
                                                :name="`responses[${question.id}]`" 
                                                value="yes" 
                                                class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300"
                                                :required="question.required"
                                            >
                                            <span class="ml-3 text-gray-700 font-medium">Yes</span>
                                        </label>
                                        <label class="flex items-center p-3 border border-gray-200 rounded-lg hover:bg-gray-50 cursor-pointer transition-colors">
                                            <input 
                                                type="radio" 
                                                :name="`responses[${question.id}]`" 
                                                value="no" 
                                                class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300"
                                                :required="question.required"
                                            >
                                            <span class="ml-3 text-gray-700 font-medium">No</span>
                                        </label>
                                    </div>

                                    <!-- Scale -->
                                    <div x-show="question.type === 'scale'" class="flex justify-center space-x-3 py-2">
                                        <template x-for="i in 5" :key="i">
                                            <label class="flex flex-col items-center cursor-pointer group">
                                                <input 
                                                    type="radio" 
                                                    :name="`responses[${question.id}]`" 
                                                    :value="i" 
                                                    class="sr-only"
                                                    :required="question.required"
                                                    @change="$event.target.parentElement.parentElement.querySelectorAll('.scale-btn').forEach(btn => btn.classList.remove('bg-blue-600', 'text-white', 'border-blue-600')); $event.target.nextElementSibling.classList.add('bg-blue-600', 'text-white', 'border-blue-600');"
                                                >
                                                <div class="scale-btn w-12 h-12 flex items-center justify-center border-2 border-gray-300 rounded-full text-gray-700 font-medium group-hover:border-blue-400 group-hover:bg-blue-50 transition-all duration-200">
                                                    <span x-text="i"></span>
                                                </div>
                                                <span class="text-xs text-gray-500 mt-1" x-text="i"></span>
                                            </label>
                                        </template>
                                    </div>

                                    <!-- Multiple Choice -->
                                    <div x-show="question.type === 'multiple_choice'" class="space-y-3">
                                        <template x-for="option in question.options || []" :key="option.id">
                                            <label class="flex items-center p-3 border border-gray-200 rounded-lg hover:bg-gray-50 cursor-pointer transition-colors">
                                                <input 
                                                    type="radio" 
                                                    :name="`responses[${question.id}]`" 
                                                    :value="option.value || option.option_text" 
                                                    class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300"
                                                    :required="question.required"
                                                >
                                                <span class="ml-3 text-gray-700" x-text="option.option_text || option.value"></span>
                                            </label>
                                        </template>
                                    </div>

                                    <!-- File Upload -->
                                    <div x-show="question.type === 'file_upload'">
                                        <input 
                                            type="file" 
                                            :name="`files[${question.id}]`"
                                            accept=".pdf"
                                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-medium file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100"
                                            :required="question.required"
                                        >
                                        <p class="text-xs text-gray-500 mt-2">PDF files only, max 5MB</p>
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

                init() {
                    this.totalSections = this.sections.length;
                    this.updateProgress();
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