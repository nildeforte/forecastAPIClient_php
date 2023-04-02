#include <iostream>
#include <string>

#include <fstream>
#include <sstream>

#include "curlUtils.h"
#include "jsonUtils.h"
#include "currentTime.h"

using json = nlohmann::json;

using namespace std;

void help()
{
  string App = "getNWSForecast";
      cout << "Basic Weather Forecast from NWS API" << endl;
      cout << "\nUsage: \n" << App << " [-h][{lat} {lng}] [-pp]" << endl;
      cout << "\nOptions:" << endl;
      cout << "-h     Display this help text" << endl;
      cout << "{lat}  Decimal degree of location latitude *required" << endl;
      cout << "{lng}  Decimal degree of location longitude *required" << endl;
      cout << "-pp    Pretty print the json output for better readability" << endl;
}

int main(int argc, char *argv[])
{
  bool prettyPrint = false;

  for (int i = 0; i < argc; i++) {
    if(string(argv[i]) == "-h" || string(argv[i]) == "--help"){
      help();
      return 0;
    }
    if(string(argv[i]) == "-pp"){
      prettyPrint = true;
    }
  }
  if(argc < 3){
    help();
    // cout << "{Error with request - need both latitude and longitude}" << endl;
    return 1;
  }

  float a = atof(argv[1]);
  float b = atof(argv[2]);
  bool correctLatLng = a > b;

  char one[8];
  char two[8];
  sprintf(one, "%.2f", a);
  sprintf(two, "%.2f", b);

  string lat = (correctLatLng) ? one : two;
  string lng = (correctLatLng) ? two : one;

  string weatherURL = "https://api.weather.gov/";

  string url  = weatherURL + "points/"+ lat+","+lng;

  string now = currentDateTime();

  /*
  //
  // get forcast URL
  //
  */
  httpGetResponse resp = curl_httpget(url);

  json j = parseJSON(resp.body);

  if(resp.httpcode == 0){
    cout << "{Error with curl request URL: '"<< url << "'}"<< endl;
    return 1;
  }
  if(resp.httpcode != 200){

    json output = {
      {"error",    "getting data from URL"},
      {"latitude",  resp.httpcode},
      {"details",   string(j["detail"])},
      {"url",       url},
      {"requested", now},
    };

    if (prettyPrint){
      cout << std::setw(4) << output << endl;
    } else {
      cout << output << endl;
    }
    return 1;
  }


  string forecastURL = j["properties"]["forecast"] ;

  json location = j["properties"]["relativeLocation"]["properties"];
  string cityState = string(location["city"]) +  ", " + string(location["state"]);
  json  reqSource = { "NWS API", weatherURL  };


  // 
  // get forecast information
  //
  httpGetResponse forecast = curl_httpget(forecastURL );
  if(forecast.httpcode == 0){
    cout << "{Error with curl request URL: '"<< url << "'}"<< endl;
    return 1;
  }
  if(forecast.httpcode != 200){
    cout << "{Error getting data from URL: '"<< url << "'. HTTPCode "<< forecast.httpcode << "}"<< endl;
    return 1;
  }


  json jf = parseJSON(forecast.body);

  json numPeriods = jf["properties"]["periods"];


  /*
   * parse needed forcast info for time periods
   */
  json periods;

  for(auto it = numPeriods.begin(); it != numPeriods.end(); ++it)
  {
    json day = *it;
    string daydate = formatTimeFromString(string(day["startTime"]));
    string temp = to_string(day["temperature"]) +  " deg" + string(day["temperatureUnit"]);
    string wind = "Wind: "+string(day["windSpeed"]) + " - " + string(day["windDirection"]);
    //  cout <<"Forcast: "<<day.detailedForecast << endl;

    periods.push_back({
        string(day["name"]),
        daydate,
        temp,
        wind,
        string(day["shortForecast"])
        });
  }


  /*
   * create data to output
   */
  json output = {
    {"closestCity", cityState},
    {"latitude"   , lat},
    {"longitude"  , lng},
    {"requested"  , now},
    {"source"     , reqSource},
    {"forecast"   , periods}
  };

  if (prettyPrint){
    cout << std::setw(4) << output << endl;
  } else {
    cout << output << endl;
  }

  return 0;
}

