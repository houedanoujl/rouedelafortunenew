@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="bg-white rounded-lg shadow-lg p-6">
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-2xl font-bold text-gray-800">Logs des messages WhatsApp</h1>
            
            <div class="flex space-x-2">
                <a href="{{ route('admin.whatsapp.logs.download') }}" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 transition">
                    <i class="fas fa-download mr-1"></i> Télécharger
                </a>
                
                <a href="{{ route('admin.whatsapp.logs.clear') }}" 
                   onclick="return confirm('Êtes-vous sûr de vouloir vider les logs ? Cette action est irréversible.')"
                   class="px-4 py-2 bg-red-600 text-white rounded hover:bg-red-700 transition">
                    <i class="fas fa-trash-alt mr-1"></i> Vider
                </a>
            </div>
        </div>
        
        @if(session('success'))
            <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-6">
                {{ session('success') }}
            </div>
        @endif
        
        @if(session('error'))
            <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6">
                {{ session('error') }}
            </div>
        @endif
        
        <!-- Filtres -->
        <form action="{{ route('admin.whatsapp.logs') }}" method="GET" class="mb-6 bg-gray-50 p-4 rounded-lg">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div>
                    <label for="status" class="block text-sm font-medium text-gray-700 mb-1">Statut</label>
                    <select id="status" name="status" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50">
                        <option value="all" {{ $filters['status'] === 'all' ? 'selected' : '' }}>Tous</option>
                        <option value="success" {{ $filters['status'] === 'success' ? 'selected' : '' }}>Succès</option>
                        <option value="error" {{ $filters['status'] === 'error' ? 'selected' : '' }}>Erreur</option>
                    </select>
                </div>
                
                <div>
                    <label for="type" class="block text-sm font-medium text-gray-700 mb-1">Type</label>
                    <select id="type" name="type" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50">
                        <option value="all" {{ $filters['type'] === 'all' ? 'selected' : '' }}>Tous</option>
                        <option value="text" {{ $filters['type'] === 'text' ? 'selected' : '' }}>Texte</option>
                        <option value="qrcode" {{ $filters['type'] === 'qrcode' ? 'selected' : '' }}>QR Code</option>
                    </select>
                </div>
                
                <div>
                    <label for="limit" class="block text-sm font-medium text-gray-700 mb-1">Limite</label>
                    <select id="limit" name="limit" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50">
                        <option value="50" {{ $filters['limit'] == 50 ? 'selected' : '' }}>50</option>
                        <option value="100" {{ $filters['limit'] == 100 ? 'selected' : '' }}>100</option>
                        <option value="200" {{ $filters['limit'] == 200 ? 'selected' : '' }}>200</option>
                        <option value="500" {{ $filters['limit'] == 500 ? 'selected' : '' }}>500</option>
                    </select>
                </div>
                
                <div>
                    <label for="search" class="block text-sm font-medium text-gray-700 mb-1">Recherche</label>
                    <input type="text" id="search" name="search" value="{{ $filters['search'] }}" 
                           class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50"
                           placeholder="Téléphone, message...">
                </div>
            </div>
            
            <div class="mt-4 flex justify-end">
                <button type="submit" class="px-4 py-2 bg-gray-800 text-white rounded hover:bg-gray-700 transition">
                    <i class="fas fa-filter mr-1"></i> Filtrer
                </button>
            </div>
        </form>
        
        <!-- Tableau des logs -->
        <div class="overflow-x-auto">
            <table class="min-w-full bg-white border border-gray-200">
                <thead>
                    <tr>
                        <th class="px-4 py-2 bg-gray-100 border-b text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Date/Heure</th>
                        <th class="px-4 py-2 bg-gray-100 border-b text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Statut</th>
                        <th class="px-4 py-2 bg-gray-100 border-b text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Téléphone</th>
                        <th class="px-4 py-2 bg-gray-100 border-b text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Type</th>
                        <th class="px-4 py-2 bg-gray-100 border-b text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Message</th>
                        <th class="px-4 py-2 bg-gray-100 border-b text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Détails</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($logs as $log)
                        <tr class="{{ $log['status'] === 'error' ? 'bg-red-50' : '' }}">
                            <td class="px-4 py-2 border-b text-sm">{{ $log['timestamp'] ?? 'N/A' }}</td>
                            <td class="px-4 py-2 border-b text-sm">
                                @if($log['status'] === 'success')
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                        Succès
                                    </span>
                                @else
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">
                                        Erreur
                                    </span>
                                @endif
                            </td>
                            <td class="px-4 py-2 border-b text-sm">{{ $log['phone'] ?? 'N/A' }}</td>
                            <td class="px-4 py-2 border-b text-sm">
                                @if(isset($log['type']))
                                    @if($log['type'] === 'text')
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800">
                                            Texte
                                        </span>
                                    @elseif($log['type'] === 'qrcode')
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-purple-100 text-purple-800">
                                            QR Code
                                        </span>
                                    @else
                                        {{ $log['type'] }}
                                    @endif
                                @else
                                    N/A
                                @endif
                            </td>
                            <td class="px-4 py-2 border-b text-sm">{{ $log['message'] ?? 'N/A' }}</td>
                            <td class="px-4 py-2 border-b text-sm">
                                @if(isset($log['error']))
                                    <span class="text-red-600">{{ $log['error'] }}</span>
                                @elseif(isset($log['message_id']))
                                    <span class="text-green-600">ID: {{ $log['message_id'] }}</span>
                                @else
                                    -
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-4 py-8 text-center text-gray-500">
                                Aucun message WhatsApp trouvé.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
