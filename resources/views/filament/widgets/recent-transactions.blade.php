<div wire:init="mount" wire:poll.{{ $this->getPollingInterval() }}s>
    <table class="w-full table-auto text-sm">
        <thead>
            <tr>
                <th class="px-2 py-1">Masuk</th>
                <th class="px-2 py-1">Plat</th>
                <th class="px-2 py-1">Area</th>
                <th class="px-2 py-1">Status</th>
                <th class="px-2 py-1">Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach($transactions as $trx)
                <tr>
                    <td class="border px-2 py-1">{{ $trx->waktu_masuk->format('d/m H:i') }}</td>
                    <td class="border px-2 py-1">{{ $trx->kendaraan->plat_nomor ?? '-' }}</td>
                    <td class="border px-2 py-1">{{ $trx->areaParkir->nama_area ?? '-' }}</td>
                    <td class="border px-2 py-1">{{ $trx->status }}</td>
                    <td class="border px-2 py-1">Rp {{ number_format($trx->biaya_total,0,',','.') }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
