'use client';

import { useEffect, useState } from 'react';
import { useRouter, useSearchParams } from 'next/navigation';
import { bookingAPI, ParkingSession } from '@/lib/api';
import { formatDateTime } from '@/lib/utils';
import { CheckCircle2, Clock, MapPin, Car, ArrowLeft, ChevronRight, TicketCheck } from 'lucide-react';

export default function BookingSuccessPage() {
  const router       = useRouter();
  const searchParams = useSearchParams();
  const ticketCode   = searchParams.get('ticket');
  const [session, setSession]     = useState<ParkingSession | null>(null);
  const [isLoading, setIsLoading] = useState(true);

  useEffect(() => {
    if (!ticketCode) { router.push('/dashboard'); return; }
    fetchSession();
  }, [ticketCode]);

  const fetchSession = async () => {
    try {
      const data = await bookingAPI.getActiveSession();
      // handle array atau single
      setSession(Array.isArray(data) ? data[0] : data);
    } catch (err) {
      console.error('Failed to fetch session:', err);
    } finally {
      setIsLoading(false);
    }
  };

  // ── Loading ──
  if (isLoading) {
    return (
      <div className="min-h-screen flex items-center justify-center bg-slate-50">
        <div className="w-10 h-10 rounded-full border-4 border-blue-200 border-t-blue-500 animate-spin" />
      </div>
    );
  }

  // ── Not found ──
  if (!session) {
    return (
      <div className="min-h-screen flex flex-col items-center justify-center bg-slate-50 gap-4 p-6">
        <p className="text-slate-600 font-medium">Data sesi tidak ditemukan</p>
        <button
          onClick={() => router.push('/dashboard')}
          className="px-5 py-2.5 rounded-xl bg-blue-500 text-white text-sm font-semibold hover:bg-blue-600 transition-colors"
        >
          Kembali ke Dashboard
        </button>
      </div>
    );
  }

  return (
    <div className="min-h-screen bg-slate-50">

      {/* Header */}
      <header className="bg-white border-b border-slate-100 sticky top-0 z-20 shadow-sm">
        <div className="max-w-2xl mx-auto px-5 h-14 flex items-center gap-3">
          <button
            onClick={() => router.push('/dashboard')}
            className="p-2 rounded-xl hover:bg-slate-100 text-slate-500 transition-colors"
          >
            <ArrowLeft size={18} />
          </button>
          <h1 className="text-base font-bold text-slate-900">Booking Berhasil</h1>
        </div>
      </header>

      <main className="max-w-2xl mx-auto px-5 py-7 space-y-5 pb-32">

        {/* ── Success Hero ── */}
        <div className="bg-white rounded-2xl border border-slate-100 shadow-sm px-6 py-8 flex flex-col items-center text-center">
          <div className="w-16 h-16 rounded-2xl bg-green-100 flex items-center justify-center mb-4">
            <CheckCircle2 size={34} className="text-green-500" />
          </div>
          <h2 className="text-lg font-extrabold text-slate-900 tracking-tight mb-1">
            Booking Parkir Berhasil!
          </h2>
          <p className="text-sm text-slate-400">
            Kendaraan Anda telah terdaftar di area parkir yang dipilih
          </p>

          {/* Ticket code pill */}
          <div className="mt-5 flex items-center gap-2.5 px-5 py-3 bg-blue-50 border border-blue-200 rounded-xl">
            <TicketCheck size={18} className="text-blue-500 shrink-0" />
            <div className="text-left">
              <p className="text-xs text-blue-400 font-medium">Kode Tiket</p>
              <p className="text-base font-extrabold text-blue-700 tracking-widest font-mono">
                {session.ticket_code}
              </p>
            </div>
          </div>
        </div>

        {/* ── Detail Tiket ── */}
        <div className="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
          <div className="flex items-center gap-2 px-5 py-4 border-b border-slate-100">
            <Car size={15} className="text-blue-500" />
            <span className="text-sm font-bold text-slate-800">Detail Tiket Parkir</span>
          </div>
          <div className="divide-y divide-slate-50">
            {[
              { label: 'Area Parkir',  value: session.parking_area.nama_area },
              { label: 'Kendaraan',    value: `${session.vehicle.plat_nomor} · ${session.vehicle.jenis_kendaraan}` },
              { label: 'Waktu Masuk', value: formatDateTime(session.entry_time) },
              { label: 'Status',       value: null },
            ].map((row, i) => (
              <div key={i} className="flex items-center justify-between px-5 py-3.5">
                <span className="text-sm text-slate-400 font-medium">{row.label}</span>
                {row.value !== null
                  ? <span className="text-sm font-semibold text-slate-700">{row.value}</span>
                  : (
                    <span className="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full bg-green-50 text-green-700 text-xs font-semibold border border-green-200">
                      <span className="w-1.5 h-1.5 rounded-full bg-green-500" />
                      Aktif
                    </span>
                  )
                }
              </div>
            ))}
          </div>
        </div>

        {/* ── Lokasi ── */}
        <div className="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
          <div className="flex items-center gap-2 px-5 py-4 border-b border-slate-100">
            <MapPin size={15} className="text-blue-500" />
            <span className="text-sm font-bold text-slate-800">Lokasi Parkir</span>
          </div>
          <div className="px-5 py-4 space-y-1.5">
            <p className="text-sm font-semibold text-slate-800">{session.parking_area.nama_area}</p>
            <p className="text-xs text-slate-400">{session.parking_area.alamat}</p>
            <div className="flex items-center gap-4 pt-1">
              <span className="flex items-center gap-1 text-xs text-slate-500">
                <Car size={11} className="text-slate-400" />
                {session.parking_area.terisi}/{session.parking_area.kapasitas} terisi
              </span>
              <span className="flex items-center gap-1 text-xs text-green-600 font-medium">
                <span className="w-1.5 h-1.5 rounded-full bg-green-500" />
                {session.parking_area.sisa} tersedia
              </span>
            </div>
          </div>
        </div>

        {/* ── Catatan penting ── */}
        <div className="bg-amber-50 border border-amber-200 rounded-2xl px-5 py-4">
          <div className="flex items-center gap-2 mb-3">
            <Clock size={14} className="text-amber-600" />
            <span className="text-sm font-bold text-amber-800">Informasi Penting</span>
          </div>
          <ul className="space-y-1.5 text-xs text-amber-700">
            {[
              'Simpan kode tiket untuk proses keluar parkir',
              'Biaya parkir: Rp 2.000 per jam (minimum 1 jam)',
              'Pastikan kendaraan diparkir di lokasi yang benar',
              'Hubungi petugas jika ada kendala',
            ].map((note, i) => (
              <li key={i} className="flex items-start gap-2">
                <span className="mt-0.5 w-1.5 h-1.5 rounded-full bg-amber-400 shrink-0" />
                {note}
              </li>
            ))}
          </ul>
        </div>

      </main>

      {/* ── CTA sticky bottom ── */}
      <div className="fixed bottom-0 left-0 right-0 bg-white border-t border-slate-100 shadow-lg px-5 py-4">
        <div className="max-w-2xl mx-auto flex gap-3">
          <button
            onClick={() => router.push('/dashboard')}
            className="flex-1 flex items-center justify-center gap-2 py-3 rounded-xl bg-gradient-to-r from-blue-400 to-blue-600 text-white text-sm font-semibold shadow-md shadow-blue-200 hover:opacity-90 transition-all"
          >
            Dashboard <ChevronRight size={15} />
          </button>
          <button
            onClick={() => router.push('/booking/exit')}
            className="flex items-center justify-center gap-1.5 px-5 py-3 rounded-xl border-2 border-slate-200 text-slate-600 text-sm font-semibold hover:border-red-300 hover:text-red-500 hover:bg-red-50 transition-all"
          >
            Keluar Parkir
          </button>
        </div>
      </div>

    </div>
  );
}
