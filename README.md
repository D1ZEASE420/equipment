# Disainimajakas — Equipment Management System

A web application for managing and tracking equipment loans. Built with Laravel 11, Vue 3, Vite, and Tailwind CSS.

---

## Requirements

| Software | Version |
|----------|---------|
| PHP | 8.2+ |
| Composer | 2+ |
| Node.js | 18+ |
| npm | 9+ |
| Git | any |

---

## Local Setup

### 1. Clone the repository

```bash
git clone https://github.com/D1ZEASE420/equipment.git
cd equipment
```

---

### 2. Backend

```bash
cd backend
composer install
cp .env.example .env
php artisan key:generate
```

Open `backend/.env` and set the database connection to SQLite:

```env
DB_CONNECTION=sqlite
```

Remove or comment out the MySQL lines:

```env
# DB_HOST=127.0.0.1
# DB_PORT=3306
# DB_DATABASE=equipment_system
# DB_USERNAME=root
# DB_PASSWORD=
```

Create the database file and run migrations with seed data:

```bash
touch database/database.sqlite
php artisan migrate --seed
```

Start the development server:

```bash
php artisan serve
```

Backend runs at `http://localhost:8000`

---

### 3. Frontend

Open a new terminal:

```bash
cd frontend
npm install
```

Create the `.env` file:

```bash
echo "VITE_API_URL=http://localhost:8000/api" > .env
```

Start the development server:

```bash
npm run dev
```

Frontend runs at `http://localhost:5173`

---

## Default Login Credentials

| Role | Email | Password |
|------|-------|----------|
| Admin | `admin@school.edu` | `password` |
| Student | `student@school.edu` | `password` |

The seeder automatically adds 50+ devices (cameras, lenses, tripods, memory cards, etc.).

---

## Features

- **Batch borrowing** — borrow multiple devices at once with a cart
- **Return** — by barcode or serial number
- **Email notifications** — overdue reminders sent to students (requires SMTP config)
- **Admin panel** — add, edit, delete devices, CSV export
- **Student management** — borrowing history and contact info
- **Dashboard** — overview of active and overdue loans
- **Bilingual** — Estonian / English toggle
- **Dark / light mode**

---

## Production Build

```bash
cd frontend
npm run build
```

Output goes to `frontend/dist/`.

---

## Email Notifications (optional)

Fill in the SMTP settings in `backend/.env`:

```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.example.com
MAIL_PORT=587
MAIL_USERNAME=your@email.com
MAIL_PASSWORD=yourpassword
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS="noreply@disainimajakas.ee"
MAIL_FROM_NAME="Disainimajakas"
```

---

## Project Structure

```
equipment/
├── backend/                        # Laravel 11 REST API
│   ├── app/
│   │   ├── Http/Controllers/Api/
│   │   └── Models/
│   ├── database/
│   │   ├── migrations/
│   │   └── seeders/
│   └── routes/api.php
└── frontend/                       # Vue 3 + Vite SPA
    ├── public/                     # Favicon and static files
    └── src/
        ├── api/
        ├── components/
        ├── stores/
        └── views/
```
