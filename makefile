default: build

build: clean
		g++ -Wall main.cpp jsonUtils.cpp curlUtils.cpp currentTime.cpp -lcurl -lstdc++ -Ijson/include/ -o getNWSForecast.o

clean:
		rm -rf ./getNWSForecast.o

run: build
	./getNWSForecast.o $(lat) $(lng) -pp
