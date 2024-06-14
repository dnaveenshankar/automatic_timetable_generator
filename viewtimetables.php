<?php
session_start();

// Include your existing database connection
require_once 'db_connection.php';

// Check if the username is provided via GET request
if (!isset($_GET['username'])) {
    echo "Username not provided.";
    exit();
}

// Retrieve username from the GET request
$username = $_GET['username'];

// Prepare and execute the query to fetch timetable details
$getTimetablesQuery = "SELECT t.template_id, t.username, t.department_id, t.years, t.class_name, s.name AS tutor_name, d.name AS department_name
                      FROM templates t
                      LEFT JOIN staffs s ON t.tutor = s.staff_id
                      LEFT JOIN departments d ON t.department_id = d.department_id
                      WHERE t.username = ?";
$stmt = $conn->prepare($getTimetablesQuery);
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();

// Check if any timetables are found
if ($result->num_rows > 0) {
    // Store timetables in an array
    $timetables = [];
    while ($row = $result->fetch_assoc()) {
        $timetables[] = $row;
    }
} else {
    echo "No timetables found.";
}

// Close the database connection
$stmt->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Timetables</title>
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
        <h1>View Timetables</h1>
        <table>
            <thead>
                <tr>
                    <th>Department Name</th>
                    <th>Year</th>
                    <th>Class Name</th>
                    <th>Tutor</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <!-- PHP code to populate the table rows -->
                <?php foreach ($timetables as $timetable) : ?>
                    <tr>
                        <td><?php echo $timetable['department_name']; ?></td>
                        <td><?php echo $timetable['years']; ?></td>
                        <td><?php echo $timetable['class_name']; ?></td>
                        <td><?php echo $timetable['tutor_name']; ?></td>
                        <!-- Link to view timetable -->
                        <td><a href="view_timetable.php?template_id=<?php echo $timetable['template_id']; ?>">View</a></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <!-- Back button -->
        <a href="#" class="back-btn" onclick="history.back()">Back</a>
    </div>
</body>
</html>
