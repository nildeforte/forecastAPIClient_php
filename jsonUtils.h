#ifndef nlohman_parse_json
#define nlohman_parse_json
#include <iostream>
#include <string>

#include <nlohmann/json.hpp>

using namespace std;

using json = nlohmann::json;

json parseJSON(string);
#endif /* nlohman_parse_json */

