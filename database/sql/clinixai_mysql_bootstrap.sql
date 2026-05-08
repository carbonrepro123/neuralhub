CREATE DATABASE IF NOT EXISTS clinixai CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE clinixai;

CREATE TABLE IF NOT EXISTS roles (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL UNIQUE,
    slug VARCHAR(255) NOT NULL UNIQUE,
    description TEXT NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL
);

CREATE TABLE IF NOT EXISTS users (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL UNIQUE,
    phone VARCHAR(255) NULL,
    email_verified_at TIMESTAMP NULL,
    password VARCHAR(255) NOT NULL,
    role_id BIGINT UNSIGNED NULL,
    role VARCHAR(100) NOT NULL,
    status VARCHAR(50) NOT NULL DEFAULT 'active',
    two_factor_enabled TINYINT(1) NOT NULL DEFAULT 0,
    remember_token VARCHAR(100) NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    INDEX idx_users_role (role),
    CONSTRAINT fk_users_role FOREIGN KEY (role_id) REFERENCES roles(id) ON DELETE SET NULL
);

CREATE TABLE IF NOT EXISTS clinics (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    slug VARCHAR(255) NOT NULL UNIQUE,
    npi_number VARCHAR(255) NULL,
    email VARCHAR(255) NULL,
    phone VARCHAR(255) NULL,
    address_line_1 VARCHAR(255) NULL,
    address_line_2 VARCHAR(255) NULL,
    city VARCHAR(255) NULL,
    state VARCHAR(20) NULL,
    postal_code VARCHAR(20) NULL,
    status VARCHAR(50) NOT NULL DEFAULT 'active',
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL
);

CREATE TABLE IF NOT EXISTS clinic_users (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    clinic_id BIGINT UNSIGNED NOT NULL,
    user_id BIGINT UNSIGNED NOT NULL,
    role VARCHAR(100) NOT NULL,
    status VARCHAR(50) NOT NULL DEFAULT 'active',
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    UNIQUE KEY uniq_clinic_user (clinic_id, user_id),
    CONSTRAINT fk_clinic_users_clinic FOREIGN KEY (clinic_id) REFERENCES clinics(id) ON DELETE CASCADE,
    CONSTRAINT fk_clinic_users_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS doctors (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL,
    clinic_id BIGINT UNSIGNED NOT NULL,
    specialty VARCHAR(255) NOT NULL,
    license_number VARCHAR(255) NULL,
    state VARCHAR(20) NULL,
    npi_number VARCHAR(255) NULL,
    status VARCHAR(50) NOT NULL DEFAULT 'active',
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    INDEX idx_doctors_specialty (specialty),
    CONSTRAINT fk_doctors_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    CONSTRAINT fk_doctors_clinic FOREIGN KEY (clinic_id) REFERENCES clinics(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS patients (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    clinic_id BIGINT UNSIGNED NOT NULL,
    user_id BIGINT UNSIGNED NULL,
    primary_doctor_id BIGINT UNSIGNED NULL,
    name VARCHAR(255) NOT NULL,
    date_of_birth DATE NULL,
    gender VARCHAR(20) NULL,
    phone VARCHAR(255) NULL,
    email VARCHAR(255) NULL,
    address TEXT NULL,
    emergency_contact JSON NULL,
    insurance_provider VARCHAR(255) NULL,
    insurance_member_id VARCHAR(255) NULL,
    medical_history JSON NULL,
    allergies JSON NULL,
    medications JSON NULL,
    conditions JSON NULL,
    notes LONGTEXT NULL,
    consent_status VARCHAR(50) NOT NULL DEFAULT 'pending',
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    CONSTRAINT fk_patients_clinic FOREIGN KEY (clinic_id) REFERENCES clinics(id) ON DELETE CASCADE,
    CONSTRAINT fk_patients_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
    CONSTRAINT fk_patients_doctor FOREIGN KEY (primary_doctor_id) REFERENCES doctors(id) ON DELETE SET NULL
);

CREATE TABLE IF NOT EXISTS patient_documents (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    clinic_id BIGINT UNSIGNED NOT NULL,
    patient_id BIGINT UNSIGNED NOT NULL,
    uploaded_by BIGINT UNSIGNED NOT NULL,
    document_type VARCHAR(255) NOT NULL,
    original_name VARCHAR(255) NOT NULL,
    disk VARCHAR(100) NOT NULL DEFAULT 's3',
    path VARCHAR(255) NOT NULL,
    mime_type VARCHAR(255) NOT NULL,
    size BIGINT UNSIGNED NOT NULL,
    encryption_key_ref VARCHAR(255) NULL,
    status VARCHAR(50) NOT NULL DEFAULT 'uploaded',
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    CONSTRAINT fk_patient_documents_clinic FOREIGN KEY (clinic_id) REFERENCES clinics(id) ON DELETE CASCADE,
    CONSTRAINT fk_patient_documents_patient FOREIGN KEY (patient_id) REFERENCES patients(id) ON DELETE CASCADE,
    CONSTRAINT fk_patient_documents_uploaded_by FOREIGN KEY (uploaded_by) REFERENCES users(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS report_extractions (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    document_id BIGINT UNSIGNED NOT NULL,
    patient_id BIGINT UNSIGNED NOT NULL,
    doctor_id BIGINT UNSIGNED NULL,
    ocr_text LONGTEXT NULL,
    summary LONGTEXT NULL,
    vitals_json JSON NULL,
    abnormal_findings_json JSON NULL,
    review_status VARCHAR(100) NOT NULL DEFAULT 'pending_doctor_review',
    approved_by BIGINT UNSIGNED NULL,
    approved_at TIMESTAMP NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    CONSTRAINT fk_report_extractions_document FOREIGN KEY (document_id) REFERENCES patient_documents(id) ON DELETE CASCADE,
    CONSTRAINT fk_report_extractions_patient FOREIGN KEY (patient_id) REFERENCES patients(id) ON DELETE CASCADE,
    CONSTRAINT fk_report_extractions_doctor FOREIGN KEY (doctor_id) REFERENCES doctors(id) ON DELETE SET NULL,
    CONSTRAINT fk_report_extractions_approved_by FOREIGN KEY (approved_by) REFERENCES users(id) ON DELETE SET NULL
);

CREATE TABLE IF NOT EXISTS consent_logs (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    patient_id BIGINT UNSIGNED NOT NULL,
    recorded_by BIGINT UNSIGNED NOT NULL,
    consent_type VARCHAR(255) NOT NULL,
    status VARCHAR(50) NOT NULL,
    recorded_at TIMESTAMP NOT NULL,
    metadata JSON NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    CONSTRAINT fk_consent_logs_patient FOREIGN KEY (patient_id) REFERENCES patients(id) ON DELETE CASCADE,
    CONSTRAINT fk_consent_logs_recorded_by FOREIGN KEY (recorded_by) REFERENCES users(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS appointments (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    clinic_id BIGINT UNSIGNED NOT NULL,
    doctor_id BIGINT UNSIGNED NOT NULL,
    patient_id BIGINT UNSIGNED NOT NULL,
    appointment_type VARCHAR(50) NOT NULL DEFAULT 'video',
    appointment_date DATE NOT NULL,
    start_time TIME NOT NULL,
    end_time TIME NULL,
    reason_for_visit VARCHAR(255) NULL,
    status VARCHAR(50) NOT NULL DEFAULT 'scheduled',
    daily_room_url VARCHAR(255) NULL,
    notes LONGTEXT NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    CONSTRAINT fk_appointments_clinic FOREIGN KEY (clinic_id) REFERENCES clinics(id) ON DELETE CASCADE,
    CONSTRAINT fk_appointments_doctor FOREIGN KEY (doctor_id) REFERENCES doctors(id) ON DELETE CASCADE,
    CONSTRAINT fk_appointments_patient FOREIGN KEY (patient_id) REFERENCES patients(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS video_rooms (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    appointment_id BIGINT UNSIGNED NOT NULL,
    provider VARCHAR(100) NOT NULL DEFAULT 'daily',
    external_room_id VARCHAR(255) NOT NULL,
    room_url VARCHAR(255) NOT NULL,
    doctor_token TEXT NULL,
    patient_token TEXT NULL,
    expires_at TIMESTAMP NULL,
    metadata JSON NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    CONSTRAINT fk_video_rooms_appointment FOREIGN KEY (appointment_id) REFERENCES appointments(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS soap_notes (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    appointment_id BIGINT UNSIGNED NOT NULL,
    doctor_id BIGINT UNSIGNED NOT NULL,
    patient_id BIGINT UNSIGNED NOT NULL,
    subjective LONGTEXT NULL,
    objective LONGTEXT NULL,
    assessment LONGTEXT NULL,
    plan LONGTEXT NULL,
    review_status VARCHAR(50) NOT NULL DEFAULT 'draft',
    approved_by BIGINT UNSIGNED NULL,
    approved_at TIMESTAMP NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    CONSTRAINT fk_soap_notes_appointment FOREIGN KEY (appointment_id) REFERENCES appointments(id) ON DELETE CASCADE,
    CONSTRAINT fk_soap_notes_doctor FOREIGN KEY (doctor_id) REFERENCES doctors(id) ON DELETE CASCADE,
    CONSTRAINT fk_soap_notes_patient FOREIGN KEY (patient_id) REFERENCES patients(id) ON DELETE CASCADE,
    CONSTRAINT fk_soap_notes_approved_by FOREIGN KEY (approved_by) REFERENCES users(id) ON DELETE SET NULL
);

CREATE TABLE IF NOT EXISTS visit_notes (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    appointment_id BIGINT UNSIGNED NOT NULL,
    doctor_id BIGINT UNSIGNED NOT NULL,
    patient_id BIGINT UNSIGNED NOT NULL,
    content LONGTEXT NULL,
    source VARCHAR(50) NOT NULL DEFAULT 'manual',
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    CONSTRAINT fk_visit_notes_appointment FOREIGN KEY (appointment_id) REFERENCES appointments(id) ON DELETE CASCADE,
    CONSTRAINT fk_visit_notes_doctor FOREIGN KEY (doctor_id) REFERENCES doctors(id) ON DELETE CASCADE,
    CONSTRAINT fk_visit_notes_patient FOREIGN KEY (patient_id) REFERENCES patients(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS patient_intake_forms (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    appointment_id BIGINT UNSIGNED NOT NULL,
    patient_id BIGINT UNSIGNED NOT NULL,
    responses JSON NULL,
    status VARCHAR(50) NOT NULL DEFAULT 'pending',
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    CONSTRAINT fk_patient_intake_forms_appointment FOREIGN KEY (appointment_id) REFERENCES appointments(id) ON DELETE CASCADE,
    CONSTRAINT fk_patient_intake_forms_patient FOREIGN KEY (patient_id) REFERENCES patients(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS ai_agents (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    agent_type VARCHAR(255) NOT NULL UNIQUE,
    status VARCHAR(50) NOT NULL DEFAULT 'active',
    config JSON NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL
);

CREATE TABLE IF NOT EXISTS ai_agent_tasks (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    clinic_id BIGINT UNSIGNED NULL,
    patient_id BIGINT UNSIGNED NULL,
    doctor_id BIGINT UNSIGNED NULL,
    agent_type VARCHAR(255) NOT NULL,
    input_text LONGTEXT NULL,
    input_json JSON NULL,
    status VARCHAR(50) NOT NULL DEFAULT 'queued',
    priority VARCHAR(50) NOT NULL DEFAULT 'normal',
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    CONSTRAINT fk_ai_agent_tasks_clinic FOREIGN KEY (clinic_id) REFERENCES clinics(id) ON DELETE SET NULL,
    CONSTRAINT fk_ai_agent_tasks_patient FOREIGN KEY (patient_id) REFERENCES patients(id) ON DELETE SET NULL,
    CONSTRAINT fk_ai_agent_tasks_doctor FOREIGN KEY (doctor_id) REFERENCES doctors(id) ON DELETE SET NULL
);

CREATE TABLE IF NOT EXISTS ai_outputs (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    task_id BIGINT UNSIGNED NOT NULL,
    patient_id BIGINT UNSIGNED NULL,
    doctor_id BIGINT UNSIGNED NULL,
    agent_type VARCHAR(255) NOT NULL,
    input_text LONGTEXT NULL,
    output_json JSON NULL,
    status VARCHAR(50) NOT NULL DEFAULT 'completed',
    reviewed_by_doctor TINYINT(1) NOT NULL DEFAULT 0,
    confidence_score DECIMAL(5,2) NULL,
    doctor_review_status VARCHAR(50) NOT NULL DEFAULT 'pending',
    disclaimer VARCHAR(255) NOT NULL DEFAULT 'Doctor review required.',
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    CONSTRAINT fk_ai_outputs_task FOREIGN KEY (task_id) REFERENCES ai_agent_tasks(id) ON DELETE CASCADE,
    CONSTRAINT fk_ai_outputs_patient FOREIGN KEY (patient_id) REFERENCES patients(id) ON DELETE SET NULL,
    CONSTRAINT fk_ai_outputs_doctor FOREIGN KEY (doctor_id) REFERENCES doctors(id) ON DELETE SET NULL
);

CREATE TABLE IF NOT EXISTS compliance_items (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    doctor_id BIGINT UNSIGNED NOT NULL,
    certification_type VARCHAR(255) NOT NULL,
    issuing_authority VARCHAR(255) NULL,
    license_number VARCHAR(255) NULL,
    state VARCHAR(20) NULL,
    issue_date DATE NULL,
    expiry_date DATE NULL,
    renewal_frequency VARCHAR(255) NULL,
    document_upload VARCHAR(255) NULL,
    status VARCHAR(50) NOT NULL DEFAULT 'pending_review',
    reminder_dates JSON NULL,
    notes LONGTEXT NULL,
    last_verified_at TIMESTAMP NULL,
    created_by BIGINT UNSIGNED NULL,
    updated_by BIGINT UNSIGNED NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    INDEX idx_compliance_certification_type (certification_type),
    CONSTRAINT fk_compliance_items_doctor FOREIGN KEY (doctor_id) REFERENCES doctors(id) ON DELETE CASCADE,
    CONSTRAINT fk_compliance_items_created_by FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL,
    CONSTRAINT fk_compliance_items_updated_by FOREIGN KEY (updated_by) REFERENCES users(id) ON DELETE SET NULL
);

CREATE TABLE IF NOT EXISTS compliance_documents (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    compliance_item_id BIGINT UNSIGNED NOT NULL,
    uploaded_by BIGINT UNSIGNED NOT NULL,
    original_name VARCHAR(255) NOT NULL,
    path VARCHAR(255) NOT NULL,
    disk VARCHAR(100) NOT NULL DEFAULT 's3',
    status VARCHAR(50) NOT NULL DEFAULT 'uploaded',
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    CONSTRAINT fk_compliance_documents_item FOREIGN KEY (compliance_item_id) REFERENCES compliance_items(id) ON DELETE CASCADE,
    CONSTRAINT fk_compliance_documents_uploaded_by FOREIGN KEY (uploaded_by) REFERENCES users(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS compliance_reminders (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    compliance_item_id BIGINT UNSIGNED NOT NULL,
    channel VARCHAR(50) NOT NULL DEFAULT 'email',
    send_at TIMESTAMP NOT NULL,
    sent_at TIMESTAMP NULL,
    status VARCHAR(50) NOT NULL DEFAULT 'scheduled',
    payload JSON NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    CONSTRAINT fk_compliance_reminders_item FOREIGN KEY (compliance_item_id) REFERENCES compliance_items(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS notifications (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL,
    type VARCHAR(100) NOT NULL,
    title VARCHAR(255) NOT NULL,
    body TEXT NOT NULL,
    data JSON NULL,
    read_at TIMESTAMP NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    CONSTRAINT fk_notifications_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS audit_logs (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NULL,
    action VARCHAR(255) NOT NULL,
    entity_type VARCHAR(255) NOT NULL,
    entity_id BIGINT UNSIGNED NULL,
    ip_address VARCHAR(45) NULL,
    user_agent TEXT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    metadata JSON NULL,
    CONSTRAINT fk_audit_logs_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
);
