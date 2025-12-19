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

## Running the Project L
