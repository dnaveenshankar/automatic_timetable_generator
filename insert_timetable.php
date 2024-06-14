<?php
// Include your database connection file
require_once 'db_connection.php';

// Initialize variables for days, sessions, and template ID
$days = $sessions = $templateId = 0;

// Fetch departments from the database
$getDepartmentsQuery = "SELECT * FROM departments";
$resultDepartments = $conn->query($getDepartmentsQuery);

// Get department ID and year from the previous page
$departmentId = isset($_GET['department_id']) ? $_GET['department_id'] : null;
$year = isset($_GET['year']) ? $_GET['year'] : null;

if (!$departmentId || !$year) {
    die("Department ID or year is missing.");
}

// Fetch the days, sessions, and template ID from the template table based on department_id and year
$getTemplateQuery = "SELECT template_id, days, sessions FROM templates WHERE department_id = ? AND years = ?";
$stmt = $conn->prepare($getTemplateQuery);
$stmt->bind_param("ii", $departmentId, $year);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    // Fetch the first row
    $row = $result->fetch_assoc();
    $templateId = $row['template_id'];
    $days = $row['days'];
    $sessions = $row['sessions'];
} else {
    echo "No data found for the selected department and year.";
}

$stmt->close();

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
    $query = "SELECT COUNT(*) AS total_hours FROM class_timetable WHERE subject_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $subjectId);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $totalHours = $row['total_hours'] ?? 0;

    // Fetch maximum hours allowed for the subject from the subjects table
    $getMaxHoursQuery = "SELECT hours FROM subjects WHERE subject_id = ?";
    $stmtMaxHours = $conn->prepare($getMaxHoursQuery);
    $stmtMaxHours->bind_param("i", $subjectId);
    $stmtMaxHours->execute();
    $resultMaxHours = $stmtMaxHours->get_result();
    $rowMaxHours = $resultMaxHours->fetch_assoc();
    $maxHours = $rowMaxHours['hours'] ?? 0;

    return $totalHours >= $maxHours;
}


// Check if form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Handle form submission
    if (isset($_POST['day'], $_POST['session'], $_POST['subject'])) {
        // Get the submitted data
        $dayArray = $_POST['day'];
        $sessionArray = $_POST['session'];
        $subjectArray = $_POST['subject'];

        // Prepare the insertion query for class_timetable
        $insertTimetableQuery = "INSERT INTO class_timetable (department_id, template_id, year, day, session, subject_id) VALUES (?, ?, ?, ?, ?, ?)";
        $stmtInsert = $conn->prepare($insertTimetableQuery);

        // Prepare the insertion query for staff_timetable
        $insertStaffTimetableQuery = "INSERT INTO staff_timetable (staff_id, day, session, department_id, subject_id) VALUES (?, ?, ?, ?, ?)";
        $stmtInsertStaff = $conn->prepare($insertStaffTimetableQuery);

        // Bind parameters and execute the statement for each subject
        foreach ($subjectArray as $index => $subjectName) {
            $day = $dayArray[$index];
            $session = $sessionArray[$index];

            // Check if the slot is free for the same session and day
            if (!isSlotFree($conn, $departmentId, $day, $session)) {
                echo "<script>alert('Slot not free for session $session on day $day.')</script>";
                continue; // Skip insertion for this slot
            }

            // Fetch the subject ID based on the subject name
            $getSubjectIdQuery = "SELECT subject_id FROM subjects WHERE subject_name = ?";
            $stmtSubjectId = $conn->prepare($getSubjectIdQuery);
            $stmtSubjectId->bind_param("s", $subjectName);
            $stmtSubjectId->execute();
            $resultSubjectId = $stmtSubjectId->get_result();

            if ($resultSubjectId->num_rows > 0) {
                $rowSubjectId = $resultSubjectId->fetch_assoc();
                $subjectId = $rowSubjectId['subject_id'];

                // Check if subject hours limit exceeded
                if (isSubjectHoursLimitExceeded($conn, $subjectId)) {
                    echo "<script>alert('Subject hours limit exceeded for subject ID $subjectId.')</script>";
                    continue; // Skip insertion for this subject
                }

                // Insert into class_timetable
                $stmtInsert->bind_param("iiiiii", $departmentId, $templateId, $year, $day, $session, $subjectId);
                $stmtInsert->execute();

                // Get staff IDs from the subjects table
                $getStaffIdsQuery = "SELECT staff1_id, staff2_id FROM subjects WHERE subject_id = ?";
                $stmtStaffIds = $conn->prepare($getStaffIdsQuery);
                $stmtStaffIds->bind_param("i", $subjectId);
                $stmtStaffIds->execute();
                $resultStaffIds = $stmtStaffIds->get_result();
                $rowStaffIds = $resultStaffIds->fetch_assoc();
                $staff1Id = $rowStaffIds['staff1_id'];
                $staff2Id = $rowStaffIds['staff2_id'];

// Insert into staff_timetable for staff1_id
                if ($staff1Id !== null && $staff1Id !== 0) {
                    $insertStaffTimetableQuery = "INSERT INTO staff_timetable (staff_id, day, session, department_id, created_at, subject_id) VALUES (?, ?, ?, ?, NOW(), ?)";
                    $stmtInsertStaff1 = $conn->prepare($insertStaffTimetableQuery);
                    $stmtInsertStaff1->bind_param("iiiii", $staff1Id, $day, $session, $departmentId, $subjectId);
                    $stmtInsertStaff1->execute();
                }

// Insert into staff_timetable for staff2_id
if ($staff2Id !== null && $staff2Id !== 0) {
    $insertStaffTimetableQuery = "INSERT INTO staff_timetable (staff_id, day, session, department_id, created_at, subject_id) VALUES (?, ?, ?, ?, NOW(), ?)";
    $stmtInsertStaff2 = $conn->prepare($insertStaffTimetableQuery);
    $stmtInsertStaff2->bind_param("iiiii", $staff2Id, $day, $session, $departmentId, $subjectId);
    $stmtInsertStaff2->execute();
}

            } else {
                echo "<script>alert('Subject ID not found for the subject name: $subjectName.')</script>";
            }
        }

        echo "<script>alert('Timetable Updated successfully.');</script>";
    } else {
        echo "<script>alert('Incomplete form data.')</script>";
    }

    $stmtInsert->close();
    $stmtInsertStaff->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Insert Timetable Manually</title>
    <style>
        /* Your CSS styles */
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
        <h2>Insert Timetable Manually</h2>
        <!-- Display total sessions and days -->
        <div class="session-info">
            Total Days: <?php echo $days; ?> | Total Sessions: <?php echo $sessions; ?>
        </div>
        <form method="post" id="timetableForm">
            <!-- Default set of fields -->
            <fieldset class="subject-fieldset">
                <label for="day">Day:</label>
                <select name="day[]" class="day" required>
                    <?php for ($i = 1; $i <= $days; $i++) { ?>
                        <option value="<?php echo $i; ?>"><?php echo $i; ?></option>
                    <?php } ?>
                </select>
                <br><br>
                <label for="session">Session:</label>
                <select name="session[]" class="session" required>
                    <?php for ($i = 1; $i <= $sessions; $i++) { ?>
                        <option value="<?php echo $i; ?>"><?php echo $i; ?></option>
                    <?php } ?>
                </select>
                <br><br>
                <label for="subject">Subject:</label>
                <select name="subject[]" class="subject" required>
                    <?php
                        // Fetch subject names from the database
                        $getSubjectsQuery = "SELECT subject_name FROM subjects";
                        $result = $conn->query($getSubjectsQuery);
                        while ($row = $result->fetch_assoc()) {
                            echo "<option value='{$row['subject_name']}'>{$row['subject_name']}</option>";
                        }
                    ?>
                </select>
                <br><br>
            </fieldset>

            <!-- Add more button -->
            <button type="button" id="addMore">Add More</button>
            

            <!-- Insert subjects and back buttons -->
            <button type="submit" style="margin-left: 10px;">Insert Subjects</button>
            <a href="javascript:history.back();" style="margin-left: 10px;">Back</a><br>
            <a href="auto_insert_load.php?year=<?php echo urlencode($year); ?>&department_id=<?php echo urlencode($departmentId); ?>" class="btn btn-primary" style="margin-left: 10px;">Auto Insert</a>
        </form>

        <script>
            // JavaScript code to add more sets of fields dynamically
            document.addEventListener('DOMContentLoaded', function () {
                const addMoreButton = document.getElementById('addMore');
                const form = document.getElementById('timetableForm');
                const subjectFieldset = document.querySelector('.subject-fieldset');

                addMoreButton.addEventListener('click', function () {
                    const newFieldset = subjectFieldset.cloneNode(true);
                    form.insertBefore(newFieldset, addMoreButton);
                });
            });
        </script>
    </div>
</body>
</html>
