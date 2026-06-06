# study_spot_prototype

A lightweight prototype for a web‑based study‑spot platform that allows students to browse courses, enroll, and communicate with administrators. Built with plain PHP and a MySQL database, it demonstrates core CRUD operations, authentication, and a simple admin dashboard.

---

## Overview

`study_spot_prototype` provides a minimal yet functional environment for:

- Student registration, login, and profile management  
- Browsing, enrolling, and canceling enrollment in courses  
- Admin panel for managing courses, viewing enrolled users, and replying to messages  

The project is intended as a starting point for further development (e.g., adding REST APIs, modern UI frameworks, or role‑based access control).

---

## Features

| Student Side | Admin Side |
|--------------|------------|
| Register / Login / Logout | Secure admin login |
| View available courses (`view_courses.php`) | Add, edit, delete courses |
| Enroll in a course (`enroll.php`) | View enrolled users (`enrolled_users.php`) |
| Cancel enrollment (`cancel_enrollment.php`) | Reply to student messages (`admin_reply.php`) |
| Update personal profile (`update_profile.php`) | Manage admin account (`update_admin.php`) |
| View personal course list (`my_courses.php`) | View all messages (`view_messages.php`) |
| Contact support (`contact_support.php`) | Admin navigation bar (`admin_navbar.php`) |

---

## Tech Stack

| Layer | Technology |
|-------|------------|
| Backend | PHP 7.4+ |
| Database | MySQL (see `Database/studyspot.sql`) |
| Front‑end | HTML5, CSS3 (`css/style.css`) |
| Server | Apache / Nginx (any LAMP/LEMP stack) |

---

## Installation

1. **Clone the repository**

   ```bash
   git clone https://github.com/yourusername/study_spot_prototype.git
   cd study_spot_prototype
   ```

2. **Create the database**

   ```bash
   mysql -u root -p < Database/studyspot.sql
   ```

   *Adjust the credentials in `config.php` and `admin/config.php` as needed.*

3. **Configure PHP**

   - Ensure the `pdo_mysql` extension is enabled.
   - Set the document root to the project folder (e.g., `/var/www/html/study_spot_prototype`).

4. **Update configuration files**

   ```php
   // config.php & admin/config.php
   define('DB_HOST', 'localhost');
   define('DB_NAME', 'studyspot');
   define('DB_USER', 'YOUR_DB_USERNAME');
   define('DB_PASS', 'YOUR_DB_PASSWORD');
   ```

5. **Set proper permissions**

   ```bash
   chmod -R 755 .
   ```

6. **Start the server**

   - For Apache: restart with `sudo service apache2 restart`.
   - For built‑in PHP server (development only):

     ```bash
     php -S localhost:8000
     ```

---

## Usage

1. **Access the site**

   Open a browser and navigate to `http://localhost/` (or the domain you configured).

2. **Student workflow**

   - Register via **Register** → `register.php`.
   - Log in via **Login** → `login.php`.
   - Browse courses on the home page (`home.php`) or `view_courses.php`.
   - Enroll with **Enroll** → `enroll.php`.
   - View and manage your courses via **My Courses** → `my_courses.php`.
   - Update profile → `update_profile.php`.
   - Contact support → `contact_support.php`.

3. **Admin workflow**

   - Log in via **Admin Login** → `admin/admin_login.php`.
   - Use the admin navigation bar to:
     - Add / edit / delete courses (`add_courses.php`, `edit_courses.php`).
     - View enrolled users (`enrolled_users.php`).
     - Reply to messages