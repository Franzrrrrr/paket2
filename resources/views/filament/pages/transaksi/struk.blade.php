<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Struk Parkir #{{ $transaksi->id }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Courier New', Courier, monospace;
            font-size: 12px;
            background: #f0f0f0;
            display: flex;
            justify-content: center;
            align-items: flex-start;
            min-height: 100vh;
            padding: 20px;
        }

        .struk {
            background: white;
            width: 300px;
            padding: 20px 16px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.15);
            border-radius: 4px;
        }

        .header {
            text-align: center;
            border-bottom: 1px dashed #333;
            padding-bottom: 12px;
            margin-bottom: 12px;
        }

        .header h1 {
            font-size: 16px;
            font-weight: bold;
            letter-spacing: 2px;
            text-transform: uppercase;
        }

        .header p {
            font-size: 11px;
            color: #555;
            margin-top: 2px;
        }

        .no-transaksi {
            text-align: center;
            font-size: 11px;
            color: #666;
            margin-bottom: 12px;
        }

        .section {
            margin-bottom: 10px;
        }

        .section-title {
            font-weight: bold;
            font-size: 10px;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: #888;
            margin-bottom: 4px;
        }

        .row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 3px;
        }

        .row .label {
            color: #555;
        }

        .row .value {
            font-weight: 600;
            text-align: right;
            max-width: 55%;
            word-break: break-word;
        }

        .divider {
            border: none;
            border-top: 1px dashed #bbb;
            margin: 10px 0;
        }

        .total-section {
            border-top: 2px solid #333;
            border-bottom: 2px solid #333;
            padding: 8px 0;
            margin: 10px 0;
        }

        .total-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .total-row .total-label {
            font-weight: bold;
            font-size: 13px;
        }

        .total-row .total-value {
            font-weight: bold;
            font-size: 15px;
        }

        .status-badge {
            display: inline-block;
            padding: 2px 8px;
            border-radius: 20px;
            font-size: 10px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .status-selesai  { background: #dcfce7; color: #166534; }
        .status-masuk    { background: #fef9c3; color: #854d0e; }
        .status-dibatalkan { background: #fee2e2; color: #991b1b; }

        .footer {
            text-align: center;
            margin-top: 14px;
            padding-top: 12px;
            border-top: 1px dashed #333;
            font-size: 10px;
            color: #888;
            line-height: 1.6;
        }

        .plat-besar {
            font-size: 20px;
            font-weight: bold;
            letter-spacing: 4px;
            text-align: center;
            padding: 6px 0;
            border: 2px solid #333;
            border-radius: 4px;
            margin: 8px 0;
        }

        .print-btn {
            display: block;
            width: 100%;
            margin-top: 16px;
            padding: 10px;
            background: #1d4ed8;
            color: white;
            border: none;
            border-radius: 6px;
            font-size: 13px;
            font-family: sans-serif;
            font-weight: 600;
            cursor: pointer;
            letter-spacing: 0.5px;
        }

        .print-btn:hover {
            background: #1e40af;
        }

        @media print {
            body {
                background: white;
                padding: 0;
            }
            .struk {
                box-shadow: none;
                border-radius: 0;
                width: 100%;
                max-width: 300px;
                margin: 0 auto;
            }
            .print-btn {
                display: none;
            }
        }
    </style>
</head>
<body>

<div class="struk">

    {{-- Header --}}
    <div class="header">
        <h1>🅿 ParkingApp</h1>
        <p>Sistem Manajemen Parkir</p>
        <p>{{ now()->format('d M Y, H:i:s') }}</p>
    </div>

    <div class="no-transaksi">
        No. Transaksi: <strong>#{{ str_pad($transaksi->id, 6, '0', STR_PAD_LEFT) }}</strong>
    </div>

    {{-- Plat Nomor Besar --}}
    <div class="plat-besar">{{ $transaksi->kendaraan->plat_nomor ?? '-' }}</div>

    {{-- Status --}}
    <div style="text-align:center; margin-bottom: 10px;">
        <span class="status-badge status-{{ $transaksi->status }}">
            {{ ucfirst($transaksi->status) }}
        </span>
    </div>

    <hr class="divider">

    {{-- Kendaraan --}}
    <div class="section">
        <div class="section-title">Kendaraan</div>
        <div class="row">
            <span class="label">Jenis</span>
            <span class="value">{{ ucfirst($transaksi->kendaraan->jenis_kendaraan ?? '-') }}</span>
        </div>
        <div class="row">
            <span class="label">Pemilik</span>
            <span class="value">{{ $transaksi->kendaraan->pemilik ?? '-' }}</span>
        </div>
    </div>

    <hr class="divider">

    {{-- Waktu --}}
    <div class="section">
        <div class="section-title">Waktu Parkir</div>
        <div class="row">
            <span class="label">Masuk</span>
            <span class="value">{{ $transaksi->waktu_masuk?->format('d/m/Y H:i') ?? '-' }}</span>
        </div>
        <div class="row">
            <span class="label">Keluar</span>
            <span class="value">{{ $transaksi->waktu_keluar?->format('d/m/Y H:i') ?? '-' }}</span>
        </div>
        <div class="row">
            <span class="label">Durasi</span>
            <span class="value">{{ $transaksi->durasi_jam ? $transaksi->durasi_jam . ' jam' : '-' }}</span>
        </div>
        <div class="row">
            <span class="label">Area</span>
            <span class="value">{{ $transaksi->areaParkir->nama_area ?? '-' }}</span>
        </div>
    </div>

    <hr class="divider">

    {{-- Rincian Biaya --}}
    <div class="section">
        <div class="section-title">Rincian Biaya</div>
        <div class="row">
            <span class="label">Tarif/jam</span>
            <span class="value">Rp {{ number_format($transaksi->tarif->tarif_per_jam ?? 0, 0, ',', '.') }}</span>
        </div>
        <div class="row">
            <span class="label">Durasi</span>
            <span class="value">{{ $transaksi->durasi_jam ?? 0 }} jam</span>
        </div>
        @if(($transaksi->tarif->denda_inap_per_hari ?? 0) > 0)
        <div class="row">
            <span class="label">Denda Inap</span>
            <span class="value">Rp {{ number_format($transaksi->tarif->denda_inap_per_hari, 0, ',', '.') }}/hari</span>
        </div>
        @endif
    </div>

    {{-- Total --}}
    <div class="total-section">
        <div class="total-row">
            <span class="total-label">TOTAL</span>
            <span class="total-value">Rp {{ number_format($transaksi->biaya_total ?? 0, 0, ',', '.') }}</span>
        </div>
    </div>

    {{-- Petugas --}}
    <div class="section">
        <div class="row">
            <span class="label">Petugas</span>
            <span class="value">{{ $transaksi->user->nama_lengkap ?? '-' }}</span>
        </div>
    </div>

    {{-- Footer --}}
    <div class="footer">
        <p>Terima kasih telah menggunakan</p>
        <p><strong>ParkingApp</strong></p>
        <p>Simpan struk ini sebagai bukti parkir</p>
    </div>

    {{-- Tombol Cetak (hilang saat print) --}}
    <button class="print-btn" onclick="window.print()">
        🖨️ Cetak Struk
    </button>

</div>

</body>
</html>
