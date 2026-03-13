'use client';

import { useState } from 'react';
import { useRouter } from 'next/navigation';
import { useAuth } from '@/contexts/AuthContext';
import { authAPI } from '@/lib/api';
import { Loader2, Car, Eye, EyeOff, UserPlus, ArrowLeft } from 'lucide-react';

export default function RegisterPage() {
  const [formData, setFormData] = useState({
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
  });
  const [error, setError] = useState('');
  const [isLoading, setIsLoading] = useState(false);
  const [showPass, setShowPass] = useState(false);
  const [showConfirmPass, setShowConfirmPass] = useState(false);
  const { register } = useAuth();
  const router = useRouter();

  const handleSubmit = async (e: React.FormEvent<HTMLFormElement>) => {
    e.preventDefault();
    setIsLoading(true);
    setError('');

    // Validation
    if (formData.password !== formData.password_confirmation) {
      setError('Password dan konfirmasi password tidak cocok.');
      setIsLoading(false);
      return;
    }

    if (formData.password.length < 6) {
      setError('Password minimal 6 karakter.');
      setIsLoading(false);
      return;
    }

    try {
      // Register API call
      await register(
        formData.name,
        formData.email,
        formData.password,
        formData.password_confirmation
      );

      // Auto redirect to dashboard after successful registration
      router.push('/dashboard');
    } catch (err: any) {
      setError(err.response?.data?.message || err.message || 'Registrasi gagal. Silakan coba lagi.');
    } finally {
      setIsLoading(false);
    }
  };

  const handleChange = (e: React.ChangeEvent<HTMLInputElement>) => {
    setFormData(prev => ({
      ...prev,
      [e.target.name]: e.target.value
    }));
  };

  return (
    <>
      <style>{`
        @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700&display=swap');

        .reg-root {
          min-height: 100svh;
          display: flex;
          align-items: center;
          justify-content: center;
          background: #f5f7fa;
          font-family: 'Plus Jakarta Sans', sans-serif;
          padding: 24px 16px;
          position: relative;
          overflow: hidden;
        }

        .reg-blob-1 {
          position: absolute;
          width: 500px; height: 500px;
          border-radius: 50%;
          background: radial-gradient(circle, #dbeafe 0%, transparent 70%);
          top: -160px; left: -160px;
          pointer-events: none;
        }
        .reg-blob-2 {
          position: absolute;
          width: 380px; height: 380px;
          border-radius: 50%;
          background: radial-gradient(circle, #e0f2fe 0%, transparent 70%);
          bottom: -100px; right: -100px;
          pointer-events: none;
        }

        .reg-card {
          background: #ffffff;
          border-radius: 28px;
          padding: 44px 40px;
          width: 100%;
          max-width: 420px;
          box-shadow:
            0 2px 4px rgba(0,0,0,0.04),
            0 12px 40px rgba(99,179,237,0.12),
            0 40px 80px rgba(0,0,0,0.06);
          position: relative;
          z-index: 1;
          animation: reg-rise 0.5s cubic-bezier(0.22,1,0.36,1) both;
        }

        @keyframes reg-rise {
          from { opacity: 0; transform: translateY(24px); }
          to   { opacity: 1; transform: translateY(0);    }
        }

        .reg-logo {
          width: 52px; height: 52px;
          border-radius: 16px;
          background: linear-gradient(135deg, #bfdbfe, #93c5fd);
          display: flex; align-items: center; justify-content: center;
          margin-bottom: 18px;
        }

        .reg-title {
          font-size: 22px;
          font-weight: 700;
          color: #0f172a;
          margin-bottom: 4px;
          letter-spacing: -0.02em;
        }

        .reg-sub {
          font-size: 13.5px;
          color: #94a3b8;
          margin-bottom: 32px;
          font-weight: 400;
        }

        .reg-label {
          display: block;
          font-size: 13px;
          font-weight: 500;
          color: #475569;
          margin-bottom: 6px;
        }

        .reg-field {
          position: relative;
          margin-bottom: 16px;
        }

        .reg-input {
          width: 100%;
          padding: 11px 14px;
          border: 1.5px solid #e8edf3;
          border-radius: 12px;
          font-size: 14px;
          font-family: 'Plus Jakarta Sans', sans-serif;
          color: #0f172a;
          background: #f8fafc;
          outline: none;
          transition: border-color 0.18s, box-shadow 0.18s, background 0.18s;
        }
        .reg-input:focus {
          border-color: #93c5fd;
          background: #fff;
          box-shadow: 0 0 0 3.5px rgba(147,197,253,0.25);
        }
        .reg-input::placeholder { color: #cbd5e1; }
        .reg-input.has-eye { padding-right: 42px; }

        .reg-eye {
          position: absolute;
          right: 12px; top: 50%;
          background: none; border: none;
          cursor: pointer; color: #cbd5e1;
          display: flex; align-items: center;
          padding: 4px; border-radius: 6px;
          transition: color 0.15s;
        }
        .reg-eye:hover { color: #60a5fa; }

        .reg-error {
          background: #fff5f5;
          border: 1px solid #fed7d7;
          color: #e53e3e;
          border-radius: 10px;
          padding: 9px 13px;
          font-size: 13px;
          margin-bottom: 18px;
          display: flex; align-items: center; gap: 7px;
          animation: reg-fade 0.2s ease;
        }
        @keyframes reg-fade {
          from { opacity: 0; transform: translateY(-4px); }
          to   { opacity: 1; transform: none; }
        }

        .reg-btn {
          width: 100%;
          padding: 12px;
          background: linear-gradient(135deg, #93c5fd, #3b82f6);
          color: #fff;
          border: none;
          border-radius: 12px;
          font-size: 14.5px;
          font-weight: 600;
          font-family: 'Plus Jakarta Sans', sans-serif;
          cursor: pointer;
          box-shadow: 0 4px 16px rgba(59,130,246,0.28);
          transition: opacity 0.15s, transform 0.15s, box-shadow 0.15s;
          display: flex; align-items: center; justify-content: center; gap: 8px;
          margin-top: 8px;
          letter-spacing: 0.01em;
        }
        .reg-btn:hover:not(:disabled) {
          opacity: 0.9;
          transform: translateY(-1px);
          box-shadow: 0 6px 22px rgba(59,130,246,0.32);
        }
        .reg-btn:active:not(:disabled) { transform: scale(0.98); }
        .reg-btn:disabled { opacity: 0.6; cursor: not-allowed; }

        .reg-footer {
          text-align: center;
          margin-top: 22px;
          font-size: 12px;
          color: #cbd5e1;
        }
        .reg-footer a {
          color: #3b82f6;
          text-decoration: none;
          font-weight: 500;
        }
        .reg-footer a:hover { text-decoration: underline; }

        .role-badge {
          display: inline-flex;
          align-items: center;
          gap: 6px;
          padding: 4px 10px;
          background: #f0f9ff;
          border: 1px solid #bae6fd;
          border-radius: 8px;
          font-size: 11px;
          color: #0369a1;
          font-weight: 600;
          margin-bottom: 20px;
        }
      `}</style>

      <div className="reg-root">
        <div className="reg-blob-1" />
        <div className="reg-blob-2" />

        <div className="reg-card">
          <button
            onClick={() => router.push('/login')}
            className="mb-4 flex items-center gap-2 text-blue-600 hover:text-blue-700 text-sm font-medium transition-colors"
          >
            <ArrowLeft size={14} />
            Kembali ke Login
          </button>

          <div className="reg-logo">
            <UserPlus size={24} color="#2563eb" />
          </div>

          <h1 className="reg-title">Daftar Owner</h1>
          <p className="reg-sub">Buat akun owner untuk sistem parkir</p>

          <div className="role-badge">
            <Car size={12} />
            Role: Owner (Otomatis)
          </div>

          {error && (
            <div className="reg-error">
              <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2.5" strokeLinecap="round" strokeLinejoin="round">
                <circle cx="12" cy="12" r="10"/>
                <line x1="12" y1="8" x2="12" y2="12"/>
                <line x1="12" y1="16" x2="12.01" y2="16"/>
              </svg>
              {error}
            </div>
          )}

          <form onSubmit={handleSubmit} noValidate>
            <div className="reg-field">
              <label className="reg-label" htmlFor="name">Nama Lengkap</label>
              <input
                id="name"
                name="name"
                type="text"
                className="reg-input"
                placeholder="John Doe"
                value={formData.name}
                onChange={handleChange}
                required
                autoComplete="name"
              />
            </div>

            <div className="reg-field">
              <label className="reg-label" htmlFor="email">Email</label>
              <input
                id="email"
                name="email"
                type="email"
                className="reg-input"
                placeholder="owner@parking.com"
                value={formData.email}
                onChange={handleChange}
                required
                autoComplete="email"
              />
            </div>

            <div className="reg-field">
              <label className="reg-label" htmlFor="password">Password</label>
              <input
                id="password"
                name="password"
                type={showPass ? 'text' : 'password'}
                className="reg-input has-eye"
                placeholder="Minimal 6 karakter"
                value={formData.password}
                onChange={handleChange}
                required
                autoComplete="new-password"
                minLength={6}
              />
              <button type="button" className="reg-eye" onClick={() => setShowPass(!showPass)} tabIndex={-1}>
                {showPass ? <EyeOff size={15} /> : <Eye size={15} />}
              </button>
            </div>

            <div className="reg-field">
              <label className="reg-label" htmlFor="password_confirmation">Konfirmasi Password</label>
              <input
                id="password_confirmation"
                name="password_confirmation"
                type={showConfirmPass ? 'text' : 'password'}
                className="reg-input has-eye"
                placeholder="Ulangi password"
                value={formData.password_confirmation}
                onChange={handleChange}
                required
                autoComplete="new-password"
              />
              <button type="button" className="reg-eye" onClick={() => setShowConfirmPass(!showConfirmPass)} tabIndex={-1}>
                {showConfirmPass ? <EyeOff size={15} /> : <Eye size={15} />}
              </button>
            </div>

            <button type="submit" className="reg-btn" disabled={isLoading}>
              {isLoading
                ? <><Loader2 size={16} className="animate-spin" /> Mendaftar...</>
                : <>Daftar sebagai Owner <UserPlus size={15} /></>
              }
            </button>
          </form>

          <p className="reg-footer">
            Sudah punya akun? <a href="/login">Masuk di sini</a>
          </p>
        </div>
      </div>
    </>
  );
}
