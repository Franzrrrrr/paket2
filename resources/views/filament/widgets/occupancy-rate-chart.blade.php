<div wire:init="mount">
    <canvas id="occupancy-chart" style="max-height:300px;"></canvas>

    <script>
        document.addEventListener('livewire:load', function () {
            const ctx = document.getElementById('occupancy-chart').getContext('2d');
            new Chart(ctx, {
                type: 'doughnut',
                data: {
                    labels: @json($labels),
                    datasets: [{
                        label: 'Occupancy (%)',
                        data: @json($data),
                        backgroundColor: [
                            '#4dc9f6',
                            '#f67019',
                            '#f53794',
                            '#537bc4',
                            '#acc236',
                        ],
                    }],
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'bottom',
                        },
                        tooltip: {
                            callbacks: {
                                label: function (context) {
                                    return context.parsed + '%';
                                },
                            },
                        },
                    },
                },
            });
        });
    </script>
</div>
