<x-filament-panels::page>
    <div class="mb-4">
        <button 
            onclick="downloadCsv()" 
            class="inline-flex items-center px-4 py-2 bg-green-600 hover:bg-green-700 text-white text-sm font-medium rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2"
        >
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
            </svg>
            Exporter en CSV
        </button>
    </div>

    {{ $this->table }}

    <script>
    function downloadCsv() {
        // Créer un lien temporaire et le cliquer
        const link = document.createElement('a');
        link.href = '{{ route('admin.winners.export-csv') }}';
        link.download = 'liste-gagnants-' + new Date().toISOString().slice(0,10) + '.csv';
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
    }
    </script>
</x-filament-panels::page>
