<?php
  session_start();
  $username = isset($_SESSION['username']) ? $_SESSION['username'] : '';
  echo $username;
?>
