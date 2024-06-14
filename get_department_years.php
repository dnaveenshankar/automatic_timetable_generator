<?php
// Include your database connection
require_once 'db_connection.php';

if (isset($_GET['department_id'])) {
    $departmentId = $_GET['department_id'];

    // Fetch years for the selected department
    $query = "SELECT years FROM departments WHERE department_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $departmentId);
    $stmt->execute();
    $stmt->bind_result($yearsResult);
    $stmt->fetch();
    $stmt->close();

    $years = explode(',', $yearsResult);
    echo json_encode($years);
} else {
    echo json_encode([]);
}
?>
