@extends('layouts.app')

@section('content')
<div class="admin-entries">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>Gestion des participations</h1>
    </div>
    
    <div class="card">
        <div class="card-header">
            <h5>Liste des participations</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Participant</th>
                            <th>Concours</th>
                            <th>Prix</th>
                            <th>Résultat</th>
                            <th>Date de participation</th>
                            <th>QR Code</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($entries as $entry)
                            <tr>
                                <td>{{ $entry->id }}</td>
                                <td>
                                    @if ($entry->participant)
                                        {{ $entry->participant->full_name }}
                                    @else
                                        <span class="text-muted">Inconnu</span>
                                    @endif
                                </td>
                                <td>
                                    @if ($entry->contest)
                                        {{ $entry->contest->name }}
                                    @else
                                        <span class="text-muted">Inconnu</span>
                                    @endif
                                </td>
                                <td>
                                    @if ($entry->prize)
                                        {{ $entry->prize->name }}
                                    @else
                                        <span class="text-muted">Aucun prix</span>
                                    @endif
                                </td>
                                <td>
                                    @if ($entry->result === 'win')
                                        <span class="badge bg-success">Gagné</span>
                                    @elseif ($entry->result === 'lose')
                                        <span class="badge bg-danger">Perdu</span>
                                    @else
                                        <span class="badge bg-secondary">{{ $entry->result }}</span>
                                    @endif
                                </td>
                                <td>{{ $entry->played_at->format('d/m/Y H:i') }}</td>
                                <td>
                                    @if ($entry->qrCode)
                                        @if ($entry->qrCode->scanned)
                                            <span class="badge bg-success">Réclamé</span>
                                        @else
                                            <span class="badge bg-warning">Non réclamé</span>
                                        @endif
                                    @else
                                        <span class="badge bg-secondary">Pas de QR</span>
                                    @endif
                                </td>
                                <td>
                                    @if ($entry->result === 'win')
                                        <a href="{{ route('admin.qr-code', $entry->id) }}" class="btn btn-sm btn-primary">
                                            <i class="fas fa-qrcode"></i>
                                        </a>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center">Aucune participation enregistrée</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            
            <div class="mt-3">
                {{ $entries->links() }}
            </div>
        </div>
    </div>
</div>
@endsection
