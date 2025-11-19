CREATE DATABASE IF NOT EXISTS tb_mas CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

USE tb_mas;

CREATE TABLE users (
    user_id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(150) UNIQUE NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    role ENUM('super_admin','health_worker','patient') NOT NULL,
    barangay_assigned VARCHAR(100),
    is_verified TINYINT DEFAULT 0,
    verification_token VARCHAR(150),
    password_reset_required TINYINT DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE patients (
    patient_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NULL,
    patient_code VARCHAR(50) UNIQUE NOT NULL,
    age INT,
    sex ENUM('M','F','Unknown') DEFAULT 'Unknown',
    barangay VARCHAR(100) NOT NULL,
    contact_number VARCHAR(50),
    tb_case_number VARCHAR(80),
    bacteriological_status ENUM('BC','CD','Unknown') DEFAULT 'Unknown',
    anatomical_site ENUM('P','EP','Unknown') DEFAULT 'Unknown',
    drug_susceptibility ENUM('DS','DR','Unknown') DEFAULT 'Unknown',
    treatment_history ENUM('New','Retreatment','Unknown') DEFAULT 'Unknown',
    created_by INT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE SET NULL
);

CREATE TABLE referrals (
    referral_id INT AUTO_INCREMENT PRIMARY KEY,
    patient_id INT NOT NULL,
    referral_date DATE,
    referring_unit VARCHAR(150),
    referring_tel VARCHAR(50),
    referring_email VARCHAR(150),
    referring_address TEXT,
    reason_for_referral TEXT,
    details TEXT,
    receiving_unit VARCHAR(150),
    receiving_officer VARCHAR(150),
    date_received DATE,
    action_taken TEXT,
    remarks TEXT,
    created_by INT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (patient_id) REFERENCES patients(patient_id) ON DELETE CASCADE
);

CREATE TABLE contacts (
    contact_id INT AUTO_INCREMENT PRIMARY KEY,
    patient_id INT NOT NULL,
    contact_code VARCHAR(50) UNIQUE NOT NULL,
    age INT,
    sex ENUM('M','F','Unknown') DEFAULT 'Unknown',
    relationship VARCHAR(100),
    contact_number VARCHAR(50),
    address TEXT,
    screening_result ENUM('pending','negative','positive') DEFAULT 'pending',
    status ENUM('monitoring','converted_patient','cleared') DEFAULT 'monitoring',
    converted_patient_id INT NULL,
    created_by INT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (patient_id) REFERENCES patients(patient_id) ON DELETE CASCADE,
    FOREIGN KEY (converted_patient_id) REFERENCES patients(patient_id) ON DELETE SET NULL
);

CREATE TABLE medications (
    medication_id INT AUTO_INCREMENT PRIMARY KEY,
    patient_id INT NOT NULL,
    regimen VARCHAR(150),
    start_date DATE,
    end_date DATE,
    notes TEXT,
    created_by INT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (patient_id) REFERENCES patients(patient_id) ON DELETE CASCADE
);

CREATE TABLE notifications (
    notification_id INT AUTO_INCREMENT PRIMARY KEY,
    patient_id INT NOT NULL,
    notification_type ENUM('patient_reminder','staff_follow_up','referral_update','verification') DEFAULT 'patient_reminder',
    title VARCHAR(200),
    message TEXT,
    scheduled_at DATETIME,
    is_sent TINYINT DEFAULT 0,
    sent_at DATETIME NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (patient_id) REFERENCES patients(patient_id) ON DELETE CASCADE
);

CREATE TABLE audit_logs (
    log_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    action VARCHAR(100),
    table_name VARCHAR(100),
    record_id INT,
    old_values JSON,
    new_values JSON,
    ip_address VARCHAR(45),
    user_agent TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE SET NULL
);

CREATE TABLE import_logs (
    import_id INT AUTO_INCREMENT PRIMARY KEY,
    filename VARCHAR(200),
    rows_imported INT,
    imported_by INT,
    imported_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (imported_by) REFERENCES users(user_id)
);

