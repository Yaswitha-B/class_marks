<?php
require_once "db_connect.php";
session_start();

function redirectWithMessage($location, $message) {
    $_SESSION['flash_message'] = $message;
    header("Location: $location");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    redirectWithMessage("../index.php", "Invalid request.");
}

$role = trim($_POST['role'] ?? '');
$username = trim($_POST['username'] ?? '');
$password = $_POST['password'] ?? '';

if (!$role || !$username || !$password) {
    redirectWithMessage("index.php", "All fields are required.");
}

$stmt = $conn->prepare("SELECT user_id, password FROM Users WHERE username = ? AND role = ?");
$stmt->bind_param("ss", $username, $role);
$stmt->execute();
$stmt->store_result();

if ($stmt->num_rows === 0) {
    $stmt->close();
    redirectWithMessage("../index.php", "User not found or role mismatch.");
}

$stmt->bind_result($user_id, $hashed_password);
$stmt->fetch();

if (!password_verify($password, $hashed_password)) {
    $stmt->close();
    redirectWithMessage("../index.php", "Invalid password.");
}

$stmt->close();

// Login success
$_SESSION['user_id'] = $user_id;
$_SESSION['username'] = $username;
$_SESSION['role'] = $role;

// Redirect based on role
switch ($role) {
    case "admin":
        header("Location: ../app/admin_dashboard.php");
        break;
    case "faculty":
        header("Location: ../app/faculty_dashboard.php");
        break;
    case "student":
        header("Location: ../student_dashboard.php");
        break;
    default:
        redirectWithMessage("index.php", "Invalid role.");
}
exit();
