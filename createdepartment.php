<?php
session_start();

// Include your existing database connection
require_once 'db_connection.php';

// Redirect to the login page if the user is not logged in
if (!isset($_SESSION['username'])) {
    header('Location: login.php');
    exit();
}

// Retrieve user information from the session
$username = $_SESSION['username'];

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get data from the form
    $departmentName = $_POST['departmentName'];
    $years = $_POST['years'];

    // Check if the department already exists for the user
    $checkDepartmentQuery = "SELECT * FROM departments WHERE username = '$username' AND name = '$departmentName'";
    $result = $conn->query($checkDepartmentQuery);

    if ($result && $result->num_rows > 0) {
        echo "<script>alert('Department already exists. Please choose a different name.');</script>";
    } else {
        // Check if the user exists
        $checkUserQuery = "SELECT * FROM users WHERE username = '$username'";
        $result = $conn->query($checkUserQuery);

        if (!$result || $result->num_rows === 0) {
            echo "<script>alert('User does not exist. Please log in again.');</script>";
            header('Location: login.php');
            exit();
        }

        // Create table if not exists
        $createTableQuery = "CREATE TABLE IF NOT EXISTS departments (
            department_id INT PRIMARY KEY AUTO_INCREMENT,
            username VARCHAR(8),
            name VARCHAR(50),
            years INT,
            FOREIGN KEY (username) REFERENCES users(username)
        )";

        if ($conn->query($createTableQuery) !== TRUE) {
            echo "Error creating table: " . $conn->error;
        }

        // Use prepared statements to prevent SQL injection
        $insertQuery = "INSERT INTO departments (username, name, years) VALUES (?, ?, ?)";
        $stmt = $conn->prepare($insertQuery);
        $stmt->bind_param("ssi", $username, $departmentName, $years);

        if ($stmt->execute()) {
            // Redirect to the next page (addstaffs.php) with necessary details
            header("Location: addstaffs.php?username=" . urlencode($username) . "&departmentName=" . urlencode($departmentName));
            exit();
        } else {
            echo "Error inserting data: " . $stmt->error;
        }

        $stmt->close();
    }
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
            max-width: 800px;
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

        input,
        select {
            width: 100%;
            padding: 10px;
            margin-bottom: 20px;
            border: 1px solid #3498db;
            border-radius: 5px;
            box-sizing: border-box;
        }

        button {
            padding: 15px 30px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }

        button:hover {
            background-color: #2980b9;
        }

        .footer {
            margin-top: 20px;
            color: #3498db;
        }
    </style>
</head>
<body>

<div class="container">
    <div class="form-container">
        <!-- Your form for creating a department goes here -->
        <form method="post" action="">
            <label for="departmentName">Department Name:</label>
            <input type="text" name="departmentName" maxlength="48" required>

            <label for="years">Years:</label>
            <select name="years" required>
                <option value="1">1</option>
                <option value="2">2</option>
                <option value="3">3</option>
                <option value="4">4</option>
                <option value="5">5</option>
            </select>

            <!-- Hidden input fields to store username and departmentId -->
            <input type="hidden" name="username" value="<?php echo urlencode($username); ?>">

            <!-- "Back" as a link inside the form -->
            <a href="dashboard.php" class="btn btn-primary">Back</a>

            <!-- "Next" as a button inside the form -->
            <button type="submit" class="btn btn-success">Next</button>
        </form>
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