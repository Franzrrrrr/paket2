'use client';

import { useState, useEffect } from 'react';
import { useRouter } from 'next/navigation';
import { useAuth } from '@/contexts/AuthContext';
import { parkingAreasAPI, bookingAPI, ParkingArea, ParkingSession } from '@/lib/api';
import { MapPin, Car, Clock, Layers, LogOut, ChevronRight, TicketCheck } from 'lucide-react';
import dynamic from 'next/dynamic';
import LogoutModal from '@/components/LogoutModal';

const MapComponent = dynamic(() => import('@/components/MapComponent'), {
  ssr: false,
  loading: () => <div className="h-80 bg-blue-50 animate-pulse rounded-2xl" />,
});

export default function DashboardPage() {
  const { user, logout } = useAuth();
  const router = useRouter();
  const [parkingAreas, setParkingAreas]   = useState<ParkingArea[]>([]);
  const [activeSessions, setActiveSessions] = useState<ParkingSession[]>([]);
  const [isLoading, setIsLoading]         = useState(true);
  const [showLogoutModal, setShowLogoutModal] = useState(false);

  useEffect(() => { fetchData(); }, []);

  const fetchData = async () => {
    try {
      const [areasData, sessionData] = await Promise.all([
        parkingAreasAPI.getAll(),
        bookingAPI.getActiveSession(),
      ]);
      setParkingAreas(Array.isArray(areasData) ? areasData : []);
      setActiveSessions(Array.isArray(sessionData) ? sessionData : sessionData ? [sessionData] : []);
    } catch (err) {
      console.error('Failed to fetch data:', err);
    } finally {
      setIsLoading(false);
    }
  };

  const handleLogout = () => {
    setShowLogoutModal(true);
  };

  const handleConfirmLogout = () => {
    logout();
    router.push('/login');
  };

  const statusCfg: Record<string, { bg: string; text: string; dot: string }> = {
    'Tersedia':     { bg: 'bg-green-50',  text: 'text-green-700',  dot: 'bg-green-500'  },
    'Hampir Penuh': { bg: 'bg-amber-50',  text: 'text-amber-700',  dot: 'bg-amber-400'  },
    'Penuh':        { bg: 'bg-red-50',    text: 'text-red-600',    dot: 'bg-red-500'    },
  };
  const getStatus = (s: string) => statusCfg[s] ?? { bg: 'bg-slate-50', text: 'text-slate-500', dot: 'bg-slate-400' };

  const occBarColor = (occ: number) =>
    occ >= 100 ? 'bg-red-400' : occ >= 80 ? 'bg-amber-400' : 'bg-green-400';

  const totalKapasitas = parkingAreas.reduce((s, a) => s + a.kapasitas, 0);
  const totalTersedia  = parkingAreas.reduce((s, a) => s + a.sisa, 0);

  if (isLoading) {
    return (
      <div className="min-h-screen flex items-center justify-center bg-slate-50">
        <div className="w-10 h-10 rounded-full border-4 border-blue-200 border-t-blue-500 animate-spin" />
      </div>
    );
  }

  return (
    <div className="min-h-screen bg-slate-50 font-sans">

      {/* ── Header ── */}
      <header className="bg-white border-b border-slate-100 sticky top-0 z-20 shadow-sm">
        <div className="max-w-6xl mx-auto px-6 h-14 flex items-center justify-between">

          <div className="flex items-center gap-2.5">
            <div className="w-8 h-8 rounded-xl bg-gradient-to-br from-blue-200 to-blue-400 flex items-center justify-center">
              <Car size={16} className="text-white" />
            </div>
            <span className="text-base font-bold text-slate-900 tracking-tight">ParkirKu</span>
          </div>

          <div className="flex items-center gap-4">
            <span className="text-sm text-slate-500 hidden sm:block">
              Halo, <span className="font-semibold text-slate-700">{user?.name}</span>
            </span>
            <button
              onClick={() => router.push('/booking/my-bookings')}
              className="flex items-center gap-1.5 px-3 py-1.5 rounded-lg border border-slate-200 text-slate-500 text-xs font-medium hover:border-blue-300 hover:text-blue-500 hover:bg-blue-50 transition-all"
            >
              <Car size={13} /> Booking Saya
            </button>
            <button
              onClick={handleLogout}
              className="flex items-center gap-1.5 px-3 py-1.5 rounded-lg border border-slate-200 text-slate-500 text-xs font-medium hover:border-blue-300 hover:text-blue-500 hover:bg-blue-50 transition-all"
            >
              <LogOut size={13} /> Keluar
            </button>
          </div>

        </div>
      </header>

      <main className="max-w-6xl mx-auto px-6 py-7 space-y-6">

        {/* ── Active Sessions Banner ── */}
        {activeSessions.length > 0 && (
          <div className="bg-blue-50 border border-blue-200 rounded-2xl px-5 py-4 space-y-3">
            <div className="flex items-center justify-between">
              <div className="flex items-center gap-2">
                <TicketCheck size={16} className="text-blue-600" />
                <span className="text-sm font-bold text-blue-900">Sesi Parkir Aktif</span>
                <span className="px-2 py-0.5 rounded-full bg-blue-500 text-white text-xs font-bold">
                  {activeSessions.length}
                </span>
              </div>
              <button
                onClick={() => router.push('/booking/exit')}
                className="flex items-center gap-1.5 px-4 py-1.5 rounded-xl bg-blue-500 hover:bg-blue-600 text-white text-xs font-semibold shadow-md shadow-blue-200 transition-all"
              >
                Keluar Parkir <ChevronRight size={13} />
              </button>
            </div>
            <div className="space-y-2">
              {activeSessions.map((session) => (
                <div key={session.ticket_code} className="flex items-center gap-3 bg-white rounded-xl px-4 py-2.5 border border-blue-100">
                  <div className="w-7 h-7 rounded-lg bg-blue-100 flex items-center justify-center shrink-0">
                    <Car size={13} className="text-blue-600" />
                  </div>
                  <div className="flex-1 min-w-0">
                    <p className="text-xs font-semibold text-slate-800 truncate">{session.parking_area.nama_area}</p>
                    <p className="text-xs text-slate-400">{session.ticket_code} · Masuk {new Date(session.entry_time).toLocaleTimeString('id-ID')}</p>
                  </div>
                </div>
              ))}
            </div>
          </div>
        )}

        {/* ── Stats ── */}
        <div className="grid grid-cols-1 sm:grid-cols-3 gap-4">
          {[
            { label: 'Total Area',      value: parkingAreas.length, icon: <MapPin size={20} className="text-blue-500" />,  iconBg: 'bg-blue-50'  },
            { label: 'Total Kapasitas', value: totalKapasitas,      icon: <Layers size={20} className="text-green-500" />, iconBg: 'bg-green-50' },
            { label: 'Slot Tersedia',   value: totalTersedia,       icon: <Car size={20} className="text-green-600" />,    iconBg: 'bg-green-50', valueClass: 'text-green-600' },
          ].map((stat, i) => (
            <div key={i} className="bg-white rounded-2xl border border-slate-100 shadow-sm px-5 py-4 flex items-center gap-4">
              <div className={`w-11 h-11 rounded-xl ${stat.iconBg} flex items-center justify-center shrink-0`}>
                {stat.icon}
              </div>
              <div>
                <p className="text-xs text-slate-400 font-medium mb-1">{stat.label}</p>
                <p className={`text-2xl font-extrabold tracking-tight ${stat.valueClass ?? 'text-slate-900'}`}>{stat.value}</p>
              </div>
            </div>
          ))}
        </div>

        {/* ── Map + List ── */}
        <div className="grid grid-cols-1 lg:grid-cols-2 gap-5">

          {/* Map */}
          <div className="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
            <div className="flex items-center gap-2 px-5 py-4 border-b border-slate-100">
              <MapPin size={15} className="text-blue-500" />
              <span className="text-sm font-bold text-slate-800">Peta Area Parkir</span>
            </div>
            <div className="p-4">
              <MapComponent areas={parkingAreas} />
            </div>
          </div>

          {/* List */}
          <div className="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
            <div className="flex items-center gap-2 px-5 py-4 border-b border-slate-100">
              <Car size={15} className="text-blue-500" />
              <span className="text-sm font-bold text-slate-800">Daftar Area Parkir</span>
            </div>
            <div className="p-3 max-h-[420px] overflow-y-auto space-y-2 scrollbar-thin scrollbar-thumb-slate-200">
              {parkingAreas.map((area) => {
                const s   = getStatus(area.status);
                const occ = area.occupancy_rate;
                return (
                  <div
                    key={area.id}
                    className="border border-slate-100 rounded-xl p-3.5 hover:border-blue-200 hover:shadow-sm transition-all bg-white"
                  >
                    {/* Top row */}
                    <div className="flex items-start justify-between mb-1.5">
                      <span className="text-sm font-semibold text-slate-800 leading-snug">{area.nama_area}</span>
                      <span className={`inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-semibold shrink-0 ml-2 ${s.bg} ${s.text}`}>
                        <span className={`w-1.5 h-1.5 rounded-full ${s.dot}`} />
                        {area.status}
                      </span>
                    </div>

                    {/* Address */}
                    <p className="text-xs text-slate-400 mb-3">{area.alamat}</p>

                    {/* Bottom row */}
                    <div className="flex items-center justify-between">
                      <div className="flex items-center gap-3">
                        <span className="flex items-center gap-1 text-xs text-slate-500">
                          <Car size={11} className="text-slate-400" />
                          {area.terisi}/{area.kapasitas}
                        </span>
                        <span className="flex items-center gap-1 text-xs text-slate-500">
                          <Clock size={11} className="text-slate-400" />
                          {occ}%
                        </span>
                        {/* Occ bar */}
                        <div className="w-14 h-1.5 rounded-full bg-slate-100 overflow-hidden">
                          <div
                            className={`h-full rounded-full transition-all ${occBarColor(occ)}`}
                            style={{ width: `${occ}%` }}
                          />
                        </div>
                      </div>

                      <button
                        onClick={() => router.push(`/booking/new?area=${area.id}`)}
                        disabled={area.status === 'Penuh' || activeSessions.length > 0}
                        className="px-3 py-1.5 rounded-lg bg-gradient-to-r from-blue-400 to-blue-500 text-white text-xs font-semibold shadow-sm shadow-blue-200 hover:-translate-y-0.5 hover:shadow-md transition-all disabled:from-slate-200 disabled:to-slate-200 disabled:text-slate-400 disabled:shadow-none disabled:cursor-not-allowed disabled:translate-y-0"
                      >
                        {activeSessions.length > 0 ? 'Parkir' : 'Pesan'}
                      </button>
                    </div>
                  </div>
                );
              })}
            </div>
          </div>

        </div>
      </main>

      {/* Logout Modal */}
      <LogoutModal
        isOpen={showLogoutModal}
        onClose={() => setShowLogoutModal(false)}
        onConfirm={handleConfirmLogout}
      />
    </div>
  );
}
