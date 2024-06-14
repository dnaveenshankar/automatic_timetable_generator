<?php
session_start();

// Include your existing database connection
require_once 'db_connection.php';

// Initialize variables
$selectedDepartmentId = null;
$selectedDepartmentYears = array();

// Retrieve user information from the session
if (!isset($_SESSION['username'])) {
    header('Location: login.php');
    exit();
}

// Handle form submission to view staffs and years
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get data from the form
    $selectedDepartmentId = $_POST['departmentId'];
    $action = $_POST['action'];

    if ($action === 'confirm') {
        // Retrieve years for the selected department from the departments table
        $getDepartmentYearsQuery = "SELECT years FROM departments WHERE department_id = ?";
        $stmt = $conn->prepare($getDepartmentYearsQuery);
        $stmt->bind_param("i", $selectedDepartmentId);
        $stmt->execute();
        $stmt->bind_result($years);
        $stmt->fetch();
        $stmt->close();

        // Convert years string to array
        $selectedDepartmentYears = explode(',', $years);

        // Redirect to template.php with selected department ID and years
        header("Location: template.php?department_id=$selectedDepartmentId&years=" . urlencode(json_encode($selectedDepartmentYears)));
        exit();
    }
}

// Get departments associated with the logged-in user
$getDepartmentsQuery = "SELECT department_id, name FROM departments WHERE username = ?";
$stmt = $conn->prepare($getDepartmentsQuery);
$stmt->bind_param("s", $_SESSION['username']);
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
            max-width: 500px; 
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

        .back-link {
            margin-top: 10px;
            color: #3498db;
            text-decoration: none;
        }

        .back-link:hover {
            background-color: #2980b9;
        }
    </style>
</head>
<body>

<div class="container">
    <div class="form-container">
        <h2 style="color: #3498db;">Choose Department</h2>

        <!-- Form for selecting departments -->
        <form method="post" action="">
            <label for="departmentId" style="color: #3498db;">Select Department:</label>
            <select name="departmentId" id="departmentId" required>
                <?php
                while ($row = $result->fetch_assoc()) {
                    echo "<option value='" . $row['department_id'] . "'>" . $row['name'] . "</option>";
                }
                ?>
            </select>

            <button type="submit" name="action" value="confirm">Confirm</button>
        </form>

        <!-- Back link to navigate to the previous page -->
        <a href="javascript:history.back()" class="back-link">Back</a>
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
