<div wire:init="mount">
    <canvas id="revenue-chart" style="max-height:300px;"></canvas>
    <script>
        document.addEventListener('livewire:load', function () {
            const ctx = document.getElementById('revenue-chart').getContext('2d');
            new Chart(ctx, {
                type: 'line',
                data: {
                    labels: @json($labels),
                    datasets: [{
                        label: 'Pendapatan (Rp)',
                        data: @json($data),
                        borderColor: '#4bc0c0',
                        fill: false,
                    }],
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                },
            });
        });
    </script>
</div>
