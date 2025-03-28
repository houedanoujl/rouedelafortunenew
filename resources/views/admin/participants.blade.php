@extends('layouts.app')

@section('content')
<div class="admin-participants">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>Gestion des participants</h1>
    </div>
    
    <div class="card">
        <div class="card-header">
            <h5>Liste des participants</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Nom</th>
                            <th>Prénom</th>
                            <th>Téléphone</th>
                            <th>Email</th>
                            <th>Participations</th>
                            <th>Date d'inscription</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($participants as $participant)
                            <tr>
                                <td>{{ $participant->last_name }}</td>
                                <td>{{ $participant->first_name }}</td>
                                <td>{{ $participant->phone }}</td>
                                <td>{{ $participant->email }}</td>
                                <td>{{ $participant->entries_count }}</td>
                                <td>{{ $participant->created_at->format('d/m/Y H:i') }}</td>
                                <td>
                                    <a href="{{ route('admin.participant.show', $participant->id) }}" class="btn btn-sm btn-info">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center">Aucun participant enregistré</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            
            <div class="mt-3">
                {{ $participants->links() }}
            </div>
        </div>
    </div>
</div>
@endsection
