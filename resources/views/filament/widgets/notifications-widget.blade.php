<div wire:init="mount" wire:poll.{{ $this->getPollingInterval() }}s>
    <ul class="text-sm space-y-1">
        @forelse($messages as $msg)
            <li class="border-l-4 pl-2 {{ str_contains($msg, 'penuh') ? 'border-red-500' : 'border-yellow-500' }}">
                {{ $msg }}
            </li>
        @empty
            <li class="text-gray-500">Tidak ada notifikasi</li>
        @endforelse
    </ul>
</div>
