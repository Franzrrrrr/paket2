# Deployment Guide

## Prerequisites

- PHP 8.2+
- Node.js 18+
- Composer
- MySQL 8.0+ (for production)
- Laravel 11+

## Local Development

1. **Clone and Setup**
   ```bash
   git clone <repository-url>
   cd UkkPaket2Frans
   composer install
   cd frontend && npm install
   ```

2. **Environment Setup**
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

3. **Database Setup**
   ```bash
   php artisan migrate
   php artisan db:seed
   ```

4. **Run Development Server**
   ```bash
   npm run dev
   ```

## Production Deployment

### Backend (Laravel)

1. **Server Requirements**
   - PHP 8.2+ with required extensions
   - MySQL 8.0+ or MariaDB
   - Composer
   - Web server (Nginx/Apache)

2. **Database Configuration**
   ```bash
   # Update .env for production
   DB_CONNECTION=mysql
   DB_HOST=your-db-host
   DB_DATABASE=parking_app
   DB_USERNAME=your-db-user
   DB_PASSWORD=your-db-password
   ```

3. **Deploy Commands**
   ```bash
   composer install --no-dev --optimize-autoloader
   php artisan config:cache
   php artisan route:cache
   php artisan view:cache
   php artisan migrate --force
   php artisan storage:link
   ```

4. **Queue Setup (Optional)**
   ```bash
   php artisan queue:work --daemon
   ```

### Frontend (Next.js)

1. **Build for Production**
   ```bash
   cd frontend
   npm run build
   ```

2. **Environment Variables**
   ```bash
   NEXT_PUBLIC_API_URL=https://your-backend-domain.com/api
   ```

3. **Deploy to Vercel**
   ```bash
   # Install Vercel CLI
   npm i -g vercel
   
   # Deploy
   vercel --prod
   ```

## Vercel Configuration

The `vercel.json` file handles:

- Next.js build configuration
- API proxy to backend
- Environment variables

Update the backend URL in `vercel.json`:

```json
{
  "routes": [
    {
      "src": "/api/(.*)",
      "dest": "https://your-backend-domain.com/api/$1"
    }
  ],
  "env": {
    "NEXT_PUBLIC_API_URL": "https://your-backend-domain.com/api"
  }
}
```

## Database Issues

### MySQL Native Password Error

If you encounter `SQLSTATE[HY000] [1524] Plugin 'mysql_native_password' is not loaded`:

1. **Update MySQL Configuration** (already configured in `config/database.php`)
2. **Alternative: Create user with caching_sha2_password**
   ```sql
   CREATE USER 'laravel'@'%' IDENTIFIED WITH caching_sha2_password BY 'password';
   GRANT ALL PRIVILEGES ON parking_app.* TO 'laravel'@'%';
   FLUSH PRIVILEGES;
   ```

## Troubleshooting

### Next.js Build Errors

**Error: `useSearchParams() should be wrapped in a suspense boundary`**

Solution: Components using `useSearchParams` are wrapped in `<Suspense>` boundaries (already implemented).

### Session Issues

Ensure session driver is configured correctly:

```env
SESSION_DRIVER=database
SESSION_LIFETIME=120
```

Run migrations to create sessions table:

```bash
php artisan migrate
```

### CORS Issues

Update `config/cors.php` for production:

```php
'paths' => ['api/*', 'sanctum/csrf-cookie'],
'allowed_origins' => ['https://your-frontend-domain.com'],
```

## Monitoring

- **Laravel Logs**: `storage/logs/laravel.log`
- **Vercel Logs**: Vercel dashboard
- **Database**: Monitor connection pool and slow queries

## Security

1. **Environment Variables**: Never commit `.env` files
2. **HTTPS**: Enable SSL in production
3. **Firewall**: Restrict database access
4. **Updates**: Keep dependencies updated

## Performance

1. **Caching**: Enable Laravel cache
2. **CDN**: Use for static assets
3. **Database**: Add indexes for frequently queried columns
4. **Images**: Optimize and use WebP format
