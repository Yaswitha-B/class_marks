<?php
require_once __DIR__ . '/db_connect.php';
session_start();

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../index.php');
    exit();
}

// Serve HTML if no action
if ($_SERVER['REQUEST_METHOD'] === 'GET' && !isset($_GET['action'])) {
    include __DIR__ . '\..\views\admin_dashboard.html';
    exit();
}

// Handle AJAX
$action = $_REQUEST['action'] ?? '';
$data = $_POST['data'] ?? $_REQUEST['data'] ?? '';

// Decode JSON if string
if (is_string($data)) $data = json_decode($data,true);

switch ($action) {
    case 'fetch':
        fetchData($conn);
        break;
    case 'add_sections':
        addSection($conn, $data);
        break;
    case 'add_courses':
        addCourse($conn, $data);
        break;
    case 'add_assigned':
        assignCourse($conn, $data);
        break;
    default:
        echo json_encode(['error'=>'Invalid action']);
        break;
}

// ---------------- Functions ----------------
function fetchData($conn) {
    $tables = ['Student','Faculty','Section','Courses_Available','Assigned_Class'];
    $data = [];
    foreach($tables as $t){
        $res = $conn->query("SELECT * FROM $t");
        $data[strtolower($t)] = $res->fetch_all(MYSQLI_ASSOC);
    }
    echo json_encode($data);
}

function addSection($conn,$d){
    $id=$d['section_id']??''; $year=$d['year']??''; $dept=$d['dept']??''; $fac=$d['faculty_id']??'';
    if($id && $year){
        $stmt=$conn->prepare('INSERT INTO Section(section_id,year,dept,faculty_id) VALUES(?,?,?,?)');
        $stmt->bind_param('siss',$id,$year,$dept,$fac); $stmt->execute();
        echo json_encode(['success'=>true]);
    } else echo json_encode(['error'=>'Invalid data']);
}

function addCourse($conn,$d){
    $id=$d['course_id']??''; $name=$d['name']??''; $year=$d['year']??'';
    if($id && $name && $year){
        $stmt=$conn->prepare('INSERT INTO Courses_Available(course_id,name,year) VALUES(?,?,?)');
        $stmt->bind_param('ssi',$id,$name,$year); $stmt->execute();
        echo json_encode(['success'=>true]);
    } else echo json_encode(['error'=>'Invalid data']);
}

function assignCourse($conn,$d){
    $c=$d['course_id']??''; $s=$d['section_id']??''; $f=$d['faculty_id']??'';
    if($c && $s && $f){
        $stmt=$conn->prepare('INSERT INTO Assigned_Class(course_id,section_id,faculty_id) VALUES(?,?,?)');
        $stmt->bind_param('sss',$c,$s,$f); $stmt->execute();
        echo json_encode(['success'=>true]);
    } else echo json_encode(['error'=>'Invalid data']);
}
?>
