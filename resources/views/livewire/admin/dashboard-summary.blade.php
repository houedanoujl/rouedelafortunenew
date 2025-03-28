<div class="dashboard-summary">
    <div class="row">
        <div class="col-md-3">
            <div class="card">
                <div class="card-body text-center">
                    <h5 class="card-title">Participants</h5>
                    <p class="card-text display-4">{{ $totalParticipants }}</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card">
                <div class="card-body text-center">
                    <h5 class="card-title">Participations</h5>
                    <p class="card-text display-4">{{ $totalEntries }}</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card">
                <div class="card-body text-center">
                    <h5 class="card-title">Prix disponibles</h5>
                    <p class="card-text display-4">{{ $totalPrizes }}</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card">
                <div class="card-body text-center">
                    <h5 class="card-title">Prix gagnés</h5>
                    <p class="card-text display-4">{{ $totalPrizesWon }}</p>
                </div>
            </div>
        </div>
    </div>

    <div class="row mt-4">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5>Participations récentes</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Participant</th>
                                    <th>Prix</th>
                                    <th>Résultat</th>
                                    <th>Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($recentEntries as $entry)
                                    <tr>
                                        <td>{{ $entry['participant_name'] }}</td>
                                        <td>{{ $entry['prize_name'] }}</td>
                                        <td>
                                            @if ($entry['result'] === 'win')
                                                <span class="badge bg-success">Gagné</span>
                                            @else
                                                <span class="badge bg-danger">Perdu</span>
                                            @endif
                                        </td>
                                        <td>{{ $entry['played_at'] }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="text-center">Aucune participation récente</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5>Distribution des prix</h5>
                </div>
                <div class="card-body">
                    <div id="prizes-chart" style="height: 300px;"></div>
                </div>
            </div>
        </div>
    </div>

    <div class="row mt-4">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h5>Participants par jour (30 derniers jours)</h5>
                </div>
                <div class="card-body">
                    <div id="participants-chart" style="height: 300px;"></div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
<script>
    document.addEventListener('livewire:initialized', () => {
        // Graphique de distribution des prix
        const prizesData = @json($prizesDistribution);
        
        if (prizesData.length > 0) {
            const prizesOptions = {
                series: prizesData.map(item => item.count),
                chart: {
                    type: 'donut',
                    height: 300
                },
                labels: prizesData.map(item => item.name),
                responsive: [{
                    breakpoint: 480,
                    options: {
                        chart: {
                            width: 200
                        },
                        legend: {
                            position: 'bottom'
                        }
                    }
                }]
            };

            const prizesChart = new ApexCharts(document.querySelector("#prizes-chart"), prizesOptions);
            prizesChart.render();
        } else {
            document.querySelector("#prizes-chart").innerHTML = '<div class="text-center py-5">Aucune donnée disponible</div>';
        }

        // Graphique des participants par jour
        const participantsData = @json($participantsPerDay);
        
        if (participantsData.length > 0) {
            const participantsOptions = {
                series: [{
                    name: 'Participants',
                    data: participantsData.map(item => item.count)
                }],
                chart: {
                    height: 300,
                    type: 'area',
                    zoom: {
                        enabled: false
                    }
                },
                dataLabels: {
                    enabled: false
                },
                stroke: {
                    curve: 'smooth'
                },
                xaxis: {
                    categories: participantsData.map(item => {
                        const date = new Date(item.date);
                        return date.toLocaleDateString('fr-FR', { day: '2-digit', month: '2-digit' });
                    })
                },
                tooltip: {
                    y: {
                        formatter: function (val) {
                            return val + " participants";
                        }
                    }
                }
            };

            const participantsChart = new ApexCharts(document.querySelector("#participants-chart"), participantsOptions);
            participantsChart.render();
        } else {
            document.querySelector("#participants-chart").innerHTML = '<div class="text-center py-5">Aucune donnée disponible</div>';
        }
    });
</script>
@endpush
