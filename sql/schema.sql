-- Database schema for chem_coursework (run this in phpMyAdmin)
CREATE DATABASE IF NOT EXISTS chem_coursework DEFAULT CHARACTER SET utf8mb4;
USE chem_coursework;

CREATE TABLE IF NOT EXISTS admins (
  id INT AUTO_INCREMENT PRIMARY KEY,
  username VARCHAR(100) UNIQUE,
  password VARCHAR(255)
);

CREATE TABLE IF NOT EXISTS groups (
  id INT AUTO_INCREMENT PRIMARY KEY,
  group_id VARCHAR(50) UNIQUE,
  password VARCHAR(255)
);

CREATE TABLE IF NOT EXISTS students (
  id INT AUTO_INCREMENT PRIMARY KEY,
  group_id VARCHAR(50),
  reg_no VARCHAR(100),
  name VARCHAR(255)
);

CREATE TABLE IF NOT EXISTS supervisors (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(255)
);

CREATE TABLE IF NOT EXISTS personnel (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(255),
  code VARCHAR(50)
);

CREATE TABLE IF NOT EXISTS submissions (
  id INT AUTO_INCREMENT PRIMARY KEY,
  group_id VARCHAR(50),
  supervisor_id INT,
  personnel_id INT,
  file_name VARCHAR(255),
  date DATETIME
);

CREATE TABLE IF NOT EXISTS remarks (
  id INT AUTO_INCREMENT PRIMARY KEY,
  submission_id INT,
  student_id INT,
  remark VARCHAR(50)
);

-- Sample initial data (passwords plain; will be hashed automatically on first successful login)
INSERT INTO admins (username, password) VALUES ('admin','adminpass');
INSERT INTO groups (group_id, password) VALUES ('GP1','gp1pass'), ('GP2','gp2pass'), ('GP3','gp3pass');
INSERT INTO students (group_id, reg_no, name) VALUES
('GP1','ND/2024/001','Alice Johnson'),
('GP1','ND/2024/002','Bob Smith'),
('GP1','ND/2024/003','Chinedu Okoro'),
('GP2','ND/2024/004','Daniel Ade'),
('GP2','ND/2024/005','Eunice U.'),
('GP3','ND/2024/006','Funke Olu');
INSERT INTO supervisors (name) VALUES ('Dr. A. Supervisor'), ('Engr. B. Supervisor');
INSERT INTO personnel (name, code) VALUES ('ABDULAZEEZ A. ABDULAZEEZ','LAB01'),('MUHAMMAD YASIR','LAB05');
INSERT INTO submissions (group_id, supervisor_id, personnel_id, file_name, date) VALUES
('GP1',1,1,'experiment1.pdf',NOW()),
('GP2',2,2,'experiment2.docx',NOW());

INSERT INTO remarks (submission_id, student_id, remark) VALUES
(1,1,'Good'),(1,2,'Average'),(1,3,'Excellent'),
(2,4,'Poor'),(2,5,'Good');
(2,6,'Average');
-- End of schema.sql