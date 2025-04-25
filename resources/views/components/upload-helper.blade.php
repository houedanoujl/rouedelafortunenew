@php
    $uploadDir = public_path('assets/prizes');
    $baseUrl = url('/assets/prizes/');
    
    // Créer le dossier s'il n'existe pas
    if (!file_exists($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }
    
    // Vérifier les permissions
    if (!is_writable($uploadDir)) {
        chmod($uploadDir, 0777);
    }
@endphp

<div class="card">
    <div class="card-header bg-primary text-white">
        <h5 class="mb-0">Assistant d'upload d'images</h5>
    </div>
    <div class="card-body">
        <form action="{{ route('prizes.upload') }}" method="post" enctype="multipart/form-data">
            @csrf
            <div class="mb-3">
                <label for="image" class="form-label">Sélectionnez une image à uploader</label>
                <input type="file" class="form-control" name="image" id="image" accept="image/jpeg,image/png,image/gif">
                <div class="form-text">L'image sera sauvegardée dans public/assets/prizes</div>
            </div>
            <button type="submit" class="btn btn-primary">Uploader</button>
        </form>
        
        <hr>
        
        <h5>Images existantes</h5>
        <div class="row">
            @php
                $files = [];
                if (is_dir($uploadDir)) {
                    $files = array_diff(scandir($uploadDir), ['.', '..']);
                }
            @endphp
            
            @if (count($files) > 0)
                @foreach ($files as $file)
                    @php
                        $fullPath = $uploadDir . '/' . $file;
                        $imageUrl = $baseUrl . $file;
                        $filesize = filesize($fullPath);
                        $filesizeFormatted = $filesize < 1024 ? $filesize . ' bytes' : 
                                            ($filesize < 1048576 ? round($filesize / 1024, 2) . ' KB' : 
                                            round($filesize / 1048576, 2) . ' MB');
                    @endphp
                    <div class="col-md-4 mb-3">
                        <div class="card">
                            <img src="{{ $imageUrl }}" alt="{{ $file }}" class="card-img-top" style="max-height: 150px; object-fit: contain;">
                            <div class="card-body">
                                <h6 class="card-title text-truncate">{{ $file }}</h6>
                                <p class="card-text">
                                    <small>Taille: {{ $filesizeFormatted }}</small><br>
                                    <small class="text-muted">URL: <span class="text-primary">{{ $imageUrl }}</span></small>
                                </p>
                                <div class="d-flex">
                                    <button class="btn btn-sm btn-outline-primary me-2" onclick="copyToClipboard('{{ $imageUrl }}')">
                                        Copier l'URL
                                    </button>
                                    <a href="{{ route('prizes.delete', ['filename' => $file]) }}" 
                                      class="btn btn-sm btn-outline-danger"
                                      onclick="return confirm('Êtes-vous sûr de vouloir supprimer cette image?')">
                                        Supprimer
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            @else
                <div class="col-12">
                    <div class="alert alert-info">
                        Aucune image n'a été uploadée pour le moment.
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>

<script>
function copyToClipboard(text) {
    const textarea = document.createElement('textarea');
    textarea.value = text;
    document.body.appendChild(textarea);
    textarea.select();
    document.execCommand('copy');
    document.body.removeChild(textarea);
    
    // Notification
    alert('URL copiée: ' + text);
}
</script>
