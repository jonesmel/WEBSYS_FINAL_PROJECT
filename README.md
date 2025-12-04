# Tuberculosis (TB) Management System

A comprehensive web-based Tuberculosis case management system designed to streamline patient care, treatment monitoring, contact tracing, and health worker coordination in TB control programs.

## 📋 Table of Contents
- [Features](#features)
- [Tech Stack](#tech-stack)
- [Prerequisites](#prerequisites)
- [Installation](#installation)
- [Configuration](#configuration)
- [Database Setup](#database-setup)
- [Usage](#usage)
- [User Roles](#user-roles)
- [Database Schema](#database-schema)
- [Automated Processes](#automated-processes)
- [API Documentation](#api-documentation)
- [Contributing](#contributing)
- [License](#license)

## ✨ Features

### Core Functionality
- **Multi-role User Management**: Super Admin, Health Workers, and Patient accounts
- **Patient Registration & Management**: Comprehensive patient profiles with TB case tracking
- **Treatment Monitoring**: Medication compliance tracking and treatment outcome recording
- **Contact Tracing**: Automated contact identification and monitoring system
- **Referral System**: Seamless patient referrals between healthcare facilities
- **Automated Notifications**: Email alerts for medication compliance and follow-ups
- **Real-time Dashboards**: Role-specific dashboards with key metrics and alerts

### Advanced Features
- **AJAX-powered Search & Filtering**: Dynamic patient filtering with instant results
- **Automated Follow-up System**: Automatic detection of missed medications and overdue treatments
- **Comprehensive Reporting**: Export patient data and analytics to CSV/PDF
- **Audit Logging**: Complete activity tracking for compliance and security
- **CRON Automation**: Scheduled email notifications and compliance monitoring
- **Data Import/Export**: Bulk import of patient data with CSV support

### Health Administrative Features
- **Barangay-based Case Management**: Location-specific patient tracking
- **Treatment Success Rate Calculation**: Automated analytics for program effectiveness
- **Referral Tracking**: End-to-end referral management with status updates
- **Medication Adherence Alerts**: Automated notifications for missed doses
- **Contact Conversion**: Convert contacts to patients when diagnosed

## 🛠 Tech Stack

### Backend
- **PHP 7.4+**: Server-side scripting and business logic
- **Composer**: PHP dependency management
- **MySQL/MariaDB**: Relational database management

### Frontend
- **HTML5/CSS3**: Semantic markup and responsive styling
- **JavaScript (ES6+)**: Client-side interactions and AJAX
- **Bootstrap 5**: Responsive design framework
- **jQuery**: DOM manipulation and AJAX requests

### Infrastructure
- **Apache/Nginx**: Web server
- **CRON**: Scheduled task automation
- **PHPMailer**: Email service integration
- **TCPDF**: PDF generation

## 📋 Prerequisites

- **PHP 7.4 or higher**
- **MySQL 5.7+ or MariaDB 10.0+**
- **Composer** (PHP dependency manager)
- **Apache or Nginx web server**
- **SMTP server** for email notifications (optional, but recommended)

## 🚀 Installation

### 1. Clone the Repository
```bash
git clone https://github.com/jonesmel/WEBSYS_FINAL_PROJECT.git
cd WEBSYS_FINAL_PROJECT
```

### 2. Install PHP Dependencies
```bash
composer install
```

### 3. Create Database
```sql
CREATE DATABASE tb_mas;
```

### 4. Configure Environment
Create `.env` file or update `config/config.php` with your database credentials and email settings.

### 5. Import Database Schema
```bash
mysql -u username -p tb_mas < database/tb_mas.sql
```

### 6. Set Up Web Server
Configure your web server to serve the `public/` directory as the document root.

Example Apache virtual host:
```
<VirtualHost *:80>
    ServerName tb-management.local
    DocumentRoot /path/to/tb-management-system/public

    <Directory /path/to/tb-management-system/public>
        AllowOverride All
        Require all granted
        Options Indexes FollowSymLinks
    </Directory>
</VirtualHost>
```

### 7. Set File Permissions
```bash
chmod -R 755 storage/
chmod -R 755 public/uploads/
```

### 8. Set Up CRON Jobs (Optional)
```bash
# Add to crontab for automated processes
* * * * * php /path/to/cron/send_notifications.php
```

## ⚙ Configuration

### Database Configuration
Update `config/db.php` with your database credentials:

```php
define('DB_HOST', 'localhost');
define('DB_NAME', 'tb_mas');
define('DB_USER', 'your_db_user');
define('DB_PASS', 'your_db_password');
```

### Email Configuration
Update `config/email.php` with your SMTP settings:

```php
define('SMTP_HOST', 'your_smtp_host');
define('SMTP_PORT', '587');
define('SMTP_USER', 'your_email@domain.com');
define('SMTP_PASS', 'your_email_password');
```

### Barangay Configuration
Add/update barangay names in `config/barangays.txt` for location-based filtering.

## 📊 Database Setup

The system uses the following main entities:
- **patients**: TB patient information and treatment details
- **users**: System users (admin, health workers, patients)
- **medications**: Treatment regimens and compliance tracking
- **contacts**: Contact tracing for exposed individuals
- **referrals**: Patient referrals between facilities
- **notifications**: Automated alerts and communications

See `database/tb_mas.sql` for complete schema.

## 🎯 Usage

### Access the Application
Navigate to your configured domain (e.g., `http://tb-mas.local`)

### Initial Setup
1. The database seed creates a default super admin
2. Log in with the seeded admin credentials
3. Configure barangays and health worker accounts

### Daily Operations
- **Super Admin**: Oversee all operations, manage users, view analytics
- **Health Workers**: Manage patients in assigned barangays, track medications, handle referrals
- **Patients**: View treatment progress, receive notifications, access resources

## 👥 User Roles

### 1. Super Administrator
- Full system access and configuration
- User management and permissions
- System analytics and reporting
- Barangay assignment and oversight
- Data import/export functionality

### 2. Health Worker
- Assigned to specific barangay(s)
- Patient management within assigned areas
- Medication compliance monitoring
- Contact tracing operations
- Referral management
- Receive automated alerts for follow-ups

### 3. Patient
- Access to personal treatment information
- Medication schedules and reminders
- Results and treatment progress
- Communication with healthcare providers

## 🗄 Database Schema

### Key Tables
```sql
patients (patient_id, patient_code, name, age, sex, barangay, contact_number, philhealth_id, tb_case_number, treatment_outcome)
users (user_id, email, password_hash, role, barangay_assigned)
medications (medication_id, patient_id, drugs, start_date, end_date, compliance_status)
contacts (contact_id, patient_id, name, relationship, screening_result, status)
referrals (referral_id, patient_id, referring_unit, receiving_barangay, referral_status)
notifications (notification_id, user_id, patient_id, type, title, message, is_sent, is_read)
```

## 🤖 Automated Processes

### CRON Jobs (`cron/send_notifications.php`)
- **Scheduled Email Delivery**: Sends queued notifications based on `scheduled_at`
- **Automated Missed Medication Detection**: Flags overdue medications (3+ days)
- **Follow-up Notifications**: Triggers health worker alerts for urgent patient follow-ups

### Auto-Mark Missed Medications
- **Grace Period**: 3 days after compliance deadline
- **Notification Cascade**: Alerts both health workers and patients
- **Staff Follow-up**: Escalates to administrators for unresolved issues

## 📚 API Documentation

### AJAX Endpoints
- `GET /ajax/fetch_patients` - Patient listing with filters
- `GET /ajax/fetch_medications` - Medication records
- `GET /ajax/fetch_referrals` - Referral management
- `GET /ajax/fetch_contacts` - Contact tracing data
- `GET /ajax/search_barangay` - Barangay autocomplete

### Parameters
Common query parameters:
- `q`: Search term (patient code, name, TB case number)
- `barangay`: Location filter
- `treatment_outcome`: Treatment status filter
- `barangay_assigned`: Health worker location filter

## 🤝 Contributing

1. Fork the repository
2. Create a feature branch (`git checkout -b feature/amazing-feature`)
3. Commit your changes (`git commit -m 'Add some amazing feature'`)
4. Push to the branch (`git push origin feature/amazing-feature`)
5. Open a Pull Request

### Development Guidelines
- Follow PSR-12 coding standards
- Use meaningful commit messages
- Test changes thoroughly
- Update documentation as needed
- Maintain security best practices

## 🆘 Support

For support, issues, or feature requests:
- Create an issue in the GitHub repository
- Check existing documentation
- Review system logs for debugging

---

**Made with ❤️ for effective TB control and management**
