'use client';

import { useState } from 'react';
import { useRouter } from 'next/navigation';
import { useAuth } from '@/contexts/AuthContext';
import { Loader2, Car, Eye, EyeOff } from 'lucide-react';

export default function LoginPage() {
  const [email, setEmail]       = useState('');
  const [password, setPassword] = useState('');
  const [error, setError]       = useState('');
  const [isLoading, setIsLoading] = useState(false);
  const [showPass, setShowPass] = useState(false);
  const { login } = useAuth();
  const router    = useRouter();

  const handleSubmit = async (e: React.FormEvent<HTMLFormElement>) => {
    e.preventDefault();
    setIsLoading(true);
    setError('');
    try {
      await login(email, password);
      router.push('/dashboard');
    } catch (err: any) {
      setError(err.response?.data?.message || 'Email atau password salah.');
    } finally {
      setIsLoading(false);
    }
  };

  return (
    <>
      <style>{`
        @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700&display=swap');

        .lp-root {
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

        .lp-blob-1 {
          position: absolute;
          width: 500px; height: 500px;
          border-radius: 50%;
          background: radial-gradient(circle, #dbeafe 0%, transparent 70%);
          top: -160px; left: -160px;
          pointer-events: none;
        }
        .lp-blob-2 {
          position: absolute;
          width: 380px; height: 380px;
          border-radius: 50%;
          background: radial-gradient(circle, #e0f2fe 0%, transparent 70%);
          bottom: -100px; right: -100px;
          pointer-events: none;
        }

        .lp-card {
          background: #ffffff;
          border-radius: 28px;
          padding: 44px 40px;
          width: 100%;
          max-width: 400px;
          box-shadow:
            0 2px 4px rgba(0,0,0,0.04),
            0 12px 40px rgba(99,179,237,0.12),
            0 40px 80px rgba(0,0,0,0.06);
          position: relative;
          z-index: 1;
          animation: lp-rise 0.5s cubic-bezier(0.22,1,0.36,1) both;
        }

        @keyframes lp-rise {
          from { opacity: 0; transform: translateY(24px); }
          to   { opacity: 1; transform: translateY(0);    }
        }

        .lp-logo {
          width: 52px; height: 52px;
          border-radius: 16px;
          background: linear-gradient(135deg, #bfdbfe, #93c5fd);
          display: flex; align-items: center; justify-content: center;
          margin-bottom: 18px;
        }

        .lp-title {
          font-size: 22px;
          font-weight: 700;
          color: #0f172a;
          margin-bottom: 4px;
          letter-spacing: -0.02em;
        }

        .lp-sub {
          font-size: 13.5px;
          color: #94a3b8;
          margin-bottom: 32px;
          font-weight: 400;
        }

        .lp-label {
          display: block;
          font-size: 13px;
          font-weight: 500;
          color: #475569;
          margin-bottom: 6px;
        }

        .lp-field {
          position: relative;
          margin-bottom: 16px;
        }

        .lp-input {
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
        .lp-input:focus {
          border-color: #93c5fd;
          background: #fff;
          box-shadow: 0 0 0 3.5px rgba(147,197,253,0.25);
        }
        .lp-input::placeholder { color: #cbd5e1; }
        .lp-input.has-eye { padding-right: 42px; }

        .lp-eye {
          position: absolute;
          right: 12px; top: 50%;
          background: none; border: none;
          cursor: pointer; color: #cbd5e1;
          display: flex; align-items: center;
          padding: 4px; border-radius: 6px;
          transition: color 0.15s;
        }
        .lp-eye:hover { color: #60a5fa; }

        .lp-error {
          background: #fff5f5;
          border: 1px solid #fed7d7;
          color: #e53e3e;
          border-radius: 10px;
          padding: 9px 13px;
          font-size: 13px;
          margin-bottom: 18px;
          display: flex; align-items: center; gap: 7px;
          animation: lp-fade 0.2s ease;
        }
        @keyframes lp-fade {
          from { opacity: 0; transform: translateY(-4px); }
          to   { opacity: 1; transform: none; }
        }

        .lp-btn {
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
        .lp-btn:hover:not(:disabled) {
          opacity: 0.9;
          transform: translateY(-1px);
          box-shadow: 0 6px 22px rgba(59,130,246,0.32);
        }
        .lp-btn:active:not(:disabled) { transform: scale(0.98); }
        .lp-btn:disabled { opacity: 0.6; cursor: not-allowed; }

        .lp-footer {
          text-align: center;
          margin-top: 22px;
          font-size: 12px;
          color: #cbd5e1;
        }
      `}</style>

      <div className="lp-root">
        <div className="lp-blob-1" />
        <div className="lp-blob-2" />

        <div className="lp-card">
          <div className="lp-logo">
            <Car size={24} color="#2563eb" />
          </div>

          <h1 className="lp-title">ParkirKu</h1>
          <p className="lp-sub">Masuk ke akun Anda untuk melanjutkan</p>

          {error && (
            <div className="lp-error">
              <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2.5" strokeLinecap="round" strokeLinejoin="round">
                <circle cx="12" cy="12" r="10"/>
                <line x1="12" y1="8" x2="12" y2="12"/>
                <line x1="12" y1="16" x2="12.01" y2="16"/>
              </svg>
              {error}
            </div>
          )}

          <form onSubmit={handleSubmit} noValidate>
            <div className="lp-field">
              <label className="lp-label" htmlFor="email">Email</label>
              <input
                id="email"
                type="email"
                className="lp-input"
                placeholder="nama@email.com"
                value={email}
                onChange={(e) => setEmail(e.target.value)}
                required
                autoComplete="email"
              />
            </div>

            <div className="lp-field">
              <label className="lp-label" htmlFor="password">Password</label>
              <input
                id="password"
                type={showPass ? 'text' : 'password'}
                className="lp-input has-eye"
                placeholder="••••••••"
                value={password}
                onChange={(e) => setPassword(e.target.value)}
                required
                autoComplete="current-password"
              />
              <button type="button" className="lp-eye" onClick={() => setShowPass(!showPass)} tabIndex={-1}>
                {showPass ? <EyeOff size={15} /> : <Eye size={15} />}
              </button>
            </div>

            <button type="submit" className="lp-btn" disabled={isLoading}>
              {isLoading
                ? <><Loader2 size={16} className="animate-spin" /> Memproses...</>
                : 'Masuk'}
            </button>
          </form>

          <p className="lp-footer">
            Belum punya akun?
            <a
              href="/register"
              style={{
                color: '#3b82f6',
                textDecoration: 'none',
                fontWeight: 500
              }}
            >
              Daftar di sini
            </a>
          </p>
          <p className="lp-footer">ParkirKu © {new Date().getFullYear()}</p>
        </div>
      </div>
    </>
  );
}
