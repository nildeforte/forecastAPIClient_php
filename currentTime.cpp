#include <string>
#include "currentTime.h"

const std::string currentDateTime() {
  time_t     now = time(0);
  struct tm  tstruct;
  tstruct = *localtime(&now);

  return formatTimeDbStyle(tstruct);
}

std::string formatTimeDbStyle(tm tstruct){
  char       buf[80];
  strftime(buf, sizeof(buf), "%Y-%m-%d %X", &tstruct);

  return buf;
}
std::string formatTimeFromString(std::string givenTime){
  return givenTime.replace(10, 1, " " );
}
