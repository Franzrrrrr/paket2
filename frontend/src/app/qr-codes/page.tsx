'use client';

import { useState, useEffect } from 'react';
import { useRouter } from 'next/navigation';
import { qrCodeAPI } from '@/lib/api';
import { QRCodeCanvas } from 'qrcode.react';
import { ArrowLeft, QrCode, Download, Eye, EyeOff, RefreshCw, AlertCircle } from 'lucide-react';

interface QRCodeItem {
  booking_id?: number;
  ticket_code?: string;
  qr_data: any;
  area?: { id: number; nama_area?: string } | null;
  status: string;
  expires_at: string;
}

// ── Single QR Card ─────────────────────────────────────────────────────────
function QRCard({ qr, index }: { qr: QRCodeItem; index: number }) {
  const router = useRouter();
  const [showData, setShowData] = useState(false);

  const expired  = new Date(qr.expires_at) < new Date();
  const qrValue  = JSON.stringify(qr.qr_data);
  const canvasId = `qr-canvas-${index}`;

  const statusCfg: Record<string, { bg: string; text: string }> = {
    ACTIVE:     { bg: 'bg-green-50',  text: 'text-green-700'  },
    BOOKED:     { bg: 'bg-blue-50',   text: 'text-blue-700'   },
    CHECKED_IN: { bg: 'bg-amber-50',  text: 'text-amber-700'  },
    EXPIRED:    { bg: 'bg-red-50',    text: 'text-red-600'    },
  };
  const s = statusCfg[qr.status] ?? { bg: 'bg-slate-50', text: 'text-slate-500' };

  const handleDownload = () => {
    const canvas = document.getElementById(canvasId) as HTMLCanvasElement;
    if (!canvas) return;
    const url = canvas.toDataURL('image/png');
    const a   = document.createElement('a');
    a.href     = url;
    a.download = `qr-${qr.ticket_code ?? qr.area?.nama_area ?? 'qr'}.png`;
    a.click();
  };

  return (
    <div className="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden hover:-translate-y-0.5 hover:shadow-md transition-all">
      <div className="p-5">

        {/* Header */}
        <div className="flex items-center justify-between mb-4">
          <span className="text-sm font-semibold text-slate-800 truncate">
            {qr.ticket_code ?? qr.area?.nama_area ?? '—'}
          </span>
          <span className={`px-2.5 py-0.5 rounded-full text-xs font-bold shrink-0 ml-2 ${s.bg} ${s.text}`}>
            {qr.status}
          </span>
        </div>

        {/* QR Code — generated di browser */}
        <div className="flex justify-center mb-3">
          <div className={`p-3 rounded-xl border-2 transition-all ${
            expired ? 'border-slate-100 opacity-40 grayscale' : 'border-blue-100'
          }`}>
            <QRCodeCanvas
              id={canvasId}
              value={qrValue}
              size={150}
              bgColor="#ffffff"
              fgColor="#1e293b"
              level="M"
              includeMargin={false}
            />
          </div>
        </div>

        {/* Ticket code mono */}
        {qr.ticket_code && (
          <p className="text-center text-xs font-mono font-bold text-slate-400 tracking-widest mb-3">
            {qr.ticket_code}
          </p>
        )}

        {/* Kadaluarsa */}
        <div className="flex justify-between text-xs mb-4 px-1">
          <span className="text-slate-400 font-medium">Kadaluarsa:</span>
          <span className={`font-semibold ${expired ? 'text-red-500' : 'text-slate-600'}`}>
            {new Date(qr.expires_at).toLocaleString('id-ID')}
          </span>
        </div>

        {/* Actions */}
        <div className="flex items-center gap-2 pt-3 border-t border-slate-100">
          <button
            onClick={() => setShowData(!showData)}
            className="flex-1 flex items-center justify-center gap-1.5 py-2 rounded-xl border border-slate-200 text-slate-500 text-xs font-semibold hover:border-blue-300 hover:text-blue-600 hover:bg-blue-50 transition-all"
          >
            {showData ? <EyeOff size={13} /> : <Eye size={13} />}
            {showData ? 'Tutup' : 'Lihat Data'}
          </button>

          {!expired ? (
            <button
              onClick={handleDownload}
              className="flex-1 flex items-center justify-center gap-1.5 py-2 rounded-xl border border-slate-200 text-slate-500 text-xs font-semibold hover:border-blue-300 hover:text-blue-600 hover:bg-blue-50 transition-all"
            >
              <Download size={13} /> Download
            </button>
          ) : (
            <button
              onClick={() => router.push('/booking/new')}
              className="flex-1 flex items-center justify-center gap-1.5 py-2 rounded-xl border border-slate-200 text-slate-500 text-xs font-semibold hover:border-blue-300 hover:text-blue-600 hover:bg-blue-50 transition-all"
            >
              <RefreshCw size={13} /> Buat Baru
            </button>
          )}
        </div>

        {/* Raw data (toggleable) */}
        {showData && (
          <div className="mt-3 p-3 bg-slate-50 rounded-xl border border-slate-100">
            <pre className="text-xs text-slate-500 whitespace-pre-wrap break-all">
              {JSON.stringify(qr.qr_data, null, 2)}
            </pre>
          </div>
        )}
      </div>
    </div>
  );
}

// ── Main Page ──────────────────────────────────────────────────────────────
export default function QRCodesPage() {
  const router = useRouter();
  const [qrCodes, setQRCodes] = useState<QRCodeItem[]>([]);
  const [areas, setAreas]     = useState<any[]>([]);
  const [isLoading, setIsLoading] = useState(true);
  const [error, setError]         = useState('');

  useEffect(() => { fetchData(); }, []);

  const fetchData = async () => {
    try {
      const response = await qrCodeAPI.getMyQRCodes();
      setQRCodes(response.qr_codes ?? []);
      setAreas(response.areas ?? []);
    } catch (err: any) {
      setError(err.response?.data?.message || 'Gagal memuat QR codes');
    } finally {
      setIsLoading(false);
    }
  };

  const generateAreaQR = async (areaId: number) => {
    try {
      const response = await qrCodeAPI.generateAreaQR(areaId);
      // Backend cukup return qr_data (JSON), tidak perlu gambar
      setQRCodes(prev => [{
        area: response.area || { id: areaId, nama_area: 'Area Tidak Diketahui' },
        qr_data: response.qr_data,
        status: 'ACTIVE',
        expires_at: response.qr_data.expires_at,
      }, ...prev]);
    } catch (err: any) {
      setError(err.response?.data?.message || 'Gagal generate QR code');
    }
  };

  if (isLoading) {
    return (
      <div className="min-h-screen flex items-center justify-center bg-slate-50">
        <div className="w-10 h-10 rounded-full border-4 border-blue-200 border-t-blue-500 animate-spin" />
      </div>
    );
  }

  return (
    <div className="min-h-screen bg-slate-50">

      {/* Header */}
      <header className="bg-white border-b border-slate-100 sticky top-0 z-20 shadow-sm">
        <div className="max-w-6xl mx-auto px-5 h-14 flex items-center gap-3">
          <button onClick={() => router.back()} className="p-2 rounded-xl hover:bg-slate-100 text-slate-500 transition-colors">
            <ArrowLeft size={18} />
          </button>
          <h1 className="text-base font-bold text-slate-900">QR Code Parkir</h1>
        </div>
      </header>

      <main className="max-w-6xl mx-auto px-5 py-6 space-y-6">

        {/* Error */}
        {error && (
          <div className="flex items-center gap-2 px-4 py-3 bg-red-50 border border-red-200 rounded-xl text-red-600 text-sm">
            <AlertCircle size={15} /> {error}
          </div>
        )}

        {/* Generate Area QR */}
        {areas.length > 0 && (
          <div className="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
            <div className="flex items-center gap-2 px-5 py-4 border-b border-slate-100">
              <QrCode size={15} className="text-blue-500" />
              <span className="text-sm font-bold text-slate-800">Generate QR Area Parkir</span>
            </div>
            <div className="p-4 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">
              {areas.map((area) => (
                <div key={area.id} className="flex items-center justify-between p-3.5 rounded-xl border border-slate-100 hover:border-blue-200 transition-all">
                  <div>
                    <p className="text-sm font-semibold text-slate-800">{area.nama_area}</p>
                    <p className="text-xs text-slate-400">{area.terisi}/{area.kapasitas} terisi</p>
                  </div>
                  <button
                    onClick={() => generateAreaQR(area.id)}
                    className="px-3 py-1.5 rounded-lg bg-gradient-to-r from-blue-400 to-blue-600 text-white text-xs font-semibold shadow-sm hover:opacity-90 transition-all"
                  >
                    Generate
                  </button>
                </div>
              ))}
            </div>
          </div>
        )}

        {/* QR List */}
        <div>
          <h2 className="text-sm font-bold text-slate-700 mb-4">QR Code Saya</h2>

          {qrCodes.length === 0 ? (
            <div className="flex flex-col items-center justify-center py-16 bg-white rounded-2xl border border-slate-100">
              <QrCode size={44} className="text-slate-200 mb-3" />
              <p className="text-sm font-semibold text-slate-600 mb-1">Belum Ada QR Code</p>
              <p className="text-xs text-slate-400 mb-5">Buat booking terlebih dahulu</p>
              <button
                onClick={() => router.push('/booking/new')}
                className="px-5 py-2 rounded-xl bg-blue-500 text-white text-sm font-semibold hover:bg-blue-600 transition-colors"
              >
                Buat Booking
              </button>
            </div>
          ) : (
            <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
              {qrCodes.map((qr, i) => (
                <QRCard key={i} qr={qr} index={i} />
              ))}
            </div>
          )}
        </div>

      </main>
    </div>
  );
}
