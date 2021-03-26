#include <Arduino.h>
#include <ESP8266WebServer.h>
#include <EEPROM.h>
#include "config.hpp"
#include "storage.hpp"
#include "wifi.hpp"

// The web server object
ESP8266WebServer webserver(80);

// The start of our program
void setup() {
    // Init the serial output
    Serial.begin(9600);

    // Setup the EEPROM
    EEPROM.begin(512);

    // Load the config from EEPROM
    load_config();

    // Init the local wifi
    init_local_wifi();

    // Connect to wifi with ssid and password when not empty
    if (wifi_ssid != "" && wifi_password != "") {
        connect_wifi();
    }

    // Init the webserver
    init_webserver(webserver);
}

// The program loop
void loop() {
    // Handle any http clients
    webserver.handleClient();
}
