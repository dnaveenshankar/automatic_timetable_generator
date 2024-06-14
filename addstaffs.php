    <?php
    session_start();

    // Include your existing database connection
    require_once 'db_connection.php';

    // Retrieve user information from the session
    if (!isset($_SESSION['username'])) {
        header('Location: login.php');
        exit();
    }

    // Get departments associated with the logged-in user
    $getDepartmentsQuery = "SELECT department_id, name FROM departments WHERE username = ?";
    $stmt = $conn->prepare($getDepartmentsQuery);
    $stmt->bind_param("s", $_SESSION['username']);
    $stmt->execute();
    $result = $stmt->get_result();

    // Retrieve department information if provided in the URL
    if (isset($_GET['department_id'])) {
        $departmentId = $_GET['department_id'];
        
        // Use prepared statement to prevent SQL injection
        $getDepartmentNameQuery = "SELECT name FROM departments WHERE department_id = ?";
        $stmt = $conn->prepare($getDepartmentNameQuery);
        $stmt->bind_param("i", $departmentId);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            // Department exists, retrieve department name
            $stmt->bind_result($departmentName);
            $stmt->fetch();
        } else {
            echo "<script>alert('Invalid department ID or unauthorized access.');";
            echo "window.location.href='dashboard.php';</script>";
            exit();
        }

        $stmt->close();
    } else {
        // Department information not provided, set default values
        $departmentId = null;
        $departmentName = null;
    }

    // Handle form submission
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // Get data from the form
        $numStaffs = $_POST['numStaffs'];
        $selectedDepartmentId = $_POST['departmentId'];

        // Retrieve department name using the selected department ID
        $getDepartmentNameQuery = "SELECT name FROM departments WHERE department_id = ?";
        $stmt = $conn->prepare($getDepartmentNameQuery);
        $stmt->bind_param("i", $selectedDepartmentId);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            // Department exists, retrieve department name
            $stmt->bind_result($selectedDepartmentName);
            $stmt->fetch();
        } else {
            echo "<script>alert('Invalid department ID or unauthorized access.');";
            echo "window.location.href='dashboard.php';</script>";
            exit();
        }

        $stmt->close();

        // Create table if not exists
        $createTableQuery = "CREATE TABLE IF NOT EXISTS staffs (
            department_id INT,
            staff_id INT PRIMARY KEY AUTO_INCREMENT,
            name VARCHAR(255),
            role VARCHAR(255),
            FOREIGN KEY (department_id) REFERENCES departments(department_id)
        )";

        if ($conn->query($createTableQuery) !== TRUE) {
            echo "Error creating table: " . $conn->error;
        }

        // Use prepared statements to prevent SQL injection
        $insertQuery = "INSERT INTO staffs (department_id, name, role) VALUES (?, ?, ?)";
        $stmt = $conn->prepare($insertQuery);
        $stmt->bind_param("iss", $selectedDepartmentId, $name, $role);

        for ($i = 1; $i <= $numStaffs; $i++) {
            $name = $_POST["name$i"];
            $role = $_POST["role$i"];

            if ($stmt->execute() !== TRUE) {
                echo "Error inserting data: " . $stmt->error;
                exit();
            }
        }

        $stmt->close();

        // Display success alert and redirect after 2 seconds
        echo "<script>alert('Staffs added successfully. You can proceed to choose department.');";
        echo "setTimeout(function(){ window.location.href='choosedepartment.php?username=" . urlencode($_SESSION['username']) . "'; }, 2000);</script>";
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
            max-width: 1000px;
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
            padding: 8px; 
            margin-bottom: 12px; 
            border: 1px solid #3498db;
            border-radius: 5px;
            box-sizing: border-box;
            color: #3498db;
        }

        .form-group {
            display: flex;
            flex-direction: row;
            justify-content: space-between; 
            margin-bottom: 20px;
        }

        .form-group label,
        .form-group select {
            width: 48%; 
        }

        button {
            padding: 12px 24px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            background-color: #3498db;
            color: #fff;
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
        <!-- Display department information -->
        <h4 style="color: #3498db;">Staff Information</h4>
        <!-- Form for adding staffs -->
        <form method="post" action="">
            <label for="numStaffs" style="color: #3498db;">Number of Staffs:</label>
            <input type="number" name="numStaffs" id="numStaffs" required>

            <!-- Department selection dropdown -->
            <div class="form-group">
                <label for="departmentId" style="color: #3498db;">Select Department:</label>
                <select name="departmentId" id="departmentId" required>
                    <?php while ($row = $result->fetch_assoc()) { ?>
                        <option value="<?= $row['department_id'] ?>"><?= $row['name'] ?></option>
                    <?php } ?>
                </select>
            </div>

            <div id="staffFieldsContainer"></div>

            <script>
                // Function to generate dynamic fields based on the number of staffs
                function generateStaffFields() {
                    var numStaffs = document.getElementById("numStaffs").value;
                    var container = document.getElementById("staffFieldsContainer");
                    container.innerHTML = "";

                    for (var i = 1; i <= numStaffs; i++) {
                        var formGroup = document.createElement("div");
                        formGroup.classList.add("form-group");

                        var nameLabel = document.createElement("label");
                        nameLabel.setAttribute("for", "name" + i);
                        nameLabel.innerText = "Name:";

                        var nameInput = document.createElement("input");
                        nameInput.setAttribute("type", "text");
                        nameInput.setAttribute("name", "name" + i);
                        nameInput.setAttribute("required", "");

                        var roleLabel = document.createElement("label");
                        roleLabel.setAttribute("for", "role" + i);
                        roleLabel.innerText = "Role:";

                        var roleSelect = document.createElement("select");
                        roleSelect.setAttribute("name", "role" + i);
                        roleSelect.setAttribute("required", "");

                        var roles = ["HOD & Assistant Professor", "Assistant Professor", "HOD & Associate Professor", "Associate Professor", "HOD"];

                        for (var j = 0; j < roles.length; j++) {
                            var option = document.createElement("option");
                            option.setAttribute("value", roles[j]);
                            option.innerText = roles[j];
                            roleSelect.appendChild(option);
                        }

                        formGroup.appendChild(nameLabel);
                        formGroup.appendChild(nameInput);
                        formGroup.appendChild(roleLabel);
                        formGroup.appendChild(roleSelect);

                        container.appendChild(formGroup);
                    }
                }

                // Call the function initially and whenever the number of staffs changes
                generateStaffFields();
                document.getElementById("numStaffs").addEventListener("input", generateStaffFields);
            </script>

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
