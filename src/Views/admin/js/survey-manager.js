export default {
    surveyId: null,
    sections: [],
    loading: false,
    error: null,
    successMessage: null,

    init() {
        this.surveyId = document.querySelector('[data-survey-id]')?.dataset.surveyId;
        this.loadSections();
    },

    async loadSections() {
        if (!this.surveyId) return;
        
        try {
            this.loading = true;
            const response = await fetch(`/admin/surveys/${this.surveyId}/sections`);
            if (response.ok) {
                this.sections = await response.json();
            }
        } catch (error) {
            this.error = 'Failed to load sections: ' + error.message;
            console.error(error);
        } finally {
            this.loading = false;
        }
    },

    async addSection() {
        try {
            this.loading = true;
            this.error = null;
            
            const response = await fetch(`/admin/surveys/${this.surveyId}/sections`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify({
                    title: 'New Section',
                    description: '',
                    order_sequence: this.sections.length + 1
                })
            });

            if (response.ok) {
                const newSection = await response.json();
                this.sections.push(newSection);
                this.showSuccess('Section added successfully');
            } else {
                this.error = 'Failed to add section';
            }
        } catch (error) {
            this.error = 'Error adding section: ' + error.message;
            console.error(error);
        } finally {
            this.loading = false;
        }
    },

    async updateSection(sectionId, updates) {
        try {
            this.loading = true;
            this.error = null;

            const response = await fetch(`/admin/surveys/${this.surveyId}/sections/${sectionId}`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify(updates)
            });

            if (response.ok) {
                const sectionIndex = this.sections.findIndex(s => s.id === sectionId);
                if (sectionIndex >= 0) {
                    this.sections[sectionIndex] = { ...this.sections[sectionIndex], ...updates };
                }
                this.showSuccess('Section updated successfully');
            } else {
                this.error = 'Failed to update section';
            }
        } catch (error) {
            this.error = 'Error updating section: ' + error.message;
            console.error(error);
        } finally {
            this.loading = false;
        }
    },

    async deleteSection(sectionId) {
        if (!confirm('Delete this section and all its questions?')) return;

        try {
            this.loading = true;
            this.error = null;

            const response = await fetch(`/admin/surveys/${this.surveyId}/sections/${sectionId}/delete`, {
                method: 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });

            if (response.ok) {
                this.sections = this.sections.filter(s => s.id !== sectionId);
                this.showSuccess('Section deleted successfully');
            } else {
                this.error = 'Failed to delete section';
            }
        } catch (error) {
            this.error = 'Error deleting section: ' + error.message;
            console.error(error);
        } finally {
            this.loading = false;
        }
    },

    async addQuestion(sectionId) {
        try {
            this.loading = true;
            this.error = null;

            const section = this.sections.find(s => s.id === sectionId);
            if (!section) return;

            const response = await fetch(`/admin/surveys/${this.surveyId}/questions`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify({
                    section_id: sectionId,
                    question_text: 'New Question',
                    type: 'text',
                    required: false,
                    order_sequence: (section.questions?.length || 0) + 1
                })
            });

            if (response.ok) {
                const newQuestion = await response.json();
                if (!section.questions) section.questions = [];
                section.questions.push(newQuestion);
                this.showSuccess('Question added successfully');
            } else {
                this.error = 'Failed to add question';
            }
        } catch (error) {
            this.error = 'Error adding question: ' + error.message;
            console.error(error);
        } finally {
            this.loading = false;
        }
    },

    async updateQuestion(questionId, updates) {
        try {
            this.loading = true;
            this.error = null;

            const response = await fetch(`/admin/surveys/${this.surveyId}/questions/${questionId}`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify(updates)
            });

            if (response.ok) {
                // Find and update the question in nested structure
                for (let section of this.sections) {
                    const questionIndex = section.questions?.findIndex(q => q.id === questionId);
                    if (questionIndex >= 0) {
                        section.questions[questionIndex] = { ...section.questions[questionIndex], ...updates };
                        break;
                    }
                }
                this.showSuccess('Question updated successfully');
            } else {
                this.error = 'Failed to update question';
            }
        } catch (error) {
            this.error = 'Error updating question: ' + error.message;
            console.error(error);
        } finally {
            this.loading = false;
        }
    },

    async deleteQuestion(questionId) {
        if (!confirm('Delete this question?')) return;

        try {
            this.loading = true;
            this.error = null;

            const response = await fetch(`/admin/surveys/${this.surveyId}/questions/${questionId}/delete`, {
                method: 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });

            if (response.ok) {
                // Remove question from nested structure
                for (let section of this.sections) {
                    section.questions = section.questions?.filter(q => q.id !== questionId) || [];
                }
                this.showSuccess('Question deleted successfully');
            } else {
                this.error = 'Failed to delete question';
            }
        } catch (error) {
            this.error = 'Error deleting question: ' + error.message;
            console.error(error);
        } finally {
            this.loading = false;
        }
    },

    showSuccess(message) {
        this.successMessage = message;
        setTimeout(() => {
            this.successMessage = null;
        }, 3000);
    }
};
