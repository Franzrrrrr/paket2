'use client';

import { useState, useEffect, useRef } from 'react';
import { useRouter } from 'next/navigation';
import { qrScanAPI, bookingReservationAPI } from '@/lib/api';
import { ArrowLeft, Upload, QrCode, CheckCircle2, AlertCircle, Loader2, Download, Camera } from 'lucide-react';

interface DemoQRCode {
  type: string;
  area?: any;
  qr_code: string;
  qr_data: any;
  download_filename: string;
}

export default function ScanUploadPage() {
  const router = useRouter();
  const fileInputRef = useRef<HTMLInputElement>(null);
  const [selectedFile, setSelectedFile] = useState<File | null>(null);
  const [isLoading, setIsLoading] = useState(false);
  const [error, setError] = useState('');
  const [success, setSuccess] = useState('');
  const [scanResult, setScanResult] = useState<any>(null);
  const [demoQRCodes, setDemoQRCodes] = useState<DemoQRCode[]>([]);
  const [showDemo, setShowDemo] = useState(false);

  useEffect(() => {
    fetchDemoQRCodes();
  }, []);

  const fetchDemoQRCodes = async () => {
    try {
      const response = await qrScanAPI.getDemoQRCodes();
      setDemoQRCodes(response.demo_qr_codes);
    } catch (err: any) {
      console.error('Failed to fetch demo QR codes:', err);
    }
  };

  const handleFileSelect = (event: React.ChangeEvent<HTMLInputElement>) => {
    const file = event.target.files?.[0];
    if (file) {
      // Validate file
      if (!file.type.startsWith('image/')) {
        setError('Harap pilih file gambar (JPEG, PNG, JPG, GIF)');
        return;
      }

      if (file.size > 5 * 1024 * 1024) { // 5MB
        setError('Ukuran file maksimal 5MB');
        return;
      }

      setSelectedFile(file);
      setError('');
      setSuccess('');
      setScanResult(null);
    }
  };

  const handleUpload = async () => {
    if (!selectedFile) {
      setError('Silakan pilih file terlebih dahulu');
      return;
    }

    setIsLoading(true);
    setError('');
    setSuccess('');
    setScanResult(null);

    try {
      const result = await qrScanAPI.uploadAndScan(selectedFile);

      if (result.success) {
        setSuccess('QR code berhasil terdeteksi dan divalidasi!');
        setScanResult(result);

        // If valid QR and requires check-in, proceed
        if (result.validation.valid && result.validation.action_required === 'check_in') {
          if (result.validation.type === 'booking_check_in') {
            // Auto check-in for booking QR
            await handleAutoCheckIn(result.validation.booking.ticket_code);
          } else {
            // For area QR, redirect to booking page with area pre-selected
            const areaId = result.validation.area.id;
            router.push(`/booking/new?area=${areaId}`);
          }
        }
      } else {
        setError(result.message || 'QR code tidak valid');
      }
    } catch (err: any) {
      setError(err.response?.data?.message || 'Gagal memproses QR code');
    } finally {
      setIsLoading(false);
    }
  };

  const handleAutoCheckIn = async (ticketCode: string) => {
    try {
      await bookingReservationAPI.checkIn({ ticket_code: ticketCode });
      setSuccess('Check-in berhasil! Selamat menggunakan layanan parkir kami.');

      // Redirect to dashboard after 2 seconds
      setTimeout(() => {
        router.push('/dashboard');
      }, 2000);
    } catch (err: any) {
      setError(err.response?.data?.message || 'Check-in gagal');
    }
  };

  const downloadQR = (qrCode: DemoQRCode) => {
    const link = document.createElement('a');
    link.href = qrCode.qr_code;
    link.download = qrCode.download_filename;
    link.click();
  };

  const resetForm = () => {
    setSelectedFile(null);
    setError('');
    setSuccess('');
    setScanResult(null);
    if (fileInputRef.current) {
      fileInputRef.current.value = '';
    }
  };

  return (
    <>
      <style>{`
        .scan-upload-root {
          min-height: 100vh;
          background: #f5f7fa;
          font-family: 'Plus Jakarta Sans', sans-serif;
        }

        .drop-zone {
          border: 2px dashed #cbd5e1;
          border-radius: 12px;
          background: white;
          transition: all 0.3s ease;
        }

        .drop-zone:hover {
          border-color: #3b82f6;
          background: #f0f9ff;
        }

        .drop-zone.dragover {
          border-color: #3b82f6;
          background: #dbeafe;
        }

        .qr-preview {
          width: 120px;
          height: 120px;
          border: 2px solid #e5e7eb;
          border-radius: 8px;
          background: white;
        }

        .result-card {
          background: white;
          border-radius: 12px;
          box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
          overflow: hidden;
        }
      `}</style>

      <div className="scan-upload-root">
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
            <h1 className="text-lg font-bold text-slate-800">Scan QR dari Gambar</h1>
            <button
              onClick={() => setShowDemo(!showDemo)}
              className="flex items-center gap-1.5 px-3 py-1.5 rounded-lg border border-slate-200 text-slate-500 text-xs font-medium hover:border-blue-300 hover:text-blue-500 hover:bg-blue-50 transition-all"
            >
              <QrCode size={13} />
              {showDemo ? 'Sembunyikan' : 'Demo'}
            </button>
          </div>
        </div>

        {/* Content */}
        <div className="max-w-4xl mx-auto p-5">
          {/* Demo QR Codes Section */}
          {showDemo && (
            <div className="mb-6 bg-white rounded-2xl border border-slate-100 p-6">
              <h2 className="text-lg font-bold text-slate-800 mb-4">QR Code Demo untuk Testing</h2>

              <div className="bg-blue-50 border border-blue-200 rounded-xl p-4 mb-4">
                <h3 className="font-semibold text-blue-900 mb-2">Cara Testing:</h3>
                <ol className="text-sm text-blue-800 space-y-1 list-decimal list-inside">
                  <li>Download QR code di bawah</li>
                  <li>Buka QR code di HP atau device lain</li>
                  <li>Ambil screenshot QR code</li>
                  <li>Upload screenshot di sini untuk test scan</li>
                </ol>
              </div>

              <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                {demoQRCodes.map((qr, index) => (
                  <div key={index} className="text-center">
                    <div className="result-card p-4">
                      <img
                        src={qr.qr_code}
                        alt="QR Code Demo"
                        className="qr-preview mx-auto mb-3"
                      />
                      <h4 className="font-semibold text-slate-800 mb-1">
                        {qr.type === 'parking_area' ? `Area: ${qr.area?.nama_area}` : 'Booking Demo'}
                      </h4>
                      <p className="text-xs text-slate-600 mb-3">
                        {qr.type === 'parking_area' ? 'QR untuk check-in area' : 'QR untuk check-in booking'}
                      </p>
                      <button
                        onClick={() => downloadQR(qr)}
                        className="w-full px-3 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors text-sm font-medium flex items-center justify-center gap-1"
                      >
                        <Download size={14} />
                        Download
                      </button>
                    </div>
                  </div>
                ))}
              </div>
            </div>
          )}

          {/* Upload Section */}
          <div className="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
            <div className="p-6">
              <div className="flex items-center gap-2 mb-4">
                <Upload size={20} className="text-blue-600" />
                <h2 className="text-lg font-bold text-slate-800">Upload QR Code</h2>
              </div>

              {/* File Drop Zone */}
              <div
                className="drop-zone p-8 text-center cursor-pointer"
                onClick={() => fileInputRef.current?.click()}
                onDragOver={(e) => {
                  e.preventDefault();
                  e.currentTarget.classList.add('dragover');
                }}
                onDragLeave={(e) => {
                  e.preventDefault();
                  e.currentTarget.classList.remove('dragover');
                }}
                onDrop={(e) => {
                  e.preventDefault();
                  e.currentTarget.classList.remove('dragover');
                  const file = e.dataTransfer.files[0];
                  if (file) {
                    const event = { target: { files: [file] } } as any;
                    handleFileSelect(event);
                  }
                }}
              >
                <input
                  ref={fileInputRef}
                  type="file"
                  accept="image/*"
                  onChange={handleFileSelect}
                  className="hidden"
                />

                <Upload size={48} className="text-slate-400 mx-auto mb-4" />
                <p className="text-lg font-semibold text-slate-800 mb-2">
                  {selectedFile ? selectedFile.name : 'Pilih atau Drop QR Code'}
                </p>
                <p className="text-sm text-slate-500 mb-4">
                  JPEG, PNG, JPG, GIF (Maks. 5MB)
                </p>
                <button className="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors font-medium">
                  Pilih File
                </button>
              </div>

              {/* Selected File Preview */}
              {selectedFile && (
                <div className="mt-4 p-4 bg-slate-50 rounded-xl">
                  <div className="flex items-center justify-between">
                    <div className="flex items-center gap-3">
                      <QrCode size={32} className="text-blue-600" />
                      <div>
                        <p className="text-sm font-medium text-slate-800">{selectedFile.name}</p>
                        <p className="text-xs text-slate-500">
                          {(selectedFile.size / 1024 / 1024).toFixed(2)} MB
                        </p>
                      </div>
                    </div>
                    <button
                      onClick={resetForm}
                      className="text-slate-400 hover:text-slate-600"
                    >
                      ×
                    </button>
                  </div>
                </div>
              )}
            </div>

            {/* Upload Button */}
            <div className="px-6 pb-6">
              <button
                onClick={handleUpload}
                disabled={!selectedFile || isLoading}
                className="w-full py-3 bg-blue-600 text-white rounded-xl hover:bg-blue-700 transition-colors font-medium disabled:bg-blue-400 disabled:cursor-not-allowed flex items-center justify-center gap-2"
              >
                {isLoading ? (
                  <>
                    <Loader2 size={18} className="animate-spin" />
                    Memproses...
                  </>
                ) : (
                  <>
                    <Camera size={18} />
                    Scan QR Code
                  </>
                )}
              </button>
            </div>
          </div>

          {/* Error Alert */}
          {error && (
            <div className="mt-4 flex items-center gap-2 px-4 py-3 bg-red-50 border border-red-200 rounded-xl text-red-600 text-sm">
              <AlertCircle size={16} />
              {error}
            </div>
          )}

          {/* Success Alert */}
          {success && (
            <div className="mt-4 flex items-center gap-2 px-4 py-3 bg-green-50 border border-green-200 rounded-xl text-green-600 text-sm">
              <CheckCircle2 size={16} />
              {success}
            </div>
          )}

          {/* Scan Result */}
          {scanResult && (
            <div className="mt-6 bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
              <div className="p-6">
                <h3 className="text-lg font-bold text-slate-800 mb-4">Hasil Scan</h3>

                <div className="space-y-4">
                  {/* Validation Result */}
                  <div className={`p-4 rounded-xl ${
                    scanResult.validation.valid
                      ? 'bg-green-50 border border-green-200'
                      : 'bg-red-50 border border-red-200'
                  }`}>
                    <div className="flex items-center gap-2 mb-2">
                      {scanResult.validation.valid ? (
                        <CheckCircle2 size={20} className="text-green-600" />
                      ) : (
                        <AlertCircle size={20} className="text-red-600" />
                      )}
                      <span className={`font-semibold ${
                        scanResult.validation.valid ? 'text-green-800' : 'text-red-800'
                      }`}>
                        {scanResult.validation.valid ? 'QR Code Valid' : 'QR Code Invalid'}
                      </span>
                    </div>
                    <p className={`text-sm ${
                      scanResult.validation.valid ? 'text-green-700' : 'text-red-700'
                    }`}>
                      {scanResult.validation.message}
                    </p>
                  </div>

                  {/* QR Data */}
                  {scanResult.qr_data && (
                    <div className="p-4 bg-slate-50 rounded-xl">
                      <h4 className="font-semibold text-slate-800 mb-2">QR Data:</h4>
                      <pre className="text-xs text-slate-600 whitespace-pre-wrap break-all">
                        {JSON.stringify(scanResult.qr_data, null, 2)}
                      </pre>
                    </div>
                  )}

                  {/* Debug Info */}
                  {scanResult.debug_info && (
                    <div className="p-4 bg-amber-50 rounded-xl">
                      <h4 className="font-semibold text-amber-800 mb-2">Debug Info:</h4>
                      <pre className="text-xs text-amber-700 whitespace-pre-wrap">
                        {JSON.stringify(scanResult.debug_info, null, 2)}
                      </pre>
                    </div>
                  )}
                </div>
              </div>
            </div>
          )}
        </div>
      </div>
    </>
  );
}
