<?php
// Include your existing database connection
require_once 'db_connection.php';

// Check if the department_id is provided via GET request
if (!isset($_GET['department_id'])) {
    // Return an empty array if department_id is not provided
    echo json_encode([]);
    exit();
}

// Retrieve department_id from the GET request
$departmentId = $_GET['department_id'];

// Prepare and execute the query to fetch staff names for the given department_id
$getStaffNamesQuery = "SELECT staff_id, name FROM staffs WHERE department_id = ?";
$stmt = $conn->prepare($getStaffNamesQuery);
$stmt->bind_param("i", $departmentId);
$stmt->execute();
$result = $stmt->get_result();

// Fetch staff names into an array
$staffNames = [];

// Add an "Other" option with ID 'other'
$staffNames[] = [
    'staff_id' => 'other',
    'name' => 'Other'
];

while ($row = $result->fetch_assoc()) {
    $staffNames[] = [
        'staff_id' => $row['staff_id'],
        'name' => $row['name']
    ];
}

// Close the database connection and free the result set
$stmt->close();
$conn->close();

// Return the staff names as JSON
echo json_encode($staffNames);
?>
