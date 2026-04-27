# Equipment Borrowing System

A school equipment inventory and borrowing system built with Laravel 11 (backend) and Vue 3 (frontend).

---

## Local Setup

### Prerequisites

**PHP 8.2+**
```bash
sudo apt update
sudo apt install php8.2 php8.2-cli php8.2-mbstring php8.2-xml php8.2-curl php8.2-mysql php8.2-zip unzip -y
php -v
```

**Composer**
```bash
curl -sS https://getcomposer.org/installer | php
sudo mv composer.phar /usr/local/bin/composer
composer -V
```

**MySQL**
```bash
sudo apt install mysql-server -y
sudo systemctl start mysql
sudo mysql_secure_installation
```

**Node.js 18+**
```bash
curl -fsSL https://deb.nodesource.com/setup_18.x | sudo -E bash -
sudo apt install nodejs -y
node -v
npm -v
```

---

### Step 1 — Clone the repository

```bash
cd ~
git clone https://github.com/D1ZEASE420/equipment.git
cd equipment
```

---

### Step 2 — Create the MySQL database

```bash
sudo mysql -u root -p
```

Inside the MySQL shell:
```sql
CREATE DATABASE equipment_system;
EXIT;
```

---

### Step 3 — Set up the backend

```bash
cd ~/equipment/backend
```

Install PHP dependencies:
```bash
composer install
```

Create the environment file:
```bash
cp .env.example .env
```

Open `.env` and update the database credentials:
```
DB_DATABASE=equipment_system
DB_USERNAME=root
DB_PASSWORD=your_mysql_password
```

Generate the app key:
```bash
php artisan key:generate
```

Run migrations and seed the database:
```bash
php artisan migrate --seed
```

Set storage permissions:
```bash
chmod -R 775 storage bootstrap/cache
```

Start the backend server:
```bash
php artisan serve
```

Backend runs at **http://localhost:8000** — keep this terminal open.

---

### Step 4 — Set up the frontend

Open a **new terminal** and run:

```bash
cd ~/equipment/frontend
cp .env.example .env
npm install
npm run dev
```

Frontend runs at **http://localhost:5173** — keep this terminal open too.

---

### Step 5 — Open in browser

Go to **http://localhost:5173** and log in with the seeded accounts:

| Role | Email | Password |
|---|---|---|
| Admin | admin@school.edu | password |
| Student | student@school.edu | password |

---

## Running the project

You need **both servers running simultaneously** in separate terminals.

| | Directory | Command | URL |
|---|---|---|---|
| Backend | `backend/` | `php artisan serve` | http://localhost:8000 |
| Frontend | `frontend/` | `npm run dev` | http://localhost:5173 |

---

## Troubleshooting

**Class not found error**
```bash
composer dump-autoload
```

**MySQL connection refused**
```bash
sudo systemctl start mysql
```

**Port 8000 already in use**
```bash
php artisan serve --port=8001
```
Then update `frontend/.env`: `VITE_API_URL=http://localhost:8001/api`
