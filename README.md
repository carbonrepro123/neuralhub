# ClinixAI Blade Portal

ClinixAI is a Laravel-based healthcare SaaS portal built for shared hosting and Blade-first deployment.

## Included MVP Modules

- Secure login and registration
- Role-aware dashboards
- Patient management
- Report/document upload
- AI task review
- Compliance Bot tracking
- Appointment scheduling
- Daily.co room generation flow
- Consultation side panel

## Roles

- Super Admin
- Clinic Admin
- Doctor
- Staff
- Patient

## Seeded Access

- Admin: `admin@clinixai.com` / `password123`
- Doctor sample: `doctor@clinixai.test` / `Password123!`
- Patient sample: `patient@clinixai.test` / `Password123!`

## Shared Hosting Notes

This project is adapted for shared hosting where:

- PHP works
- Composer works
- MySQL works
- Node/npm may not be available

The Blade portal does not require a frontend build step to function.

## Important Hostinger Path Note

If the subdomain cannot point to Laravel `public/`, the project can still run from the subdomain root by:

- copying `public/index.php` to root `index.php`
- copying `public/.htaccess` to root `.htaccess`
- updating root `index.php` paths to use local `vendor` and `bootstrap`

The current project folder already reflects that workaround.

## Install

```bash
composer install --no-dev --optimize-autoloader
cp .env.example .env
php artisan key:generate
php artisan migrate:fresh --seed
```

If `php artisan storage:link` fails because `exec()` is disabled, create the symlink manually:

```bash
ln -s /full/project/path/storage/app/public /full/project/path/public/storage
```

## Recommended `.env`

```env
APP_NAME=ClinixAI
APP_ENV=production
APP_DEBUG=false
APP_URL=https://neuralhub.carbonrepro.com

DB_CONNECTION=mysql
DB_HOST=localhost
DB_PORT=3306
DB_DATABASE=your_db_name
DB_USERNAME=your_db_user
DB_PASSWORD=your_db_password

CACHE_STORE=file
SESSION_DRIVER=file
QUEUE_CONNECTION=database
FILESYSTEM_DISK=local

OPENAI_API_KEY=your_openai_key
OPENAI_BASE_URL=https://api.openai.com/v1
OPENAI_MODEL=gpt-4o-mini

DAILY_API_KEY=your_daily_key
DAILY_BASE_URL=https://api.daily.co/v1
DAILY_DOMAIN=your-team.daily.co
```

## Queue / AI Behavior

For shared hosting, the Blade portal favors synchronous doctor-facing AI actions where helpful, so core portal actions remain usable even if a queue worker is not continuously running.

## Current Web Areas

- `/` public portal landing
- `/login` login
- `/register` registration
- `/dashboard` role-aware dashboard
- `/patients`
- `/appointments`
- `/compliance`
- `/ai-activity`

## Next Expansion Areas

- richer RBAC enforcement by route/action
- doctor/staff-specific create flows
- secure signed file streaming
- transcript ingestion
- advanced SOAP review workflow
- Daily token join flow in embedded UI
- reminder cron commands
