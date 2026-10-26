-- ============================================================
-- Clinic Appointment & Patient Record Management System
-- Database: clinic_db
-- Import this file first (phpMyAdmin > Import, or command line)
-- ============================================================

DROP DATABASE IF EXISTS clinic_db;
CREATE DATABASE clinic_db CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE clinic_db;

-- ------------------------------------------------------------
-- 1. USER_ACCOUNT  (login for patient / doctor / receptionist)
-- ------------------------------------------------------------
CREATE TABLE user_account (
    User_ID     INT AUTO_INCREMENT PRIMARY KEY,
    Username    VARCHAR(50)  NOT NULL UNIQUE,
    Password    VARCHAR(255) NOT NULL,
    User_Type   ENUM('Patient','Doctor','Receptionist') NOT NULL,
    Created_At  TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ------------------------------------------------------------
-- 2. CLINIC
-- ------------------------------------------------------------
CREATE TABLE clinic (
    Clinic_ID   INT AUTO_INCREMENT PRIMARY KEY,
    Clinic_Name VARCHAR(100) NOT NULL,
    Address     VARCHAR(200),
    Contact_No  VARCHAR(20)
) ENGINE=InnoDB;

-- ------------------------------------------------------------
-- 3. SPECIALIZATION
-- ------------------------------------------------------------
CREATE TABLE specialization (
    Special_ID   INT AUTO_INCREMENT PRIMARY KEY,
    Special_Name VARCHAR(100) NOT NULL,
    Description  VARCHAR(255)
) ENGINE=InnoDB;

-- ------------------------------------------------------------
-- 4. DOCTOR
-- ------------------------------------------------------------
CREATE TABLE doctor (
    Doctor_ID   INT AUTO_INCREMENT PRIMARY KEY,
    First_Name  VARCHAR(50) NOT NULL,
    Last_Name   VARCHAR(50) NOT NULL,
    Phone       VARCHAR(20),
    Email       VARCHAR(100),
    Special_ID  INT,
    Clinic_ID   INT,
    User_ID     INT,
    Consultation_Fee DECIMAL(10,2) DEFAULT 2500.00,
    CONSTRAINT fk_doctor_special FOREIGN KEY (Special_ID) REFERENCES specialization(Special_ID),
    CONSTRAINT fk_doctor_clinic  FOREIGN KEY (Clinic_ID)  REFERENCES clinic(Clinic_ID),
    CONSTRAINT fk_doctor_user    FOREIGN KEY (User_ID)    REFERENCES user_account(User_ID)
) ENGINE=InnoDB;

-- ------------------------------------------------------------
-- 5. PATIENT
-- ------------------------------------------------------------
CREATE TABLE patient (
    Patient_ID      INT AUTO_INCREMENT PRIMARY KEY,
    NIC             VARCHAR(20) UNIQUE,
    First_Name      VARCHAR(50) NOT NULL,
    Last_Name       VARCHAR(50) NOT NULL,
    DOB             DATE,
    Gender          ENUM('Male','Female','Other'),
    Phone           VARCHAR(20),
    Email           VARCHAR(100),
    Address         VARCHAR(200),
    Registered_Date DATE DEFAULT (CURRENT_DATE),
    User_ID         INT,
    CONSTRAINT fk_patient_user FOREIGN KEY (User_ID) REFERENCES user_account(User_ID)
) ENGINE=InnoDB;

-- ------------------------------------------------------------
-- 6. RECEPTIONIST
-- ------------------------------------------------------------
CREATE TABLE receptionist (
    Receptionist_ID INT AUTO_INCREMENT PRIMARY KEY,
    First_Name      VARCHAR(50) NOT NULL,
    Last_Name       VARCHAR(50) NOT NULL,
    Phone           VARCHAR(20),
    Clinic_ID       INT,
    User_ID         INT,
    CONSTRAINT fk_recep_clinic FOREIGN KEY (Clinic_ID) REFERENCES clinic(Clinic_ID),
    CONSTRAINT fk_recep_user   FOREIGN KEY (User_ID)   REFERENCES user_account(User_ID)
) ENGINE=InnoDB;

-- ------------------------------------------------------------
-- 7. DOCTOR_SCHEDULE   <-- your module
-- ------------------------------------------------------------
CREATE TABLE doctor_schedule (
    Schedule_ID    INT AUTO_INCREMENT PRIMARY KEY,
    Doctor_ID      INT NOT NULL,
    Available_Date DATE NOT NULL,
    Start_Time     TIME NOT NULL,
    End_Time       TIME NOT NULL,
    Is_Available   TINYINT(1) DEFAULT 1,
    CONSTRAINT fk_schedule_doctor FOREIGN KEY (Doctor_ID) REFERENCES doctor(Doctor_ID),
    CONSTRAINT uq_schedule UNIQUE (Doctor_ID, Available_Date, Start_Time)
) ENGINE=InnoDB;

-- ------------------------------------------------------------
-- 8. APPOINTMENT   <-- your module
-- ------------------------------------------------------------
CREATE TABLE appointment (
    Appointment_ID   INT AUTO_INCREMENT PRIMARY KEY,
    Patient_ID       INT NOT NULL,
    Doctor_ID        INT NOT NULL,
    Schedule_ID      INT,
    Receptionist_ID  INT,
    Appointment_Date DATE NOT NULL,
    Time_Slot        TIME NOT NULL,
    Queue_No         INT,
    Status           ENUM('Pending','Confirmed','Completed','Cancelled','No-Show')
                     NOT NULL DEFAULT 'Pending',
    Reason           VARCHAR(255),
    Created_At       TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    -- Slot_Key is Time_Slot for live appointments and NULL for cancelled ones.
    -- MySQL allows repeated NULLs in a UNIQUE index, so a cancelled slot
    -- becomes free again while live slots stay protected.
    Slot_Key TIME GENERATED ALWAYS AS (IF(Status = 'Cancelled', NULL, Time_Slot)) STORED,

    CONSTRAINT fk_appt_patient  FOREIGN KEY (Patient_ID)  REFERENCES patient(Patient_ID),
    CONSTRAINT fk_appt_doctor   FOREIGN KEY (Doctor_ID)   REFERENCES doctor(Doctor_ID),
    CONSTRAINT fk_appt_schedule FOREIGN KEY (Schedule_ID) REFERENCES doctor_schedule(Schedule_ID),
    CONSTRAINT fk_appt_recep    FOREIGN KEY (Receptionist_ID) REFERENCES receptionist(Receptionist_ID),
    -- stops two patients booking the same doctor at the same date + time
    CONSTRAINT uq_doctor_slot UNIQUE (Doctor_ID, Appointment_Date, Slot_Key)
) ENGINE=InnoDB;

-- ------------------------------------------------------------
-- 9. MEDICAL_RECORD   (Member 4)
-- ------------------------------------------------------------
CREATE TABLE medical_record (
    Record_ID       INT AUTO_INCREMENT PRIMARY KEY,
    Appointment_ID  INT NOT NULL UNIQUE,
    Diagnosis       VARCHAR(255),
    Symptoms        VARCHAR(255),
    Treatment_Notes TEXT,
    Record_Date     DATE,
    CONSTRAINT fk_record_appt FOREIGN KEY (Appointment_ID) REFERENCES appointment(Appointment_ID)
) ENGINE=InnoDB;

-- ------------------------------------------------------------
-- 10. PRESCRIPTION / 11. MEDICINE / 12. PRESCRIPTION_ITEM
-- ------------------------------------------------------------
CREATE TABLE prescription (
    Prescription_ID   INT AUTO_INCREMENT PRIMARY KEY,
    Record_ID         INT NOT NULL,
    Prescription_Date DATE,
    CONSTRAINT fk_pres_record FOREIGN KEY (Record_ID) REFERENCES medical_record(Record_ID)
) ENGINE=InnoDB;

CREATE TABLE medicine (
    Medicine_ID   INT AUTO_INCREMENT PRIMARY KEY,
    Medicine_Name VARCHAR(100) NOT NULL,
    Description   VARCHAR(255)
) ENGINE=InnoDB;

CREATE TABLE prescription_item (
    Item_ID         INT AUTO_INCREMENT PRIMARY KEY,
    Prescription_ID INT NOT NULL,
    Medicine_ID     INT NOT NULL,
    Dosage          VARCHAR(50),
    Frequency       VARCHAR(50),
    Duration        VARCHAR(50),
    Quantity        INT,
    Instructions    VARCHAR(255),
    CONSTRAINT fk_item_pres FOREIGN KEY (Prescription_ID) REFERENCES prescription(Prescription_ID),
    CONSTRAINT fk_item_med  FOREIGN KEY (Medicine_ID)     REFERENCES medicine(Medicine_ID)
) ENGINE=InnoDB;

-- ------------------------------------------------------------
-- 13. PAYMENT   (Member 5)
-- ------------------------------------------------------------
CREATE TABLE payment (
    Payment_ID     INT AUTO_INCREMENT PRIMARY KEY,
    Appointment_ID INT NOT NULL UNIQUE,
    Amount         DECIMAL(10,2),
    Payment_Date   DATE,
    Payment_Status ENUM('Paid','Not Paid','Refunded') DEFAULT 'Not Paid',
    CONSTRAINT fk_payment_appt FOREIGN KEY (Appointment_ID) REFERENCES appointment(Appointment_ID)
) ENGINE=InnoDB;

-- ============================================================
-- SAMPLE DATA
-- ============================================================
INSERT INTO clinic (Clinic_Name, Address, Contact_No) VALUES
('MediCare Clinic', '45 High Level Road, Nugegoda', '0112825566');

INSERT INTO specialization (Special_Name, Description) VALUES
('General Medicine', 'General consultations and common illnesses'),
('Cardiology',       'Heart and blood vessel conditions'),
('Dermatology',      'Skin, hair and nail conditions');

INSERT INTO user_account (Username, Password, User_Type) VALUES
('dr.perera',  'doctor123', 'Doctor'),
('dr.silva',   'doctor123', 'Doctor'),
('dr.fernando','doctor123', 'Doctor'),
('reception',  'admin123',  'Receptionist'),
('kamal@mail.com',  'patient123','Patient'),
('nimali@mail.com', 'patient123','Patient'),
('sunil@mail.com',  'patient123','Patient');

INSERT INTO doctor (First_Name, Last_Name, Phone, Email, Special_ID, Clinic_ID, User_ID, Consultation_Fee) VALUES
('Anura',  'Perera',   '0771234567', 'perera@medicare.lk',   1, 1, 1, 2500.00),
('Ruwan',  'Silva',    '0772345678', 'silva@medicare.lk',    2, 1, 2, 3500.00),
('Dilani', 'Fernando', '0773456789', 'fernando@medicare.lk', 3, 1, 3, 3000.00);

INSERT INTO receptionist (First_Name, Last_Name, Phone, Clinic_ID, User_ID) VALUES
('Shanika', 'Jayasuriya', '0759876543', 1, 4);

INSERT INTO patient (NIC, First_Name, Last_Name, DOB, Gender, Phone, Email, Address, User_ID) VALUES
('200012345678', 'Kamal',  'Bandara',   '2000-05-14', 'Male',   '0761112223', 'kamal@mail.com',  '12 Temple Road, Maharagama',  5),
('199823456789', 'Nimali', 'Wijesinghe','1998-11-02', 'Female', '0762223334', 'nimali@mail.com', '80 Station Road, Dehiwala',   6),
('197734567890', 'Sunil',  'Rajapaksha','1977-03-21', 'Male',   '0763334445', 'sunil@mail.com',  '5 Lake Drive, Kotte',         7);

-- Schedules for today + next 6 days (morning and evening sessions)
INSERT INTO doctor_schedule (Doctor_ID, Available_Date, Start_Time, End_Time, Is_Available)
SELECT d.Doctor_ID,
       DATE_ADD(CURDATE(), INTERVAL n.num DAY),
       s.st, s.et, 1
FROM doctor d
CROSS JOIN (SELECT 0 num UNION SELECT 1 UNION SELECT 2 UNION SELECT 3
            UNION SELECT 4 UNION SELECT 5 UNION SELECT 6) n
CROSS JOIN (SELECT '09:00:00' st, '12:00:00' et
            UNION SELECT '16:00:00', '18:00:00') s;

-- A few existing appointments for today so the queue page is not empty
INSERT INTO appointment (Patient_ID, Doctor_ID, Appointment_Date, Time_Slot, Queue_No, Status, Reason) VALUES
(1, 1, CURDATE(), '09:00:00', 1, 'Completed', 'Fever and cough'),
(2, 1, CURDATE(), '09:30:00', 2, 'Confirmed', 'Follow-up visit'),
(3, 1, CURDATE(), '10:00:00', 3, 'Pending',   'Headache');

-- Medicines (Member 4 master list)
INSERT INTO medicine (Medicine_Name, Description) VALUES
('Paracetamol 500mg',  'Pain relief and fever'),
('Amoxicillin 250mg',  'Antibiotic for bacterial infections'),
('Cetirizine 10mg',    'Antihistamine for allergies'),
('Omeprazole 20mg',    'Reduces stomach acid'),
('Metformin 500mg',    'Blood sugar control'),
('Salbutamol Inhaler', 'Relieves wheezing and shortness of breath'),
('Ibuprofen 400mg',    'Pain and inflammation'),
('Vitamin C 100mg',    'Supplement');

-- One finished consultation written up, so the Records page is not empty
INSERT INTO medical_record (Appointment_ID, Diagnosis, Symptoms, Treatment_Notes, Record_Date)
VALUES (1, 'Acute viral pharyngitis', 'Fever for 3 days, sore throat, dry cough',
        'Rest and fluids. Return in 5 days if the fever continues.', CURDATE());

INSERT INTO prescription (Record_ID, Prescription_Date) VALUES (1, CURDATE());

INSERT INTO prescription_item
    (Prescription_ID, Medicine_ID, Dosage, Frequency, Duration, Quantity, Instructions) VALUES
(1, 1, '500mg', '3 times a day', '5 days', 15, 'After meals'),
(1, 3, '10mg',  'Once at night', '5 days',  5, 'May cause drowsiness'),
(1, 8, '100mg', 'Once a day',    '7 days',  7, 'With breakfast');

-- Payments (Member 5). Appointment 1 was completed and paid, 2 is still open.
INSERT INTO payment (Appointment_ID, Amount, Payment_Date, Payment_Status) VALUES
(1, 2500.00, CURDATE(), 'Paid'),
(2, 2500.00, NULL,      'Not Paid'),
(3, 3000.00, CURDATE(), 'Paid');
