<?php
session_start();

// Include your existing database connection
require_once 'db_connection.php';

// Check if the staff_id is provided via GET request
if (!isset($_GET['staff_id'])) {
    echo "Staff ID not provided.";
    exit();
}

// Retrieve staff_id from the GET request
$staffId = $_GET['staff_id'];

// Fetch staff name based on staff_id
$getStaffNameQuery = "SELECT name FROM staffs WHERE staff_id = ?";
$stmtStaffName = $conn->prepare($getStaffNameQuery);
$stmtStaffName->bind_param("i", $staffId);
$stmtStaffName->execute();
$resultStaffName = $stmtStaffName->get_result();

if ($resultStaffName->num_rows > 0) {
    $rowStaffName = $resultStaffName->fetch_assoc();
    $staffName = $rowStaffName['name'];
} else {
    echo "Staff not found.";
}

// Close the prepared statement
$stmtStaffName->close();

// Prepare and execute the query to fetch staff's timetable details
$getTimetableQuery = "SELECT * FROM staff_timetable WHERE staff_id = ?";
$stmt = $conn->prepare($getTimetableQuery);
$stmt->bind_param("i", $staffId);
$stmt->execute();
$result = $stmt->get_result();

// Check if any timetable is found
if ($result->num_rows > 0) {
    // Store timetable data in an array
    $timetableData = [];
    while ($row = $result->fetch_assoc()) {
        $timetableData[] = $row;
    }
} else {
    echo "No timetable found.";
}

// Close the prepared statement
$stmt->close();

// Function to get subject name by subject ID
function getSubjectName($conn, $subjectId) {
    $query = "SELECT subject_name FROM subjects WHERE subject_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $subjectId);
    $stmt->execute();
    $result = $stmt->get_result();
    $subjectName = '';
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $subjectName = $row['subject_name'];
    }
    $stmt->close();
    return $subjectName;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Staff Timetable</title>
    <style>
        body {
            background-color: #3498db;
            color: blue; /* Blue font color */
            display: flex;
            align-items: center;
            justify-content: center;
            height: 100vh;
            margin: 0;
            padding: 20px;
            font-family: Arial, sans-serif;
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
        h1 {
            color: #3498db;
            margin-bottom: 20px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        table th, table td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        table th {
            background-color: #f2f2f2;
        }
        table td a {
            text-decoration: none;
        }
        table td a:hover {
            text-decoration: underline;
        }
        .back-btn {
            margin-top: 20px;
            background-color: #3498db;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            text-decoration: none;
            font-size: 16px;
        }
        .back-btn:hover {
            background-color: #2980b9;
        }
        .download-btn {
            background-color: #4CAF50;
            border: none;
            color: white;
            padding: 10px 20px;
            text-align: center;
            text-decoration: none;
            display: inline-block;
            font-size: 16px;
            margin-top: 20px;
            cursor: pointer;
            border-radius: 5px;
        }
        .download-btn:hover {
            background-color: #45a049;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>View Staff Timetable - <?php echo $staffName; ?></h1>
        <table border="1">
            <thead>
                <tr>
                    <th>Session/Day</th>
                    <?php for ($day = 1; $day <= 6; $day++) : ?>
                        <th><?php echo "Day " . $day; ?></th>
                    <?php endfor; ?>
                </tr>
            </thead>
            <tbody>
                <?php for ($session = 1; $session <= 6; $session++) : ?>
                    <tr>
                        <td><?php echo "Session " . $session; ?></td>
                        <?php for ($day = 1; $day <= 6; $day++) : ?>
                            <?php $subjectName = ''; ?>
                            <?php foreach ($timetableData as $data) : ?>
                                <?php if ($data['day'] == $day && $data['session'] == $session) : ?>
                                    <?php $subjectName = getSubjectName($conn, $data['subject_id']); ?>
                                <?php endif; ?>
                            <?php endforeach; ?>
                            <td><?php echo $subjectName; ?></td>
                        <?php endfor; ?>
                    </tr>
                <?php endfor; ?>
            </tbody>
        </table>
        <a href="download_staff_timetable.php?staff_id=<?php echo $staffId; ?>" class="download-btn">Download Timetable</a>

        <a href="#" class="back-btn" onclick="history.back()">Back</a>
    </div>
</body>
</html>
