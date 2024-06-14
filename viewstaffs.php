<?php
session_start();

// Include your existing database connection
require_once 'db_connection.php';

// Retrieve user information from the session
if (!isset($_SESSION['username'])) {
    header('Location: login.php');
    exit();
}

// Get the department ID from the query parameters
if (isset($_GET['department_id'])) {
    $departmentId = $_GET['department_id'];

    // Use prepared statement to prevent SQL injection
    $getDepartmentInfoQuery = "SELECT name FROM departments WHERE department_id = ?";
    $stmt = $conn->prepare($getDepartmentInfoQuery);
    $stmt->bind_param("i", $departmentId);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        // Department exists, retrieve department name
        $stmt->bind_result($departmentName);
        $stmt->fetch();
    } else {
        echo "<script>alert('Invalid department ID or unauthorized access.');";
        echo "window.location.href='viewdepartments.php';</script>";
        exit();
    }

    $stmt->close();
} else {
    echo "<script>alert('Department ID not provided.'); window.location.href='viewdepartments.php';</script>";
    exit();
}

// Get staffs for the selected department
$getStaffsQuery = "SELECT staff_id, name, role FROM staffs WHERE department_id = ?";
$stmt = $conn->prepare($getStaffsQuery);
$stmt->bind_param("i", $departmentId);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <style>
        body {
            background-color: #3498db;
            color: #fff;
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
            background-color: #fff;
            border-radius: 10px;
            box-shadow: 0px 0px 20px 0px rgba(0, 0, 0, 0.2);
            padding: 40px;
            max-width: 800px; /* Adjusted the width of the container */
            text-align: center;
            overflow: auto;
            max-height: 100vh; 
        }

        .form-container {
            display: flex;
            flex-direction: column;
            align-items: center;
            margin-bottom: 20px;
        }

        h2 {
            color: #3498db;
        }

        table {
            width: 100%;
            margin-top: 20px;
            border-collapse: collapse;
            border-spacing: 0;
        }

        table, th, td {
            border: 1px solid #3498db;
            color: #3498db; /* Changed text color to blue */
        }

        th, td {
            padding: 12px;
            text-align: left;
        }

        th {
            background-color: #fff; /* Changed background color to white */
            color: #3498db;
        }

        .footer {
            margin-top: 20px;
            color: #3498db;
        }

        .back-btn {
            margin-top: 20px;
        }
    </style>
</head>
<body>

<div class="container">
    <div class="form-container">
        <h2>Staffs for Department: <?= $departmentName ?></h2>

        <table>
            <thead>
                <tr>
                    <th>Staff ID</th>
                    <th>Name</th>
                    <th>Role</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $result->fetch_assoc()) { ?>
                    <tr>
                        <td><?= $row['staff_id'] ?></td>
                        <td><?= $row['name'] ?></td>
                        <td><?= $row['role'] ?></td>
                    </tr>
                <?php } ?>
            </tbody>
        </table>

        <a href="viewdepartments.php" class="btn btn-primary back-btn">Back to Departments</a>
    </div>

    <div class="footer">
        Automatic Timetable Generator
    </div>
</div>

<!-- Bootstrap JS and Popper.js -->
<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.3/dist/umd/popper.min.js"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

</body>
</html>
