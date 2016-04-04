<?php
  require_once './logged.php';
  require_once './curl.php';

  $config = require_once('config.php');

  if(!empty($_GET['code']) && $_GET['state'] == 'linked') {
    //Ask for auth
    //echo 'ask for auth';

    $params = array(
      'client_id' => $config['client_id'],
      'client_secret' => $config['client_secret'],
      'code' => $_GET['code'],
      'redirect_uri' => $config['redirect_uri'],
      );

    $resp = curl_call('https://slack.com/api/oauth.access', $params);

    //$resp = json_decode($resp, true);
    echo "<pre>".print_r($resp, true)."</pre>";
    //die;

    if($resp['ok']) {
      echo 'save token in session';
      $_SESSION['slack_access_token'] = $resp['access_token'];
      $_SESSION['slack_team_name'] = $resp['team_name'];
      $_SESSION['slack_team_id'] = $resp['team_id'];

      $params = array('token' => $_SESSION['slack_access_token']);
      $auth = curl_call('https://slack.com/api/auth.test', $params);
      //echo "<pre>".print_r($auth, true)."</pre>";


      if($auth['ok']) {
        $_SESSION['slack_user'] = $auth['user'];
        $_SESSION['slack_user_id'] = $auth['user_id'];

        $params = array('token' => $_SESSION['slack_access_token'], 'user' => $_SESSION['slack_user_id']);
        $userinfo = curl_call('https://slack.com/api/users.info', $params);
        //echo "<pre>".print_r($userinfo, true)."</pre>";

        if($userinfo['ok']) {
          $_SESSION['slack_user_is_admin'] = ($userinfo['user']['is_admin'] || $userinfo['user']['is_owner']);
        
          header('Location: files.php');
        }
      }
    }
  }
?>
<h1>Too many files on Slack?</h1>
<p>Help your team to clean the things up by deleting older files.</p>
<a href="https://slack.com/oauth/authorize?client_id=<?php echo $config['client_id'] ?>&scope=<?php echo $config['scope'] ?>&redirect_uri=<?php echo $config['redirect_uri'] ?>&state=linked">Login to Slack</a>