<div class="p-4">
    <h2 class="text-xl font-bold mb-4">Détails de la participation gagnante</h2>
    
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div class="space-y-4">
            <div class="bg-gray-100 p-3 rounded-lg">
                <h3 class="font-semibold text-gray-700">Informations du participant</h3>
                <div class="space-y-2 mt-2">
                    <div><span class="font-medium">Nom:</span> {{ $entry->participant->last_name ?? 'Non disponible' }}</div>
                    <div><span class="font-medium">Prénom:</span> {{ $entry->participant->first_name ?? 'Non disponible' }}</div>
                    <div><span class="font-medium">Email:</span> {{ $entry->participant->email ?? 'Non disponible' }}</div>
                    <div><span class="font-medium">Téléphone:</span> {{ $entry->participant->phone ?? 'Non disponible' }}</div>
                </div>
            </div>
            
            <div class="bg-gray-100 p-3 rounded-lg">
                <h3 class="font-semibold text-gray-700">Détails du concours</h3>
                <div class="space-y-2 mt-2">
                    <div><span class="font-medium">Concours:</span> {{ $entry->contest->name ?? 'Non disponible' }}</div>
                    <div><span class="font-medium">Date de participation:</span> {{ $entry->created_at ? $entry->created_at->format('d/m/Y H:i') : 'Non disponible' }}</div>
                </div>
            </div>
        </div>
        
        <div class="space-y-4">
            <div class="bg-gray-100 p-3 rounded-lg">
                <h3 class="font-semibold text-gray-700">Prix gagné</h3>
                <div class="space-y-2 mt-2">
                    <div><span class="font-medium">Lot:</span> <span class="px-2 py-1 rounded-full bg-green-100 text-green-800 text-sm">{{ $entry->prize->name ?? 'Non disponible' }}</span></div>
                    <div><span class="font-medium">Valeur:</span> {{ $entry->prize->value ? number_format($entry->prize->value, 2, ',', ' ') . ' €' : 'Non disponible' }}</div>
                    <div><span class="font-medium">Description:</span> {{ $entry->prize->description ?? 'Aucune description' }}</div>
                </div>
            </div>
            
            <div class="bg-gray-100 p-3 rounded-lg">
                <h3 class="font-semibold text-gray-700">Code QR</h3>
                <div class="space-y-2 mt-2">
                    <div><span class="font-medium">Code:</span> 
                        @if($entry->qrCode)
                            <span class="font-mono bg-white px-2 py-1 rounded border">{{ $entry->qrCode->code }}</span>
                        @else
                            Non disponible
                        @endif
                    </div>
                    
                    <div><span class="font-medium">Scanné:</span> 
                        @if($entry->qrCode && $entry->qrCode->scanned)
                            <span class="px-2 py-1 rounded-full bg-green-100 text-green-800 text-sm">Oui</span>
                            <div class="text-sm text-gray-500">Le {{ $entry->qrCode->scanned_at ? $entry->qrCode->scanned_at->format('d/m/Y H:i') : 'date inconnue' }}</div>
                        @else
                            <span class="px-2 py-1 rounded-full bg-yellow-100 text-yellow-800 text-sm">Non</span>
                        @endif
                    </div>
                    
                    <div><span class="font-medium">Réclamé:</span>
                        @if($entry->claimed)
                            <span class="px-2 py-1 rounded-full bg-green-100 text-green-800 text-sm">Oui</span>
                            <div class="text-sm text-gray-500">Le {{ $entry->claimed_at ? $entry->claimed_at->format('d/m/Y H:i') : 'date inconnue' }}</div>
                        @else
                            <span class="px-2 py-1 rounded-full bg-yellow-100 text-yellow-800 text-sm">Non</span>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
