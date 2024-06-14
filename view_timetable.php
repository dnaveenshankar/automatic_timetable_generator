<?php
session_start();

// Include your existing database connection
require_once 'db_connection.php';

// Check if the template_id is provided via GET request
if (!isset($_GET['template_id'])) {
    echo "Template ID not provided.";
    exit();
}

// Retrieve template_id from the GET request
$templateId = $_GET['template_id'];

// Prepare and execute the query to fetch timetable details
$getTimetableQuery = "SELECT * FROM class_timetable WHERE template_id = ?";
$stmt = $conn->prepare($getTimetableQuery);
$stmt->bind_param("i", $templateId);
$stmt->execute();
$result = $stmt->get_result();

$timetableData = []; // Initialize $timetableData variable

// Check if any timetable is found
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $timetableData[] = $row;
    }
} else {
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
    <title>View Timetable</title>
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
    </style>
</head>
<body>
    <div class="container">
        <h1>View Timetable</h1>
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
        <a href="#" class="back-btn" onclick="history.back()">Back</a>

        <!-- Download Button -->
        <a href="download_timetable.php?template_id=<?php echo $templateId; ?>" class="download-btn">Download Timetable</a>
    </div>
</body>
</html>
