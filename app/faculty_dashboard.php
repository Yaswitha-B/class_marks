<?php
require_once __DIR__ . '/db_connect.php';
session_start();

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'faculty') {
    header('Location: ../index.php');
    exit();
}

// Serve HTML if no action
if ($_SERVER['REQUEST_METHOD'] === 'GET' && !isset($_GET['action'])) {
    include __DIR__ . '/../views/faculty_dashboard.html';
    exit();
}

// Handle AJAX
$action = $_REQUEST['action'] ?? '';
$data = $_POST['data'] ?? $_REQUEST['data'] ?? '';
$faculty_id = $_SESSION['user_id'] ?? '';

// Decode JSON if string
if (is_string($data)) $data = json_decode($data, true);

switch ($action) {
    case 'fetch_sections':
        fetchSections($conn, $faculty_id);
        break;
    case 'fetch_marks':
        fetchMarks($conn, $data['section_id']);
        break;
    case 'add_marks':
        addMarks($conn, $data);
        break;
    case 'update_marks':
        updateMarks($conn, $data);
        break;
    case 'delete_marks':
        deleteMarks($conn, $data);
        break;
    default:
        echo json_encode(['error' => 'Invalid action']);
        break;
}

// ---------------- Functions ----------------
function fetchSections($conn, $faculty_id) {
    $stmt = $conn->prepare("SELECT DISTINCT s.section_id, s.year, s.dept, c.course_id, c.name as course_name 
                           FROM Section s 
                           JOIN Assigned_Class ac ON s.section_id = ac.section_id 
                           JOIN Courses_Available c ON ac.course_id = c.course_id 
                           WHERE ac.faculty_id = ?");
    $stmt->bind_param('s', $faculty_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $sections = $result->fetch_all(MYSQLI_ASSOC);
    echo json_encode(['sections' => $sections]);
}

function fetchMarks($conn, $section_id) {
    $faculty_id = $_SESSION['user_id'];
    $stmt = $conn->prepare("SELECT m.*, u.username as student_name, c.name as course_name 
                           FROM Marks m 
                           JOIN Student s ON m.student_id = s.student_id 
                           JOIN Users u ON s.student_id = u.user_id 
                           JOIN Courses_Available c ON m.course_id = c.course_id 
                           JOIN Assigned_Class ac ON (ac.section_id = s.section_id AND ac.course_id = m.course_id)
                           WHERE s.section_id = ? AND ac.faculty_id = ?");
    $stmt->bind_param('ss', $section_id, $faculty_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $marks = $result->fetch_all(MYSQLI_ASSOC);
    echo json_encode(['marks' => $marks]);
}

function addMarks($conn, $data) {
    $stmt = $conn->prepare('INSERT INTO Marks(student_id, course_id, mid1, asgn1, mid2, asgn2, sem) VALUES(?, ?, ?, ?, ?, ?, ?)');
    $stmt->bind_param('ssiiiis', 
        $data['student_id'], 
        $data['course_id'], 
        $data['mid1'], 
        $data['asgn1'], 
        $data['mid2'], 
        $data['asgn2'], 
        $data['sem']
    );
    if ($stmt->execute()) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['error' => 'Failed to add marks']);
    }
}

function updateMarks($conn, $data) {
    $stmt = $conn->prepare('UPDATE Marks SET mid1 = ?, asgn1 = ?, mid2 = ?, asgn2 = ?, sem = ? WHERE student_id = ? AND course_id = ?');
    $stmt->bind_param('iiiiiss', 
        $data['mid1'], 
        $data['asgn1'], 
        $data['mid2'], 
        $data['asgn2'], 
        $data['sem'], 
        $data['student_id'], 
        $data['course_id']
    );
    if ($stmt->execute()) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['error' => 'Failed to update marks']);
    }
}

function deleteMarks($conn, $data) {
    $stmt = $conn->prepare('DELETE FROM Marks WHERE student_id = ? AND course_id = ?');
    $stmt->bind_param('ss', $data['student_id'], $data['course_id']);
    if ($stmt->execute()) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['error' => 'Failed to delete marks']);
    }
}
?>