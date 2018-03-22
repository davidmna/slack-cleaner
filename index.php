<?php
  require_once './logged.php';
  require_once './curl.php';

  $config = require_once('config.php');

  if(!empty($_GET['code']) && $_GET['state'] == 'linked') {
    $params = array(
      'client_id' => $config['client_id'],
      'client_secret' => $config['client_secret'],
      'code' => $_GET['code'],
      'redirect_uri' => $config['redirect_uri'],
      );

    $resp = curl_call('https://slack.com/api/oauth.access', $params);

    echo "<pre>".print_r($resp, true)."</pre>";

    if($resp['ok']) {
      echo 'save token in session';
      $_SESSION['slack_access_token'] = $resp['access_token'];
      $_SESSION['slack_team_name'] = $resp['team_name'];
      $_SESSION['slack_team_id'] = $resp['team_id'];

      $params = array('token' => $_SESSION['slack_access_token']);
      $auth = curl_call('https://slack.com/api/auth.test', $params);

      if($auth['ok']) {
        $_SESSION['slack_user'] = $auth['user'];
        $_SESSION['slack_user_id'] = $auth['user_id'];

        $params = array('token' => $_SESSION['slack_access_token'], 'user' => $_SESSION['slack_user_id']);
        $userinfo = curl_call('https://slack.com/api/users.info', $params);

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
<br><br><hr>
<h1>Stop the paranoia!</h1>
<p>
  I never created this tool to do any harm or steal any data. 
  I'm working in an static app that does not require for a third-party server,
  but while this happens and if it makes you feel better, you can always clone the project 
  repo and run this in a server of your own.
</p>
<p><a href="https://github.com/dmoralesm/slack-cleaner" target="_blank">Cleanslack at GitHub</a></p>