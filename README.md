# Final Fantasy Character Database

PHP and MySQL web app built for a Web Development continuous assessment.

## Features
- CRUD: add, edit, delete, list characters
- Role system (t_roles) and character table (t_characters)
- Team Builder with stat aggregation
- Battle page (Emerald Weapon) and analysis page

## Tech
PHP, MySQL, procedural MySQLi, HTML/CSS, sessions

## Run locally
1. Clone the repo
2. Place the folder in your server root (htdocs / www)
3. Create a MySQL database called `ff_characters`
4. Import `ff_characters.sql`
5. Copy `includes/db.example.php` to `includes/db.php`
6. Update credentials in `includes/db.php`
7. Visit `http://localhost/final_fantasy_db/index.php`

## Notes
GitHub hosts the source code. The project requires PHP + MySQL to run.
