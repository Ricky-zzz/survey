export default {
    respondents: [],
    filteredRespondents: [],
    searchQuery: '',
    filterSubmitted: 'all',
    sortBy: 'recent',
    loading: false,
    error: null,
    surveyId: null,

    init() {
        const surveyIdElement = document.querySelector('[data-survey-id]');
        this.surveyId = surveyIdElement?.dataset.surveyId;
        this.respondents = [];
        this.filteredRespondents = [];
        this.loadRespondents();
        this.$watch('searchQuery', () => this.filterRespondents());
        this.$watch('filterSubmitted', () => this.filterRespondents());
        this.$watch('sortBy', () => this.filterRespondents());
    },

    async loadRespondents() {
        if (!this.surveyId) return;

        try {
            this.loading = true;
            const response = await fetch(`/admin/surveys/${this.surveyId}/respondents?json=1`);
            if (response.ok) {
                this.respondents = await response.json();
                this.filterRespondents();
            }
        } catch (error) {
            this.error = 'Failed to load respondents: ' + error.message;
            console.error(error);
        } finally {
            this.loading = false;
        }
    },

    filterRespondents() {
        let filtered = [...this.respondents];

        // Filter by search query
        if (this.searchQuery) {
            const query = this.searchQuery.toLowerCase();
            filtered = filtered.filter(r => 
                (r.email && r.email.toLowerCase().includes(query)) ||
                (r.name && r.name.toLowerCase().includes(query))
            );
        }

        // Filter by submission status
        if (this.filterSubmitted !== 'all') {
            const submitted = this.filterSubmitted === 'submitted';
            filtered = filtered.filter(r => {
                const isSubmitted = r.submitted_at !== null;
                return submitted === isSubmitted;
            });
        }

        // Sort
        if (this.sortBy === 'recent') {
            filtered.sort((a, b) => {
                const aDate = new Date(a.submitted_at || a.created_at);
                const bDate = new Date(b.submitted_at || b.created_at);
                return bDate - aDate;
            });
        } else if (this.sortBy === 'oldest') {
            filtered.sort((a, b) => {
                const aDate = new Date(a.created_at);
                const bDate = new Date(b.created_at);
                return aDate - bDate;
            });
        } else if (this.sortBy === 'alphabetical') {
            filtered.sort((a, b) => {
                const aName = (a.name || a.email || '').toLowerCase();
                const bName = (b.name || b.email || '').toLowerCase();
                return aName.localeCompare(bName);
            });
        }

        this.filteredRespondents = filtered;
    },

    formatDate(dateString) {
        if (!dateString) return 'N/A';
        return new Date(dateString).toLocaleDateString('en-US', {
            year: 'numeric',
            month: 'short',
            day: 'numeric',
            hour: '2-digit',
            minute: '2-digit'
        });
    }
};
