<div wire:init="mount" wire:poll.{{ $this->getPollingInterval() }}s>
    <ul class="space-y-1 text-sm">
        @foreach($logs as $log)
            <li class="border-b py-1">
                <span class="font-semibold">{{ $log->user->name ?? 'System' }}</span>: {{ $log->aktivitas }}
                <span class="text-xs text-gray-500">({{ $log->created_at->diffForHumans() }})</span>
            </li>
        @endforeach
    </ul>
</div>
