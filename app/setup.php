<?php
require_once "db_connect.php";

$tableCheck = $conn->query("SHOW TABLES LIKE 'users'");

if ($tableCheck->num_rows == 0) {
    $sql = "
    CREATE TABLE Users (
        user_id VARCHAR(10) PRIMARY KEY,
        username VARCHAR(30) NOT NULL,
        password VARCHAR(255) NOT NULL,
        role VARCHAR(10) CHECK (role IN ('admin','faculty','student'))
    );

    CREATE TABLE Faculty (
        faculty_id VARCHAR(10) PRIMARY KEY,
        dept VARCHAR(20),
        FOREIGN KEY (faculty_id) REFERENCES Users(user_id)
    );

    CREATE TABLE Student (
        student_id VARCHAR(10) PRIMARY KEY,
        section_id VARCHAR(10),
        FOREIGN KEY (student_id) REFERENCES Users(user_id)
    );

    CREATE TABLE Section (
        section_id VARCHAR(10) PRIMARY KEY,
        year INT,
        dept VARCHAR(20),
        faculty_id VARCHAR(10),
        FOREIGN KEY (faculty_id) REFERENCES Faculty(faculty_id)
    );

    CREATE TABLE Courses_Available (
        course_id VARCHAR(10) PRIMARY KEY,
        name VARCHAR(50),
        year INT
    );

    CREATE TABLE Assigned_Class (
        course_id VARCHAR(10),
        section_id VARCHAR(10),
        faculty_id VARCHAR(10),
        PRIMARY KEY (course_id, section_id, faculty_id),
        FOREIGN KEY (course_id) REFERENCES Courses_Available(course_id),
        FOREIGN KEY (section_id) REFERENCES Section(section_id),
        FOREIGN KEY (faculty_id) REFERENCES Faculty(faculty_id)
    );

    CREATE TABLE Marks (
        student_id VARCHAR(10),
        course_id VARCHAR(10),
        mid1 INT,
        asgn1 INT,
        mid2 INT,
        asgn2 INT,
        sem INT,
        PRIMARY KEY (student_id, course_id),
        FOREIGN KEY (student_id) REFERENCES Student(student_id),
        FOREIGN KEY (course_id) REFERENCES Courses_Available(course_id)
    );
    ";
    
    if ($conn->multi_query($sql)) {
        do {} while ($conn->next_result());
    }
}
?>
