<?php
session_start();

// Include your existing database connection
require_once 'db_connection.php';

// Retrieve user information from the session
if (!isset($_SESSION['username'])) {
    header('Location: login.php');
    exit();
}

// Retrieve department_id from the query parameters
if (!isset($_GET['department_id'])) {
    echo "<script>alert('Department ID not provided.'); window.location.href='dashboard.php';</script>";
    exit();
}

$departmentId = $_GET['department_id'];

// Get department name
$getDepartmentNameQuery = "SELECT name FROM departments WHERE department_id = ?";
$stmt = $conn->prepare($getDepartmentNameQuery);
$stmt->bind_param("i", $departmentId);
$stmt->execute();
$stmt->store_result();

if ($stmt->num_rows > 0) {
    $stmt->bind_result($departmentName);
    $stmt->fetch();
} else {
    echo "<script>alert('Invalid department ID or unauthorized access.'); window.location.href='dashboard.php';</script>";
    exit();
}

$stmt->close();

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get data from the form
    $action = $_POST['action'];
    $staffId = $_POST['staffId'];

    if ($action === 'remove') {
        // Remove staff from the department
        $removeStaffQuery = "UPDATE staffs SET department_id = NULL WHERE staff_id = ?";
        $stmt = $conn->prepare($removeStaffQuery);
        $stmt->bind_param("i", $staffId);
        $stmt->execute();
        $stmt->close();

        echo "<script>alert('Staff removed successfully.'); window.location.href='removestaffs.php?department_id=$departmentId';</script>";
        exit();
    }
}

// Get staffs associated with the department
$getStaffsQuery = "SELECT staff_id, name, role FROM staffs WHERE department_id = ?";
$stmt = $conn->prepare($getStaffsQuery);
$stmt->bind_param("i", $departmentId);
$stmt->execute();
$result = $stmt->get_result();
$stmt->close();
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
            max-width: 800px; 
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

        label {
            margin-bottom: 10px;
            color: #3498db;
        }

        select {
            width: 100%;
            padding: 10px;
            margin-bottom: 20px;
            border: 1px solid #3498db;
            border-radius: 5px;
            box-sizing: border-box;
            color: #3498db;
        }

        button {
            padding: 12px 24px;
            margin-top: 10px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            background-color: #3498db;
            color: #fff;
        }

        button:hover {
            background-color: #2980b9;
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
        <h2 style="color: #3498db;">Manage Staffs for <?= $departmentName ?></h2>

        <!-- Form for adding or removing staffs -->
        <form method="post" action="">
            <label for="staffId" style="color: #3498db;">Select Staff:</label>
            <select name="staffId" id="staffId" required>
                <?php while ($row = $result->fetch_assoc()) { ?>
                    <option value="<?= $row['staff_id'] ?>"><?= $row['name'] ?> - <?= $row['role'] ?></option>
                <?php } ?>
            </select>
            <button type="submit" name="action" value="remove">Remove from Department</button>
        </form>

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
