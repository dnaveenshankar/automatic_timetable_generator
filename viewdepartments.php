<?php
session_start();

// Include your existing database connection
require_once 'db_connection.php';

// Retrieve user information from the session
if (!isset($_SESSION['username'])) {
    header('Location: login.php');
    exit();
}

// Get departments associated with the logged-in user
$getDepartmentsQuery = "SELECT department_id, name FROM departments WHERE username = ?";
$stmt = $conn->prepare($getDepartmentsQuery);
$stmt->bind_param("s", $_SESSION['username']);
$stmt->execute();
$result = $stmt->get_result();

// Handle form submission to view or manage staffs
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get data from the form
    $selectedDepartmentId = $_POST['departmentId'];
    $action = $_POST['action'];

    if ($action === 'view') {
        // Redirect to viewstaffs.php with selected department ID
        header("Location: viewstaffs.php?department_id=$selectedDepartmentId");
        exit();
    } elseif ($action === 'add') {
        // Redirect to addstaffs.php with selected department ID
        header("Location: addstaffs.php?department_id=$selectedDepartmentId");
        exit();
    } elseif ($action === 'remove') {
        // Redirect to removestaffs.php with selected department ID
        header("Location: removestaffs.php?department_id=$selectedDepartmentId");
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <!-- Your styles -->
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
            max-width: 500px; /* Adjusted the width of the container */
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
        <h2 style="color: #3498db;">View or Manage Departments</h2>

        <!-- Form for selecting and managing departments -->
        <form method="post" action="">
            <label for="departmentId" style="color: #3498db;">Select Department:</label>
            <select name="departmentId" id="departmentId" required>
                <?php while ($row = $result->fetch_assoc()) { ?>
                    <option value="<?= $row['department_id'] ?>"><?= $row['name'] ?></option>
                <?php } ?>
            </select>

            <button type="submit" name="action" value="view">View Staffs</button>
            <button type="submit" name="action" value="add">Add Staff</button>
            <button type="submit" name="action" value="remove">Remove Staff</button>
        </form>

        <a href="dashboard.php" class="btn btn-primary back-btn">Back to Dashboard</a>
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
