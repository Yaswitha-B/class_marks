<?php
require_once "db_connect.php";
session_start();

// Redirect with message
function redirectWithMessage($location, $message) {
    $_SESSION['flash_message'] = $message;
    header("Location: $location");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    redirectWithMessage("../index.php", "Invalid request.");
}

$role = trim($_POST['role'] ?? '');
$user_id = trim($_POST['user_id'] ?? '');
$username = trim($_POST['username'] ?? '');
$password = $_POST['password'] ?? '';
$dept = trim($_POST['dept'] ?? '');
$section_id = trim($_POST['section_id'] ?? '');

// Basic validation
if (!$role || !$user_id || !$username || !$password) {
    redirectWithMessage("../index.php", "All fields are required.");
}

try {
    // Start transaction
    $conn->begin_transaction();

    // Insert into Users
    $stmt = $conn->prepare("INSERT INTO Users (user_id, username, password, role) VALUES (?, ?, ?, ?)");
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    $stmt->bind_param("ssss", $user_id, $username, $hashed_password, $role);
    $stmt->execute();
    $stmt->close();

    // Insert into role-specific table
    if ($role === "student") {
        if (!$section_id) {
            throw new Exception("Section is required for student.");
        }
        $stmt = $conn->prepare("INSERT INTO Student (student_id, section_id) VALUES (?, ?)");
        $stmt->bind_param("ss", $user_id, $section_id);
    } elseif ($role === "faculty") {
        if (!$dept) {
            throw new Exception("Department is required for faculty.");
        }
        $stmt = $conn->prepare("INSERT INTO Faculty (faculty_id, dept) VALUES (?, ?)");
        $stmt->bind_param("ss", $user_id, $dept);
    } elseif ($role === "admin") {
        // Optional: Insert into Admin table if exists
        $stmt = null; // no extra insert
    } else {
        throw new Exception("Invalid role.");
    }

    if ($stmt) {
        $stmt->execute();
        $stmt->close();
    }

    // Commit changes
    $conn->commit();
    switch ($role) {
    case "admin":
        header("Location: ../admin_dashboard.php");
        break;
    case "faculty":
        header("Location: ../faculty_dashboard.php");
        break;
    case "student":
        header("Location: ../student_dashboard.php");
        break;
    default:
        redirectWithMessage("index.php", "Invalid role.");
}
exit();

} catch (Exception $e) {
    $conn->rollback();
    redirectWithMessage("../index.php", "Registration failed: " . $e->getMessage());
}
