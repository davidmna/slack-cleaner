<?php 
  session_start();

  if(empty($_SESSION['slack_access_token'])) {
    header('Location: index.php');
  }