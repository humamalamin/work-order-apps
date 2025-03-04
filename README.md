# Work Order Application Installation Guide

## Requirements

- PHP 8.2 or higher
- Composer
- PostgreSQL
- Redis
- Docker (optional, for Laravel Sail)

## Installation and Setup

### Method 1: Using Laravel Sail (Recommended)

1. Clone the repository:
```bash
git clone <repository-url>
cd work_order
```

2. Copy the environment file:
```bash
cp .env.example .env
```

3. Configure your `.env` file:
```env
DB_CONNECTION=pgsql
DB_HOST=pgsql
DB_PORT=5432
DB_DATABASE=tripship
DB_USERNAME=postgres
DB_PASSWORD=password

OCTANE_SERVER=frankenphp

QUEUE_CONNECTION=redis
QUEUE_DRIVER=redis

MAIL_MAILER=log
MAIL_SCHEME=null
MAIL_HOST=127.0.0.1
MAIL_PORT=2525
MAIL_USERNAME=null
MAIL_PASSWORD=null
MAIL_FROM_ADDRESS="hello@example.com"
MAIL_FROM_NAME="${APP_NAME}"
```

4. Install dependencies using Docker:
```bash
docker run --rm \
    -u "$(id -u):$(id -g)" \
    -v "$(pwd):/var/www/html" \
    -w /var/www/html \
    laravelsail/php82-composer:latest \
    composer install --ignore-platform-reqs
```

5. Start the application using Sail:
```bash
./vendor/bin/sail up -d
```

6. Generate application key:
```bash
./vendor/bin/sail artisan key:generate
```

7. Run migrations and seeders:
```bash
./vendor/bin/sail artisan migrate --seed
```

The application will be available at:
- Main application: http://localhost
- Filament Admin Panel: http://localhost/admin

### Method 2: Manual Installation

1. Clone the repository:
```bash
git clone <repository-url>
cd work_order
```

2. Copy the environment file:
```bash
cp .env.example .env
```

3. Configure your `.env` file with your database credentials and other settings.

4. Install PHP dependencies:
```bash
composer install
```

5. Generate application key:
```bash
php artisan key:generate
```

6. Install Laravel Octane:
```bash
composer require laravel/octane
php artisan octane:install --server=frankenphp
```

7. Run migrations and seeders:
```bash
php artisan migrate --seed
```

8. Start the application:
```bash
php artisan octane:start
```

The application will be available at:
- Main application: http://localhost:8000
- Filament Admin Panel: http://localhost:8000/admin

## Development Commands

### Using Sail

- Start the application: `./vendor/bin/sail up -d`
- Stop the application: `./vendor/bin/sail down`
- Run migrations: `./vendor/bin/sail artisan migrate`
- Run tests: `./vendor/bin/sail test`
- Run npm commands: `./vendor/bin/sail npm <command>`
- Access PostgreSQL: `./vendor/bin/sail psql`
- Access Redis CLI: `./vendor/bin/sail redis`
- Start queue worker: `./vendor/bin/sail artisan queue:work`
- Start queue worker in background: `./vendor/bin/sail artisan queue:work --daemon`
- Monitor queues: `./vendor/bin/sail artisan queue:monitor`

### Manual

- Start Octane server: `php artisan octane:start`
- Run migrations: `php artisan migrate`
- Run tests: `php artisan test`
- Clear cache: `php artisan cache:clear`
- Clear config: `php artisan config:clear`
- Start queue worker: `php artisan queue:work`
- Start queue worker in background: `nohup php artisan queue:work --daemon &`
- Monitor queues: `php artisan queue:monitor`

## Troubleshooting

1. If you encounter permission issues:
```bash
./vendor/bin/sail root-shell
chown -R laravel:laravel /var/www/html/storage
chmod -R 775 /var/www/html/storage
```

2. If the application is not accessible:
- Check if all containers are running: `./vendor/bin/sail ps`
- Check logs: `./vendor/bin/sail logs`
- Ensure ports 80 and 5432 are not in use by other services

3. Database connection issues:
- Verify database credentials in `.env`
- Ensure PostgreSQL container is running
- Try recreating the containers: `./vendor/bin/sail down && ./vendor/bin/sail up -d`
