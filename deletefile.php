<?php
  require_once './sessions.php';
  require_once './curl.php';

  sleep(1);

  if(!empty($_GET['id'])) {
    $params = array('token' => $_SESSION['slack_access_token'], 'file' => $_GET['id']);
    $response = array('ok' => true);
    //$response = curl_call('https://slack.com/api/files.delete', $params);
    header('Content-Type: application/json');
    echo json_encode($response);
  }