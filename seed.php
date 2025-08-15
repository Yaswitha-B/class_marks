<?php
// seed.php — run once in dev/testing to populate DB
require_once __DIR__ . "/app/db_connect.php";

function hp($p) { return password_hash($p, PASSWORD_DEFAULT); }
function execQ($conn, $q) {
    if (!$conn->query($q)) {
        echo "<p style='color:red'>Error: " . $conn->error . "<br>Query: " . htmlspecialchars($q) . "</p>";
    }
}

// 1) Wipe all relevant tables
echo "<h2>Wiping tables...</h2>";
execQ($conn, "SET FOREIGN_KEY_CHECKS=0");

$tables = ['Marks', 'Assigned_Class', 'Student', 'Section', 'Courses_Available', 'Faculty', 'Users'];
foreach ($tables as $t) {
    if ($conn->query("TRUNCATE TABLE `$t`") === TRUE) {
        echo "<p>Truncated $t</p>";
    } else {
        execQ($conn, "DELETE FROM `$t`");
        echo "<p>Deleted rows from $t</p>";
    }
}

execQ($conn, "SET FOREIGN_KEY_CHECKS=1");
echo "<p style='color:green'>Wipe complete.</p>";

// 2) Admins
$admins = [
    ['A001','Ravi Kumar','admin123'],
    ['A002','Neha Sharma','admin123']
];
foreach ($admins as $a) {
    execQ($conn, "INSERT INTO Users (user_id, username, password, role) VALUES 
        ('{$a[0]}','{$a[1]}','" . hp($a[2]) . "','admin')");
}

// 3) Faculty
$facultyData = [
    ['F001','Dr. Anil Mehta','fac123','CSE'],
    ['F002','Dr. Priya Singh','fac123','ECE'],
    ['F003','Dr. Ramesh Iyer','fac123','ME'],
    ['F004','Dr. Deepa Nair','fac123','CSE'],
    ['F005','Dr. Suresh Gupta','fac123','ECE']
];
foreach ($facultyData as $f) {
    execQ($conn, "INSERT INTO Users (user_id, username, password, role) VALUES 
        ('{$f[0]}','{$f[1]}','" . hp($f[2]) . "','faculty')");
    execQ($conn, "INSERT INTO Faculty (faculty_id, dept) VALUES ('{$f[0]}','{$f[3]}')");
}

// 4) Sections
$sections = [
    ['2CSE1', 2, 'CSE', 'F001'],
    ['2CSE2', 2, 'CSE', 'F004'],
    ['3CSE1', 3, 'CSE', 'F001'],
    ['3ECE1', 3, 'ECE', 'F002'],
    ['3ECE2', 3, 'ECE', 'F005'],
    ['4ME1', 4, 'ME', 'F003']
];
foreach ($sections as $s) {
    execQ($conn, "INSERT INTO Section (section_id, year, dept, faculty_id) VALUES 
        ('{$s[0]}', {$s[1]}, '{$s[2]}', '{$s[3]}')");
}

// 5) Students
$students = [
    ['S001','Aman Verma','stu123','2CSE1'],
    ['S002','Kavya Reddy','stu123','2CSE1'],
    ['S003','Mohit Jain','stu123','2CSE2'],
    ['S004','Sneha Kapoor','stu123','3CSE1'],
    ['S005','Arjun Das','stu123','4ME1'],
    ['S006','Pooja Menon','stu123','3ECE1'],
    ['S007','Rahul Nair','stu123','3ECE2'],
    ['S008','Fatima Khan','stu123','2CSE2'],
    ['S009','Vikram Patel','stu123','3CSE1'],
    ['S010','Isha Patel','stu123','4ME1'],
    ['S011','Karan Shah','stu123','3ECE1'],
    ['S012','Meera Krishnan','stu123','2CSE1']
];
foreach ($students as $st) {
    execQ($conn, "INSERT INTO Users (user_id, username, password, role) VALUES 
        ('{$st[0]}','{$st[1]}','" . hp($st[2]) . "','student')");
    execQ($conn, "INSERT INTO Student (student_id, section_id) VALUES ('{$st[0]}','{$st[3]}')");
}

// 6) Courses
$courses = [
    ['C201','Data Structures',2],
    ['C202','Database Systems',2],
    ['C301','Algorithms',3],
    ['C302','Operating Systems',3],
    ['C303','Digital Electronics',3],
    ['C401','Thermodynamics',4],
    ['C402','Fluid Mechanics',4]
];
foreach ($courses as $c) {
    execQ($conn, "INSERT INTO Courses_Available (course_id, name, year) VALUES 
        ('{$c[0]}','{$c[1]}',{$c[2]})");
}

// 7) Assigned Classes (no duplicates)
$assigned = [
    ['C201','2CSE1','F001'],
    ['C202','2CSE1','F001'],
    ['C201','2CSE2','F004'],
    ['C202','2CSE2','F004'],
    ['C301','3CSE1','F001'],
    ['C302','3CSE1','F004'],
    ['C303','3ECE1','F002'],
    ['C303','3ECE2','F005'],
    ['C401','4ME1','F003'],
    ['C402','4ME1','F003']
];
foreach ($assigned as $a) {
    execQ($conn, "INSERT INTO Assigned_Class (course_id, section_id, faculty_id) VALUES 
        ('{$a[0]}','{$a[1]}','{$a[2]}')");
}

// 8) Marks
$sectionYear = [];
$res = $conn->query("SELECT section_id, year FROM Section");
while ($r = $res->fetch_assoc()) {
    $sectionYear[$r['section_id']] = (int)$r['year'];
}

foreach ($students as $st) {
    $sid = $st[0];
    $ssection = $st[3];
    $syear = $sectionYear[$ssection] ?? null;
    if ($syear === null) continue;

    foreach ($courses as $c) {
        if ($c[2] === $syear) {
            $mid1 = rand(18, 28);
            $asgn1 = rand(6, 10);
            $mid2 = rand(18, 28);
            $asgn2 = rand(6, 10);
            execQ($conn, "INSERT INTO Marks (student_id, course_id, mid1, asgn1, mid2, asgn2, sem)
                          VALUES ('$sid','{$c[0]}',$mid1,$asgn1,$mid2,$asgn2,$syear)");
        }
    }
}

echo "<p style='color:green'>✅ Seeding complete. Review tables in phpMyAdmin or your app.</p>";
echo "<p>Remove or rename <strong>seed.php</strong> after successful run to avoid overwriting data.</p>";
