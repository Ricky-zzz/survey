export default {
    surveys: [],
    filteredSurveys: [],
    searchQuery: '',
    filterPublic: 'all',
    sortBy: 'recent',
    loading: false,
    error: null,

    init() {
        this.surveys = [];
        this.filteredSurveys = [];
        this.loadSurveys();
        this.$watch('searchQuery', () => this.filterSurveys());
        this.$watch('filterPublic', () => this.filterSurveys());
        this.$watch('sortBy', () => this.filterSurveys());
    },

    async loadSurveys() {
        try {
            this.loading = true;
            const response = await fetch('/admin/surveys?json=1');
            if (response.ok) {
                this.surveys = await response.json();
                this.filterSurveys();
            }
        } catch (error) {
            this.error = 'Failed to load surveys: ' + error.message;
            console.error(error);
        } finally {
            this.loading = false;
        }
    },

    filterSurveys() {
        let filtered = [...this.surveys];

        // Filter by search query
        if (this.searchQuery) {
            const query = this.searchQuery.toLowerCase();
            filtered = filtered.filter(s => 
                s.title.toLowerCase().includes(query) ||
                (s.description && s.description.toLowerCase().includes(query))
            );
        }

        // Filter by public/private
        if (this.filterPublic !== 'all') {
            const isPublic = this.filterPublic === 'public';
            filtered = filtered.filter(s => s.is_public === isPublic);
        }

        // Sort
        if (this.sortBy === 'recent') {
            filtered.sort((a, b) => new Date(b.created_at) - new Date(a.created_at));
        } else if (this.sortBy === 'oldest') {
            filtered.sort((a, b) => new Date(a.created_at) - new Date(b.created_at));
        } else if (this.sortBy === 'responses') {
            filtered.sort((a, b) => (b.response_count || 0) - (a.response_count || 0));
        } else if (this.sortBy === 'alphabetical') {
            filtered.sort((a, b) => a.title.localeCompare(b.title));
        }

        this.filteredSurveys = filtered;
    },

    async deleteSurvey(surveyId) {
        if (!confirm('Are you sure you want to delete this survey and all its data?')) return;

        try {
            this.loading = true;
            const formData = new FormData();
            const response = await fetch(`/admin/surveys/${surveyId}/delete`, {
                method: 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: formData
            });

            if (response.ok) {
                this.surveys = this.surveys.filter(s => s.id !== surveyId);
                this.filterSurveys();
            } else {
                this.error = 'Failed to delete survey';
            }
        } catch (error) {
            this.error = 'Error deleting survey: ' + error.message;
            console.error(error);
        } finally {
            this.loading = false;
        }
    }
};
