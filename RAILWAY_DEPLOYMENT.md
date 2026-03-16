# 🚀 Railway Deployment Guide

## 📋 Prerequisites
- Railway account
- GitHub repository
- Laravel project ready

## 🛠️ Railway Setup

### 1. Railway Configuration
File: `railway.toml` (sudah dibuat)

### 2. Environment Variables
Set di Railway dashboard:
```
APP_ENV=production
APP_DEBUG=false
APP_URL=https://your-app-name.railway.app
DB_CONNECTION=mysql
DB_HOST=containers.railway.app
DB_PORT=3306
DB_DATABASE=railway
DB_USERNAME=railway
DB_PASSWORD=password_dari_railway
```

### 3. Deployment Commands
```bash
# Build
composer install --no-dev --optimize-autoloader
php artisan config:cache
php artisan route:cache

# Migrate
php artisan migrate --force
php artisan db:seed --class=DatabaseSeeder --force

# Optimize
php artisan config:clear
php artisan route:clear
php artisan view:clear
```

## 🎯 Access Points

### Filament Admin Panel
- URL: `https://your-app-name.railway.app/admin`
- Login: Gunakan user yang sudah ada

### Public Pages
- Booking: `https://your-app-name.railway.app/booking`
- My Bookings: `https://your-app-name.railway.app/my-bookings`
- QR Codes: `https://your-app-name.railway.app/qr-codes`

### API Endpoints
- Base: `https://your-app-name.railway.app/api`
- Documentation: Swagger di `/api/documentation`

### Health Check
- Simple: `https://your-app-name.railway.app/up` (Laravel default)
- Detailed: `https://your-app-name.railway.app/health`
- Ping: `https://your-app-name.railway.app/ping`

## 🔧 Local Development vs Production

### Routes Setup
```php
// routes/web.php
Route::get('/', function () {
    return redirect('/booking'); // Public landing
});

// Filament otomatis handle /admin
```

### Frontend Configuration
```typescript
// Frontend API base URL
const API_BASE_URL = process.env.NEXT_PUBLIC_API_URL || 'https://your-app-name.railway.app/api';
```

## 🚨 Troubleshooting

### Common Issues
1. **404 Errors**: Check `APP_URL` di Railway
2. **Database**: Verify connection string
3. **Permissions**: Jalankan `php artisan storage:link`
4. **Cache**: Clear cache dengan `php artisan optimize:clear`

### Health Check Issues
```bash
# Test health endpoints
curl https://your-app.railway.app/up
curl https://your-app.railway.app/ping
curl https://your-app.railway.app/health

# Common fixes:
1. Check database connection in Railway dashboard
2. Verify APP_URL matches Railway domain
3. Ensure migrations ran successfully
4. Check logs in Railway dashboard
```

### Logs Check
```bash
# Railway logs
railway logs

# Local troubleshooting
php artisan log:clear
tail -f storage/logs/laravel.log
```

## 📱 Mobile App Integration

### API Base URL
```
Development: http://localhost:8000/api
Production: https://your-app-name.railway.app/api
```

### QR Code Scanning
- Mobile apps scan QR dari booking
- Check-in endpoint: `POST /api/booking-reservation/check-in`
- Include Authorization header

## 🎉 Deployment Commands

### Push to Railway
```bash
# Connect repo ke Railway
git remote add railway https://railway.com/your-project.git
git push railway main

# Atau deploy dari GitHub
# Connect GitHub repo di Railway dashboard
```

## 🔐 Security Notes

### Production Checklist
- [ ] `APP_DEBUG=false`
- [ ] `APP_ENV=production`
- [ ] Database credentials secure
- [ ] HTTPS enabled
- [ ] CORS configured properly
- [ ] Rate limiting enabled
- [ ] Input validation active

### Admin Access
- Gunakan email/password yang sudah ada
- Atau create super admin: `php artisan make:filament-user`
- Role assignment: Owner/Admin/Petugas
