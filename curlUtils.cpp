#include "curlUtils.h"


int writer(char *data, size_t size, size_t nmemb, string *buffer)
{
  int result = 0;
  if (buffer != NULL)
  {
    buffer->append(data, size * nmemb);
    result = size * nmemb;
  }
  return result;
}


//GetResponse curl_httpget(const string &url)
httpGetResponse curl_httpget(string url)
{

  url.erase(remove(url.begin(), url.end(), '\"' ), url.end());
  string buffer;
  httpGetResponse gr;

  CURL *curl;
  CURLcode result;

  curl = curl_easy_init();

  if (curl)
  {
    curl_easy_setopt(curl, CURLOPT_URL, url.c_str());
    curl_easy_setopt(curl, CURLOPT_USERAGENT, "Example NWS API Call cpp/1");
    curl_easy_setopt(curl, CURLOPT_HEADER, 0);
    curl_easy_setopt(curl, CURLOPT_WRITEFUNCTION, writer);
    curl_easy_setopt(curl, CURLOPT_WRITEDATA, &buffer);

    result = curl_easy_perform(curl);//http get performed

    long httpCode;
    curl_easy_getinfo(curl, CURLINFO_RESPONSE_CODE, &httpCode);
    gr.httpcode = httpCode; 

    //error codes: http://curl.haxx.se/libcurl/c/libcurl-errors.html
    if (result == CURLE_OK)
    {
      string headers;
      curl_easy_cleanup(curl);//must cleanup

      gr.body = buffer;
      gr.headers = headers;
      return gr;
    }

    //curl_easy_strerror was added in libcurl 7.12.0
    //l cerr << "error: " << result << " " << curl_easy_strerror(result) <<endl;
    return gr;
  }

  cerr << "error: could not initalize curl" << endl;
  return gr;
}

