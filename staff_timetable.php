<?php
// Include your database connection file
require_once 'db_connection.php';

// Check if the username is provided in the URL
if (isset($_GET['username'])) {
    // Retrieve username from the URL
    $username = $_GET['username'];

    // Fetch department ID using the provided username
    $getDepartmentIdQuery = "SELECT department_id FROM departments WHERE username = ?";
    $stmtDepartmentId = $conn->prepare($getDepartmentIdQuery);
    $stmtDepartmentId->bind_param("s", $username);
    $stmtDepartmentId->execute();
    $resultDepartmentId = $stmtDepartmentId->get_result();

    if ($resultDepartmentId->num_rows > 0) {
        $rowDepartmentId = $resultDepartmentId->fetch_assoc();
        $departmentId = $rowDepartmentId['department_id'];

        // Fetch department name
        $getDepartmentNameQuery = "SELECT name FROM departments WHERE department_id = ?";
        $stmtDepartmentName = $conn->prepare($getDepartmentNameQuery);
        $stmtDepartmentName->bind_param("i", $departmentId);
        $stmtDepartmentName->execute();
        $resultDepartmentName = $stmtDepartmentName->get_result();

        if ($resultDepartmentName->num_rows > 0) {
            $rowDepartmentName = $resultDepartmentName->fetch_assoc();
            $departmentName = $rowDepartmentName['name'];

            // Fetch staff information for the department
            $getStaffQuery = "SELECT s.staff_id, s.name AS staff_name, s.role FROM staffs s WHERE s.department_id = ?";
            $stmtStaff = $conn->prepare($getStaffQuery);
            $stmtStaff->bind_param("i", $departmentId);
            $stmtStaff->execute();
            $resultStaff = $stmtStaff->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Staff Timetable</title>
    <style>
        /* Your provided CSS styles */
        body {
            background-color: #3498db;
            color: #3498db;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            height: 100vh;
            margin: 0;
            padding: 20px;
            font-size: 16px;
        }

        .container {
            background-color: #ffffff;
            border-radius: 10px;
            box-shadow: 0px 0px 20px 0px rgba(0, 0, 0, 0.2);
            padding: 40px;
            max-width: 800px; /* Adjusted width for better alignment */
            text-align: center;
            overflow: auto;
            max-height: 100vh; 
        }

        .btn-container {
            display: flex;
            flex-direction: column;
            align-items: center;
            margin-top: 20px;
        }

        .btn {
            margin: 10px;
            padding: 12px 24px;
            background-color: #3498db;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            text-decoration: none;
            font-size: 16px;
            transition: background-color 0.3s ease;
        }

        .btn:hover {
            background-color: #2980b9;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        th, td {
            border: 1px solid #dddddd;
            text-align: left;
            padding: 8px;
        }

        th {
            background-color: #f2f2f2;
        }
    </style>
</head>
<body>
<div class="container">
    <h2>Staff Timetable for <?php echo $departmentName; ?></h2>

    <table>
        <thead>
            <tr>
                <th>Staff Name</th>
                <th>Role</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <?php
            // Output staff information in table rows
            while ($rowStaff = $resultStaff->fetch_assoc()) {
                $staffId = $rowStaff['staff_id'];
                $staffName = $rowStaff['staff_name'];
                $role = $rowStaff['role'];
            ?>
            <tr>
                <td><?php echo $staffName; ?></td>
                <td><?php echo $role; ?></td>
                <td><a href="view_staff_timetable.php?department_id=<?php echo $departmentId; ?>&staff_id=<?php echo $staffId; ?>">View</a></td>
            </tr>
            <?php } ?>
        </tbody>
    </table>
    <a href="#" class="back-btn" onclick="history.back()">Back</a>
</div>

</body>
</html>


<?php
        } else {
            echo "Department not found.";
        }

        $stmtDepartmentName->close();
        $stmtStaff->close();
    } else {
        echo "Department not found for the provided username.";
    }

    $stmtDepartmentId->close();
} else {
    echo "Username not provided.";
}
?>
