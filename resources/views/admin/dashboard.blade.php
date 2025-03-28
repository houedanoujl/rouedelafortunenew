   @extends('layouts.app')

@section('content')
<div class="admin-dashboard">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>Tableau de bord</h1>
    </div>
    
    @livewire('admin.dashboard-summary')
</div>
@endsection
