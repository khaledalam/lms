# Laravel Learning Management System (LMS)

A mini learning management system built with **Laravel 12**, featuring lessons, comments, file attachments, instructor/student roles, email notitfications, analytics dashboards, and API endpoints — fully containerized using **Docker** with SQLite for simplicity.

---

## Features

### Core
- Instructor & Student roles with policies
- Courses, Lessons, Comments, Attachments
- Enrollment system (with EnrollmentConfirmed email notifications)
- Role-based dashboards (instructor/student)
- Chart.js analytics
- Search & filtering (title/content/deep search)

### Technical
- Laravel 12 / PHP 8.3
- Blade templates
- SQLite database (via Docker)
- Horizon + Redis Services
- File caching layer with Cache::remember()
- Telescope & Debugbar integration for local monitoring
- Event-based cache invalidation
- Optimized Eloquent scopes & relationships

---

## Docker Setup (SQLite)

The application is containerized for quick local development using **SQLite** (no MySQL needed).

### First-time setup

```bash
make setup
```
OR
```bash
./scripts/setup_docker_sqlite.sh
```


This will:
1. Build and start the containers.
2. Create an SQLite database file.
3. Install dependencies.
4. Run migrations and seed data.
5. Build frontend assets (if Node container exists).

After setup, visit → [http://localhost:8000](http://localhost:8000)

---

## Manual Commands

```bash
# Start services
docker compose up -d

# Enter PHP container
docker compose exec app bash

# Run Laravel commands
php artisan migrate --seed
php artisan cache:clear
php artisan telescope:install
```

---

## Environment Variables (.env)

Example `.env` snippet:

```env
APP_NAME="LMS"
APP_ENV=local
APP_KEY=
APP_DEBUG=true
APP_URL=http://localhost:8000

DB_CONNECTION=sqlite
DB_DATABASE=/var/www/html/database/database.sqlite

CACHE_STORE=redis
SESSION_DRIVER=file
QUEUE_CONNECTION=redis
```

---

## Performance Optimizations

- Query-level caching with `Cache::remember()`
- Indexed columns on `courses`, `lessons`, `comments`, and pivot tables
- Preloaded relationships (`with`, `withCount`)
- Lazy collection use for large queries
- Dashboard & chart caching
- File cache invalidation through model events

---

## Monitoring

Telescope and Laravel Debugbar are available locally for profiling:

```bash
docker exec -it lms-app php artisan telescope:install
docker exec -it lms-app php artisan migrate

# Emails:
docker compose exec app tail -f storage/logs/laravel.log
```

Access Telescope at: `/telescope`

#### Access Laravel Horizon at:
http://localhost:8000/horizon￼

#### Check Telescope jobs (for debugging) at:
http://localhost:8000/telescope/jobs￼

#### Dispatching Example Job:
```bash
docker compose exec app php artisan tinker
>>> dispatch((new \App\Jobs\SendEnrollmentEmail(\App\Models\User::first(), \App\Models\Course::first()))->onQueue('default'));
```

#### Cleanup horizon redis data:

```bash
docker compose exec redis redis-cli FLUSHALL
```


---

## License

This project is licensed under the MIT License.
