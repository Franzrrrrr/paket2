'use client';

import { useEffect, useState } from 'react';
import { useRouter, useSearchParams } from 'next/navigation';
import { bookingAPI, ParkingSession } from '@/lib/api';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { CheckCircle, Clock, MapPin, Car, ArrowLeft } from 'lucide-react';
import { formatDateTime } from '@/lib/utils';

export default function BookingSuccessPage() {
  const router = useRouter();
  const searchParams = useSearchParams();
  const ticketCode = searchParams.get('ticket');
  const [session, setSession] = useState<ParkingSession | null>(null);
  const [isLoading, setIsLoading] = useState(true);

  useEffect(() => {
    if (!ticketCode) {
      router.push('/dashboard');
      return;
    }

    fetchSession();
  }, [ticketCode]);

  const fetchSession = async () => {
    try {
      const sessionData = await bookingAPI.getActiveSession();
      setSession(sessionData);
    } catch (error) {
      console.error('Failed to fetch session:', error);
    } finally {
      setIsLoading(false);
    }
  };

  if (isLoading) {
    return (
      <div className="min-h-screen flex items-center justify-center">
        <div className="animate-spin rounded-full h-32 w-32 border-b-2 border-blue-600"></div>
      </div>
    );
  }

  if (!session) {
    return (
      <div className="min-h-screen flex items-center justify-center">
        <div className="text-center">
          <h1 className="text-2xl font-bold text-gray-900 mb-4">Data tidak ditemukan</h1>
          <Button onClick={() => router.push('/dashboard')}>
            Kembali ke Dashboard
          </Button>
        </div>
      </div>
    );
  }

  return (
    <div className="min-h-screen bg-gray-50">
      {/* Header */}
      <header className="bg-white shadow-sm border-b">
        <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
          <div className="flex items-center h-16">
            <Button
              variant="ghost"
              onClick={() => router.push('/dashboard')}
              className="mr-4"
            >
              <ArrowLeft className="h-4 w-4 mr-2" />
              Kembali
            </Button>
            <h1 className="text-xl font-bold text-gray-900">Booking Berhasil</h1>
          </div>
        </div>
      </header>

      <main className="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        {/* Success Message */}
        <div className="text-center mb-8">
          <div className="mx-auto h-16 w-16 flex items-center justify-center rounded-full bg-green-100 mb-4">
            <CheckCircle className="h-10 w-10 text-green-600" />
          </div>
          <h2 className="text-2xl font-bold text-gray-900 mb-2">
            Booking Parkir Berhasil!
          </h2>
          <p className="text-gray-600">
            Kendaraan Anda telah terdaftar di area parkir yang dipilih
          </p>
        </div>

        {/* Ticket Details */}
        <Card className="mb-6">
          <CardHeader>
            <CardTitle className="flex items-center">
              <Car className="h-5 w-5 mr-2" />
              Detail Tiket Parkir
            </CardTitle>
          </CardHeader>
          <CardContent>
            <div className="space-y-4">
              <div className="flex justify-between items-center py-3 border-b">
                <span className="font-medium">Kode Tiket</span>
                <span className="font-mono font-bold text-lg text-blue-600">
                  {session.ticket_code}
                </span>
              </div>
              
              <div className="flex justify-between items-center py-3 border-b">
                <span className="font-medium">Area Parkir</span>
                <span>{session.parking_area.nama_area}</span>
              </div>
              
              <div className="flex justify-between items-center py-3 border-b">
                <span className="font-medium">Kendaraan</span>
                <span>{session.vehicle.plat_nomor} ({session.vehicle.jenis_kendaraan})</span>
              </div>
              
              <div className="flex justify-between items-center py-3 border-b">
                <span className="font-medium">Waktu Masuk</span>
                <span>{formatDateTime(session.entry_time)}</span>
              </div>
              
              <div className="flex justify-between items-center py-3">
                <span className="font-medium">Status</span>
                <span className="px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-600">
                  Aktif
                </span>
              </div>
            </div>
          </CardContent>
        </Card>

        {/* Parking Location */}
        <Card className="mb-6">
          <CardHeader>
            <CardTitle className="flex items-center">
              <MapPin className="h-5 w-5 mr-2" />
              Lokasi Parkir
            </CardTitle>
          </CardHeader>
          <CardContent>
            <div className="space-y-2">
              <h3 className="font-semibold">{session.parking_area.nama_area}</h3>
              <p className="text-gray-600">{session.parking_area.alamat}</p>
              <div className="flex space-x-4 text-sm text-gray-500">
                <span>Kapasitas: {session.parking_area.terisi}/{session.parking_area.kapasitas}</span>
                <span>Tersedia: {session.parking_area.sisa}</span>
              </div>
            </div>
          </CardContent>
        </Card>

        {/* Important Notes */}
        <Card className="mb-6 border-yellow-200 bg-yellow-50">
          <CardContent className="p-6">
            <h3 className="font-semibold text-yellow-900 mb-2 flex items-center">
              <Clock className="h-5 w-5 mr-2" />
              Penting
            </h3>
            <ul className="text-sm text-yellow-800 space-y-1">
              <li>• Simpan kode tiket dengan aman untuk proses keluar parkir</li>
              <li>• Biaya parkir: Rp 2.000 per jam (minimum 1 jam)</li>
              <li>• Pastikan kendaraan diparkir di lokasi yang benar</li>
              <li>• Hubungi petugas jika ada kendala</li>
            </ul>
          </CardContent>
        </Card>

        {/* Actions */}
        <div className="flex space-x-4">
          <Button
            onClick={() => router.push('/dashboard')}
            className="flex-1"
          >
            Kembali ke Dashboard
          </Button>
          <Button
            variant="outline"
            onClick={() => router.push('/booking/exit')}
          >
            Keluar Parkir
          </Button>
        </div>
      </main>
    </div>
  );
}
