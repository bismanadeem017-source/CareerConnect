CAREERCONNECT — INSTALLATION GUIDE
=================================

REQUIREMENTS
- XAMPP with Apache, PHP 8+, and MySQL/MariaDB
- A modern web browser
- VS Code (recommended)

INSTALLATION
1. Extract the CareerConnect folder into:
   C:\xampp\htdocs\CareerConnect

2. Open XAMPP Control Panel and start Apache and MySQL.

3. Check the database settings in:
   CareerConnect/config/config.php

   Default XAMPP settings:
   DB_HOST = localhost
   DB_NAME = careerconnect
   DB_USER = root
   DB_PASS = empty

4. DATABASE SETUP
   For a NEW installation only:
   - Open http://localhost/phpmyadmin
   - Import CareerConnect/database/careerconnect.sql

   If you already have a working CareerConnect database, keep using it.
   Do not drop or re-import over your existing database or project data.
   The supplied SQL file is for a fresh database and uses CREATE TABLE IF NOT EXISTS;
   it does not migrate older table structures.

5. Open:
   http://localhost/CareerConnect/

6. Register a student account and a company account through Sign Up.
   Company job listings remain pending until an admin approves them.

CREATE AN ADMIN
1. Register a normal account through signup.php.
2. In phpMyAdmin, open the SQL tab and run this statement,
   replacing the email with the account you registered:

   UPDATE users SET role = 'admin' WHERE email = 'your-email@example.com';

3. Log out and log in again. The account will now have access to the Admin Panel.

FEATURES
- Student and company registration/login
- Password hashing and prepared SQL statements
- CSRF protection for POST forms
- Job posting and admin approval workflow
- Job search and filters
- Student profile and CV upload (PDF/DOC/DOCX, max 5 MB)
- Apply for jobs and track application status
- Save/bookmark jobs
- Company applicant list and status updates
- Admin dashboard, job moderation and user management
- Responsive layout and mobile navigation

DESIGN
- Deep burgundy and crimson visual theme
- Animated ambient background effects and glowing highlights
- Smooth page transitions and scroll reveal animations
- Hover effects for buttons, cards, forms and navigation
- Animated dashboard statistics
- Reduced-motion accessibility support

PRIVACY AND SECURITY
- Real uploaded CV files are not included in this ZIP.
- The uploads/cvs folder is included with its access-protection file.
- Keep uploaded CVs private and do not publish them in a public repository.
- The local database configuration is included for XAMPP convenience.
  Do not publish database credentials or private configuration files.
- Before public deployment, configure HTTPS, email verification, password
  reset, rate limiting, audit logging, upload scanning and privacy policies.
- CV downloads are handled through download-cv.php with role checks.

PROJECT SETTINGS
- BASE_URL in config/config.php assumes the project folder is /CareerConnect.
- Change BASE_URL if you use a different folder name or path.
- Ensure uploads/cvs is writable by the PHP process.
