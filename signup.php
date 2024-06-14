<?php
// Include the database connection file
include 'db_connection.php';

// Function to create the users table if it doesn't exist
function createUsersTable($conn) {
  $sql = "CREATE TABLE IF NOT EXISTS users (
    username VARCHAR(8) PRIMARY KEY,
    name VARCHAR(30) NOT NULL,
    password VARCHAR(255) NOT NULL
  )";

  if ($conn->query($sql) === TRUE) {
    echo "Table created successfully";
  } else {
    echo "Error creating table: " . $conn->error;
  }
}

// Function to check if the user already exists in the database
function userExists($conn, $username) {
  $stmt = $conn->prepare("SELECT * FROM users WHERE username = ?");
  $stmt->bind_param("s", $username);
  $stmt->execute();
  $result = $stmt->get_result();
  return $result->num_rows > 0;
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $name = $_POST['name'];
  $username = $_POST['username'];
  $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

  // Create the users table if it doesn't exist
  createUsersTable($conn);

  // Validate if the user already exists
  if (userExists($conn, $username)) {
    echo '<script>alert("Username already exists. Please choose a different username.");</script>';
  } else {
    // Save the user information to the database
    $stmt = $conn->prepare("INSERT INTO users (name, username, password) VALUES (?, ?, ?)");
    $stmt->bind_param("sss", $name, $username, $password);
    $stmt->execute();

    // Redirect to the dashboard after successful signup
    session_start();
    $_SESSION['username'] = $username;
    $_SESSION['name'] = $name;
    header('Location: dashboard.php');
    exit();
  }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Timetable Generator</title>
  <!-- Bootstrap CSS -->
  <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
  <style>
  body {
    background-color: #3498db;
    color: #fff;
    display: flex;
    align-items: center;
    justify-content: center;
    height: 100vh;
    margin: 0;
  }

  .container {
    background-color: #fff;
    border-radius: 10px;
    box-shadow: 0px 0px 10px 0px rgba(0, 0, 0, 0.1);
    padding: 20px;
    display: flex;
    flex-direction: column;
    align-items: center; 
    justify-content: center;
    overflow: auto;
    max-height: 100vh; 

  }

  .signup-form {
    margin-top: 20px;
    width: 100%;
  }

  .already-user {
    color: #3498db;
    margin-top: 15px;
  }

  .form-group label {
    color: #3498db;
    text-align: left;
  }
</style>

</head>
<body>

<div class="container">
  <div class="row">
    <div class="col-md-6 center-vertically">
      <hr class="my-4">
      <h2 class="mb-4 text-center" style="color: #3498db;">Timetable Generator</h2>
      <form class="signup-form" method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
        <div class="form-group">
          <label for="name">Name:</label>
          <input type="text" class="form-control" id="name" name="name" placeholder="Enter your name" maxlength="25" required>
        </div>
        <div class="form-group">
          <label for="username">Username (Max 8 Characters):</label>
          <input type="text" class="form-control" id="username" name="username" placeholder="Enter your username" required maxlength="8">
        </div>
        <div class="form-group">
          <label for="password">Password:</label>
          <input type="password" class="form-control" id="password" name="password" placeholder="Enter your password (8 characters or more)" required minlength="8">
        </div>
        <button type="submit" class="btn btn-primary">Sign Up</button>
      </form>
      <p class="mt-3 already-user text-center">Already a user? <a href="login.php">Login</a></p>
    </div>
    <div class="col-md-6">
      <img src="img1.png" alt="Image" class="img-fluid">
    </div>
  </div>
</div>

<!-- Bootstrap JS and Popper.js -->
<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.3/dist/umd/popper.min.js"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

</body>
</html>
