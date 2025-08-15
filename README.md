# Class Marks Management System

## Overview
The Class Marks Management System is a PHP + MySQL-based web application for managing courses, sections, faculty, students, and marks.  
It provides an easy-to-use interface for administrators, faculty, and students to register, log in, and view/manage academic records.  

The system is designed to be clean, dynamic, and maintainable — no hardcoded values for departments or sections.  
All dropdowns and selection fields are populated directly from the database.

## Features
- Role-based access: Admin, Faculty, and Student logins.
- Dynamic registration:
  - Departments are fetched from the `Faculty` table.
  - Sections are fetched from the `Section` table.
- Marks management:
  - Faculty can add and update marks for their assigned classes.
  - Students can view their marks by semester and course.


