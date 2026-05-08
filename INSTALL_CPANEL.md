# ClinixAI Hostinger / cPanel Checklist

## 1. Upload the project

Upload the full `neuralhub` folder contents to your subdomain directory.

Example live path:

`/home/USERNAME/domains/yourdomain.com/public_html/neuralhub`

## 2. If your subdomain cannot point to `/public`

Use the root-folder workaround already included in this project:

- root `index.php`
- root `.htaccess`
- root `favicon.ico`
- root `robots.txt`

Those files let Laravel run from the subdomain folder itself when the host cannot change document root after creation.

## 3. Create MySQL database

- Create database
- Create user
- Assign user with full privileges

## 4. Configure `.env`

Copy `.env.example` to `.env` and set:

```env
APP_NAME=ClinixAI
APP_ENV=production
APP_DEBUG=false
APP_URL=https://neuralhub.carbonrepro.com

DB_CONNECTION=mysql
DB_HOST=localhost
DB_PORT=3306
DB_DATABASE=your_database_name
DB_USERNAME=your_database_user
DB_PASSWORD=your_database_password

CACHE_STORE=file
SESSION_DRIVER=file
QUEUE_CONNECTION=database
FILESYSTEM_DISK=local

OPENAI_API_KEY=your_openai_api_key
OPENAI_BASE_URL=https://api.openai.com/v1
OPENAI_MODEL=gpt-4o-mini

DAILY_API_KEY=your_daily_api_key
DAILY_BASE_URL=https://api.daily.co/v1
DAILY_DOMAIN=your-team.daily.co
```

## 5. Install dependencies

```bash
composer install --no-dev --optimize-autoloader
php artisan key:generate
php artisan migrate:fresh --seed
```

## 6. Storage link workaround

If `php artisan storage:link` fails because `exec()` is disabled:

```bash
rm -rf public/storage
ln -s /full/project/path/storage/app/public public/storage
```

## 7. Clear caches

```bash
php artisan optimize:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear
```

## 8. Seeded logins

- Admin: `admin@clinixai.com` / `password123`
- Doctor sample: `doctor@clinixai.test` / `Password123!`
- Patient sample: `patient@clinixai.test` / `Password123!`

## 9. Shared hosting note

This Blade-first version does not require `npm` or a frontend build step to render the portal.
