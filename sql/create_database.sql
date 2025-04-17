-- Create database if not exists
CREATE DATABASE IF NOT EXISTS simple_project_db;
USE simple_project_db;

-- Create users table
CREATE TABLE IF NOT EXISTS users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin', 'teacher', 'student') NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Add status column to users table if not exists
ALTER TABLE users 
ADD COLUMN IF NOT EXISTS status ENUM('active', 'inactive') DEFAULT 'active';

-- Drop and recreate sections table with specific section types
DROP TABLE IF EXISTS sections;
CREATE TABLE sections (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name ENUM('A', 'B', 'C', 'D') NOT NULL,
    status ENUM('open', 'closed') DEFAULT 'closed',
    created_by INT,
    FOREIGN KEY (created_by) REFERENCES users(id)
);

-- Insert default sections
INSERT INTO sections (name, status) VALUES
('A', 'open'), ('B', 'open'), ('C', 'open'), ('D', 'open');

-- Create student_assignments table
CREATE TABLE IF NOT EXISTS student_assignments (
    id INT PRIMARY KEY AUTO_INCREMENT,
    student_id INT,
    section_id INT,
    assigned_by INT,
    FOREIGN KEY (student_id) REFERENCES users(id),
    FOREIGN KEY (section_id) REFERENCES sections(id),
    FOREIGN KEY (assigned_by) REFERENCES users(id)
);

-- Create courses table
CREATE TABLE IF NOT EXISTS courses (
    id INT PRIMARY KEY AUTO_INCREMENT,
    course_name ENUM('CSE1', 'CSE2', 'CSE3', 'CSE4', 'CSE5') NOT NULL,
    teacher_id INT,
    status ENUM('active', 'inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (teacher_id) REFERENCES users(id)
);

-- Modify student_assignments table
ALTER TABLE student_assignments 
ADD course_id INT,
ADD status ENUM('active', 'inactive') DEFAULT 'active',
ADD FOREIGN KEY (course_id) REFERENCES courses(id);

-- Create assignments table
CREATE TABLE IF NOT EXISTS assignments (
    id INT PRIMARY KEY AUTO_INCREMENT,
    title VARCHAR(255) NOT NULL,
    description TEXT,
    section_id INT,
    course_id INT,
    teacher_id INT,
    due_date DATE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    status ENUM('active', 'inactive') DEFAULT 'active',
    FOREIGN KEY (section_id) REFERENCES sections(id),
    FOREIGN KEY (course_id) REFERENCES courses(id),
    FOREIGN KEY (teacher_id) REFERENCES users(id)
);
