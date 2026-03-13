'use client';

import { useState, useEffect } from 'react';
import { useRouter } from 'next/navigation';
import { bookingReservationAPI, BookingResponse } from '@/lib/api';
import { ArrowLeft, Car, Clock, CheckCircle2, QrCode, AlertCircle, X } from 'lucide-react';

export default function MyBookingsPage() {
  const router = useRouter();
  const [bookings, setBookings] = useState<BookingResponse[]>([]);
  const [isLoading, setIsLoading] = useState(true);
  const [error, setError] = useState('');
  const [selectedBooking, setSelectedBooking] = useState<BookingResponse | null>(null);

  useEffect(() => {
    fetchBookings();
  }, []);

  const fetchBookings = async () => {
    try {
      const response = await bookingReservationAPI.myBookings();
      setBookings(response.bookings);
    } catch (err: any) {
      setError(err.response?.data?.message || 'Gagal memuat booking');
    } finally {
      setIsLoading(false);
    }
  };

  const handleCheckIn = async (ticketCode: string) => {
    try {
      await bookingReservationAPI.checkIn({ ticket_code: ticketCode });
      await fetchBookings(); // Refresh bookings
      setSelectedBooking(null);
    } catch (err: any) {
      setError(err.response?.data?.message || 'Check-in gagal');
      setTimeout(() => setError(''), 3000);
    }
  };

  const handleCancel = async (bookingId: number) => {
    if (!confirm('Apakah Anda yakin ingin membatalkan booking ini?')) return;
    
    try {
      await bookingReservationAPI.cancel(bookingId);
      await fetchBookings(); // Refresh bookings
    } catch (err: any) {
      setError(err.response?.data?.message || 'Gagal membatalkan booking');
      setTimeout(() => setError(''), 3000);
    }
  };

  const getStatusColor = (status: string) => {
    switch (status) {
      case 'BOOKED':
        return { bg: 'bg-blue-50', text: 'text-blue-700', border: 'border-blue-200' };
      case 'CHECKED_IN':
        return { bg: 'bg-green-50', text: 'text-green-700', border: 'border-green-200' };
      case 'CANCELLED':
        return { bg: 'bg-red-50', text: 'text-red-700', border: 'border-red-200' };
      case 'EXPIRED':
        return { bg: 'bg-amber-50', text: 'text-amber-700', border: 'border-amber-200' };
      default:
        return { bg: 'bg-slate-50', text: 'text-slate-700', border: 'border-slate-200' };
    }
  };

  const getStatusText = (status: string) => {
    switch (status) {
      case 'BOOKED':
        return 'Dipesan';
      case 'CHECKED_IN':
        return 'Check-in';
      case 'CANCELLED':
        return 'Dibatalkan';
      case 'EXPIRED':
        return 'Kadaluarsa';
      default:
        return status;
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
    <>
      <style>{`
        .bookings-root {
          min-height: 100vh;
          background: #f5f7fa;
          font-family: 'Plus Jakarta Sans', sans-serif;
        }
      `}</style>

      <div className="bookings-root">
        {/* Header */}
        <div className="bg-white border-b border-slate-100 px-5 py-4">
          <div className="max-w-4xl mx-auto flex items-center justify-between">
            <button
              onClick={() => router.back()}
              className="flex items-center gap-2 text-slate-600 hover:text-slate-800 transition-colors"
            >
              <ArrowLeft size={18} />
              <span className="font-medium">Kembali</span>
            </button>
            <h1 className="text-lg font-bold text-slate-800">Booking Saya</h1>
            <div className="w-16" />
          </div>
        </div>

        {/* Content */}
        <div className="max-w-4xl mx-auto p-5">
          {/* Error Alert */}
          {error && (
            <div className="mb-4 flex items-center gap-2 px-4 py-3 bg-red-50 border border-red-200 rounded-xl text-red-600 text-sm">
              <AlertCircle size={16} />
              {error}
            </div>
          )}

          {/* Bookings List */}
          {bookings.length === 0 ? (
            <div className="text-center py-12">
              <Car size={48} className="text-slate-300 mx-auto mb-4" />
              <h3 className="text-lg font-semibold text-slate-700 mb-2">Belum Ada Booking</h3>
              <p className="text-slate-500 mb-6">Anda belum memiliki booking. Buat booking sekarang!</p>
              <button
                onClick={() => router.push('/booking/new')}
                className="px-6 py-2 bg-blue-600 text-white rounded-xl hover:bg-blue-700 transition-colors font-medium"
              >
                Buat Booking
              </button>
            </div>
          ) : (
            <div className="space-y-4">
              {bookings.map((booking) => {
                const statusColor = getStatusColor(booking.status);
                const isExpired = new Date(booking.expires_at) < new Date();
                
                return (
                  <div key={booking.id} className="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
                    <div className="p-5">
                      {/* Header */}
                      <div className="flex items-start justify-between mb-4">
                        <div className="flex-1">
                          <div className="flex items-center gap-2 mb-2">
                            <QrCode size={16} className="text-blue-600" />
                            <span className="font-mono text-sm font-bold text-blue-600">{booking.ticket_code}</span>
                          </div>
                          <h3 className="font-semibold text-slate-800">{booking.parking_area.nama_area}</h3>
                          <p className="text-sm text-slate-500">{booking.parking_area.alamat}</p>
                        </div>
                        <div className={`px-3 py-1 rounded-full text-xs font-medium ${statusColor.bg} ${statusColor.text} ${statusColor.border} border`}>
                          {getStatusText(booking.status)}
                        </div>
                      </div>

                      {/* Details */}
                      <div className="grid grid-cols-2 gap-4 mb-4 text-sm">
                        <div>
                          <span className="text-slate-500">Kendaraan:</span>
                          <p className="font-medium text-slate-800">{booking.vehicle_type} - {booking.vehicle_plate}</p>
                        </div>
                        <div>
                          <span className="text-slate-500">Waktu Booking:</span>
                          <p className="font-medium text-slate-800">
                            {new Date(booking.booking_time).toLocaleString('id-ID')}
                          </p>
                        </div>
                        {booking.check_in_time && (
                          <div>
                            <span className="text-slate-500">Check-in:</span>
                            <p className="font-medium text-slate-800">
                              {new Date(booking.check_in_time).toLocaleString('id-ID')}
                            </p>
                          </div>
                        )}
                        <div>
                          <span className="text-slate-500">Kadaluarsa:</span>
                          <p className={`font-medium ${isExpired ? 'text-red-600' : 'text-slate-800'}`}>
                            {new Date(booking.expires_at).toLocaleString('id-ID')}
                          </p>
                        </div>
                      </div>

                      {/* Actions */}
                      <div className="flex gap-2">
                        {booking.status === 'BOOKED' && booking.can_check_in && (
                          <button
                            onClick={() => handleCheckIn(booking.ticket_code)}
                            className="flex-1 px-4 py-2 bg-green-600 text-white rounded-xl hover:bg-green-700 transition-colors font-medium text-sm"
                          >
                            <CheckCircle2 size={16} className="inline mr-1" />
                            Check-in
                          </button>
                        )}
                        {booking.status === 'BOOKED' && (
                          <button
                            onClick={() => handleCancel(booking.id)}
                            className="px-4 py-2 border border-slate-200 text-slate-700 rounded-xl hover:bg-slate-50 transition-colors font-medium text-sm"
                          >
                            <X size={16} className="inline mr-1" />
                            Batalkan
                          </button>
                        )}
                        {booking.status === 'CHECKED_IN' && (
                          <button
                            onClick={() => router.push('/dashboard')}
                            className="flex-1 px-4 py-2 bg-blue-600 text-white rounded-xl hover:bg-blue-700 transition-colors font-medium text-sm"
                          >
                            Lihat Dashboard
                          </button>
                        )}
                      </div>
                    </div>
                  </div>
                );
              })}
            </div>
          )}
        </div>
      </div>

      {/* Booking Detail Modal */}
      {selectedBooking && (
        <div className="fixed inset-0 bg-black/50 flex items-center justify-center p-4 z-50">
          <div className="bg-white rounded-2xl p-6 max-w-md w-full">
            <div className="flex items-center justify-between mb-4">
              <h3 className="text-lg font-bold text-slate-800">Detail Booking</h3>
              <button
                onClick={() => setSelectedBooking(null)}
                className="text-slate-400 hover:text-slate-600"
              >
                <X size={20} />
              </button>
            </div>
            
            <div className="space-y-3 text-sm">
              <div className="flex justify-between">
                <span className="text-slate-500">Kode Booking:</span>
                <span className="font-mono font-bold text-blue-600">{selectedBooking.ticket_code}</span>
              </div>
              <div className="flex justify-between">
                <span className="text-slate-500">Area:</span>
                <span className="font-medium">{selectedBooking.parking_area.nama_area}</span>
              </div>
              <div className="flex justify-between">
                <span className="text-slate-500">Kendaraan:</span>
                <span className="font-medium">{selectedBooking.vehicle_type} - {selectedBooking.vehicle_plate}</span>
              </div>
              <div className="flex justify-between">
                <span className="text-slate-500">Status:</span>
                <span className={`px-2 py-1 rounded-full text-xs font-medium ${getStatusColor(selectedBooking.status).bg} ${getStatusColor(selectedBooking.status).text}`}>
                  {getStatusText(selectedBooking.status)}
                </span>
              </div>
            </div>
          </div>
        </div>
      )}
    </>
  );
}
