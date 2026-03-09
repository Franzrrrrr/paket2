<x-filament-panels::page>
        <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
    <div class="space-y-6">

        {{-- Header Card --}}
        <div class="fi-section rounded-xl bg-white shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">
            <div class="fi-section-header flex items-center gap-x-3 px-6 py-4">
                <div class="flex-1">
                    <h3 class="fi-section-header-heading text-base font-semibold leading-6 text-gray-950 dark:text-white">
                        Detail Transaksi #{{ $record->id }}
                    </h3>
                    <p class="fi-section-header-description mt-1 text-sm text-gray-500 dark:text-gray-400">
                        Informasi lengkap transaksi parkir
                    </p>
                </div>

                {{-- Status Badge --}}
                <span @class([
                    'inline-flex items-center rounded-full px-3 py-1 text-xs font-medium ring-1 ring-inset',
                    'bg-yellow-50 text-yellow-700 ring-yellow-600/20 dark:bg-yellow-400/10 dark:text-yellow-400' => $record->status === 'masuk',
                    'bg-green-50 text-green-700 ring-green-600/20 dark:bg-green-400/10 dark:text-green-400'   => $record->status === 'selesai',
                    'bg-red-50 text-red-700 ring-red-600/20 dark:bg-red-400/10 dark:text-red-400'             => $record->status === 'dibatalkan',
                    'bg-gray-50 text-gray-700 ring-gray-600/20 dark:bg-gray-400/10 dark:text-gray-400'        => !in_array($record->status, ['masuk', 'selesai', 'dibatalkan']),
                ])>
                    {{ ucfirst($record->status) }}
                </span>

                {{-- Tombol Cetak --}}
                <a
                    href="{{ route('transaksi.struk', $record->id) }}"
                    target="_blank"
                    class="fi-btn fi-btn-color-primary fi-btn-size-md inline-flex items-center gap-x-1.5 rounded-lg bg-gray-700 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-primary-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-primary-600"
                >
                    <x-heroicon-o-printer class="h-4 w-4" />
                    Cetak Struk
                </a>
            </div>
        </div>

        {{-- Info Grid --}}
        <div class="grid grid-cols-1 gap-6 md:grid-cols-2">

            {{-- Informasi Kendaraan --}}
            <div class="fi-section rounded-xl bg-white shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">
                <div class="fi-section-header px-6 py-4 border-b border-gray-200 dark:border-white/10">
                    <h3 class="text-sm font-semibold text-gray-950 dark:text-white flex items-center gap-2">
                        <x-heroicon-o-truck class="h-4 w-4 text-primary-500" />
                        Informasi Kendaraan
                    </h3>
                </div>
                <div class="px-6 py-4 space-y-3">
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-500 dark:text-gray-400">Plat Nomor</span>
                        <span class="font-semibold text-gray-950 dark:text-white font-mono tracking-widest">
                            {{ $record->kendaraan->plat_nomor ?? '-' }}
                        </span>
                    </div>
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-500 dark:text-gray-400">Jenis</span>
                        <span class="font-medium text-gray-950 dark:text-white capitalize">
                            {{ $record->kendaraan->jenis_kendaraan ?? '-' }}
                        </span>
                    </div>
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-500 dark:text-gray-400">Warna</span>
                        <span class="font-medium text-gray-950 dark:text-white">
                            {{ $record->kendaraan->warna ?? '-' }}
                        </span>
                    </div>
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-500 dark:text-gray-400">Pemilik</span>
                        <span class="font-medium text-gray-950 dark:text-white">
                            {{ $record->kendaraan->pemilik ?? '-' }}
                        </span>
                    </div>
                </div>
            </div>

            {{-- Informasi Parkir --}}
            <div class="fi-section rounded-xl bg-white shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">
                <div class="fi-section-header px-6 py-4 border-b border-gray-200 dark:border-white/10">
                    <h3 class="text-sm font-semibold text-gray-950 dark:text-white flex items-center gap-2">
                        <x-heroicon-o-map-pin class="h-4 w-4 text-primary-500" />
                        Informasi Parkir
                    </h3>
                </div>
                <div class="px-6 py-4 space-y-3">
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-500 dark:text-gray-400">Area Parkir</span>
                        <span class="font-medium text-gray-950 dark:text-white">
                            {{ $record->areaParkir->nama_area ?? '-' }}
                        </span>
                    </div>
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-500 dark:text-gray-400">Waktu Masuk</span>
                        <span class="font-medium text-gray-950 dark:text-white">
                            {{ $record->waktu_masuk?->format('d M Y, H:i') ?? '-' }}
                        </span>
                    </div>
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-500 dark:text-gray-400">Waktu Keluar</span>
                        <span class="font-medium text-gray-950 dark:text-white">
                            {{ $record->waktu_keluar?->format('d M Y, H:i') ?? '-' }}
                        </span>
                    </div>
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-500 dark:text-gray-400">Durasi</span>
                        <span class="font-medium text-gray-950 dark:text-white">
                            {{ $record->durasi_jam ? $record->durasi_jam . ' jam' : '-' }}
                        </span>
                    </div>
                </div>
            </div>

            {{-- Informasi Tarif & Biaya --}}
            <div class="fi-section rounded-xl bg-white shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">
                <div class="fi-section-header px-6 py-4 border-b border-gray-200 dark:border-white/10">
                    <h3 class="text-sm font-semibold text-gray-950 dark:text-white flex items-center gap-2">
                        <x-heroicon-o-banknotes class="h-4 w-4 text-primary-500" />
                        Rincian Biaya
                    </h3>
                </div>
                <div class="px-6 py-4 space-y-3">
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-500 dark:text-gray-400">Tarif Per Jam</span>
                        <span class="font-medium text-gray-950 dark:text-white">
                            Rp {{ number_format($record->tarif->tarif_per_jam ?? 0, 0, ',', '.') }}
                        </span>
                    </div>
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-500 dark:text-gray-400">Denda Inap/Hari</span>
                        <span class="font-medium text-gray-950 dark:text-white">
                            Rp {{ number_format($record->tarif->denda_inap_per_hari ?? 0, 0, ',', '.') }}
                        </span>
                    </div>
                    <div class="border-t border-gray-200 dark:border-white/10 pt-3 flex justify-between text-sm">
                        <span class="font-semibold text-gray-950 dark:text-white">Total Biaya</span>
                        <span class="font-bold text-primary-600 dark:text-primary-400 text-base">
                            Rp {{ number_format($record->biaya_total ?? 0, 0, ',', '.') }}
                        </span>
                    </div>
                </div>
            </div>

            {{-- Petugas --}}
            <div class="fi-section rounded-xl bg-white shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">
                <div class="fi-section-header px-6 py-4 border-b border-gray-200 dark:border-white/10">
                    <h3 class="text-sm font-semibold text-gray-950 dark:text-white flex items-center gap-2">
                        <x-heroicon-o-user class="h-4 w-4 text-primary-500" />
                        Petugas
                    </h3>
                </div>
                <div class="px-6 py-4 space-y-3">
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-500 dark:text-gray-400">Nama</span>
                        <span class="font-medium text-gray-950 dark:text-white">
                            {{ $record->user->nama_lengkap ?? '-' }}
                        </span>
                    </div>
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-500 dark:text-gray-400">Username</span>
                        <span class="font-medium text-gray-950 dark:text-white">
                            {{ $record->user->username ?? '-' }}
                        </span>
                    </div>
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-500 dark:text-gray-400">Role</span>
                        <span class="font-medium text-gray-950 dark:text-white capitalize">
                            {{ $record->user->role ?? '-' }}
                        </span>
                    </div>
                </div>
            </div>

        </div>

        {{-- Kembali --}}
        <div>
            <a
                href="{{ url()->previous() }}"
                class="fi-btn fi-btn-color-gray fi-btn-size-md inline-flex items-center gap-x-1.5 rounded-lg bg-white px-3 py-2 text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50"
            >
                <x-heroicon-o-arrow-left class="h-4 w-4" />
                Kembali
            </a>
        </div>

    </div>
</x-filament-panels::page>
