<?php
// Include your database connection file
require_once 'db_connection.php';
// Extracting parameters from the URL
$year = isset($_GET['year']) ? $_GET['year'] : null;
$departmentId = isset($_GET['department_id']) ? $_GET['department_id'] : null;
// Check if the parameters are valid
if ($year !== null && $departmentId !== null) {
    // Function to check if the slot is free for the same session and day in both class_timetable and staff_timetable
    function isSlotFree($conn, $departmentId, $day, $session) {
        $checkClassTimetableQuery = "SELECT * FROM class_timetable WHERE department_id = ? AND day = ? AND session = ?";
        $stmtClass = $conn->prepare($checkClassTimetableQuery);
        $stmtClass->bind_param("iii", $departmentId, $day, $session);
        $stmtClass->execute();
        $resultClass = $stmtClass->get_result();
        $checkStaffTimetableQuery = "SELECT * FROM staff_timetable WHERE department_id = ? AND day = ? AND session = ?";
        $stmtStaff = $conn->prepare($checkStaffTimetableQuery);
        $stmtStaff->bind_param("iii", $departmentId, $day, $session);
        $stmtStaff->execute();
        $resultStaff = $stmtStaff->get_result();
        return $resultClass->num_rows == 0 && $resultStaff->num_rows == 0;
    }
    // Function to check if subject hours limit exceeded
    function isSubjectHoursLimitExceeded($conn, $subjectId) {
        $query = "SELECT hours FROM subjects WHERE subject_id = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("i", $subjectId);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $hoursNeeded = $row['hours'] ?? 0;
        $totalHoursQuery = "SELECT COUNT(*) AS total_hours FROM class_timetable WHERE subject_id = ?";
        $stmtTotalHours = $conn->prepare($totalHoursQuery);
        $stmtTotalHours->bind_param("i", $subjectId);
        $stmtTotalHours->execute();
        $resultTotalHours = $stmtTotalHours->get_result();
        $rowTotalHours = $resultTotalHours->fetch_assoc();
        $totalHours = $rowTotalHours['total_hours'] ?? 0;

        return $totalHours >= $hoursNeeded;
    }
    // Function to shuffle an array while maintaining key-value pairs
    function shuffle_assoc(&$array) {
        $keys = array_keys($array);
        shuffle($keys);
        $newArray = [];
        foreach ($keys as $key) {
            $newArray[$key] = $array[$key];
        }
        $array = $newArray;
    }
    // Get department ID from the previous page
    $departmentId = isset($_GET['department_id']) ? $_GET['department_id'] : null;
    if (!$departmentId) {
        die("Department ID is missing.");
    }
    // Fetch templates data from the templates table based on department_id
    $getTemplatesQuery = "SELECT * FROM templates WHERE department_id = ?";
    $stmtTemplates = $conn->prepare($getTemplatesQuery);
    $stmtTemplates->bind_param("i", $departmentId);
    $stmtTemplates->execute();
    $resultTemplates = $stmtTemplates->get_result();
    if ($resultTemplates->num_rows > 0) {
        // Iterate through each template
        while ($rowTemplate = $resultTemplates->fetch_assoc()) {
            $templateId = $rowTemplate['template_id'];
            $year = $rowTemplate['years'];
            $days = $rowTemplate['days'];
            $sessions = $rowTemplate['sessions'];
            // Fetch subjects from the subjects table based on department_id and year
            $getSubjectsQuery = "SELECT * FROM subjects WHERE department_id = ? AND year = ?";
            $stmtSubjects = $conn->prepare($getSubjectsQuery);
            $stmtSubjects->bind_param("ii", $departmentId, $year);
            $stmtSubjects->execute();
            $resultSubjects = $stmtSubjects->get_result();
            if ($resultSubjects->num_rows > 0) {
                // Fetch subjects into an array
                $subjectsArray = [];
                while ($rowSubject = $resultSubjects->fetch_assoc()) {
                    $subjectsArray[$rowSubject['subject_id']] = $rowSubject;
                }
                // Shuffle the subjects array
                shuffle_assoc($subjectsArray);
                // Initialize an array to keep track of inserted subjects for each day
                $insertedSubjects = [];
                // Iterate through each subject
                foreach ($subjectsArray as $subjectId => $rowSubject) {
                    $subjectType = $rowSubject['subject_type'];
                    $staff1Id = $rowSubject['staff1_id'];
                    $staff2Id = $rowSubject['staff2_id'];
                    // Iterate through each day and session
                    for ($day = 1; $day <= $days; $day++) {
                        for ($session = 1; $session <= $sessions; $session++) {
                            // Check if the slot is free
                            if (isSlotFree($conn, $departmentId, $day, $session)) {
                                // Check if subject hours limit exceeded
                                if (!isSubjectHoursLimitExceeded($conn, $subjectId)) {
                                    // Check if the subject is not inserted more than once on the same day (except for labs)
                                    if ($subjectType !== 'Lab' && isset($insertedSubjects[$day][$subjectId])) {
                                        continue; // Skip insertion if the subject is already inserted
                                    }
                                    // Insert into class_timetable
                                    $insertTimetableQuery = "INSERT INTO class_timetable (department_id, template_id, year, day, session, subject_id) VALUES (?, ?, ?, ?, ?, ?)";
                                    $stmtInsert = $conn->prepare($insertTimetableQuery);
                                    $stmtInsert->bind_param("iiiiii", $departmentId, $templateId, $year, $day, $session, $subjectId);
                                    $stmtInsert->execute();
                                    $stmtInsert->close();
                                    // Add the inserted subject to the list for the current day
                                    $insertedSubjects[$day][$subjectId] = true;
                                    // Insert into staff_timetable for staff1_id if not null or 0
                                    if ($staff1Id !== null && $staff1Id !== 0) {
                                        $insertStaffTimetableQuery = "INSERT INTO staff_timetable (staff_id, day, session, department_id, subject_id) VALUES (?, ?, ?, ?, ?)";
                                        $stmtInsertStaff1 = $conn->prepare($insertStaffTimetableQuery);
                                        $stmtInsertStaff1->bind_param("iiiii", $staff1Id, $day, $session, $departmentId, $subjectId);
                                        $stmtInsertStaff1->execute();
                                        $stmtInsertStaff1->close();
                                    }
                                    // Insert into staff_timetable for staff2_id if not null or 0
                                    if ($staff2Id !== null && $staff2Id !== 0) {
                                        $insertStaffTimetableQuery = "INSERT INTO staff_timetable (staff_id, day, session, department_id, subject_id) VALUES (?, ?, ?, ?, ?)";
                                        $stmtInsertStaff2 = $conn->prepare($insertStaffTimetableQuery);
                                        $stmtInsertStaff2->bind_param("iiiii", $staff2Id, $day, $session, $departmentId, $subjectId);
                                        $stmtInsertStaff2->execute();
                                        $stmtInsertStaff2->close();
                                    }
                                }
                            }
                        }
                    }
                }
            } else {
                echo "No subjects found for the department and year.";
            }
        }
    } else {
        echo "No templates found for the department.";
    }
    $stmtTemplates->close();
    $stmtSubjects->close();
    // After insertion, redirect to the dashboard with an alert
    echo "<script>alert('Timetable created successfully!'); window.location.href = 'dashboard.php';</script>";
} else {
    echo "Invalid parameters.";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Creating Timetable</title>
    <style>
        /* Your CSS styles */
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
            background-color: #fff;
            border-radius: 10px;
            box-shadow: 0px 0px 20px 0px rgba(0, 0, 0, 0.2);
            padding: 40px;
            max-width: 1200px;
            text-align: center;
            overflow: auto;
            max-height: 100vh; 
        }
        .subject-fieldset {
            margin-bottom: 20px;
        }
        .session-info {
            color: #2980b9; 
            font-weight: bold;
            margin-bottom: 20px;
        }
        label {
            margin-bottom: 10px;
            color: #3498db;
            display: block;
            font-weight: bold;
        }
        select {
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
            text-decoration: none;
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
        <h1>Creating Timetable...</h1>
        <div class="loader"></div>
        <!-- Placeholder for displaying year and department name -->
        <div id="yearDepartmentInfo"></div>
        <div id="errorMessage" class="error-message"></div>
        <script>
            // Function to display an alert with the provided message
            function showAlert(message) {
                alert(message);
            }
            // Function to extract query parameters from the URL
            function getParameterByName(name, url) {
                if (!url) url = window.location.href;
                name = name.replace(/[\[\]]/g, "\\$&");
                var regex = new RegExp("[?&]" + name + "(=([^&#]*)|&|#|$)"),
                    results = regex.exec(url);
                if (!results) return null;
                if (!results[2]) return '';
                return decodeURIComponent(results[2].replace(/\+/g, " "));
            }
            // Extract year and department ID from the URL
            var year = getParameterByName('year');
            var departmentId = getParameterByName('department_id');
            // Display the year and department name
            var yearDepartmentInfo = document.getElementById('yearDepartmentInfo');
            if (year && departmentId) {
                yearDepartmentInfo.innerHTML = '<p>Year: ' + year + '</p><p>Department ID: ' + departmentId + '</p>';
            } else {
                showAlert('Invalid parameters.');
            }
            // Handle errors
            var errorMessage = getParameterByName('error');
            var errorMessageDiv = document.getElementById('errorMessage');
            if (errorMessage) {
                errorMessageDiv.innerText = errorMessage;
            }
        </script>
    </div>
    <?php
    // Redirect to insert_success.php after successful insertion
    header("Location: insert_success.php");
    exit();
    ?>
</body>
</html>