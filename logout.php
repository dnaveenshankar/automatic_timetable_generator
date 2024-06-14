<?php
  // Start the session to access session variables
  session_start();

  // Clear all session variables
  session_unset();

  // Destroy the session
  session_destroy();
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Logout - Timetable Generator</title>
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
      box-shadow: 0px 0px 10px 0px rgba(0, 0, 0, 0.1);
      padding: 20px;
      max-width: 400px;
      text-align: center;
    }
    .confirmation-message {
      margin-bottom: 20px;
    }
    .btn {
      background-color: #3498db;
      color: #fff;
    }
    .btn-secondary {
      background-color: #95a5a6;
    }
  </style>
</head>
<body>

<div class="container">
  <div class="confirmation-message">
    <p>You have successfully logged out.</p>
  </div>
  <script>
    setTimeout(function() {
      window.location.href = "login.php";
    }, 10); 
  </script>
</div>

<!-- Bootstrap JS and Popper.js -->
<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.3/dist/umd/popper.min.js"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

</body>
</html>
