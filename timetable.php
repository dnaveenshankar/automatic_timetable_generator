<?php
session_start();

// Check if username is provided via GET request
if (!isset($_GET['username'])) {
    echo "Username not provided.";
    exit();
}

$username = $_GET['username']; // Get username from URL
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Timetable</title>
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
            background-color: #ffffff;
            border-radius: 10px;
            box-shadow: 0px 0px 20px 0px rgba(0, 0, 0, 0.2);
            padding: 40px;
            max-width: 1200px; /* Increased the width of the container */
            text-align: center;
            overflow: auto;
            max-height: 100vh; 
        }

        .btn-container {
            display: flex;
            flex-direction: column;
            align-items: center;
            margin-top: 20px;
        }

        .btn {
            margin: 10px;
            padding: 12px 24px;
            background-color: #3498db;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            text-decoration: none;
            font-size: 16px;
            transition: background-color 0.3s ease;
        }

        .btn:hover {
            background-color: #2980b9;
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Button Container -->
        <div class="btn-container">
            <!-- Button for Staff Timetable -->
            <a href="staff_timetable.php?username=<?php echo urlencode($username); ?>" class="btn">Staff Timetable</a>

            <!-- Button for Class Timetable -->
            <a href="viewtimetables.php?username=<?php echo urlencode($username); ?>" class="btn">Class Timetable</a>
        </div>

        <!-- Back Button -->
        <div class="btn-container">
            <a href="dashboard.php" class="btn">Back</a>
        </div>
    </div>
</body>
</html>
