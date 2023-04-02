# Weather forecast by location - C++ 

## Summary
A simple program in C++ using [cURL](https://curl.se/), run from the command line,
 that takes latitude and longitude and returns extracted json from a
basic weather forecast from NWS API (for the U.S.A)


## Requires
* [libcurl](https://curl.se/libcurl/c/)
* [json parser](https://github.com/nlohmann/json) in local dir (or anywhere but update makefile build)


## Usage

inluded makefile (uses g++)
* `make run lat=33.94159 lng=-118.40853`


## Licensing
MIT License
