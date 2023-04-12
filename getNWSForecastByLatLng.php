<?php 
require('sendGetRequest.php');

header( 'Content-type: application/json; charset=utf-8');

/*
 * Get user input lat & lng 
 */
$path = $_SERVER['SCRIPT_NAME'];
$info = array(
  "help"=> array(
    "description"=> "Get basic weather forecast (within the USA) from the NWS API via latitude and longitude",
    "usage"=> array("web"=> $path.'?lat=<latitude>&lng=<longitude>',  "cli"=>'php '. basename($path).' <latitude> <longitude>'),
    "note"=>"latitude and logitude need to be in decimal degrees"
  )
);
if (php_sapi_name() == 'cli') {
  $arguments = $_SERVER['argv'];

  if ($_SERVER['argc'] < 3){
    if ($arguments[1] == "-h" || $arguments[1] == "--help"){
      echo json_encode($info);
      exit;
    } else { 
      echo '{"error":"Please provide latitude and longitude"}';
      exit;
    }
  }   

  $a = $arguments[1];
  $b = $arguments[2];

  // check: in north america lat > lng
  $correctLatLng = ($a > $b);
  $lat = ($correctLatLng) ? $a : $b;
  $lng = ($correctLatLng) ? $b : $a;
} else {
  $help = filter_input(INPUT_GET, 'help', FILTER_VALIDATE_BOOL);
  if ($help){
    echo json_encode($info);
    exit;
  }

  $lat = filter_input(INPUT_GET, 'lat', FILTER_VALIDATE_FLOAT);
  $lng = filter_input(INPUT_GET, 'lng', FILTER_VALIDATE_FLOAT);
  if (!isset($lat) || !isset($lng)){
    echo '{"error":"Please provide latitude and longitude in decimal degrees"}';
    exit;
  }
}


/*
 * If inputs are not numbers return error
 */ 
if(!is_numeric($lat) || !is_numeric($lng)){
  echo '{"error":"Error with given latitude ('.$lat.') or longitude ('.$lng.')"}';
  exit;
}
$now = (new DateTime())->format('Y-m-d H:i:s');


/*
 * Construct API url
 */
$points =  $lat.",".$lng;
$baseURL = "https://api.weather.gov" ;
$API_options = "/points";
$url = $baseURL. $API_options."/".$points;

// NWS requires a set user-agent to make non browser requests
$extraheaders = array('User-Agent'=>"Example NWS API Call/1");

$params = array();
$auth = array();


/*
 * GET basic information about lat,lng location + next forcast URL
 */
$response = sendGetRequest($url, $extraheaders, $params, $auth);
if($response['code'] != 200){
  $msg = (isset($response['body']->detail)) ? $response['body']->detail : "unable to get data";
  echo '{"error":"Error getting data from '. $url.'", "httpcode":'.$response["code"].', "message":"'.$msg.'"}';
  exit;
}
$pointData = $response['body'];
$location = $pointData->properties->relativeLocation->properties;
$forecastURL = $pointData->properties->forecast;


/*
 * GET forcast information
 */
$forecastResp = sendGetRequest($forecastURL, $extraheaders, $params, $auth);
if($forecastResp['code'] != 200){
  $msg = (isset($forecastResp['body']->detail)) ? $forecastResp['body']->detail : "unable to get data";
  echo '{"error":"Error getting data from '. $forecastURL.'", "httpcode":'.$forecastResp["code"].', "message":"'.$msg.'"}';
  exit;
}
$forecast = $forecastResp['body'];
$numPeriods = count($forecast->properties->periods);


/*
 * parse needed forcast info for time periods
 */
$periods = array();
for ($i = 0; $i < $numPeriods; $i++) {
  $day = $forecast->properties->periods[$i];
  $thisdate = DateTime::createFromFormat(DATE_ATOM, $day->startTime);
  $daydate = $thisdate->format('M.jS');

  // "Wind: ".$day->windSpeed." - ". $day->windDirection;
  // "Forcast: ".$day->detailedForecast;

  $periods[] = array(
    $day->name, 
    $daydate,
    $day->temperature . " deg" . $day->temperatureUnit,
    $day->shortForecast
  );
}


/*
 * create data to output
 */
$output = array(
  "closestCity" => $location->city. ", " .$location->state,
  "latitude"    => $lat,
  "longitude"   => $lng,
  "requested"   => $now,
  "source"      => array("NWS API",$baseURL),
  'forecast'    => $periods,
);


/*
 * Output data as json file
 */
echo json_encode($output);

