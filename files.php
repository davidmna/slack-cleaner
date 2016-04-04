<?php
  require_once './sessions.php';
  require_once './curl.php';

  if(!empty($_POST['deletefiles'])) {
    //echo "<pre>".print_r($_POST, true)."</pre>";
    foreach ($_POST['deletefiles'] as $file_id) {
      $params = array('token' => $_SESSION['slack_access_token'], 'file' => $file_id);
      curl_call('https://slack.com/api/files.delete', $params);
      //break;
    }
  }

  $files = null;
  $total_size = 0;
?>

<h1>Files</h1>

<?php
//echo "<pre>".print_r($_SESSION, true)."</pre>";

$params = array(
  'token' => $_SESSION['slack_access_token'],
  'ts_to' => time() - (24 * 60 * 60 * 15),
  'count' => 10,
  'page' => 1
  );

if (!$_SESSION['slack_user_is_admin']) {
  $params['user'] = $_SESSION['slack_user_id'];
}

while(true) {
  $fileslist = curl_call('https://slack.com/api/files.list', $params);
  //echo "<pre>".print_r($fileslist, true)."</pre>";
  
  if($fileslist['ok'] && !empty($fileslist['files'])) {
    foreach ($fileslist['files'] as $file) {
      $files[] = $file;
      $total_size+=$file['size'];
    }

    $params['page']+=1;
  } else {
    break;
  }
}

?>

<?php if(!empty($files)): $files = array_reverse($files); ?>
<?php echo count($files);?> files older than 15 days. Total size: <?php echo formatSizeUnits($total_size); ?>
<form name="filestodelete" id="filestodelete" method="POST" action="" onsubmit="return confirm('Do you really want to delete all these files?');">
<p><input type="submit" value="Delete all"/></p>
<table>
  <thead>
    <tr>
      <th>File</th>
      <th>Date</th>
      <th>Size</th>
    </tr>
  </thead>
  <tbody>
  <?php foreach ($files as $file): ?>
    <tr id="<?php echo $file['id']; ?>">
      <input type="hidden" name="deletefiles[]" value="<?php echo $file['id']; ?>"/>
      <td><a href="<?php echo $file['url_private_download']; ?>"><?php echo $file['name']; ?></a></td>
      <td><?php echo  date('Y-m-d', $file['created']); ?></td>
      <td><?php echo formatSizeUnits($file['size']); ?></td>
    </tr>
  <?php endforeach; ?>

  </tbody>
</table>
</form>
<?php else: ?>
<p>All clear.</p>
<?php endif; ?>


<?php
// Snippet from PHP Share: http://www.phpshare.org

function formatSizeUnits($bytes)
{
    if ($bytes >= 1073741824)
    {
        $bytes = number_format($bytes / 1073741824, 2) . ' GB';
    }
    elseif ($bytes >= 1048576)
    {
        $bytes = number_format($bytes / 1048576, 2) . ' MB';
    }
    elseif ($bytes >= 1024)
    {
        $bytes = number_format($bytes / 1024, 2) . ' KB';
    }
    elseif ($bytes > 1)
    {
        $bytes = $bytes . ' bytes';
    }
    elseif ($bytes == 1)
    {
        $bytes = $bytes . ' byte';
    }
    else
    {
        $bytes = '0 bytes';
    }

    return $bytes;
}
?>