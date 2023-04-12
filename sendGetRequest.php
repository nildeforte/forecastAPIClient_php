<?php 

/*
 * parseHeaders take a curl response header string and returns an associative array 
 *
 * parsed with http_parse_headers() 
 * or if not available the string is split and converted it into an array
 *
 * @param string $raw_headers raw curl respones header 
 * @return array of the respones header as key value pairs
 *
 */
function parseHeaders($raw_headers) {
  if (function_exists('http_parse_headers')) {
    return http_parse_headers($raw_headers);
  } else {
    $key = '';
    $headers = array();

    foreach (explode("\n", $raw_headers) as $i => $h) {
      $h = explode(':', $h, 2);

      if (isset($h[1])) {
        if (!isset($headers[$h[0]])) {
          $headers[$h[0]] = trim($h[1]);
        } elseif (is_array($headers[$h[0]])) {
          $headers[$h[0]] = array_merge($headers[$h[0]], array(trim($h[1])));
        } else {
          $headers[$h[0]] = array_merge(array($headers[$h[0]]), array(trim($h[1])));
        }

        $key = $h[0];
      } else {
        if (substr($h[0], 0, 1) == "\t") {
          $headers[$key] .= "\r\n\t".trim($h[0]);
        } elseif (!$key) {
          $headers[0] = trim($h[0]);
        }
      }
    }

    return $headers;
  }
}

/*
 * sendGetRequest is a basic curl GET request
 * with options to include additional url queries, headers, and auth
 *
 * @param string $url required web address sets CURLOPT_URL
 * @param array $headers key=>value pairs convert to "Key: Value" and used w/ CURLOPT_HTTPHEADER 
 * @param array $params  key=>value pairs convert to url query string using http_build_query function
 * @param array $auth{
 *   If user, pass, & method are set: it adds curl options: CURLOPT_HTTPAUTH & CURLOPT_USERPWD   
 *   Else if token is set: it adds the header 'X-Authentication-Token'
 *   Else does not include any authentication
 *
 *   @type string user   value string site's auth username
 *   @type string pass   value string site's auth password
 *   @type string method value string go to is 'CURLAUTH_BASIC' 
 *   @type string token  value string site's given token
 * }
 *
 * @return object{
 *  @type int code the http status code
 *  @type objet body the curl_exec() response
 *  @type array headers the curl_exec() header string parsed into an array
 * }
 */
function sendGetRequest($url, $headers, $params = null, $auth = array()){
  $handle = curl_init();

  if (is_array($params) && (count($params) >0)) {
    $url .= (strpos($url, '?') !== false) ? '&' : '?';

    $url .= urldecode(http_build_query($params));
  }


  /* $auth = array( 'user' = $username ,'pass' = $password ,'method' = CURLAUTH_BASIC ,'token' = $token); */
  if (!empty($auth['user']) && !empty($auth['method'])) {
    curl_setopt_array($handle, array(
      CURLOPT_HTTPAUTH  => $auth['method'],
      CURLOPT_USERPWD   => $auth['user'] . ':' . $this->auth['pass']
    ));
  } else if (!empty($auth['token'])) {
    $headers('X-Authentication-Token', $token);
  }

  $formattedHeaders = array('Accept: application/json');

  foreach ($headers as $key => $val) {
    $key = trim(strtolower($key));
    $formattedHeaders[] = $key . ': ' . $val;
  }

  $curl_base_options = [
    CURLOPT_URL => $url,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_FOLLOWLOCATION => true,
    CURLOPT_MAXREDIRS => 10,
    CURLOPT_HTTPHEADER => $formattedHeaders,
    CURLOPT_HEADER => true,
    CURLOPT_SSL_VERIFYPEER => true,
    CURLOPT_SSL_VERIFYHOST => 2,
    CURLOPT_ENCODING => ''
  ];
  curl_setopt_array($handle, $curl_base_options);

  $response   = curl_exec($handle);
  $error      = curl_error($handle);
  $info       = curl_getinfo($handle);

  if ($error) {
    error_log("sendGetRequest Error request to: ". $url);
    error_log("CURL error: ".$error);
    return array("code"=>400,"body"=>"", "error"=>"Error getting data with curl");
  }

  // Split the full response in its headers and body
  $header_size = $info['header_size'];
  $respHeader  = substr($response, 0, $header_size);
  $raw_body    = substr($response, $header_size);
  $httpCode    = $info['http_code'];

  $headers  = parseHeaders($respHeader);
  $body     = $raw_body;


  // $jsonOpts = args for json_decode: array((assoc = true || obj = false), depth, flags)
  $jsonOpts = array(false, 512, 0);

  // make sure raw_body is the first argument
  array_unshift($jsonOpts, $raw_body);
  if (function_exists('json_decode')) {
    $json = call_user_func_array('json_decode', $jsonOpts);
    if (json_last_error() === JSON_ERROR_NONE) {
      $body = $json;
    }
  }

  curl_close($handle);
  return array("code"=>$httpCode, "body"=>$body, "headers"=>$headers);
}
