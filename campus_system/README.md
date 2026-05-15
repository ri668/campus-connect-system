# Campus Database Management System
## Setup Instructions

### 1. Import Database
Run the SQL file in phpMyAdmin or MySQL CLI:
```
mysql -u root -p < setup.sql
```

### 2. Place Files
Copy all files into your web server root (e.g., `htdocs/campus_system/`).

### 3. Configure Database
Edit `database.php` if your MySQL credentials differ:
- host: localhost
- dbname: finals_lab1
- username: root
- password: (empty by default)

---

## LOGIN CREDENTIALS

### Admin
| Username | Password   |
|----------|------------|
| admin    | Admin@2025 |

### Campus Users
| Username                        | Password       |
|---------------------------------|----------------|
| UNIVERSITY OF BAGUIO            | UBConnect      |
| SAINT LOUIS UNIVERSITY          | SLUConnect     |
| UNIVERSITY OF PHILIPPINES       | UPConnect      |
| BAGUIO CENTRAL UNIVERSITY       | BCUConnect     |
| PINES CITY COLLEGES             | PCCConnect     |
| PINES CITY NATIONAL HIGH SCHOOL | PCNHSConnect   |
| GUISAD VALLEY                   | GVNHSonnect    |
| BENGUET NATIONAL HIGH SCHOOL    | BNHSConnect    |
| EASTER COLLEGES                 | ECConnect      |
| ACATECH AVIATION COLLEGE        | AACConnect     |
| UNIVERSITY OF CORDILLERAS       | UCConnect      |
| QUEZON HILL NATIONAL HIGH SCHOOL| QHNHSConnect   |
| BENGUET STATE UNIVERSITY        | BSUConnect     |
| OTHER CAMPUS                    | OCConnect      |

---

## Features

### User Dashboard
- View & manage own campus records only
- Add, Edit, Delete (soft), Restore records
- Search across all fields
- Analytics (charts + CSV export) for own campus
- Send notification/message to Admin
- Receive notifications from Admin via bell icon

### Admin Dashboard
- View ALL campus records with campus filter dropdown
- Add, Edit, Delete records from any campus
- Analytics for all campuses combined + per-campus counts
- Send notifications to selected campuses (checkbox) with admin note
- Recycle bin showing all campus deleted records
- Bell icon showing all incoming user notifications

---

## File List
- `login.php` — Login page
- `logout.php` — Session logout
- `database.php` — DB connection + auth helpers + user list
- `style.css` — Full design system
- `index.php` — User dashboard
- `add.php` — Add record (user)
- `edit.php` — Edit record
- `delete.php` — Soft delete
- `restore.php` — Restore from recycle bin
- `recycle_bin.php` — Recycle bin (user + admin)
- `search.php` — Search API
- `analytics.php` — User analytics
- `send_notification.php` — User → Admin notification
- `mark_read.php` — Mark notifications read
- `admin_dashboard.php` — Admin overview
- `admin_records.php` — Admin all records + campus filter
- `admin_analytics.php` — Admin analytics (all campuses)
- `admin_notify.php` — Admin send notification to campuses
- `setup.sql` — Database schema
- `background.jpg` — Background image
