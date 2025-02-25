# Online Exam System 🖥️📝

A web-based platform for conducting online examinations with automated evaluation and real-time monitoring.

![Online Exam System Demo](screenshots/demo.gif) *Add screenshots/demo GIF if available*

## Features ✨

- **User Roles:**
  - 👨💻 Admin: Create exams, manage questions, and view results
  - 🧑🎓 Student: Take exams, view scores, and check history
- **Exam Management:**
  - ⏱️ Timed exams with auto-submission
  - 📝 Multiple question types (MCQ, True/False, Short Answers)
  - 📊 Result generation with scores
- **Security:**
  - 🔑 User authentication system
  - 🛡️ Session management
  - ⚠️ Cheat prevention mechanisms
- **Additional Features:**
  - 📅 Exam scheduling
  - 📈 Performance analytics
  - 📤📥 Import/Export questions (CSV/Excel)

## Technologies Used 💻

- **Frontend:** HTML, CSS, JavaScript, Bootstrap
- **Backend:** PHP
- **Database:** MySQL
- **Tools:** XAMPP/WAMP, Composer (if used)
- **Dependencies:** (List any important libraries/packages)

## Installation ⚙️

1. **Prerequisites:**
   - XAMPP/WAMP server installed
   - PHP >= 7.0
   - MySQL >= 5.6

2. **Setup Instructions:**
   ```bash
   # Clone repository
   git clone https://github.com/Souvikparua/Online_Exam_System.git
   # Move to project directory
   cd Online_Exam_System

3 . Database Configuration:

Create MySQL database: online_exam

Import SQL file: database/exam_db.sql

Update config file: include/config.php
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'online_exam');
