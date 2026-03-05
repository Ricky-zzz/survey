<?php include __DIR__ . '/../layout/head.php'; ?>
<div class="flex h-screen">
    <?php include __DIR__ . '/../layout/app-layout.php'; ?>
    
    <div x-data="dashboardData" @load="loadSurveys()">
        <!-- Header -->
        <div class="mb-8 flex justify-between items-center">
            <div>
                <h3 class="text-lg font-semibold text-gray-900">Your Surveys</h3>
            </div>
            <a href="/admin/surveys/create" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition">
                Create New Survey
            </a>
        </div>

        <!-- Filters -->
        <div class="bg-white rounded-lg shadow p-4 mb-6">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <input 
                        type="text" 
                        x-model="searchQuery"
                        placeholder="Search surveys..."
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                    >
                </div>
                <div>
                    <select 
                        x-model="filterPublic"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                    >
                        <option value="all">All Surveys</option>
                        <option value="public">Public Only</option>
                        <option value="private">Private Only</option>
                    </select>
                </div>
                <div>
                    <select 
                        x-model="sortBy"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                    >
                        <option value="recent">Most Recent</option>
                        <option value="oldest">Oldest</option>
                        <option value="responses">Most Responses</option>
                        <option value="alphabetical">Alphabetical</option>
                    </select>
                </div>
            </div>
        </div>

        <!-- Error Alert -->
        <div x-show="error" class="bg-red-50 border border-red-200 rounded-lg p-4 mb-6 text-red-700 text-sm" style="display: none;">
            <p x-text="error"></p>
        </div>

        <!-- Surveys List -->
        <div class="space-y-4">
            <template x-if="filteredSurveys.length === 0 && !loading">
                <div class="bg-white rounded-lg shadow p-8 text-center">
                    <p class="text-gray-500 mb-4">No surveys found. Create your first survey to get started.</p>
                    <a href="/admin/surveys/create" class="inline-block bg-blue-600 text-white px-6 py-2 rounded-lg hover:bg-blue-700 transition">
                        Create Survey
                    </a>
                </div>
            </template>

            <template x-for="survey in filteredSurveys" :key="survey.id">
                <div class="bg-white rounded-lg shadow p-6 flex justify-between items-center hover:shadow-lg transition">
                    <div class="flex-1">
                        <h4 class="text-lg font-semibold text-gray-900" x-text="survey.title"></h4>
                        <p class="text-gray-600 text-sm mt-1" x-text="survey.description || ''"></p>
                        <div class="mt-2 flex gap-4 text-xs text-gray-500">
                            <span x-text="survey.is_public ? '🌍 Public' : '🔒 Private'"></span>
                            <span x-text="`${survey.response_count || 0} responses`"></span>
                            <span x-text="`${new Date(survey.created_at).toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric' })}`"></span>
                        </div>
                    </div>
                    <div class="flex gap-2">
                        <a :href="`/admin/surveys/${survey.id}/results`" class="text-blue-600 hover:text-blue-700 text-sm font-medium px-3 py-2">
                            Results
                        </a>
                        <a :href="`/admin/surveys/${survey.id}/edit`" class="text-gray-600 hover:text-gray-700 text-sm font-medium px-3 py-2">
                            Edit
                        </a>
                        <button 
                            @click="deleteSurvey(survey.id)"
                            class="text-red-600 hover:text-red-700 text-sm font-medium px-3 py-2"
                            :disabled="loading"
                        >
                            Delete
                        </button>
                    </div>
                </div>
            </template>
        </div>
    </div>

    <?php include __DIR__ . '/../layout/footer.php'; ?>
</div>

<script type="module">
    import dashboardData from './js/dashboard-manager.js';
    
    document.addEventListener('alpine:init', () => {
        Alpine.data('dashboardData', dashboardData);
    });
</script>
