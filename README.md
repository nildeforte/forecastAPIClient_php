# Weather forecast by location - PHP 

## Summary
A simple program using php & cURL, run from either command line or over the web,
that takes latitude and longitude and returns extracted json of a
basic seven day weather forecast from NWS API (for the U.S.A.)

## Usage 
* in the command line `php getNWSForecastByLatLng.php {lat} {lng}`
* over a web server, url: `{...}/getNWSForecastByLatLng.php?lat={lat}&lng={lng}`

#### sample locations 
* LAX: 33.94159 -118.40853
* BUR: 34.20056 -118.35861
* SFO: 37.61522 -122.38997
* ORD: 41.97805 -87.90611
* AUS: 30.194   -97.67
* JFK: 40.63992 -73.77869

### How NWA API works
From the [NWS API documentation](https://www.weather.gov/documentation/services-web-api )
> The [National Weather Service (NWS) API](https://api.weather.gov) allows 
developers public access to critical forecasts, alerts, and observations, 
along with other weather data.

* need to know the coordinates of the location in decimal degrees (geospatially technical a WGS 84 or EPSG 4326 coordinate)
  * the latitude and longitude with up to four decimal places of precision 
* make sure the program includes a User-Agent header in the request
* follow the three part process: 
  1. Retrieve the metadata for location from https://api.weather.gov/points/{lat},{lng}
  2. The response is a JSON document, inside the properties object find the forecast property URL and retrieve the desired forecast information
    (For example: https://api.weather.gov/gridpoints/{pointArea}/{point1},{point2}/forecast)
  3. That URL response has a another JSON document, which contains the forecast information for the location


## Licensing
[MIT License](LICENSE)
