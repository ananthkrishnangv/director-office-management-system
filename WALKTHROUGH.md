# Director Office Management System - Walkthrough

## 📦 What Was Built

A complete **Laravel 11 + Vue 3 + PostgreSQL** system for managing director office operations.

### Backend Components

| Component | Files |
|-----------|-------|
| **Migrations** | 9 database tables (users, appointments, meetings, availability, logbooks, todos, journal, reports, notifications) |
| **Models** | 10 Eloquent models with relationships |
| **Controllers** | 7 controllers (Auth, Dashboard, Appointment, Meeting, Calendar, Todo, Journal) |
| **Services** | 2 services (AvailabilityService, NotificationService) |
| **Mail** | 3 email templates (approved, rejected, reminder) |
| **Routes** | Web + API routes with Sanctum authentication |

### Frontend Components

| Component | Location |
|-----------|----------|
| Login Page | `Pages/Auth/Login.vue` |
| Dashboard | `Pages/Director/Dashboard.vue` |
| Appointments | `Pages/Appointments/Index.vue`, `Create.vue` |
| Calendar | `Pages/Calendar/View.vue` |
| Tasks | `Pages/Todos/Index.vue` |
| Layout | `Components/AppLayout.vue` |

---

## 🚀 How to Run

### Quick Start

```bash
cd "/home/ananthakrishnan/Documents/Director Office Management System/app"

# 1. Create PostgreSQL database
createdb director_office

# 2. Configure environment
cp .env.example .env
# Edit .env with your database credentials

# 3. Generate key and migrate
php artisan key:generate
php artisan migrate --seed

# 4. Start server
php artisan serve

# Visit: http://localhost:8000
```

### Default Credentials

| Role | Email | Password |
|------|-------|----------|
| Director | director@serc.res.in | password |
| PA | pa@serc.res.in | password |
| Admin | admin@serc.res.in | password |

---

## 📁 Project Structure

```
app/
├── app/
│   ├── Http/Controllers/     # 7 controllers
│   ├── Models/               # 10 models
│   ├── Services/             # Business logic
│   ├── Mail/                 # Email templates
│   └── Http/Middleware/      # Inertia middleware
├── database/migrations/      # 9 table migrations
├── resources/
│   ├── js/Pages/             # Vue pages
│   ├── js/Components/        # Shared components
│   ├── css/                  # Tailwind CSS
│   └── views/                # Blade + email templates
├── routes/
│   ├── web.php               # Web routes
│   └── api.php               # API routes
├── .env.example              # Environment template
└── deploy.sh                 # Linux deployment script
```

---

## ✨ Key Features

- **Appointment Management**: Request, approve, reject with notifications
- **Calendar**: Month/week/day views with color-coded meetings
- **Dashboard**: Statistics, today's meetings, pending approvals, ticker notifications
- **Tasks**: Priority-based todo list with due dates
- **Notifications**: Email + WhatsApp (Twilio) support
- **Responsive**: Mobile-friendly Fluent UI design

---

## 🔧 Production Deployment

Use the included `deploy.sh` script for Rocky Linux/RHEL servers:

```bash
sudo bash deploy.sh
```

This will install PHP, PostgreSQL, Nginx, and configure everything automatically.
