<?php
session_start();
if ($_SESSION['role'] !== 'faculty') {
    header('Location: index.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Faculty Dashboard</title>
</head>
<body>
    <h1>Welcome to the Faculty Dashboard</h1>
    <p>View and manage your classes, assignments, and student performance here.</p>
</body>
</html>