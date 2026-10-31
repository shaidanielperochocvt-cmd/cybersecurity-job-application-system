# PDO User Entity + Parent/Child Entity Project

This is a working PHP 8+ / MySQL PDO example based on the earlier **Cybersecurity Job Application** project. It adds account registration, login/logout, a protected dashboard, and audit fields so every parent and child record shows who created and last updated it.

## 1. Project structure

```text
perochoshai/
├── assets/style.css
├── core/
│   ├── auth.php
│   ├── bootstrap.php
│   ├── dbconfig.php
│   └── functions.php
├── partials/
│   ├── footer.php
│   └── header.php
├── database.sql
├── register.php
├── login.php
├── logout.php
├── index.php
├── application_create.php
├── application_edit.php
├── application_delete.php
├── skill_create.php
├── skill_edit.php
└── skill_delete.php
```

## 2. Install it in XAMPP

1. Start **Apache** and **MySQL** in the XAMPP Control Panel.
2. Put the `perochoshai` folder inside `C:\xampp\htdocs\`.
3. Open `http://localhost/phpmyadmin/`.
4. Choose **Import**, select this project's `database.sql`, and click **Import**. The script creates/uses the `russian_grocery_db` database and creates the three tables.
5. Open `core/dbconfig.php`. The defaults are `localhost`, database `russian_grocery_db`, username `root`, and a blank password. Change them only if your MySQL setup is different.
6. Open `http://localhost/perochoshai/register.php` and register an account.
7. Log in at `http://localhost/perochoshai/login.php`. The dashboard (`index.php`) is protected; a logged-out visitor is redirected to the login page.

**Important:** Back up your existing database before importing SQL. This script creates the new `users`, `job_applications`, and `application_skills` tables if they do not already exist. It does not automatically convert old application records or alter an older table with a different structure.

## 3. What the entities mean

- **User entity (`users`)**: stores first name, last name, email, phone, address, date of birth, and a securely hashed password.
- **Parent entity (`job_applications`)**: stores each job application. `created_by` identifies the account that inserted it; `updated_by` identifies the account that last changed it; `created_at` and `updated_at` store timestamps.
- **Child entity (`application_skills`)**: stores one skill per row and links it to a job application using `application_id`. It has the same creator/updater audit fields.
- **Relationships**: one user can create many job applications; one job application can have many skills. Deleting a parent application also deletes its child skills through `ON DELETE CASCADE`.

The child entity is separate from the parent so one application can have multiple skills (for example, PHP, SQL, and Network Security) instead of storing a comma-separated list in one field.

## 4. How to demonstrate the audit trail correctly

To prove that `created_by` and `updated_by` identify different people, use two registered accounts:

1. Register **Account A**, log in as Account A, create a job application, and add at least three skills to it.
2. Log out. Register **Account B** (or log in using a second account), then log in as Account B.
3. Edit the application and edit at least one of its skills.
4. Return to the dashboard. The application and edited skill should still show Account A under **Created by**, but Account B under **Last updated by**. Their **Last updated** timestamps should reflect the update.
5. Delete one skill using its own Delete action. Then delete another test application to show that its related child records are also removed.

The system uses prepared PDO statements, password hashing, password verification, session ID regeneration after login, session-based access checks, output escaping, CSRF tokens on forms, and POST requests for deletion.

## 5. Written answer for assignment item 1: steps to implement the User Entity

**Step 1.** I created a `users` table in the MySQL database with an auto-incrementing primary key and personal information columns such as first name, last name, email, phone number, address, and date of birth. I also added a `password_hash` column so passwords are stored securely instead of being saved as plain text.

**Step 2.** I connected the PHP project to the database using PDO and created the registration page. The page validates the user's information, checks that the email is not already registered, hashes the password using `password_hash()`, and inserts the new user using a prepared statement.

**Step 3.** I created the login and logout functions using PHP sessions. After a successful login, the system stores the user's ID in the session and uses that ID in the `created_by` and `updated_by` columns of the parent and child tables. I also protected the dashboard so users must log in before they can access it.

## 6. Screenshots required by the worksheet

Capture the **entire desktop screen** while the application is open. Do not crop the browser window if the worksheet specifically asks for the entire desktop.

| Worksheet section | Minimum screenshots | What to show |
|---|---:|---|
| User registration | 3 | Three successful registrations, showing the registration process and success message/login page after each account is created. Use different email addresses. |
| User logging in | 3 | Three successful login demonstrations. You can use the two accounts in separate sessions and repeat one login after logging out. |
| Parent – inserting | 3 | Three different job applications added; show each successful save and the dashboard row with its Created by value. |
| Parent – updating | 3 | Three update demonstrations; show the edit form and the updated dashboard row with Last updated by and Last updated. |
| Parent – deleting | 2 | Two deletion demonstrations; show the confirmation/action and the dashboard after the selected record is removed. |
| Child – inserting | 3 | Three skill records added to a parent application; show the dashboard's Application Skills table. |
| Child – updating | 3 | Three skill edits; show the changed skill and the Last updated by/timestamp columns. |
| Child – deleting | 2 | Two skill deletions; show the dashboard after each selected skill is removed. |
| Logging out | 2 | Click Log out, then show the login page and the result of trying to open `index.php` while logged out (it should redirect to login). |

For every insert/update section, show the dashboard table after saving so the instructor can see the creator/updater and timestamp fields. Use test information only; do not put real sensitive personal data in screenshots or in a public GitHub repository.

## 7. GitHub submission

Create a new repository on GitHub, then run these commands from the project folder in Command Prompt or the VS Code terminal (replace the URL with your own repository URL):

```bash
git init
git add .
git commit -m "Add PDO user authentication and entity audit trail"
git branch -M main
git remote add origin https://github.com/YOUR-USERNAME/YOUR-REPOSITORY.git
git push -u origin main
```

Paste your actual GitHub repository URL into item 4 of the worksheet. Do not submit a made-up URL. Do not upload real passwords, database backups containing personal data, or real user information.
