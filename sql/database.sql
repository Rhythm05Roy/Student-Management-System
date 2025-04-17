CREATE TABLE users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin', 'teacher', 'student') NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE sections (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(50) NOT NULL,
    status ENUM('open', 'closed') DEFAULT 'closed',
    created_by INT,
    FOREIGN KEY (created_by) REFERENCES users(id)
);

CREATE TABLE student_assignments (
    id INT PRIMARY KEY AUTO_INCREMENT,
    student_id INT,
    section_id INT,
    assigned_by INT,
    FOREIGN KEY (student_id) REFERENCES users(id),
    FOREIGN KEY (section_id) REFERENCES sections(id),
    FOREIGN KEY (assigned_by) REFERENCES users(id)
);
