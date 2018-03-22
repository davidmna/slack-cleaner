<?php
  function curl_call($url, $params = null, $method = 'GET') {
    // Get cURL resource
    $curl = curl_init();

    if ($method = 'GET') {
      $url.= url_params($params);
    }

    $options = array(
        CURLOPT_RETURNTRANSFER => 1,
        CURLOPT_URL => $url,
        CURLOPT_USERAGENT => 'Slack request'
    );

    // Set some options - we are passing in a useragent too here
    curl_setopt_array($curl, $options);
    // Send the request & save response to $resp
    $resp = curl_exec($curl);
    // Close request to clear up some resources
    curl_close($curl);

    return json_decode($resp, true);
  }

  function url_params($params) {
    $querystring = '?';
    foreach ($params as $key => $value) {
      if(strlen($querystring) > 1) {
        $querystring.='&';
      }
      $querystring.="$key=$value";

    }

    return $querystring;

  }