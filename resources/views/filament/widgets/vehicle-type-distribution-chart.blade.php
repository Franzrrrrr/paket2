<div wire:init="mount">
    <canvas id="vehicle-type-chart" style="max-height:300px;"></canvas>
    <script>
        document.addEventListener('livewire:load', function () {
            const ctx = document.getElementById('vehicle-type-chart').getContext('2d');
            new Chart(ctx, {
                type: 'pie',
                data: {
                    labels: @json($labels),
                    datasets: [{
                        data: @json($data),
                        backgroundColor: ['#36a2eb', '#ff6384', '#ffcd56'],
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
