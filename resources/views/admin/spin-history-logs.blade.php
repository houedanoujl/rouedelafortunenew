<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Logs de tours de roue</title>
    <!-- Tailwind CSS via CDN -->
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <!-- Alpine.js -->
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <style>
        .search-highlight {
            background-color: #FEFCE8;
            color: #854D0E;
        }
        .search-results {
            max-height: 300px;
            overflow-y: auto;
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
        }
    </style>
</head>
<body class="bg-gray-100 dark:bg-gray-900 text-gray-800 dark:text-gray-200" x-data="spinLogsApp()">
    <div class="container mx-auto py-8 px-4">
        <header class="mb-8">
            <h1 class="text-2xl font-bold mb-4">Logs de tours de roue</h1>
            <p class="text-gray-600 dark:text-gray-400 mb-4">
                Cette page présente l'historique complet des tours de roue, incluant les résultats, les participants, et les logs de console associés.
                Ces informations sont particulièrement utiles pour vérifier que le système de QR code et l'expérience de révélation différée fonctionnent correctement.
            </p>
            <a href="/admin" class="inline-block px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">
                Retour à l'administration
            </a>
        </header>

        <!-- Zone de recherche avancée -->
        <div class="mb-6 bg-white dark:bg-gray-800 rounded shadow p-4">
            <div class="flex flex-col md:flex-row gap-4">
                <div class="relative flex-grow">
                    <label for="searchQuery" class="block mb-2 text-sm font-medium text-gray-700 dark:text-gray-300">Recherche</label>
                    <input 
                        type="text" 
                        id="searchQuery" 
                        x-model="searchQuery" 
                        x-on:input="search" 
                        placeholder="Nom, email, résultat..." 
                        class="w-full px-4 py-2 rounded border border-gray-300 dark:border-gray-700 focus:ring-blue-500 focus:border-blue-500"
                    >
                    <!-- Suggestions de recherche -->
                    <div 
                        x-show="showSuggestions && filteredResults.length > 0" 
                        x-transition 
                        class="absolute z-10 w-full mt-1 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-700 rounded search-results"
                    >
                        <template x-for="(result, index) in filteredResults" :key="index">
                            <div 
                                x-on:click="selectResult(result)" 
                                class="p-2 cursor-pointer hover:bg-gray-100 dark:hover:bg-gray-700"
                            >
                                <div class="font-semibold" x-text="result.participant?.name || 'Inconnu'"></div>
                                <div class="text-sm text-gray-500" x-text="result.participant?.email || '-'"></div>
                                <div class="text-xs">
                                    <span 
                                        class="px-1 rounded" 
                                        :class="result.result === 'win' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'"
                                        x-text="result.result === 'win' ? 'GAGNÉ' : 'PERDU'"
                                    ></span>
                                    <span x-text="formatDate(result.timestamp)"></span>
                                </div>
                            </div>
                        </template>
                    </div>
                </div>
                <div>
                    <label for="resultFilter" class="block mb-2 text-sm font-medium text-gray-700 dark:text-gray-300">Résultat</label>
                    <select 
                        id="resultFilter" 
                        x-model="resultFilter" 
                        x-on:change="search" 
                        class="w-full px-4 py-2 rounded border border-gray-300 dark:border-gray-700 focus:ring-blue-500 focus:border-blue-500"
                    >
                        <option value="">Tous</option>
                        <option value="win">GAGNÉ</option>
                        <option value="lose">PERDU</option>
                    </select>
                </div>
                <div>
                    <label for="dateFilter" class="block mb-2 text-sm font-medium text-gray-700 dark:text-gray-300">Date</label>
                    <input 
                        type="date" 
                        id="dateFilter" 
                        x-model="dateFilter" 
                        x-on:change="search" 
                        class="w-full px-4 py-2 rounded border border-gray-300 dark:border-gray-700 focus:ring-blue-500 focus:border-blue-500"
                    >
                </div>
                <div class="flex items-end">
                    <button 
                        x-on:click="resetSearch" 
                        class="px-4 py-2 bg-gray-200 text-gray-800 rounded hover:bg-gray-300 dark:bg-gray-700 dark:text-gray-200 dark:hover:bg-gray-600"
                    >
                        Réinitialiser
                    </button>
                </div>
            </div>
        </div>

        <div class="overflow-x-auto bg-white dark:bg-gray-800 rounded shadow p-4">
            <table class="min-w-full text-sm text-left">
                <thead class="bg-gray-50 dark:bg-gray-700 text-gray-700 dark:text-gray-300">
                    <tr>
                        <th class="px-4 py-2">Date</th>
                        <th class="px-4 py-2">ID Participation</th>
                        <th class="px-4 py-2">Participant</th>
                        <th class="px-4 py-2">Email</th>
                        <th class="px-4 py-2">Angle</th>
                        <th class="px-4 py-2">Résultat</th>
                        <th class="px-4 py-2">Logs Console</th>
                    </tr>
                </thead>
                <tbody>
                    <template x-if="displayedLogs.length > 0">
                        <template x-for="(log, index) in displayedLogs" :key="index">
                            <tr class="border-b border-gray-200 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700">
                                <td class="px-4 py-3" x-text="formatDate(log.timestamp)"></td>
                                <td class="px-4 py-3" x-text="log.entry_id || '-'"></td>
                                <td class="px-4 py-3" x-text="log.participant?.name || '-'"></td>
                                <td class="px-4 py-3" x-text="log.participant?.email || '-'"></td>
                                <td class="px-4 py-3" x-text="log.angle || '-'"></td>
                                <td class="px-4 py-3">
                                    <template x-if="log.result">
                                        <span 
                                            class="px-2 py-1 rounded text-white" 
                                            :class="log.result === 'win' ? 'bg-green-500' : 'bg-red-500'"
                                            x-text="log.result === 'win' ? 'GAGNÉ' : 'PERDU'"
                                        ></span>
                                    </template>
                                    <template x-if="!log.result">-</template>
                                </td>
                                <td class="px-4 py-3">
                                    <template x-if="log.console_logs && log.console_logs.length > 0">
                                        <div class="max-h-32 overflow-y-auto font-mono text-xs bg-gray-100 dark:bg-gray-800 p-2 rounded">
                                            <template x-for="(console, i) in log.console_logs" :key="i">
                                                <div class="mb-1" x-text="console"></div>
                                            </template>
                                        </div>
                                    </template>
                                    <template x-if="!log.console_logs || log.console_logs.length === 0">
                                        <em class="text-gray-400">Aucun log</em>
                                    </template>
                                </td>
                            </tr>
                        </template>
                    </template>
                    <template x-if="displayedLogs.length === 0">
                        <tr>
                            <td colspan="7" class="text-center text-gray-500 py-4">Aucun log trouvé.</td>
                        </tr>
                    </template>
                </tbody>
            </table>

            <!-- Pagination simple -->
            <div class="mt-4 flex justify-between items-center">
                <div x-text="`Affichage ${paginationStart + 1} à ${Math.min(paginationStart + pageSize, filteredLogs.length)} sur ${filteredLogs.length} résultats`"></div>
                <div class="flex space-x-2">
                    <button 
                        x-on:click="prevPage" 
                        :disabled="paginationStart === 0" 
                        :class="paginationStart === 0 ? 'opacity-50 cursor-not-allowed' : ''"
                        class="px-3 py-1 bg-gray-200 rounded hover:bg-gray-300 dark:bg-gray-700 dark:hover:bg-gray-600"
                    >
                        Précédent
                    </button>
                    <button 
                        x-on:click="nextPage" 
                        :disabled="paginationStart + pageSize >= filteredLogs.length" 
                        :class="paginationStart + pageSize >= filteredLogs.length ? 'opacity-50 cursor-not-allowed' : ''"
                        class="px-3 py-1 bg-gray-200 rounded hover:bg-gray-300 dark:bg-gray-700 dark:hover:bg-gray-600"
                    >
                        Suivant
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script>
        function spinLogsApp() {
            return {
                allLogs: @json($logs),
                filteredLogs: [],
                displayedLogs: [],
                searchQuery: '',
                resultFilter: '',
                dateFilter: '',
                showSuggestions: false,
                filteredResults: [],
                paginationStart: 0,
                pageSize: 10,

                init() {
                    // Au chargement, initialiser avec tous les logs
                    this.filteredLogs = [...this.allLogs];
                    this.updateDisplayedLogs();
                    
                    // Cliquer ailleurs ferme les suggestions
                    document.addEventListener('click', (e) => {
                        if (!e.target.closest('#searchQuery')) {
                            this.showSuggestions = false;
                        }
                    });
                },
                
                search() {
                    this.showSuggestions = this.searchQuery.length > 0;
                    
                    // Filtrage
                    this.filteredLogs = this.allLogs.filter(log => {
                        // Filtre par recherche textuelle
                        const searchMatch = !this.searchQuery || 
                            (log.participant?.name && log.participant.name.toLowerCase().includes(this.searchQuery.toLowerCase())) ||
                            (log.participant?.email && log.participant.email.toLowerCase().includes(this.searchQuery.toLowerCase())) ||
                            (log.entry_id && log.entry_id.toString().includes(this.searchQuery));
                        
                        // Filtre par résultat
                        const resultMatch = !this.resultFilter || log.result === this.resultFilter;
                        
                        // Filtre par date
                        let dateMatch = true;
                        if (this.dateFilter) {
                            const filterDate = new Date(this.dateFilter);
                            const logDate = new Date(log.timestamp);
                            dateMatch = filterDate.toDateString() === logDate.toDateString();
                        }
                        
                        return searchMatch && resultMatch && dateMatch;
                    });
                    
                    // Suggestions pour l'autocomplétion
                    this.filteredResults = this.filteredLogs.slice(0, 5);
                    
                    // Réinitialiser la pagination
                    this.paginationStart = 0;
                    this.updateDisplayedLogs();
                },
                
                selectResult(result) {
                    // Sélection d'un résultat dans les suggestions
                    this.searchQuery = result.participant?.name || result.entry_id;
                    this.showSuggestions = false;
                    this.search();
                },
                
                resetSearch() {
                    this.searchQuery = '';
                    this.resultFilter = '';
                    this.dateFilter = '';
                    this.filteredLogs = [...this.allLogs];
                    this.paginationStart = 0;
                    this.showSuggestions = false;
                    this.updateDisplayedLogs();
                },
                
                formatDate(dateString) {
                    if (!dateString) return '-';
                    const date = new Date(dateString);
                    return date.toLocaleDateString('fr-FR', { 
                        day: '2-digit',
                        month: '2-digit', 
                        year: 'numeric',
                        hour: '2-digit',
                        minute: '2-digit',
                        second: '2-digit'
                    });
                },
                
                nextPage() {
                    if (this.paginationStart + this.pageSize < this.filteredLogs.length) {
                        this.paginationStart += this.pageSize;
                        this.updateDisplayedLogs();
                    }
                },
                
                prevPage() {
                    if (this.paginationStart > 0) {
                        this.paginationStart -= this.pageSize;
                        this.updateDisplayedLogs();
                    }
                },
                
                updateDisplayedLogs() {
                    this.displayedLogs = this.filteredLogs.slice(
                        this.paginationStart,
                        this.paginationStart + this.pageSize
                    );
                }
            }
        }
    </script>
</body>
</html>
