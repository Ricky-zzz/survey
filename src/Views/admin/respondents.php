<?php include __DIR__ . '/../layout/head.php'; ?>
<div class="flex h-screen">
    <?php include __DIR__ . '/../layout/app-layout.php'; ?>
    
    <div x-data="respondentsData" data-survey-id="<?php echo $survey['id']; ?>" @load="loadRespondents()">
        <!-- Respondents List -->
        <div class="mb-8">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Respondents for "<span x-text="'<?php echo htmlspecialchars($survey['title']); ?>'"></span>"</h3>
        </div>

        <!-- Filters -->
        <div class="bg-white rounded-lg shadow p-4 mb-6">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <input 
                        type="text" 
                        x-model="searchQuery"
                        placeholder="Search by email or name..."
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                    >
                </div>
                <div>
                    <select 
                        x-model="filterSubmitted"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                    >
                        <option value="all">All Respondents</option>
                        <option value="submitted">Submitted Only</option>
                        <option value="incomplete">Incomplete Only</option>
                    </select>
                </div>
                <div>
                    <select 
                        x-model="sortBy"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                    >
                        <option value="recent">Most Recent</option>
                        <option value="oldest">Oldest</option>
                        <option value="alphabetical">Alphabetical</option>
                    </select>
                </div>
            </div>
        </div>

        <!-- Error Alert -->
        <div x-show="error" class="bg-red-50 border border-red-200 rounded-lg p-4 mb-6 text-red-700 text-sm" style="display: none;">
            <p x-text="error"></p>
        </div>

        <!-- Respondents Table -->
        <template x-if="filteredRespondents.length === 0 && !loading">
            <div class="bg-white rounded-lg shadow p-8 text-center">
                <p class="text-gray-500">No responses yet.</p>
            </div>
        </template>

        <template x-if="filteredRespondents.length > 0">
            <div class="bg-white rounded-lg shadow overflow-hidden">
                <table class="w-full">
                    <thead class="bg-gray-50 border-b border-gray-200">
                        <tr>
                            <th class="px-6 py-3 text-left text-sm font-semibold text-gray-900">Email</th>
                            <th class="px-6 py-3 text-left text-sm font-semibold text-gray-900">Name</th>
                            <th class="px-6 py-3 text-left text-sm font-semibold text-gray-900">Submitted</th>
                            <th class="px-6 py-3 text-left text-sm font-semibold text-gray-900">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        <template x-for="respondent in filteredRespondents" :key="respondent.id">
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 text-sm">
                                    <div x-text="respondent.email || 'N/A'"></div>
                                </td>
                                <td class="px-6 py-4 text-sm">
                                    <div x-text="respondent.name || 'N/A'"></div>
                                </td>
                                <td class="px-6 py-4 text-sm">
                                    <div x-text="respondent.submitted_at ? formatDate(respondent.submitted_at) : 'Not submitted'"></div>
                                </td>
                                <td class="px-6 py-4 text-sm">
                                    <a :href="`/admin/surveys/<?php echo $survey['id']; ?>/respondents/${respondent.id}`" class="text-blue-600 hover:text-blue-700 font-medium">
                                        View
                                    </a>
                                </td>
                            </tr>
                        </template>
                    </tbody>
                </table>
            </div>
        </template>
    </div>

    <?php include __DIR__ . '/../layout/footer.php'; ?>
</div>

<script type="module">
    import respondentsData from './js/respondents-manager.js';
    
    document.addEventListener('alpine:init', () => {
        Alpine.data('respondentsData', respondentsData);
    });
</script>
