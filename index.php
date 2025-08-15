<?php
require_once __DIR__ . '/app/setup.php';
require_once __DIR__ . '/app/db_connect.php';

$sections = [];
if ($result = $conn->query("SELECT section_id, dept, year FROM Section ORDER BY year, dept, section_id")) {
    while ($row = $result->fetch_assoc()) {
        $sections[] = $row;
    }
}

$departments = [];
if ($result = $conn->query("SELECT DISTINCT dept FROM Faculty ORDER BY dept")) {
    while ($row = $result->fetch_assoc()) {
        $departments[] = $row['dept'];
    }
}

echo '<script>';
echo 'const sectionsData = ' . json_encode($sections) . ';';
echo 'const departmentsData = ' . json_encode($departments) . ';';
echo '</script>';
require_once __DIR__ . '\views\index.html';
?>
