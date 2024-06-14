<?php
session_start();
require_once 'db_connection.php'; // Include your database connection

// Retrieve department ID from the URL or session, whichever way you're handling it
$departmentId = isset($_GET['department_id']) ? $_GET['department_id'] : null;

if (!$departmentId) {
    // Handle the case when department ID is not provided
    // You might want to redirect the user or display an error message
    die("Department ID is missing.");
}

// Fetch available years for the department from the templates table
$getYearsQuery = "SELECT DISTINCT years FROM templates WHERE department_id = ?";
$stmt = $conn->prepare($getYearsQuery);
$stmt->bind_param("i", $departmentId);
$stmt->execute();
$result = $stmt->get_result();

$years = [];
while ($row = $result->fetch_assoc()) {
    $years[] = $row['years'];
}
$stmt->close();

// Check if the form is submitted and the selected year is provided
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['year'])) {
    // Retrieve the selected year
    $selectedYear = $_POST['year'];

    if (!$selectedYear) {
        // Handle the case when year is not selected
        // You might want to redirect the user or display an error message
        die("Year is missing.");
    }

    // Redirect to insert_timetable.php with department ID and selected year
    header("Location: insert_timetable.php?department_id=$departmentId&year=$selectedYear");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Select Year</title>
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

        .form-container {
            background-color: #fff;
            border-radius: 10px;
            box-shadow: 0px 0px 20px 0px rgba(0, 0, 0, 0.2);
            padding: 40px;
            max-width: 500px; /* Adjust the max-width as needed */
            text-align: center;
        }

        .form-group {
            margin-bottom: 20px;
        }

        label {
            margin-bottom: 10px;
            color: #3498db;
            display: block;
        }

        select {
            width: 100%;
            padding: 10px;
            border: 1px solid #3498db;
            border-radius: 5px;
            box-sizing: border-box;
            font-size: 16px;
        }

        button {
            padding: 15px 24px;
            margin-top: 10px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            background-color: #3498db;
            color: #fff;
            font-size: 16px;
        }

        button:hover {
            background-color: #2980b9;
        }

        .back-link {
            margin-top: 20px;
            color: #3498db;
        }
    </style>
</head>
<body>
<div class="form-container">
    <h2 style="color: #3498db;">Select Year</h2>
    <!-- Form for selecting the year -->
    <form method="post" action="">
        <div class="form-group">
            <label for="year">Select Year:</label>
            <select name="year" id="year" required>
    <option value="">Select Year</option>
    <?php
    // Get the maximum year from the fetched years
    $maxYear = max($years);
    // Loop to generate options from 1 to the maximum year
    for ($i = 1; $i <= $maxYear; $i++) {
        echo "<option value='$i'>$i</option>";
    }
    ?>
</select>

        </div>
        <button type="submit">Proceed</button>
    </form>
    <!-- Back button -->
    <a href="javascript:history.back()" class="back-link">Back</a>
</div>
</body>
</html>
