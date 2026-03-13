'use client';

import { useState, useRef, useEffect } from 'react';
import { useRouter } from 'next/navigation';
import { bookingReservationAPI, CheckInRequest } from '@/lib/api';
import { ArrowLeft, Camera, QrCode, CheckCircle2, AlertCircle, Loader2, Upload } from 'lucide-react';

export default function ScanQRPage() {
  const router = useRouter();
  const videoRef = useRef<HTMLVideoElement>(null);
  const [isLoading, setIsLoading] = useState(false);
  const [error, setError] = useState('');
  const [success, setSuccess] = useState('');
  const [isScanning, setIsScanning] = useState(false);
  const [manualCode, setManualCode] = useState('');
  const [showManual, setShowManual] = useState(false);

  useEffect(() => {
    startCamera();
    return () => {
      stopCamera();
    };
  }, []);

  const startCamera = async () => {
    try {
      const constraints = {
        video: {
          facingMode: 'environment' as any // Type assertion for browser compatibility
        },
        audio: false
      };

      const stream = await navigator.mediaDevices.getUserMedia(constraints);

      if (videoRef.current) {
        videoRef.current.srcObject = stream;
        setIsScanning(true);
      }
    } catch (err) {
      console.error('Camera access denied:', err);
      setError('Tidak dapat mengakses kamera. Silakan gunakan input manual.');
      setShowManual(true);
    }
  };

  const stopCamera = () => {
    if (videoRef.current?.srcObject) {
      const stream = videoRef.current.srcObject as MediaStream;
      stream.getTracks().forEach(track => track.stop());
      videoRef.current.srcObject = null;
      setIsScanning(false);
    }
  };

  const simulateQRScan = () => {
    // Simulate QR code detection (in real implementation, use QR scanning library)
    const mockQRCode = 'BK' + Math.random().toString(36).substr(2, 8).toUpperCase();

    setIsLoading(true);
    setError('');

    setTimeout(() => {
      handleCheckIn(mockQRCode);
    }, 2000);
  };

  const handleCheckIn = async (ticketCode: string) => {
    setIsLoading(true);
    setError('');
    setSuccess('');

    try {
      const checkInData: CheckInRequest = {
        ticket_code: ticketCode
      };

      const response = await bookingReservationAPI.checkIn(checkInData);

      setSuccess('Check-in berhasil! Selamat menggunakan layanan parkir kami.');
      stopCamera();

      // Redirect to dashboard after 2 seconds
      setTimeout(() => {
        router.push('/dashboard');
      }, 2000);

    } catch (err: any) {
      setError(err.response?.data?.message || 'Check-in gagal. Kode booking tidak valid atau sudah digunakan.');
      setIsLoading(false);
    }
  };

  const handleManualSubmit = async (e: React.FormEvent<HTMLFormElement>) => {
    e.preventDefault();
    if (!manualCode.trim()) {
      setError('Silakan masukkan kode booking');
      return;
    }

    await handleCheckIn(manualCode.trim().toUpperCase());
  };

  const toggleManualInput = () => {
    if (showManual) {
      setShowManual(false);
      startCamera();
    } else {
      stopCamera();
      setShowManual(true);
    }
  };

  return (
    <>
      <style>{`
        .scan-root {
          min-height: 100vh;
          background: #f5f7fa;
          font-family: 'Plus Jakarta Sans', sans-serif;
        }

        .scan-container {
          position: relative;
          width: 100%;
          max-width: 400px;
          margin: 0 auto;
        }

        .scan-overlay {
          position: absolute;
          top: 50%;
          left: 50%;
          transform: translate(-50%, -50%);
          width: 250px;
          height: 250px;
          border: 3px solid #3b82f6;
          border-radius: 12px;
          background: rgba(59, 130, 246, 0.1);
        }

        .scan-line {
          position: absolute;
          top: 50%;
          left: 0;
          right: 0;
          height: 3px;
          background: linear-gradient(90deg, transparent, #3b82f6, transparent);
          animation: scan 2s linear infinite;
        }

        @keyframes scan {
          0% { transform: translateY(-100px); }
          100% { transform: translateY(100px); }
        }
      `}</style>

      <div className="scan-root">
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
            <h1 className="text-lg font-bold text-slate-800">Scan QR Check-in</h1>
            <div className="w-16" />
          </div>
        </div>

        {/* Content */}
        <div className="max-w-4xl mx-auto p-5">
          <div className="scan-container">
            {/* Error Alert */}
            {error && (
              <div className="mb-4 flex items-center gap-2 px-4 py-3 bg-red-50 border border-red-200 rounded-xl text-red-600 text-sm">
                <AlertCircle size={16} />
                {error}
              </div>
            )}

            {/* Success Alert */}
            {success && (
              <div className="mb-4 flex items-center gap-2 px-4 py-3 bg-green-50 border border-green-200 rounded-xl text-green-600 text-sm">
                <CheckCircle2 size={16} />
                {success}
              </div>
            )}

            {/* Scanner View */}
            {!showManual ? (
              <div className="relative bg-black rounded-2xl overflow-hidden">
                <video
                  ref={videoRef}
                  autoPlay
                  playsInline
                  muted
                  className="w-full h-full object-cover"
                />

                {/* Scan Overlay */}
                <div className="scan-overlay">
                  <div className="scan-line" />
                </div>

                {/* QR Icon */}
                <div className="absolute top-4 left-4 bg-white/90 backdrop-blur-sm rounded-lg p-2">
                  <QrCode size={24} className="text-blue-600" />
                </div>

                {/* Instructions */}
                <div className="absolute bottom-4 left-4 right-4 bg-white/90 backdrop-blur-sm rounded-lg p-3">
                  <p className="text-xs text-slate-600 text-center">
                    Arahkan kamera ke QR code
                  </p>
                </div>
              </div>
            ) : (
              /* Manual Input View */
              <div className="bg-white rounded-2xl border border-slate-100 shadow-sm p-6">
                <div className="text-center mb-6">
                  <div className="w-16 h-16 bg-blue-100 rounded-full flex items-center justify-center mx-auto mb-4">
                    <QrCode size={32} className="text-blue-600" />
                  </div>
                  <h3 className="text-lg font-bold text-slate-800 mb-2">Input Manual</h3>
                  <p className="text-sm text-slate-600">
                    Masukkan kode booking secara manual
                  </p>
                </div>

                <form onSubmit={handleManualSubmit} className="space-y-4">
                  <div>
                    <label className="block text-sm font-medium text-slate-700 mb-2">
                      Kode Booking
                    </label>
                    <input
                      type="text"
                      value={manualCode}
                      onChange={(e) => setManualCode(e.target.value.toUpperCase())}
                      placeholder="Contoh: BK123ABC"
                      className="w-full px-4 py-3 border-2 border-slate-200 rounded-xl text-lg font-mono text-center text-slate-800 bg-slate-50 outline-none focus:border-blue-300 focus:bg-white focus:shadow-sm transition-all placeholder:text-slate-300"
                      style={{ textTransform: 'uppercase' }}
                      disabled={isLoading}
                    />
                    <p className="mt-2 text-xs text-slate-400">
                      Format: BK diikuti 6 karakter huruf/angka
                    </p>
                  </div>

                  <button
                    type="submit"
                    disabled={isLoading || !manualCode.trim()}
                    className="w-full py-3 bg-blue-600 text-white rounded-xl hover:bg-blue-700 transition-colors font-medium disabled:bg-blue-400 disabled:cursor-not-allowed flex items-center justify-center gap-2"
                  >
                    {isLoading ? (
                      <>
                        <Loader2 size={18} className="animate-spin" />
                        Memproses...
                      </>
                    ) : (
                      'Check-in'
                    )}
                  </button>
                </form>
              </div>
            )}

            {/* Toggle Button */}
            <div className="mt-4 text-center space-y-2">
              <button
                onClick={toggleManualInput}
                className="px-6 py-2 border border-slate-200 text-slate-700 rounded-xl hover:bg-slate-50 transition-colors font-medium text-sm"
              >
                {showManual ? (
                  <>
                    <Camera size={16} className="inline mr-2" />
                    Gunakan Kamera
                  </>
                ) : (
                  <>
                    <QrCode size={16} className="inline mr-2" />
                    Input Manual
                  </>
                )}
              </button>

              <div>
                <button
                  onClick={() => router.push('/booking/scan-upload')}
                  className="px-6 py-2 bg-blue-600 text-white rounded-xl hover:bg-blue-700 transition-colors font-medium text-sm"
                >
                  <Upload size={16} className="inline mr-2" />
                  Upload Gambar QR
                </button>
              </div>
            </div>

            {/* Simulate Scan Button (for demo) */}
            {isScanning && !showManual && (
              <div className="mt-4 text-center">
                <button
                  onClick={simulateQRScan}
                  disabled={isLoading}
                  className="px-6 py-2 bg-green-600 text-white rounded-xl hover:bg-green-700 transition-colors font-medium text-sm"
                >
                  Simulasi Scan QR (Demo)
                </button>
              </div>
            )}
          </div>
        </div>
      </div>
    </>
  );
}
