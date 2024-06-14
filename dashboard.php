<?php
  // Start the session to access session variables
  session_start();

  // Redirect to the login page if the user is not logged in
  if (!isset($_SESSION['username'])) {
    header('Location: login.php');
    exit();
  }

  // Retrieve user information from the session
  $name = $_SESSION['name'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Dashboard - Timetable Generator</title>
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
    }
    .welcome-message {
      margin-bottom: 20px;
      color: #e74c3c;
      font-size: 18px;
    }
    .heading {
      font-size: 24px;
      margin-bottom: 20px;
      color: #3498db;
    }
    .button-container {
      display: flex;
      flex-wrap: wrap;
      justify-content: space-between;
      align-items: center;
    }
    .btn {
      background-color: #3498db;
      color: #fff;
      margin: 10px;
      width: 100%; 
      font-size: 16px;
      transition: background-color 0.3s ease-in-out;
      border: 2px solid #3498db;
    }
    .btn:hover {
      background-color: #2980b9;
      border: 2px solid #2980b9;
    }
    .btn-danger {
      background-color: #e74c3c;
    }
    .btn-group {
      display: flex;
      align-items: center;
      margin-top: 10px;
      width: 100%;
    }
    .btn-group .btn {
      margin: 0 5px;
      width: 48%;
    }
    .btn-group .btn-double-width {
      width: 100%;
    }
    .img-container {
      flex: 1;
      margin-right: 20px;
    }
    .img-container img {
      width: 100%;
      border-radius: 10px;
    }
    .footer {
      margin-top: 20px;
      color: #3498db;
    }
  </style>
</head>
<body>

<div class="container">
  <div class="row">
    <div class="col-md-6">
      <div class="img-container">
        <img src="img2.png" alt="Image" class="img-fluid">
      </div>
    </div>
    <div class="col-md-6">
      <div class="welcome-message">
        <p>Hello, <?php echo $name; ?>!</p>
      </div>
      <div class="button-container">
        <a href="createdepartment.php?username=<?php echo urlencode($_SESSION['username']); ?>" class="btn btn-success btn-double-width">Create Department</a>
        <a href="cchoosedepartment.php?username=<?php echo urlencode($_SESSION['username']); ?>&name=<?php echo urlencode($_SESSION['name']); ?>" class="btn btn-primary btn-double-width">Create Timetable</a>
        <a href="viewdepartments.php?username=<?php echo urlencode($_SESSION['username']); ?>&name=<?php echo urlencode($_SESSION['name']); ?>" class="btn btn-success btn-double-width">View Department</a>
        <a href="timetable.php?username=<?php echo urlencode($_SESSION['username']); ?>&name=<?php echo urlencode($_SESSION['name']); ?>" class="btn btn-info btn-double-width">View Timetables</a>
      </div>
      <div class="btn-group">
        <a href="help.php?username=<?php echo urlencode($_SESSION['username']); ?>&name=<?php echo urlencode($_SESSION['name']); ?>" class="btn btn-info">Help</a>
        <button class="btn btn-danger" onclick="confirmLogout()">Logout</button>
      </div>
    </div>
  </div>
  <div class="footer">
    <p>Automatic Timetable Generator</p>
  </div>
</div>

<!-- Bootstrap JS and Popper.js -->
<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.3/dist/umd/popper.min.js"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>


<script>
  function confirmLogout() {
    // Fetch the username using an AJAX request
    var xhr = new XMLHttpRequest();
    xhr.onreadystatechange = function() {
      if (xhr.readyState === 4 && xhr.status === 200) {
        var username = xhr.responseText.trim();
        if (confirm("Are you sure you want to log out?")) {
          window.location.href = "logout.php?username=" + encodeURIComponent(username);
        }
      }
    };
    xhr.open("GET", "get_username.php", true);
    xhr.send();
  }
</script>
</script>

</body>
</html>
