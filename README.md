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
**Setup Instructions:**
```bash
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'online_exam');


4. Run Application:

Start Apache and MySQL in XAMPP/WAMP

Access via: http://localhost/Online_Exam_System

Usage 🚀
Admin Access:

URL: /admin

Default Credentials: admin / admin123

Features:

Create/edit exams

Manage questions

View student results

Generate reports

Student Access:

Register new account

Features:

View available exams

Take timed exams

View results history

Download score reports

Contributing 🤝
Fork the repository

Create your feature branch (git checkout -b feature/AmazingFeature)

Commit changes (git commit -m 'Add some AmazingFeature')

Push to branch (git push origin feature/AmazingFeature)

Open a Pull Request

Future Enhancements 🚧
Add proctoring capabilities

Implement question randomization

Add live chat support

Develop mobile application

Integrate AI-based cheating detection

License 📄
This project is licensed under the MIT License - see LICENSE file for details

Contact 📧
Souvik Parua

GitHub: @Souvikparua

Email: [your-email@example.com]
