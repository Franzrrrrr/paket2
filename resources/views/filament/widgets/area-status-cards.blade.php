<div class="grid grid-cols-1 md:grid-cols-3 gap-4" wire:init="mount" wire:poll.{{ $this->getPollingInterval() }}s>
    @foreach($areas as $area)
        @php
            $color = 'bg-green-100';
            if($area['status']=='penuh') $color = 'bg-red-100';
            elseif($area['status']=='hampir-penuh') $color = 'bg-yellow-100';
        @endphp
        <div class="p-4 rounded shadow {{ $color }}">
            <h3 class="font-semibold">{{ $area['name'] }}</h3>
            <p>{{ $area['terisi'] }}/{{ $area['kapasitas'] }} ({{ $area['rate'] }}%)</p>
            <span class="text-sm capitalize">{{ str_replace('-', ' ', $area['status']) }}</span>
        </div>
    @endforeach
</div>
