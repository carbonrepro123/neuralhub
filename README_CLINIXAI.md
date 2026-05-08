# ClinixAI Laravel Portal MVP

ClinixAI is a Laravel portal MVP for clinics, doctors, staff, and patients.

## What This Build Includes

- Laravel portal foundation with Inertia + Vue page structure
- Automatic database creation through Laravel migrations
- Seed data for roles, admin, clinic, doctor, patient, AI agents, and compliance references
- Secure patient management scaffolding
- Daily.co video room provider abstraction
- Queued AI tasks stored inside Laravel
- Compliance Bot scaffolding with reminder scheduling
- Audit log recording for important actions

## Default Admin Login

- Email: `admin@clinixai.com`
- Password: `password123`

Change this password immediately after first login.

## Required Server Stack

- PHP 8.2 or newer
- Composer
- MySQL 8+
- Node.js 20+ for frontend assets
- Web server: Apache or Nginx
- Redis optional but recommended for queues

## Important Database Note

Laravel migrations create the tables automatically, but you must still create an empty MySQL database and database user first in your hosting panel.

After that, this one command creates the schema and sample data:

```bash
php artisan migrate --seed
```

## Quick Install

1. Upload the `backend` folder contents to your Laravel hosting location for the subdomain.
2. Create an empty MySQL database in cPanel or your hosting dashboard.
3. Copy `.env.example` to `.env`.
4. Put your database details into `.env`.
5. Run:

```bash
composer install
php artisan key:generate
php artisan storage:link
php artisan migrate --seed
```

6. Build frontend assets:

```bash
npm install
npm run build
```

7. If using queues:

```bash
php artisan queue:work
```

## Recommended First-Time Commands

```bash
composer install
cp .env.example .env
php artisan key:generate
php artisan storage:link
php artisan migrate --seed
npm install
npm run build
```

## Queue Setup

For local or VPS:

```bash
php artisan queue:work
```

For cPanel without Supervisor, use a scheduled task or background runner if supported.

## Cron Setup

Add a cron job to run Laravel scheduler every minute:

```bash
* * * * * php /home/USERNAME/path-to-subdomain/artisan schedule:run >> /dev/null 2>&1
```

This is needed for future reminder dispatching and scheduled compliance checks.

## Storage Setup

Run:

```bash
php artisan storage:link
```

Private medical files should remain in protected storage. Public symbolic links should only expose safe non-PHI assets.

## Daily.co Setup

In `.env`:

```env
DAILY_API_KEY=your_daily_api_key
DAILY_BASE_URL=https://api.daily.co/v1
DAILY_DOMAIN=your-team.daily.co
```

## OpenAI Setup

In `.env`:

```env
OPENAI_API_KEY=your_openai_api_key
OPENAI_BASE_URL=https://api.openai.com/v1
OPENAI_MODEL=gpt-4o-mini
```

If no OpenAI key is configured, the Laravel AI service falls back to safe MVP mock output so the portal can still be demonstrated.

## Automatic Database Setup

Database setup is automatic after the database exists and your `.env` is correct.

Run:

```bash
php artisan migrate --seed
```

This creates:

- tables
- foreign keys
- default roles
- seeded admin user
- sample clinic
- sample doctor
- sample patient
- sample appointment
- sample compliance record
- AI agent reference rows

## cPanel / Subdomain Checklist

See [INSTALL_CPANEL.md](/Users/umertatla/Desktop/Code%20js/clinixai/backend/INSTALL_CPANEL.md).

## Main Portal Areas

- Dashboard
- Patients
- Appointments and video consults
- AI agent activity
- Compliance tracking
- Audit logs

## Security Notes

- Role-aware access patterns are scaffolded
- Sensitive actions are written to `audit_logs`
- Document access should use signed URLs
- AI outputs are stored with doctor review required
- Do not expose raw AI output to patients until approved

## If Your Hosting Does Not Support Shell Access

Use:

- the SQL bootstrap file at [database/sql/clinixai_mysql_bootstrap.sql](/Users/umertatla/Desktop/Code%20js/clinixai/backend/database/sql/clinixai_mysql_bootstrap.sql) for manual schema import
- phpMyAdmin for database import

But the preferred method is still:

```bash
php artisan migrate --seed
```
