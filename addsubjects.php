<?php
session_start();

// Include your existing database connection
require_once 'db_connection.php';

// Retrieve user information from the session
if (!isset($_SESSION['username'])) {
    header('Location: login.php');
    exit();
}

// Initialize variables
$departmentId = null;
$departments = array();
$numSubjects = 0;
$years = array(); 

if (isset($_GET['department_id'])) {
    $departmentId = $_GET['department_id'];
    $years = fetchYears($conn, $departmentId); 
} else {
    // Redirect to the previous page if department ID is not provided
    header("Location: choosedepartment.php");
    exit();
}

// Function to fetch years based on department ID
function fetchYears($conn, $departmentId) {
    $getDepartmentYearsQuery = "SELECT years FROM departments WHERE department_id = ?";
    $stmt = $conn->prepare($getDepartmentYearsQuery);
    $stmt->bind_param("i", $departmentId);
    $stmt->execute();
    $stmt->bind_result($yearsResult);
    $stmt->fetch();
    $stmt->close();

    // Convert years string to array
    $years = explode(',', $yearsResult);

    return $years;
}

// Function to fetch staff names based on department ID
function fetchStaffNames($conn, $departmentId) {
    $getStaffQuery = "SELECT staff_id, name FROM staffs WHERE department_id = ?";
    $stmt = $conn->prepare($getStaffQuery);
    $stmt->bind_param("i", $departmentId);
    $stmt->execute();
    $result = $stmt->get_result();
    $staffs = $result->fetch_all(MYSQLI_ASSOC);
    $stmt->close();

    return $staffs;
}

// Handle form submission to add subjects
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['numSubjects'], $_POST['subjects'])) {
    $numSubjects = $_POST['numSubjects'];
    $subjects = $_POST['subjects'];

    // Process submitted subjects
    foreach ($subjects as $subject) {
        // Extract subject details
        $subjectCode = $subject['subjectCode'];
        $subjectName = $subject['subjectName'];
        $staff1Id = $subject['staff1'];
        $staff2Id = !empty($subject['staff2']) ? $subject['staff2'] : null;
        $subjectType = $subject['subjectType'];
        $hours = $subject['hours'];
        $year = $subject['year'];

        // Insert subject into database
        $insertSubjectQuery = "INSERT INTO subjects (subject_code, subject_name, staff1_id, staff2_id, subject_type, hours, year, department_id) 
                            VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($insertSubjectQuery);
        $stmt->bind_param("sssssssi", $subjectCode, $subjectName, $staff1Id, $staff2Id, $subjectType, $hours, $year, $departmentId);
        $stmt->execute();
        $stmt->close();
    }

    // Redirect to manualdata.php with username and department ID
    $username = $_SESSION['username'];
    header("Location: manualdata.php?username=$username&department_id=$departmentId");
    exit();
}
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
            max-width: 1200px;
            text-align: center;
            overflow: auto;
            max-height: 100vh; 
        }

        .form-container {
            margin-bottom: 20px;
        }

        .form-group {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }

        label {
            margin-bottom: 10px;
            color: #3498db;
            display: block;
        }

        input, select {
            width: calc(50% - 10px);
            padding: 15px;
            margin-bottom: 20px;
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

        a {
            color: #3498db;
        }

        a:hover {
            background-color: #2980b9;
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
        <h2 style="color: #3498db;">Add Subjects</h2>

        <!-- Form for adding subjects -->
        <form method="post" action="" id="subjectsForm">

            <!-- Input for the number of subjects -->
            <div class="form-group">
                <label for="numSubjects">Number of Subjects:</label>
                <input type="number" name="numSubjects" id="numSubjects" required>
            </div>

            <!-- Input for the department ID (hidden) -->
            <input type="hidden" name="departmentId" value="<?php echo $departmentId; ?>">

            <!-- Placeholder for generated fields -->
            <div id="fieldsContainer"></div>

            <button type="submit">Next</button>
        </form>

        <!-- Back link to navigate to the previous page -->
        <a href="javascript:history.back()">Back</a>
    </div>

    <div class="footer">
        Automatic Timetable Generator
    </div>
</div>

<!-- Bootstrap JS and Popper.js -->
<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.3/dist/umd/popper.min.js"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        // Event listener for input change
        document.getElementById('numSubjects').addEventListener('input', function () {
            generateFields();
        });

        // Function to generate fields based on the number of subjects
function generateFields() {
    const fieldsContainer = document.getElementById('fieldsContainer');
    const numSubjectsInput = document.getElementById('numSubjects');
    const numSubjects = parseInt(numSubjectsInput.value, 10);

    // Clear existing fields
    fieldsContainer.innerHTML = '';

    // Generate new fields
    for (let i = 1; i <= numSubjects; i++) {
        const fieldGroup = document.createElement('div');
        fieldGroup.classList.add('form-group');

        fieldGroup.innerHTML = `
            <label for="subjectCode${i}">Subject Code:</label>
            <input type="text" name="subjects[${i - 1}][subjectCode]" required>

            <label for="subjectName${i}">Subject Name:</label>
            <input type="text" name="subjects[${i - 1}][subjectName]" required>

            <label for="staff1${i}">Staff 1:</label>
            <select name="subjects[${i - 1}][staff1]" required>
                <option value="">Select Staff</option>
                <option value="other">Other</option> <!-- Add the "Other" option -->
            </select>

            <label for="staff2${i}">Staff 2 (Optional):</label>
            <select name="subjects[${i - 1}][staff2]"></select>

            <label for="subjectType${i}">Subject Type:</label>
            <select name="subjects[${i - 1}][subjectType]" required>
                <option value="Lab">Lab</option>
                <option value="Theory">Theory</option>
            </select>

            <label for="hours${i}">Hours (in 6 sessions):</label>
            <input type="number" name="subjects[${i - 1}][hours]" required>

            <label for="year${i}">Year:</label>
            <select name="subjects[${i - 1}][year]" required></select>
        `;

        fieldsContainer.appendChild(fieldGroup);

        // Fetch staff names and years for the current field
        const staff1Select = fieldGroup.querySelector(`[name="subjects[${i - 1}][staff1]"]`);
        const staff2Select = fieldGroup.querySelector(`[name="subjects[${i - 1}][staff2]"]`);
        const yearSelect = fieldGroup.querySelector(`[name="subjects[${i - 1}][year]"]`);

        // Fetch staff names and years
        fetchStaffNames(<?php echo $departmentId; ?>, staff1Select);
        fetchStaffNames(<?php echo $departmentId; ?>, staff2Select);
        fetchYears(yearSelect);
    }
}


        // Function to fetch staff names based on department ID
        function fetchStaffNames(departmentId, select) {
            fetch(`get_staff_names.php?department_id=${departmentId}`)
                .then(response => response.json())
                .then(data => {
                    // Clear existing options
                    select.innerHTML = '';

                    // Add default option
                    const defaultOption = document.createElement('option');
                    defaultOption.value = '';
                    defaultOption.textContent = 'Select Staff';
                    select.appendChild(defaultOption);

                    // Add staff options
                    data.forEach(staff => {
                        const option = document.createElement('option');
                        option.value = staff.staff_id;
                        option.textContent = staff.name;
                        select.appendChild(option);
                    });
                })
                .catch(error => console.error('Error fetching staff names:', error));
        }

        // Function to fetch years based on department ID
        function fetchYears(select) {
            const numYears = <?php echo max($years); ?>;
            for (let i = 1; i <= numYears; i++) {
                const option = document.createElement('option');
                option.value = i;
                option.textContent = i;
                select.appendChild(option);
            }
        }

        // Call the function on page load
        generateFields();
    });
</script>

</body>
</html>