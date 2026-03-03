<div wire:init="mount">
    <canvas id="traffic-chart" style="max-height:300px;"></canvas>
    <script>
        document.addEventListener('livewire:load', function () {
            const ctx = document.getElementById('traffic-chart').getContext('2d');
            new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: @json($labels),
                    datasets: [{
                        label: 'Jumlah Masuk',
                        data: @json($data),
                        backgroundColor: '#9966ff',
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
