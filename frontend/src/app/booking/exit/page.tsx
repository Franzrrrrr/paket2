'use client';

import { useState } from 'react';
import { useRouter } from 'next/navigation';
import { bookingAPI } from '@/lib/api';
import { formatCurrency, formatDateTime } from '@/lib/utils';
import {
  ArrowLeft, Car, Banknote, Clock, CheckCircle2,
  ChevronRight, TicketCheck, Loader2, ScanLine, Keyboard
} from 'lucide-react';

type ExitMode = 'manual' | 'scan';

export default function ExitPage() {
  const router = useRouter();
  const [mode, setMode]               = useState<ExitMode>('manual');
  const [ticketCode, setTicketCode]   = useState('');
  const [isLoading, setIsLoading]     = useState(false);
  const [error, setError]             = useState('');
  const [successData, setSuccessData] = useState<any>(null);

  // ── Scan via camera (html5-qrcode or zxing) ──
  const startScan = async () => {
    setMode('scan');
    setError('');
    try {
      const { Html5Qrcode } = await import('html5-qrcode');
      const qr = new Html5Qrcode('qr-reader');
      await qr.start(
        { facingMode: 'environment' },
        { fps: 10, qrbox: { width: 240, height: 240 } },
        async (decodedText) => {
          await qr.stop();
          setMode('manual');
          setTicketCode(decodedText.toUpperCase());
          await processExit(decodedText.trim());
        },
        () => {}
      );
    } catch {
      setError('Kamera tidak tersedia. Gunakan input manual.');
      setMode('manual');
    }
  };

  const processExit = async (code: string) => {
    if (!code.trim()) { setError('Silakan masukkan kode tiket'); return; }
    setIsLoading(true);
    setError('');
    try {
      const response = await bookingAPI.exit({ ticket_code: code.trim() });
      setSuccessData(response.data);
    } catch (err: any) {
      setError(err.response?.data?.message || 'Proses keluar gagal. Silakan coba lagi.');
    } finally {
      setIsLoading(false);
    }
  };

  const handleSubmit = (e: React.FormEvent<HTMLFormElement>) => {
    e.preventDefault();
    processExit(ticketCode);
  };

  const handleReset = () => {
    setTicketCode(''); setSuccessData(null); setError('');
  };

  // ────────────────────────────────────────────────────────
  // SUCCESS STATE
  // ────────────────────────────────────────────────────────
  if (successData) {
    const jam   = Math.floor(successData.duration_minutes / 60);
    const menit = successData.duration_minutes % 60;
    return (
      <div className="min-h-screen bg-slate-50">
        <header className="bg-white border-b border-slate-100 sticky top-0 z-20 shadow-sm">
          <div className="max-w-2xl mx-auto px-5 h-14 flex items-center gap-3">
            <button onClick={() => router.push('/dashboard')} className="p-2 rounded-xl hover:bg-slate-100 text-slate-500 transition-colors">
              <ArrowLeft size={18} />
            </button>
            <h1 className="text-base font-bold text-slate-900">Keluar Parkir Berhasil</h1>
          </div>
        </header>

        <main className="max-w-2xl mx-auto px-5 py-7 space-y-5 pb-32">

          {/* Hero */}
          <div className="bg-white rounded-2xl border border-slate-100 shadow-sm px-6 py-8 flex flex-col items-center text-center">
            <div className="w-16 h-16 rounded-2xl bg-green-100 flex items-center justify-center mb-4">
              <CheckCircle2 size={34} className="text-green-500" />
            </div>
            <h2 className="text-lg font-extrabold text-slate-900 tracking-tight mb-1">Terima Kasih!</h2>
            <p className="text-sm text-slate-400 mb-5">Semoga perjalanan Anda menyenangkan</p>
            <div className="flex items-center gap-2.5 px-5 py-3 bg-blue-50 border border-blue-200 rounded-xl">
              <TicketCheck size={18} className="text-blue-500 shrink-0" />
              <div className="text-left">
                <p className="text-xs text-blue-400 font-medium">Kode Tiket</p>
                <p className="text-base font-extrabold text-blue-700 tracking-widest font-mono">{successData.ticket_code}</p>
              </div>
            </div>
          </div>

          {/* Detail Pembayaran */}
          <div className="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
            <div className="flex items-center gap-2 px-5 py-4 border-b border-slate-100">
              <Banknote size={15} className="text-blue-500" />
              <span className="text-sm font-bold text-slate-800">Detail Pembayaran</span>
            </div>
            <div className="divide-y divide-slate-50">
              {[
                { label: 'Durasi Parkir', value: `${jam} jam ${menit} menit` },
                { label: 'Waktu Keluar', value: formatDateTime(successData.exit_time) },
              ].map((row, i) => (
                <div key={i} className="flex items-center justify-between px-5 py-3.5">
                  <span className="text-sm text-slate-400 font-medium">{row.label}</span>
                  <span className="text-sm font-semibold text-slate-700">{row.value}</span>
                </div>
              ))}
              {/* Total */}
              <div className="flex items-center justify-between px-5 py-4 bg-green-50">
                <span className="text-sm font-bold text-slate-800">Total Biaya</span>
                <span className="text-lg font-extrabold text-green-600 tracking-tight">
                  {formatCurrency(successData.total_price)}
                </span>
              </div>
            </div>
          </div>

        </main>

        {/* CTA */}
        <div className="fixed bottom-0 left-0 right-0 bg-white border-t border-slate-100 shadow-lg px-5 py-4">
          <div className="max-w-2xl mx-auto flex gap-3">
            <button
              onClick={() => router.push('/dashboard')}
              className="flex-1 flex items-center justify-center gap-2 py-3 rounded-xl bg-gradient-to-r from-blue-400 to-blue-600 text-white text-sm font-semibold shadow-md shadow-blue-200 hover:opacity-90 transition-all"
            >
              Dashboard <ChevronRight size={15} />
            </button>
            <button
              onClick={handleReset}
              className="px-5 py-3 rounded-xl border-2 border-slate-200 text-slate-600 text-sm font-semibold hover:border-blue-300 hover:text-blue-500 hover:bg-blue-50 transition-all"
            >
              Proses Lagi
            </button>
          </div>
        </div>
      </div>
    );
  }

  // ────────────────────────────────────────────────────────
  // FORM STATE
  // ────────────────────────────────────────────────────────
  return (
    <div className="min-h-screen bg-slate-50">
      <header className="bg-white border-b border-slate-100 sticky top-0 z-20 shadow-sm">
        <div className="max-w-2xl mx-auto px-5 h-14 flex items-center gap-3">
          <button onClick={() => router.push('/dashboard')} className="p-2 rounded-xl hover:bg-slate-100 text-slate-500 transition-colors">
            <ArrowLeft size={18} />
          </button>
          <h1 className="text-base font-bold text-slate-900">Keluar Parkir</h1>
        </div>
      </header>

      <main className="max-w-2xl mx-auto px-5 py-7 space-y-5 pb-32">

        {/* Info banner */}
        <div className="bg-blue-50 border border-blue-200 rounded-2xl px-5 py-4">
          <div className="flex items-center gap-2 mb-3">
            <Car size={14} className="text-blue-600" />
            <span className="text-sm font-bold text-blue-800">Cara Keluar Parkir</span>
          </div>
          <ol className="space-y-1.5 text-xs text-blue-700">
            {[
              'Masukkan atau scan kode tiket parkir Anda',
              'Sistem menghitung durasi dan biaya parkir',
              'Lakukan pembayaran di loket yang tersedia',
              'Tunjukkan bukti pembayaran kepada petugas',
            ].map((step, i) => (
              <li key={i} className="flex items-start gap-2">
                <span className="w-4 h-4 rounded-full bg-blue-200 text-blue-700 font-bold text-xs flex items-center justify-center shrink-0 mt-0.5">{i + 1}</span>
                {step}
              </li>
            ))}
          </ol>
        </div>

        {/* Mode Toggle */}
        <div className="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
          <div className="flex border-b border-slate-100">
            {([
              { key: 'manual', label: 'Input Manual', icon: <Keyboard size={14} /> },
              { key: 'scan',   label: 'Scan Barcode', icon: <ScanLine size={14} /> },
            ] as { key: ExitMode; label: string; icon: React.ReactNode }[]).map((tab) => (
              <button
                key={tab.key}
                onClick={() => tab.key === 'scan' ? startScan() : setMode('manual')}
                className={`flex-1 flex items-center justify-center gap-2 py-3.5 text-sm font-semibold transition-colors
                  ${mode === tab.key
                    ? 'text-blue-600 border-b-2 border-blue-500 bg-blue-50'
                    : 'text-slate-400 hover:text-slate-600'
                  }`}
              >
                {tab.icon} {tab.label}
              </button>
            ))}
          </div>

          {/* QR Reader area */}
          {mode === 'scan' && (
            <div className="p-5 flex flex-col items-center gap-3">
              <div id="qr-reader" className="w-full max-w-xs rounded-xl overflow-hidden" />
              <p className="text-xs text-slate-400 text-center">Arahkan kamera ke barcode/QR code tiket</p>
              <button onClick={() => setMode('manual')} className="text-xs text-blue-500 underline">
                Gunakan input manual
              </button>
            </div>
          )}

          {/* Manual input */}
          {mode === 'manual' && (
            <div className="p-5">
              <form onSubmit={handleSubmit} className="space-y-4">
                <div>
                  <label htmlFor="ticketCode" className="block text-xs font-semibold text-slate-500 mb-2">
                    Kode Tiket Parkir
                  </label>
                  <input
                    id="ticketCode"
                    type="text"
                    value={ticketCode}
                    onChange={(e) => setTicketCode(e.target.value.toUpperCase())}
                    placeholder="Contoh: PK-ABCD1234"
                    required
                    className="w-full px-4 py-3 border-2 border-slate-100 rounded-xl text-sm font-mono font-bold text-slate-800 tracking-widest bg-slate-50 outline-none focus:border-blue-300 focus:bg-white transition-all placeholder:text-slate-300 placeholder:font-normal placeholder:tracking-normal text-center uppercase"
                  />
                  <p className="text-xs text-slate-400 mt-1.5 text-center">Kode tiket terdapat pada struk masuk parkir</p>
                </div>

                {error && (
                  <div className="flex items-center gap-2 px-4 py-3 bg-red-50 border border-red-200 rounded-xl text-red-600 text-xs">
                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2.5" strokeLinecap="round" strokeLinejoin="round">
                      <circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/>
                    </svg>
                    {error}
                  </div>
                )}

                <button
                  type="submit"
                  disabled={isLoading || !ticketCode.trim()}
                  className="w-full flex items-center justify-center gap-2 py-3 rounded-xl bg-gradient-to-r from-blue-400 to-blue-600 text-white text-sm font-semibold shadow-md shadow-blue-200 hover:opacity-90 transition-all disabled:from-slate-200 disabled:to-slate-200 disabled:text-slate-400 disabled:shadow-none disabled:cursor-not-allowed"
                >
                  {isLoading ? <><Loader2 size={15} className="animate-spin" /> Memproses...</> : 'Proses Keluar'}
                </button>
              </form>
            </div>
          )}
        </div>

        {/* Bantuan */}
        <div className="bg-white rounded-2xl border border-slate-100 shadow-sm px-5 py-4">
          <p className="text-xs font-bold text-slate-700 mb-2.5">Bantuan</p>
          <ul className="space-y-1.5 text-xs text-slate-400">
            {[
              'Jika lupa kode tiket, hubungi petugas parkir',
              'Pastikan kendaraan sudah siap untuk keluar',
              'Biaya parkir: Rp 2.000 per jam (minimum 1 jam)',
              'Simpan struk keluar sebagai bukti pembayaran',
            ].map((h, i) => (
              <li key={i} className="flex items-start gap-1.5">
                <span className="mt-1 w-1 h-1 rounded-full bg-slate-300 shrink-0" />
                {h}
              </li>
            ))}
          </ul>
        </div>

      </main>
    </div>
  );
}
