#include "jsonUtils.h"

json parseJSON(string text)
{
  // parse with exceptions
  try {
    json j = json::parse(text);
  } catch (json::parse_error& e) {
    cout << e.what() << endl;
  }

  // parse without exceptions
  json j = json::parse(text, nullptr, false);

  if (j.is_discarded()) {
    cout << "the input is invalid JSON" << endl;
  } else {
    // cout << "the input is valid JSON: " << j << endl;
  }
  return j;
}
