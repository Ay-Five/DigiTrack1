# 🚗 Digitract
### Instructor Scheduling & Performance Monitoring System for Driving Schools

---

## 📁 Project Structure

```
digitract/
├── index.php                    ← Landing page (role selector)
├── database/
│   └── schema.sql               ← MySQL database schema
├── admin/
│   ├── admin_login.php          ← Admin login page (redesigned)
│   ├── admin_login_process.php  ← Login POST handler (with geo + rate limiting)
│   ├── dashboard.php            ← Admin dashboard (SB Admin 2)
│   └── logout.php               ← Session destroy + redirect
├── instructor/
│   ├── login.php                ← (Step 2 — to be built)
│   └── dashboard.php            ← (Step 2 — to be built)
└── student/
    ├── login.php                ← (Step 3 — to be built)
    └── dashboard.php            ← (Step 3 — to be built)
```

---

## ⚙️ Setup Instructions

### 1. Requirements
- PHP 8.0+
- MySQL 5.7+ / MariaDB 10.4+
- Apache/Nginx (XAMPP, WAMP, or Laragon recommended)
- Optional: Python + Flask service on port 5002 for reverse geocoding

### 2. Database Setup
```sql
-- In phpMyAdmin or MySQL CLI:
SOURCE /path/to/digitract/database/schema.sql;
```

### 3. Configure DB credentials
Edit `admin/admin_login_process.php`:
```php
$host   = 'localhost';
$dbname = 'digitract';
$dbuser = 'root';
$dbpass = '';
```

### 4. Default Admin Login
| Field    | Value                  |
|----------|------------------------|
| Email    | admin@digitract.com    |
| Password | Admin@1234             |

> ⚠️ **Change the default password immediately** via your DB or a password reset script.

### 5. Place project in web root
```
/xampp/htdocs/digitract/     (XAMPP)
/var/www/html/digitract/     (Linux Apache)
```
Then visit: `http://localhost/digitract`

---

## 🗺️ System Flow (from your diagram)

### Admin Flow
Login → Instructor Schedule Viewing → Check Workload Distribution
→ Monitor Compliance → Review Performance Dashboard → Update Records & Reports

### Instructor Flow
Login → View Personal Schedule → Review Performance Feedback
→ Update Certificate Status → Check Completed Lessons

### Student Flow
Login → View Available Slots → Book or Cancel Schedule
→ Submit Feedback → Update Student Record

---

## 🔒 Security Features
- PHP session-based auth with expiry (30 min)
- Session ID regeneration every 5 minutes
- Rate limiting: max 5 login attempts per 10 minutes
- Password hashing via `password_verify()` (bcrypt)
- Geolocation logging per login
- PDO prepared statements (SQL injection protection)

---

## 🛠️ Next Steps (Step-by-step build plan)
- [ ] Step 2: Instructor login + dashboard
- [ ] Step 3: Student login + booking system
- [ ] Step 4: Schedule management (admin)
- [ ] Step 5: Performance monitoring + charts
- [ ] Step 6: Compliance tracker
- [ ] Step 7: Report generation (PDF export)
- [ ] Step 8: Feedback system
