<<<<<<< HEAD
# Student Management System

## Overview
The Student Management System is a web-based application designed to manage users, sections, and assignments for educational institutions. It provides role-based access for administrators, teachers, and students, enabling efficient management of academic activities.

## Features

### Admin Features
- **User Management**: Add, update, and manage users (students, teachers, and admins).
- **Section Management**: Create and manage sections, assign students and teachers to sections.

### Teacher Features
- **Dashboard**: View assigned students, active sections, and assignments.
- **Student Assignment**: Assign students to sections and courses.
- **Create Assignments**: Create and manage assignments for students.

### Student Features
- **Dashboard**: View assigned sections and courses.
- **Assignments**: View assignments and their details.
- **Profile Management**: Update personal information and change passwords.

## Installation

1. Clone the repository:
   ```bash
   git clone <repository-url>
   ```
2. Navigate to the project directory:
   ```bash
   cd Student-Management-System
   ```
3. Set up the database:
   - Import the `sql/create_database.sql` file into your MySQL server.
   - Update the database credentials in `db.php`.

4. Start a local server (e.g., XAMPP, WAMP, or LAMP) and place the project folder in the server's root directory.

5. Access the application in your browser:
   ```
   http://localhost:8000/index.php
   ```

## File Structure

- **auth/**: Handles user authentication (login, logout, and registration).
- **admin/**: Admin-specific functionalities like managing users and sections.
- **teacher/**: Teacher-specific functionalities like assigning students and creating assignments.
- **student/**: Student-specific functionalities like viewing assignments and managing profiles.
- **css/**: Contains stylesheets for the application.
- **sql/**: SQL scripts for database setup.
- **db.php**: Database connection file.
- **index.php**: Landing page for the application.

## Technologies Used
- **Frontend**: HTML, CSS, JavaScript
- **Backend**: PHP
- **Database**: MySQL

## Authors
- Ridam Roy
- Istiake Nezoom

## Project Video Presentation
[Watch the video presentation](https://drive.google.com/file/d/1YKF2sr_LDI420O9p7VHE7W05pkTK7kiT/view?usp=sharing)

## License
This project is licensed under the MIT License.
=======
# Student-Management-System
>>>>>>> 5ec30d03b5f81bacf6dd314d0c61f04b9cddabce
