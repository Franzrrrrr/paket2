'use client';

import { useState } from 'react';
import { useRouter } from 'next/navigation';
import { bookingAPI } from '@/lib/api';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Alert, AlertDescription } from '@/components/ui/alert';
import { ArrowLeft, Car, DollarSign, Clock, CheckCircle } from 'lucide-react';
import { formatCurrency, formatDateTime } from '@/lib/utils';

export default function ExitPage() {
  const router = useRouter();
  const [ticketCode, setTicketCode] = useState('');
  const [isLoading, setIsLoading] = useState(false);
  const [error, setError] = useState('');
  const [successData, setSuccessData] = useState<any>(null);

  const handleSubmit = async (e: React.FormEvent<HTMLFormElement>) => {
    e.preventDefault();
    if (!ticketCode.trim()) {
      setError('Silakan masukkan kode tiket');
      return;
    }

    setIsLoading(true);
    setError('');

    try {
      const response = await bookingAPI.exit({ ticket_code: ticketCode.trim() });
      setSuccessData(response.data);
    } catch (error: any) {
      setError(error.response?.data?.message || 'Proses keluar gagal. Silakan coba lagi.');
    } finally {
      setIsLoading(false);
    }
  };

  const handleReset = () => {
    setTicketCode('');
    setSuccessData(null);
    setError('');
  };

  if (successData) {
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
              <h1 className="text-xl font-bold text-gray-900">Keluar Parkir Berhasil</h1>
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
              Terima Kasih!
            </h2>
            <p className="text-gray-600">
              Semoga perjalanan Anda menyenangkan
            </p>
          </div>

          {/* Payment Details */}
          <Card className="mb-6">
            <CardHeader>
              <CardTitle className="flex items-center">
                <DollarSign className="h-5 w-5 mr-2" />
                Detail Pembayaran
              </CardTitle>
            </CardHeader>
            <CardContent>
              <div className="space-y-4">
                <div className="flex justify-between items-center py-3 border-b">
                  <span className="font-medium">Kode Tiket</span>
                  <span className="font-mono font-bold text-lg text-blue-600">
                    {successData.ticket_code}
                  </span>
                </div>
                
                <div className="flex justify-between items-center py-3 border-b">
                  <span className="font-medium">Durasi Parkir</span>
                  <div className="flex items-center">
                    <Clock className="h-4 w-4 mr-2 text-gray-500" />
                    <span>{Math.floor(successData.duration_minutes / 60)} jam {successData.duration_minutes % 60} menit</span>
                  </div>
                </div>
                
                <div className="flex justify-between items-center py-3 border-b">
                  <span className="font-medium">Waktu Keluar</span>
                  <span>{formatDateTime(successData.exit_time)}</span>
                </div>
                
                <div className="flex justify-between items-center py-3 text-lg font-bold">
                  <span>Total Biaya</span>
                  <span className="text-green-600">
                    {formatCurrency(successData.total_price)}
                  </span>
                </div>
              </div>
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
              onClick={handleReset}
            >
              Proses Lagi
            </Button>
          </div>
        </main>
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
            <h1 className="text-xl font-bold text-gray-900">Keluar Parkir</h1>
          </div>
        </div>
      </header>

      <main className="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        {/* Instructions */}
        <Card className="mb-6 border-blue-200 bg-blue-50">
          <CardContent className="p-6">
            <h3 className="font-semibold text-blue-900 mb-2 flex items-center">
              <Car className="h-5 w-5 mr-2" />
              Cara Keluar Parkir
            </h3>
            <ol className="text-sm text-blue-800 space-y-1">
              <li>1. Masukkan kode tiket parkir yang Anda dapatkan saat masuk</li>
              <li>2. Sistem akan menghitung durasi dan biaya parkir</li>
              <li>3. Lakukan pembayaran di loket yang tersedia</li>
              <li>4. Tunjukkan bukti pembayaran kepada petugas</li>
            </ol>
          </CardContent>
        </Card>

        {/* Exit Form */}
        <Card>
          <CardHeader>
            <CardTitle>Masukkan Kode Tiket</CardTitle>
          </CardHeader>
          <CardContent>
            <form onSubmit={handleSubmit} className="space-y-4">
              <div>
                <label htmlFor="ticketCode" className="block text-sm font-medium text-gray-700 mb-2">
                  Kode Tiket Parkir
                </label>
                <Input
                  id="ticketCode"
                  type="text"
                  value={ticketCode}
                  onChange={(e: React.ChangeEvent<HTMLInputElement>) => setTicketCode(e.target.value.toUpperCase())}
                  placeholder="Contoh: PK-ABCD1234"
                  className="font-mono text-lg"
                  required
                />
                <p className="text-sm text-gray-600 mt-1">
                  Kode tiket terdapat pada struk masuk parkir
                </p>
              </div>

              {error && (
                <Alert variant="destructive">
                  <AlertDescription>{error}</AlertDescription>
                </Alert>
              )}

              <Button
                type="submit"
                className="w-full"
                disabled={isLoading || !ticketCode.trim()}
              >
                {isLoading ? 'Memproses...' : 'Proses Keluar'}
              </Button>
            </form>
          </CardContent>
        </Card>

        {/* Help Section */}
        <Card className="mt-6">
          <CardHeader>
            <CardTitle className="text-lg">Bantuan</CardTitle>
          </CardHeader>
          <CardContent>
            <div className="space-y-2 text-sm text-gray-600">
              <p>• Jika lupa kode tiket, hubungi petugas parkir</p>
              <p>• Pastikan kendaraan sudah siap untuk keluar</p>
              <p>• Biaya parkir: Rp 2.000 per jam (minimum 1 jam)</p>
              <p>• Simpan struk keluar sebagai bukti pembayaran</p>
            </div>
          </CardContent>
        </Card>
      </main>
    </div>
  );
}
