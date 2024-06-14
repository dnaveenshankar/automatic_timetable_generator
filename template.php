<?php
session_start();

// Include your existing database connection
require_once 'db_connection.php';

// Initialize variables
$timetableExists = false;

// Get department ID from the previous page
if (isset($_GET['department_id'])) {
    $departmentId = $_GET['department_id'];

    // Fetch number of years from the departments table
    $getDepartmentQuery = "SELECT years FROM departments WHERE department_id = ?";
    $stmt = $conn->prepare($getDepartmentQuery);
    $stmt->bind_param("i", $departmentId);
    $stmt->execute();
    $stmt->bind_result($numberOfYears);
    $stmt->fetch();
    $stmt->close();
} else {
    // Redirect to the previous page if data is not provided
    header("Location: choosedepartment.php");
    exit();
}

// Fetch staff names for the given department_id
$getStaffNamesQuery = "SELECT staff_id, name FROM staffs WHERE department_id = ?";
$stmtStaff = $conn->prepare($getStaffNamesQuery);
$stmtStaff->bind_param("i", $departmentId);
$stmtStaff->execute();
$resultStaff = $stmtStaff->get_result();

// Fetch staff names into an array
$staffNames = [];

// Add an "Other" option for staff selection
$staffNames[] = [
    'staff_id' => 'other',
    'name' => 'Other'
];

while ($row = $resultStaff->fetch_assoc()) {
    $staffNames[] = [
        'staff_id' => $row['staff_id'],
        'name' => $row['name']
    ];
}

$stmtStaff->close();

// Handle form submission to add template and timetable name
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['className'], $_POST['tutor'], $_POST['days'], $_POST['sessions'])) {
    $classNames = $_POST['className'];
    $tutors = $_POST['tutor'];
    $days = $_POST['days'];
    $sessions = $_POST['sessions'];

    // Proceed to insert into the templates table
    $insertTemplateQuery = "INSERT INTO templates (username, department_id, years, class_name, tutor, days, sessions) VALUES (?, ?, ?, ?, ?, ?, ?)";
    $stmtInsert = $conn->prepare($insertTemplateQuery);
    $stmtInsert->bind_param("siisiii", $_SESSION['username'], $departmentId, $year, $className, $tutor, $day, $session);

    for ($year = 1; $year <= $numberOfYears; $year++) {
        $className = $classNames[$year - 1];
        $tutor = $tutors[$year - 1];
        $day = $days[$year - 1];
        $session = $sessions[$year - 1];
        $stmtInsert->execute();
    }

    $stmtInsert->close();

    // Redirect to addsubjects.php with username and department_id
    header("Location: addsubjects.php?username=" . urlencode($_SESSION['username']) . "&department_id=" . urlencode($departmentId) . "&years=" . urlencode($numberOfYears));
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <style>
        /* Your provided CSS styles */
        body {
            background-color: #3498db;
            color:#3498db;
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
            max-width: 1200px;
            text-align: center;
            overflow: auto;
            max-height: 100vh; 
        }

        .form-container {
            margin-bottom: 20px;
        }

        label {
            margin-bottom: 10px;
            color: #3498db;
            display: block;
            font-weight: bold;
        }

        input, select {
            width: calc(100% - 20px); 
            padding: 10px;
            margin-bottom: 20px;
            border: 1px solid #3498db;
            border-radius: 5px;
            box-sizing: border-box;
            font-size: 16px;
        }

        button {
            padding: 12px 24px;
            margin-top: 10px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            background-color: #3498db;
            color: #fff;
            font-size: 16px;
            transition: background-color 0.3s ease;
        }

        button:hover {
            background-color: #2980b9;
        }

        a {
            color: #3498db;
        }

        a:hover {
            background-color: #2980b9;
        }

        .error-message {
            color: red;
            margin-bottom: 10px;
        }

        .footer {
            margin-top: 10px;
            color: #3498db;
        }

    </style>
</head>
<body>
<div class="container">
    <div class="form-container">
        <h2 style="color: #3498db;">Template</h2>

        <!-- Form for choosing template and timetable name -->
        <form method="post" action="" id="timetableForm">
            <div id="fieldsContainer"></div> <!-- Placeholder for generated fields -->
            <button type="submit">Next</button>
        </form>

        <!-- Back link to navigate to the previous page -->
        <a href="javascript:history.back()" class="back-btn">Back</a>
    </div>

    <div class="footer">
        Automatic Timetable Generator
    </div>
</div>

<!-- Bootstrap JS and jQuery -->
<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.3/dist/umd/popper.min.js"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        // Function to generate fields based on the number of years
        function generateFields() {
            const fieldsContainer = document.getElementById('fieldsContainer');
            const numberOfYears = <?= $numberOfYears ?>;

            for (let i = 0; i < numberOfYears; i++) {
                const fieldSet = document.createElement('fieldset');
                fieldSet.innerHTML = `
                    <legend>Year ${i + 1}</legend>
                    <div class="form-group">
                        <label for="className${i}">Class Name:</label>
                        <input type="text" name="className[]" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label for="tutor${i}">Tutor:</label>
                        <select name="tutor[]" class="form-control" required>
                            <?php foreach ($staffNames as $staff) { ?>
                                <option value="<?= $staff['staff_id'] ?>"><?= $staff['name'] ?></option>
                            <?php } ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="days${i}">Days:</label>
                        <select name="days[]" class="form-control" required>
                            <?php for ($day = 1; $day <= 6; $day++) { ?>
                                <option value="<?= $day ?>"><?= $day ?></option>
                            <?php } ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="sessions${i}">Sessions per Day:</label>
                        <select name="sessions[]" class="form-control" required>
                            <?php for ($session = 1; $session <= 6; $session++) { ?>
                                <option value="<?= $session ?>"><?= $session ?></option>
                            <?php } ?>
                        </select>
                    </div>
                `;
                fieldsContainer.appendChild(fieldSet);
            }
        }

        // Call the function on page load
        generateFields();
    });
</script>
</body>
</html>