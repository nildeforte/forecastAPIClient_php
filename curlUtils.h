#include <iostream>
#include <string>
#include <algorithm>
#include <curl/curl.h>

using namespace std;

// Response data for get request
struct httpGetResponse{
  int httpcode;
  string body;
  string headers;
};

// make the request
httpGetResponse curl_httpget(string url);


// callback for Writing Memory to buffer string during curl curlopt_writedata
int writer(char *data, size_t size, size_t nmemb, string *buffer);


