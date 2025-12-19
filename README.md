# Final Fantasy Character Database

A PHP and MySQL web application built as part of a Web Development continuous assessment.  
The project focuses on backend logic, relational data modelling, and session based state management rather than front end frameworks.

---

## Project Overview

This application is a character database and team builder inspired by the Final Fantasy series.  
Users can create, manage, and analyse characters, assemble a party, and test that party against a predefined battle scenario.

The emphasis of the project is on clean server side logic, structured data relationships, and predictable application flow.

---

## Core Features

- Full CRUD functionality for characters: create, read, update, delete
- Relational database design using roles and characters tables
- Procedural MySQLi queries with prepared statements
- Team Builder that aggregates party stats across multiple characters
- Battle logic against Emerald Weapon with win condition analysis
- Session based team persistence across pages
- Structured multi page PHP application with shared includes

---

## Technical Stack

- PHP
- MySQL
- Procedural MySQLi
- HTML and CSS
- PHP sessions for state management

No external frameworks are used. The application logic is implemented using core PHP to demonstrate understanding of fundamentals.

---

## Database Structure

- **t_characters**  
  Stores character details including role, stats, and portrait reference

- **t_roles**  
  Stores character roles used for classification and filtering

The schema is designed to support relational integrity and future extensibility.

---

## Running the Project Locally

This project is designed to run locally in a PHP and MySQL environment such as MAMP, XAMPP, or similar.

1. Clone the repository
2. Place the project folder in your server root directory  
   Example: `htdocs` or `www`
3. Create a MySQL database named `ff_characters`
4. Import the provided `ff_characters.sql` file
5. Copy `includes/db.example.php` to `includes/db.php`
6. Update database credentials in `includes/db.php`
7. Open the project in a browser  
   `http://localhost/final_fantasy_db/index.php`

---

## Notes on Hosting

GitHub hosts the source code only.  
Because the project uses PHP, MySQL, and sessions, it must be run in a server environment and cannot be executed directly via GitHub Pages.

---

## Learning Outcomes Demonstrated

- Server side application structure in PHP
- Relational database design and usage
- Procedural MySQLi with prepared statements
- State management using sessions
- Separation of concerns through includes and reusable components
- Translating a functional specification into a working application
