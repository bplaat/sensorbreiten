#pragma once

#include <ESP8266WiFi.h>
#include <ESP8266WebServer.h>

// Init local wifi network
void init_local_wifi();

// Connect the external wifi network
void connect_wifi();

// Init the web server
void init_webserver(ESP8266WebServer& webserver);

// Local wifi network ip addresses
extern IPAddress local_ip;
extern IPAddress gateway;
extern IPAddress subnet;
