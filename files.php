<?php
  require_once './sessions.php';
  require_once './curl.php';
  /*
  if(!empty($_POST['deletefiles'])) {
    //echo "<pre>".print_r($_POST, true)."</pre>";
    foreach ($_POST['deletefiles'] as $file_id) {
      $params = array('token' => $_SESSION['slack_access_token'], 'file' => $file_id);
      curl_call('https://slack.com/api/files.delete', $params);
      //break;
    }
  }
  */
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
<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.12.2/jquery.min.js"></script>
<form name="filestodelete" id="filestodelete" method="POST" action="">
<p><input type="submit" value="Delete all" id="delete-btn"/><span id="deleting-msg" style="margin-left: 8px; display: none">Deleting...</span></p>
<table id="table_files">
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
<script type="text/javascript">

  $.fn.extend({
    delayedAjax: function() {
      setTimeout ($.ajax, 1000 );
    }
  });

  $.fn.delayedAjax();

  $('#delete-btn').click(function(e){
    if(confirm('Do you really want to delete all these files?')) {
      $('#deleting-msg').show();
      $('#delete-btn').prop('disabled', true);
      var files_count = $('#table_files > tbody  > tr').length;
      $('#table_files > tbody  > tr').each(function() {
        var file_id = $(this).attr('id');
        
        $.ajax("/deletefile.php?id="+file_id)
          .done(function(data) {
            if(data.ok) {
              $('table#table_files tr#'+file_id).remove();
              files_count--;
            }
          })
          .always(function(){
            if(files_count == 0){
              location.reload();
            }
          });
      });
    }
    e.preventDefault();
  });
</script>

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