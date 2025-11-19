# TB-MAS (Tuberculosis Monitoring & Adherence System)

### **Technology Stack**
- **PHP 8+** (PDO, no frameworks)
- **MySQL / MariaDB**
- **Bootstrap 5 via CDN (no Node, no npm)**
- **Composer packages:**
  - PHPMailer
  - Dompdf
- **Cron jobs for reminders**

### **Project Goal**
A privacy‑preserving, anonymized TB patient monitoring system for Baguio City CESU.  
No personal names are ever stored. All entities use codes only (`patient_code`, `contact_code`).

---
## 🚀 Features
### **Patients**
- Anonymous record system (no names, no identifying info beyond barangay + codes)
- Optional patient accounts (only if email exists)
- Email verification + required first‑time password reset
- View medication summary, referrals (PDF), reminders

### **Health Workers**
- Restricted to their barangay
- View/manage patients, contacts, referrals
- Track upcoming follow-ups and reminders
- CSV export of anonymized patients

### **Super Admin (CESU)**
- System-wide CRUD
- User management (assign patient email to create accounts)
- Manage imports, logs, all notifications
- Full audit logs

---
## 📁 Folder Structure
```
project/
  config/db.php
  public/ (public pages)
  src/ (controllers, models, helpers, middleware)
  vendor/ (composer deps)
  cron/send_notifications.php
```

---
## 🗄️ Database Setup
Import the full SQL schema located in **Canvas 1**.  
Make sure the database uses:
- `utf8mb4`
- `InnoDB`

Create a super admin manually:
```sql
INSERT INTO users (email, password_hash, role, is_verified, password_reset_required)
VALUES ('admin@example.com', '{HASH}', 'super_admin', 1, 0);
```
Generate `{HASH}`:
```php
php -r "echo password_hash('yourpass', PASSWORD_DEFAULT);"
```

---
## 🔧 Environment Configuration
Create a private, git‑ignored file:
```
project/config/env.php
```
Example:
```php
<?php
const DB_HOST = 'localhost';
const DB_USER = 'root';
const DB_PASS = '';
const DB_NAME = 'tb_mas';

const SMTP_HOST = 'smtp.gmail.com';
const SMTP_USER = 'xxx@gmail.com';
const SMTP_PASS = 'app-password';
const SMTP_PORT = 587;

const BASE_URL = 'https://yourdomain.com';
```

---
## 🔄 CSV Import Template
The system only accepts CSVs with **exactly these columns**:
```
patient_code,email,age,sex,barangay,contact_number,tb_case_number,bacteriological_status,anatomical_site,drug_susceptibility,treatment_history
```
If a CSV contains *name fields*, they are ignored.

NOTE: Only patients with an email get accounts created.

---
## 📤 CSV Export (Anonymized Only)
Exports patients with **no emails and no names**.  
Used for CESU syncing with Google Sheets.

---
## 📨 Email System
PHPMailer is used for:
- Account verification
- Reminder notifications

Cron script: `/cron/send_notifications.php`  
Run every minute:
```
* * * * * /usr/bin/php /path/to/project/cron/send_notifications.php
```

---
## 🧾 Referral PDFs (Form 7)
Generated using Dompdf via `PDFHelper`.  
Forms are not fillable — they are rendered and downloaded.

---
## 🪪 Authentication & Security
- Password hashing via `password_hash()`
- All DB access uses prepared PDO statements
- Session regeneration on login
- HTTPS strongly recommended
- Strict role‑based access
- Patient cannot see any other patient
- Health workers limited to their barangay
- Logs generated for all CRUD actions

---
## 📱 Responsive UI
- Bootstrap 5 grid
- Mobile‑first design
- Accessible color/contrast
- Consistent card-based layout

---
## 🚦 Developer Setup
### **Step 1 — Clone the repo**
```
git clone https://github.com/your-org/tb-mas.git
cd tb-mas
```

### **Step 2 — Install dependencies**
```
composer install
```

### **Step 3 — Configure environment file**
Create:
```
project/config/env.php
```
Contents:
```php
<?php
const DB_HOST = 'localhost';
const DB_USER = 'root';
const DB_PASS = '';
const DB_NAME = 'tb_mas';

const SMTP_HOST = 'smtp.gmail.com';
const SMTP_USER = 'your-email@gmail.com';
const SMTP_PASS = 'your-app-password';
const SMTP_PORT = 587;

const BASE_URL = 'https://yourdomain.com';
```
**Never commit env.php to GitHub.**

### **Step 4 — Import database**
Use phpMyAdmin or CLI to execute full schema.

### **Step 5 — Create super admin**
```
INSERT INTO users (email, password_hash, role, is_verified, password_reset_required)
VALUES ('admin@example.com', '{HASH}', 'super_admin', 1, 0);
```
Generate `{HASH}`:
```
php -r "echo password_hash('mypassword', PASSWORD_DEFAULT);"
```

### **Step 6 — Set `public/` as Document Root**
For Apache:
```
DocumentRoot /var/www/tb-mas/public
```

### **Step 7 — Configure Cron**
```
* * * * * /usr/bin/php /var/www/tb-mas/cron/send_notifications.php >/dev/null 2>&1

---
## 🧪 Acceptance Test Checklist
- Database connects
- Create admin > login successful
- Patient creation without names works
- CSV import correctly creates users only for emails
- Referral PDF generates
- Notifications send via cron
- Patient dashboard shows only self
- Health worker restricted by barangay
- Full audit logs appear correctly

---
## 📚 About Privacy
The system **never** stores or displays:
- full names
- nicknames
- addresses beyond barangay
- any other personally identifiable information

All patients identified only by **patient_code**.
All contacts identified only by **contact_code**.

---
End of README.