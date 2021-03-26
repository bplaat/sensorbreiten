#include "wifi.hpp"
#include <Arduino.h>
#include <ESP8266WiFi.h>
#include <ESP8266WebServer.h>
#include <ArduinoJson.h>
#include "website.hpp"
#include "config.hpp"
#include "storage.hpp"

// Local wifi network ip addresses
IPAddress local_ip = IPAddress(192,168,1,1);
IPAddress gateway = IPAddress(192,168,1,1);
IPAddress subnet = IPAddress(255,255,255,0);

// Init local wifi network
void init_local_wifi() {
    Serial.print("[LOCAL WIFI] Setting up local wifi network configuration ... ");
    Serial.println(WiFi.softAPConfig(local_ip, gateway, subnet) ? "Ready" : "Failed!");

    Serial.print("[LOCAL WIFI] Setting up local wifi network ... ");
    Serial.println(WiFi.softAP(local_wifi_ssid, local_wifi_password) ? "Ready" : "Failed");

    Serial.print("[LOCAL WIFI] Local wifi IP address = ");
    Serial.println(WiFi.softAPIP());
}

// Connect to external wifi network
void connect_wifi() {
    Serial.print("[WIFI] Connecting to ");
    Serial.print(wifi_ssid);

    WiFi.begin(wifi_ssid, wifi_password);
    while (WiFi.status() != WL_CONNECTED) {
        Serial.print(".");
        delay(500);
    }

    Serial.print("\n[WIFI] Wifi IP address: ");
    Serial.println(WiFi.localIP());
}

// Init the webs server
void init_webserver(ESP8266WebServer& webserver) {
    // Return the web interface when you go to the root path
    webserver.on("/", [&]() {
        Serial.println("[WEB] /");
        webserver.send(200, "text/html", src_website_html, src_website_html_len);
    });

    // The api read config
    webserver.on("/api/config", [&]() {
        Serial.print("[WEB] /api/config ");

        // Sterialize the config to a buffer
        char buffer[STRING_BUFFER_SIZE];
        StaticJsonDocument<JSON_BUFFER_SIZE> document;
        document["station_api_key"] = station_api_key;
        document["local_wifi_ssid"] = local_wifi_ssid;
        document["local_wifi_password"] = local_wifi_password;
        document["wifi_ssid"] = wifi_ssid;
        document["wifi_password"] = wifi_password;
        serializeJson(document, buffer);
        Serial.println(buffer);

        // Return the data to the client
        webserver.send(200, "application/json", buffer);
    });

    // The api edit dconfig
    webserver.on("/api/config/edit", [&]() {
        Serial.println("[WEB] /api/config/edit");

        // Set the config variables when given
        if (webserver.arg("station_api_key") != "") {
            station_api_key = webserver.arg("station_api_key");
        }
        if (webserver.arg("local_wifi_ssid") != "") {
            local_wifi_ssid = webserver.arg("local_wifi_ssid");
        }
        if (webserver.arg("local_wifi_password") != "") {
            local_wifi_password = webserver.arg("local_wifi_password");
        }
        if (webserver.arg("wifi_ssid") != "") {
            wifi_ssid = webserver.arg("wifi_ssid");
        }
        if (webserver.arg("wifi_password") != "") {
            wifi_password = webserver.arg("wifi_password");
        }

        // Return a confirmation message
        webserver.send(200, "application/json", "{\"message\":\"The config has been edited succesfully\"}");

        // Saves the config
        save_config();

        // Reinit the local wifi network
        init_local_wifi();

        // Disconect to wifi when connected
        if (WiFi.status() == WL_CONNECTED) {
            WiFi.disconnect();
        }

        // Connect to wifi when ssid and password are not empty
        if (wifi_ssid != "" && wifi_password != "") {
            connect_wifi();
        }
    });

    // Begin the web server
    webserver.begin();
}
