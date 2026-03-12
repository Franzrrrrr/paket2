'use client';

import { useState } from 'react';
import { Button } from '@/components/ui/button';
import { AlertTriangle, LogOut } from 'lucide-react';

interface LogoutModalProps {
  isOpen: boolean;
  onClose: () => void;
  onConfirm: () => void;
}

export default function LogoutModal({ isOpen, onClose, onConfirm }: LogoutModalProps) {
  const [isLoggingOut, setIsLoggingOut] = useState(false);

  const handleConfirm = async () => {
    setIsLoggingOut(true);
    await onConfirm();
    setIsLoggingOut(false);
    onClose();
  };

  if (!isOpen) return null;

  return (
    <div className="fixed inset-0 z-1000 flex items-center justify-center bg-black/50">
      <div className="bg-white rounded-xl p-6 max-w-md mx-4 shadow-xl">
        <div className="flex items-center gap-3 mb-4">
          <div className="w-12 h-12 rounded-full bg-red-100 flex items-center justify-center">
            <AlertTriangle className="h-6 w-6 text-red-600" />
          </div>
          <div>
            <h3 className="text-lg font-semibold text-gray-900">Konfirmasi Logout</h3>
            <p className="text-sm text-gray-600">Apakah Anda yakin ingin keluar?</p>
          </div>
        </div>

        <div className="flex gap-3">
          <Button
            variant="outline"
            onClick={onClose}
            disabled={isLoggingOut}
            className="flex-1"
          >
            Batal
          </Button>
          <Button
            onClick={handleConfirm}
            disabled={isLoggingOut}
            className="flex-1 bg-red-600 hover:bg-red-700"
          >
            {isLoggingOut ? (
              <>
                <LogOut className="h-4 w-4 mr-2 animate-spin" />
                Keluar...
              </>
            ) : (
              <>
                <LogOut className="h-4 w-4 mr-2" />
                Ya, Keluar
              </>
            )}
          </Button>
        </div>
      </div>
    </div>
  );
}
