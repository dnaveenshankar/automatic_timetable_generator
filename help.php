<?php
  // Start the session to access session variables
  session_start();

  // Redirect to the login page if the user is not logged in
  if (!isset($_SESSION['username'])) {
    header('Location: login.php');
    exit();
  }

  $name = $_SESSION['name'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Help - Timetable Generator</title>
  <!-- Bootstrap CSS -->
  <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
  <style>
    body {
      background-color: #3498db;
      color: #3498db;
      display: flex;
      align-items: center;
      justify-content: center;
      height: 100vh;
      margin: 0;
      padding: 20px;
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
      transition: transform 0.3s ease-in-out; 
    }
    .heading {
      font-size: 24px;
      margin-bottom: 20px;
      color: #3498db;
    }
    .help-text {
      font-size: 16px;
      line-height: 1.5;
      text-align: left;
    }
    .back-btn {
      background-color: #3498db;
      color: #fff;
      margin-top: 20px;
      padding: 10px 20px;
      border: none;
      border-radius: 5px;
      cursor: pointer;
      text-decoration: none;
      font-size: 16px;
      transition: background-color 0.3s ease-in-out;
    }
    .back-btn:hover {
      background-color: #2980b9;
    }
    .container:hover {
      transform: scale(1.05);
    }
  </style>
</head>
<body>

<div class="container">
  <div class="heading">Help</div>
  <div class="help-text">
    <p><strong>Create Department:</strong> Click this button to create a new department.</p>
    <p><strong>Create Timetable:</strong> Click this button to create a new timetable.</p>
    <p><strong>View Department:</strong> Click this button to view existing departments.</p>
    <p><strong>View Timetables:</strong> Click this button to view existing timetables.</p>
    <p><strong>Logout:</strong> Click this button to log out of your account.</p>
  </div>
  <a href="dashboard.php?username=<?php echo urlencode($_SESSION['username']); ?>&name=<?php echo urlencode($_SESSION['name']); ?>" class="back-btn">Back</a>
</div>

<!-- Bootstrap JS -->
<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

</body>
</html>
