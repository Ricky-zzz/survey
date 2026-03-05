function surveyForm() {
    console.log('surveyForm() called, window.surveyData:', window.surveyData);
    return {
        sections: window.surveyData || [],
        currentSection: 0,
        totalSections: 0,
        progress: 0,
        scaleAnswers: {},

        init() {
            console.log('init() called');
            console.log('this.sections:', this.sections);
            console.log('this.sections.length:', this.sections.length);
            this.totalSections = this.sections.length;
            this.updateProgress();
            console.log('Survey initialized:', {
                currentSection: this.currentSection,
                totalSections: this.totalSections,
                showNext: this.currentSection < this.totalSections - 1,
                showSubmit: this.currentSection === this.totalSections - 1
            });
        },

        /**
         * Group questions, separating matrix groups from individual questions
         */
        getDisplayItems(questions) {
            if (!questions) return [];
            
            const items = [];
            const matrixGroups = {};
            const processedGroups = new Set();

            // Group matrix questions by matrix_group_id
            questions.forEach(q => {
                if (q.matrix_group_id) {
                    if (!matrixGroups[q.matrix_group_id]) {
                        matrixGroups[q.matrix_group_id] = [];
                    }
                    matrixGroups[q.matrix_group_id].push(q);
                }
            });

            // Add items in order: matrix groups first, then individual questions
            questions.forEach(q => {
                // Add matrix group (only once per group)
                if (q.matrix_group_id && !processedGroups.has(q.matrix_group_id)) {
                    const groupId = q.matrix_group_id;
                    const sortedQuestions = matrixGroups[groupId].sort((a, b) => a.order_sequence - b.order_sequence);
                    const matrixItem = {
                        type: 'matrix',
                        groupId: groupId,
                        matrixTitle: groupId.split('-').map(w => w.charAt(0).toUpperCase() + w.slice(1)).join(' '),
                        questions: sortedQuestions
                    };
                    console.log('Matrix item:', matrixItem);
                    console.log('First question options:', sortedQuestions[0]?.options);
                    items.push(matrixItem);
                    processedGroups.add(groupId);
                }
                // Add individual question (only if not in a matrix group)
                else if (!q.matrix_group_id) {
                    items.push({
                        type: 'single',
                        id: q.id,
                        question: q
                    });
                }
            });

            console.log('All items:', items);
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
