<div wire:init="mount" wire:poll.{{ $this->getPollingInterval() }}s>
    <table class="w-full table-auto text-sm">
        <thead>
            <tr>
                <th class="px-2 py-1">Plat Nomor</th>
                <th class="px-2 py-1">Durasi</th>
            </tr>
        </thead>
        <tbody>
            @forelse($vehicles as $v)
                <tr>
                    <td class="border px-2 py-1">{{ $v['plat'] }}</td>
                    <td class="border px-2 py-1">{{ $v['durasi'] }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="2" class="text-center px-2 py-4">Tidak ada kendaraan parkir</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
