'use client';

import { useState, useEffect } from 'react';
import { useRouter, useSearchParams } from 'next/navigation';
import { parkingAreasAPI, bookingReservationAPI, BookingReservationRequest, BookingResponse, ParkingArea, RatesResponse } from '@/lib/api';
import { ArrowLeft, Car, MapPin, Clock, CheckCircle2, ChevronRight, Loader2, QrCode } from 'lucide-react';

export default function NewBookingPage() {
  const router       = useRouter();
  const searchParams = useSearchParams();
  const areaId       = searchParams.get('area');

  const [parkingAreas, setParkingAreas]         = useState<ParkingArea[]>([]);
  const [rates, setRates]                       = useState<RatesResponse | null>(null);
  const [selectedArea, setSelectedArea]         = useState<ParkingArea | null>(null);
  const [selectedVehicleType, setSelectedVehicleType] = useState<'Mobil' | 'Motor' | null>(null);
  const [vehiclePlate, setVehiclePlate]         = useState('');
  const [estimatedDuration, setEstimatedDuration] = useState('');
  const [isLoading, setIsLoading]               = useState(false);
  const [error, setError]                       = useState('');
  const [bookingSuccess, setBookingSuccess]       = useState<BookingResponse | null>(null);

  useEffect(() => {
    fetchData();
  }, []);

  useEffect(() => {
    if (areaId && parkingAreas.length > 0) {
      const area = parkingAreas.find(a => a.id === parseInt(areaId));
      if (area) setSelectedArea(area);
    }
  }, [areaId, parkingAreas]);

  const fetchData = async () => {
    try {
      const areasData = await parkingAreasAPI.getAll();
      setParkingAreas(Array.isArray(areasData) ? areasData : []);
    } catch (err) {
      console.error('Failed to fetch data:', err);
    }
  };

  const handleSubmit = async (e: React.FormEvent<HTMLFormElement>) => {
    e.preventDefault();
    if (!selectedArea || !selectedVehicleType || !vehiclePlate) {
      setError('Silakan lengkapi semua field yang diperlukan');
      return;
    }
    setIsLoading(true);
    setError('');

    try {
      const bookingData: BookingReservationRequest = {
        vehicle_type: selectedVehicleType,
        vehicle_plate: vehiclePlate.toUpperCase().replace(/\s/g, ''),
        parking_area_id: selectedArea.id,
        estimated_duration: estimatedDuration ? parseInt(estimatedDuration) : undefined,
      };

      const response = await bookingReservationAPI.book(bookingData);
      setBookingSuccess(response);
    } catch (err: any) {
      setError(err.response?.data?.message || err.message || 'Booking gagal. Silakan coba lagi.');
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

  const estimatedCost = estimatedDuration && selectedVehicleType && selectedArea
    ? Math.max(1, Math.ceil(parseInt(estimatedDuration) / 60)) * (
        selectedVehicleType === 'Mobil'
            ? (selectedArea.tarifs?.mobil?.tarif_per_jam || 3000)
            : (selectedArea.tarifs?.motor?.tarif_per_jam || 2000)
      )
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

          {/* ── Pilih Tipe Kendaraan ── */}
          <div className="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
            <div className="flex items-center gap-2 px-5 py-4 border-b border-slate-100">
              <Car size={15} className="text-blue-500" />
              <span className="text-sm font-bold text-slate-800">Pilih Tipe Kendaraan</span>
            </div>
            <div className="p-4 space-y-2.5">
              {selectedArea?.tarifs && (
                <>
                  <label
                    className={`flex items-center justify-between p-3.5 rounded-xl border-2 cursor-pointer transition-all ${
                      selectedVehicleType === 'Mobil'
                        ? 'border-blue-400 bg-blue-50'
                        : 'border-slate-100 hover:border-blue-200 hover:bg-slate-50'
                    }`}
                  >
                    <div className="flex items-center gap-3">
                      <div className={`w-9 h-9 rounded-xl flex items-center justify-center shrink-0 transition-colors ${
                        selectedVehicleType === 'Mobil' ? 'bg-blue-200' : 'bg-slate-100'
                      }`}>
                        <Car size={15} className={selectedVehicleType === 'Mobil' ? 'text-blue-600' : 'text-slate-500'} />
                      </div>
                      <div>
                        <p className="text-sm font-semibold text-slate-800">Mobil</p>
                        <p className="text-xs text-slate-400">Rp {selectedArea.tarifs.mobil?.tarif_per_jam}/jam</p>
                      </div>
                    </div>
                    <div className="flex items-center gap-2">
                      {selectedVehicleType === 'Mobil' && <CheckCircle2 size={16} className="text-blue-500" />}
                      <input
                        type="radio" name="vehicleType" value="Mobil"
                        checked={selectedVehicleType === 'Mobil'}
                        onChange={() => setSelectedVehicleType('Mobil')}
                        className="sr-only"
                      />
                    </div>
                  </label>

                  <label
                    className={`flex items-center justify-between p-3.5 rounded-xl border-2 cursor-pointer transition-all ${
                      selectedVehicleType === 'Motor'
                        ? 'border-blue-400 bg-blue-50'
                        : 'border-slate-100 hover:border-blue-200 hover:bg-slate-50'
                    }`}
                  >
                    <div className="flex items-center gap-3">
                      <div className={`w-9 h-9 rounded-xl flex items-center justify-center shrink-0 transition-colors ${
                        selectedVehicleType === 'Motor' ? 'bg-blue-200' : 'bg-slate-100'
                      }`}>
                        <Car size={15} className={selectedVehicleType === 'Motor' ? 'text-blue-600' : 'text-slate-500'} />
                      </div>
                      <div>
                        <p className="text-sm font-semibold text-slate-800">Motor</p>
                        <p className="text-xs text-slate-400">Rp {selectedArea.tarifs.motor?.tarif_per_jam}/jam</p>
                      </div>
                    </div>
                    <div className="flex items-center gap-2">
                      {selectedVehicleType === 'Motor' && <CheckCircle2 size={16} className="text-blue-500" />}
                      <input
                        type="radio" name="vehicleType" value="Motor"
                        checked={selectedVehicleType === 'Motor'}
                        onChange={() => setSelectedVehicleType('Motor')}
                        className="sr-only"
                      />
                    </div>
                  </label>
                </>
              )}
              {!selectedArea && (
                <div className="text-center py-8 text-slate-400 text-sm">
                  Silakan pilih area parkir terlebih dahulu
                </div>
              )}
            </div>
          </div>

          {/* ── Nomor Plat Kendaraan ── */}
          <div className="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
            <div className="flex items-center gap-2 px-5 py-4 border-b border-slate-100">
              <Car size={15} className="text-blue-500" />
              <span className="text-sm font-bold text-slate-800">Nomor Plat Kendaraan</span>
            </div>
            <div className="p-4">
              <input
                type="text"
                placeholder="Contoh: B 1234 ABC"
                value={vehiclePlate}
                onChange={(e) => setVehiclePlate(e.target.value.toUpperCase())}
                className="w-full px-4 py-2.5 border-2 border-slate-100 rounded-xl text-sm text-slate-800 bg-slate-50 outline-none focus:border-blue-300 focus:bg-white focus:shadow-sm transition-all placeholder:text-slate-300"
                style={{ textTransform: 'uppercase' }}
              />
              <p className="mt-2 text-xs text-slate-400">Format: B 1234 ABC (tanpa spasi di tengah)</p>
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
          {(selectedArea || selectedVehicleType) && (
            <div className="flex items-center gap-3 mb-3 px-4 py-2.5 bg-slate-50 rounded-xl border border-slate-100 text-xs text-slate-500">
              <span className="font-medium text-slate-700 truncate">{selectedArea?.nama_area ?? '—'}</span>
              <span className="text-slate-300">·</span>
              <span className="truncate">{selectedVehicleType ?? '—'}</span>
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
            disabled={isLoading || !selectedArea || !selectedVehicleType || !vehiclePlate}
            className="w-full flex items-center justify-center gap-2 py-3 rounded-xl bg-gradient-to-r from-blue-400 to-blue-600 text-white text-sm font-semibold shadow-md shadow-blue-200 hover:opacity-90 active:scale-[0.98] transition-all disabled:from-slate-200 disabled:to-slate-200 disabled:text-slate-400 disabled:shadow-none disabled:cursor-not-allowed"
          >
            {isLoading
              ? <><Loader2 size={15} className="animate-spin" /> Memproses...</>
              : <>Konfirmasi Booking <ChevronRight size={15} /></>
            }
          </button>
        </div>
      </div>

      {/* Booking Success Modal */}
      {bookingSuccess && (
        <div className="fixed inset-0 bg-black/50 flex items-center justify-center p-4 z-50">
          <div className="bg-white rounded-2xl p-6 max-w-sm w-full">
            <div className="text-center">
              <div className="w-16 h-16 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-4">
                <CheckCircle2 size={32} className="text-green-600" />
              </div>

              <h2 className="text-lg font-bold text-slate-800 mb-2">Booking Berhasil!</h2>
              <p className="text-sm text-slate-600 mb-4">
                Booking Anda telah dibuat. Silakan check-in saat tiba di lokasi.
              </p>

              <div className="bg-slate-50 rounded-xl p-4 mb-4">
                <div className="text-center mb-3">
                  <QrCode size={48} className="text-blue-600 mx-auto" />
                </div>
                <p className="text-xs font-mono text-slate-700 mb-1">Kode Booking:</p>
                <p className="text-lg font-bold text-blue-600">{bookingSuccess.ticket_code}</p>
              </div>

              <div className="space-y-2 text-left text-sm text-slate-600 mb-4">
                <div className="flex justify-between">
                  <span>Area:</span>
                  <span className="font-medium">{bookingSuccess.parking_area.nama_area}</span>
                </div>
                <div className="flex justify-between">
                  <span>Kendaraan:</span>
                  <span className="font-medium">{bookingSuccess.vehicle_type} - {bookingSuccess.vehicle_plate}</span>
                </div>
                <div className="flex justify-between">
                  <span>Waktu:</span>
                  <span className="font-medium">{new Date(bookingSuccess.booking_time).toLocaleString('id-ID')}</span>
                </div>
                <div className="flex justify-between">
                  <span>Kadaluarsa:</span>
                  <span className="font-medium text-amber-600">{new Date(bookingSuccess.expires_at).toLocaleString('id-ID')}</span>
                </div>
              </div>

              <div className="flex gap-2">
                <button
                  onClick={() => setBookingSuccess(null)}
                  className="flex-1 px-4 py-2 border border-slate-200 text-slate-700 rounded-xl hover:bg-slate-50 transition-colors text-sm font-medium"
                >
                  Tutup
                </button>
                <button
                  onClick={() => router.push('/booking/my-bookings')}
                  className="flex-1 px-4 py-2 bg-blue-600 text-white rounded-xl hover:bg-blue-700 transition-colors text-sm font-medium"
                >
                  Lihat Booking
                </button>
              </div>
            </div>
          </div>
        </div>
      )}
    </div>
  );
}
