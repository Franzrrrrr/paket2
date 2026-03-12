'use client';

import { useState, useEffect, Suspense } from 'react';
import { useRouter, useSearchParams } from 'next/navigation';
import { parkingAreasAPI, bookingAPI, ParkingArea, Vehicle } from '@/lib/api';
import { ArrowLeft, Car, MapPin, Clock, CheckCircle2, ChevronRight, Loader2 } from 'lucide-react';

function NewBookingForm() {
  const router       = useRouter();
  const searchParams = useSearchParams();
  const areaId       = searchParams.get('area');

  const [parkingAreas, setParkingAreas]         = useState<ParkingArea[]>([]);
  const [vehicles, setVehicles]                 = useState<Vehicle[]>([]);
  const [selectedArea, setSelectedArea]         = useState<ParkingArea | null>(null);
  const [selectedVehicle, setSelectedVehicle]   = useState<number | null>(null);
  const [estimatedDuration, setEstimatedDuration] = useState('');
  const [isLoading, setIsLoading]               = useState(false);
  const [error, setError]                       = useState('');

  useEffect(() => { fetchData(); }, []);
  useEffect(() => {
    if (areaId && parkingAreas.length > 0) {
      const area = parkingAreas.find(a => a.id === parseInt(areaId));
      if (area) setSelectedArea(area);
    }
  }, [areaId, parkingAreas]);

  const fetchData = async () => {
    try {
      const mockVehicles: Vehicle[] = [
        { id: 1, plat_nomor: 'B 1234 ABC', jenis_kendaraan: 'Mobil', user_id: 1 },
        { id: 2, plat_nomor: 'B 5678 XYZ', jenis_kendaraan: 'Motor', user_id: 1 },
      ];
      const areasData = await parkingAreasAPI.getAll();
      setParkingAreas(Array.isArray(areasData) ? areasData : []);
      setVehicles(mockVehicles);
    } catch (err) {
      console.error('Failed to fetch data:', err);
    }
  };

  const handleSubmit = async (e: React.FormEvent<HTMLFormElement>) => {
    e.preventDefault();
    if (!selectedArea || !selectedVehicle) {
      setError('Silakan pilih area parkir dan kendaraan');
      return;
    }
    setIsLoading(true);
    setError('');
    try {
      const response = await bookingAPI.book({
        vehicle_id: selectedVehicle,
        parking_area_id: selectedArea.id,
        estimated_duration: estimatedDuration ? parseInt(estimatedDuration) : undefined,
      });
      router.push(`/booking/success?ticket=${response.data.ticket_code}`);
    } catch (err: any) {
      setError(err.response?.data?.message || 'Booking gagal. Silakan coba lagi.');
    } finally {
      setIsLoading(false);
    }
  };

  const statusCfg: Record<string, { bg: string; text: string; dot: string; border: string }> = {
    'Tersedia':     { bg: 'bg-green-50',  text: 'text-green-700',  dot: 'bg-green-500',  border: 'border-green-200' },
    'Hampir Penuh': { bg: 'bg-amber-50',  text: 'text-amber-700',  dot: 'bg-amber-400',  border: 'border-amber-200' },
    'Penuh':        { bg: 'bg-red-50',    text: 'text-red-600',    dot: 'bg-red-500',    border: 'border-red-200'   },
  };
  const getStatus = (s: string) => statusCfg[s] ?? { bg: 'bg-slate-50', text: 'text-slate-500', dot: 'bg-slate-300', border: 'border-slate-200' };

  const occBar = (occ: number) => occ >= 100 ? 'bg-red-400' : occ >= 80 ? 'bg-amber-400' : 'bg-green-400';

  const estimatedCost = estimatedDuration
    ? Math.max(1, Math.ceil(parseInt(estimatedDuration) / 60)) * 2000
    : null;

  return (
    <div className="min-h-screen bg-slate-50">

      {/* Header */}
      <header className="bg-white border-b border-slate-100 sticky top-0 z-20 shadow-sm">
        <div className="max-w-3xl mx-auto px-5 h-14 flex items-center gap-3">
          <button
            onClick={() => router.back()}
            className="p-2 rounded-xl hover:bg-slate-100 text-slate-500 transition-colors"
          >
            <ArrowLeft size={18} />
          </button>
          <h1 className="text-base font-bold text-slate-900">Booking Parkir Baru</h1>
        </div>
      </header>

      <main className="max-w-3xl mx-auto px-5 py-6 pb-32">
        <form onSubmit={handleSubmit} className="space-y-5">

          {/* ── Pilih Area ── */}
          <div className="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
            <div className="flex items-center gap-2 px-5 py-4 border-b border-slate-100">
              <MapPin size={15} className="text-blue-500" />
              <span className="text-sm font-bold text-slate-800">Pilih Area Parkir</span>
            </div>
            <div className="p-4 grid grid-cols-1 sm:grid-cols-2 gap-3">
              {parkingAreas.map((area) => {
                const s        = getStatus(area.status);
                const isFull   = area.status === 'Penuh';
                const isActive = selectedArea?.id === area.id;
                return (
                  <div
                    key={area.id}
                    onClick={() => !isFull && setSelectedArea(area)}
                    className={`relative rounded-xl border-2 p-4 transition-all
                      ${isFull ? 'opacity-50 cursor-not-allowed' : 'cursor-pointer'}
                      ${isActive
                        ? 'border-blue-400 bg-blue-50 shadow-sm shadow-blue-100'
                        : 'border-slate-100 hover:border-blue-200 hover:bg-slate-50'
                      }`}
                  >
                    {/* Selected checkmark */}
                    {isActive && (
                      <CheckCircle2 size={16} className="absolute top-3 right-3 text-blue-500" />
                    )}

                    <div className="flex items-start justify-between mb-1 pr-5">
                      <p className="text-sm font-semibold text-slate-800 leading-snug">{area.nama_area}</p>
                    </div>
                    <p className="text-xs text-slate-400 mb-3">{area.alamat}</p>

                    <div className="flex items-center justify-between">
                      <div className="flex items-center gap-3">
                        <span className="flex items-center gap-1 text-xs text-slate-500">
                          <Car size={11} className="text-slate-400" />{area.terisi}/{area.kapasitas}
                        </span>
                        <div className="w-12 h-1.5 rounded-full bg-slate-100 overflow-hidden">
                          <div className={`h-full rounded-full ${occBar(area.occupancy_rate)}`} style={{ width: `${area.occupancy_rate}%` }} />
                        </div>
                      </div>
                      <span className={`inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-semibold ${s.bg} ${s.text}`}>
                        <span className={`w-1.5 h-1.5 rounded-full ${s.dot}`} />
                        {area.status}
                      </span>
                    </div>
                  </div>
                );
              })}
            </div>
          </div>

          {/* ── Pilih Kendaraan ── */}
          <div className="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
            <div className="flex items-center gap-2 px-5 py-4 border-b border-slate-100">
              <Car size={15} className="text-blue-500" />
              <span className="text-sm font-bold text-slate-800">Pilih Kendaraan</span>
            </div>
            <div className="p-4 space-y-2.5">
              {vehicles.map((vehicle) => {
                const isActive = selectedVehicle === vehicle.id;
                return (
                  <label
                    key={vehicle.id}
                    className={`flex items-center justify-between p-3.5 rounded-xl border-2 cursor-pointer transition-all
                      ${isActive
                        ? 'border-blue-400 bg-blue-50'
                        : 'border-slate-100 hover:border-blue-200 hover:bg-slate-50'
                      }`}
                  >
                    <div className="flex items-center gap-3">
                      <div className={`w-9 h-9 rounded-xl flex items-center justify-center shrink-0 transition-colors ${isActive ? 'bg-blue-200' : 'bg-slate-100'}`}>
                        <Car size={15} className={isActive ? 'text-blue-600' : 'text-slate-500'} />
                      </div>
                      <div>
                        <p className="text-sm font-semibold text-slate-800">{vehicle.plat_nomor}</p>
                        <p className="text-xs text-slate-400">{vehicle.jenis_kendaraan}</p>
                      </div>
                    </div>
                    <div className="flex items-center gap-2">
                      {isActive && <CheckCircle2 size={16} className="text-blue-500" />}
                      <input
                        type="radio" name="vehicle" value={vehicle.id}
                        checked={isActive}
                        onChange={() => setSelectedVehicle(vehicle.id)}
                        className="sr-only"
                      />
                    </div>
                  </label>
                );
              })}
            </div>
          </div>

          {/* ── Estimasi Durasi ── */}
          <div className="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
            <div className="flex items-center gap-2 px-5 py-4 border-b border-slate-100">
              <Clock size={15} className="text-blue-500" />
              <span className="text-sm font-bold text-slate-800">Estimasi Durasi</span>
              <span className="text-xs text-slate-400 font-normal">(opsional)</span>
            </div>
            <div className="p-4">
              <div className="relative">
                <input
                  type="number"
                  placeholder="Contoh: 120"
                  value={estimatedDuration}
                  onChange={(e) => setEstimatedDuration(e.target.value)}
                  min="1"
                  className="w-full px-4 py-2.5 pr-16 border-2 border-slate-100 rounded-xl text-sm text-slate-800 bg-slate-50 outline-none focus:border-blue-300 focus:bg-white focus:shadow-sm transition-all placeholder:text-slate-300"
                />
                <span className="absolute right-4 top-1/2 -translate-y-1/2 text-xs text-slate-400 font-medium">menit</span>
              </div>

              <div className="mt-3 flex items-center justify-between">
                <p className="text-xs text-slate-400">Tarif: Rp 2.000 / jam (min. 1 jam)</p>
                {estimatedCost && (
                  <p className="text-xs font-semibold text-blue-600">
                    Est. biaya: Rp {estimatedCost.toLocaleString('id-ID')}
                  </p>
                )}
              </div>
            </div>
          </div>

          {/* Error */}
          {error && (
            <div className="flex items-center gap-2 px-4 py-3 bg-red-50 border border-red-200 rounded-xl text-red-600 text-sm">
              <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2.5" strokeLinecap="round" strokeLinejoin="round">
                <circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/>
              </svg>
              {error}
            </div>
          )}

        </form>
      </main>

      {/* ── CTA sticky bottom ── */}
      <div className="fixed bottom-0 left-0 right-0 bg-white border-t border-slate-100 shadow-lg px-5 py-4">
        <div className="max-w-3xl mx-auto">
          {/* Summary pill */}
          {(selectedArea || selectedVehicle) && (
            <div className="flex items-center gap-3 mb-3 px-4 py-2.5 bg-slate-50 rounded-xl border border-slate-100 text-xs text-slate-500">
              <span className="font-medium text-slate-700 truncate">{selectedArea?.nama_area ?? '—'}</span>
              <span className="text-slate-300">·</span>
              <span className="truncate">{vehicles.find(v => v.id === selectedVehicle)?.plat_nomor ?? '—'}</span>
            </div>
          )}
          <button
            type="submit"
            form=""
            onClick={(e) => {
              e.preventDefault();
              const form = document.querySelector('form');
              form?.requestSubmit();
            }}
            disabled={isLoading || !selectedArea || !selectedVehicle}
            className="w-full flex items-center justify-center gap-2 py-3 rounded-xl bg-gradient-to-r from-blue-400 to-blue-600 text-white text-sm font-semibold shadow-md shadow-blue-200 hover:opacity-90 active:scale-[0.98] transition-all disabled:from-slate-200 disabled:to-slate-200 disabled:text-slate-400 disabled:shadow-none disabled:cursor-not-allowed"
          >
            {isLoading
              ? <><Loader2 size={15} className="animate-spin" /> Memproses...</>
              : <>Konfirmasi Booking <ChevronRight size={15} /></>
            }
          </button>
        </div>
      </div>

    </div>
  );
}

export default function NewBookingPage() {
  return (
    <Suspense fallback={
      <div className="min-h-screen flex items-center justify-center bg-slate-50">
        <div className="w-10 h-10 rounded-full border-4 border-blue-200 border-t-blue-500 animate-spin" />
      </div>
    }>
      <NewBookingForm />
    </Suspense>
  );
}
