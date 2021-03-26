#include <Arduino.h>
#include <ArduinoJson.h>
#include <ESP8266HttpClient.h>
#include <ESP8266WebServer.h>
#include <EEPROM.h>
#include "DHTesp.h"
#include "config.hpp"
#include "storage.hpp"
#include "wifi.hpp"

#define EVENT_TYPE_LED 0
#define EVENT_TYPE_BEEPER 1

#define DHT_PIN D1
DHTesp dht;

#define LED_PIN D2
uint32_t led_time = millis();
uint32_t led_duration = 0;

#define BEEPER_PIN D3

#define LDR_PIN A0

#define CALIBRATION_BASE 1.0
#define CALIBRATION_EXP 0.0055
#define MEASUREMENT_INTERVAL 60 * 1000
uint32_t send_time = millis();

// The web server object
ESP8266WebServer webserver(80);

// Send a new measurement and return response
String send_measurement() {
    // Read sensor values
    delay(dht.getMinimumSamplingPeriod());
    float temperature = dht.getTemperature();
    float humidity = dht.getHumidity();
    float light = pow(CALIBRATION_BASE * M_E, CALIBRATION_EXP * (double)analogRead(LDR_PIN));

    // Do http POST message
    String body = "key=" + station_api_key + "&temperature=" + String(temperature) + "&humidity=" + String(humidity) + "&light=" + String(light);
    Serial.println("[HTTP] HTTP POST request url: " + api_url + "/measurements, body:" + body);

    HTTPClient http;
    http.begin(api_url + "/measurements");
    http.addHeader("Content-Type", "application/x-www-form-urlencoded");
    int httpCode = http.POST(body);
    String json;
    if (httpCode == HTTP_CODE_OK) {
        json = http.getString();
        Serial.print("[HTTP] Request success, response: ");
        Serial.println(http.getString());
    } else {
        Serial.print("[HTTP] Request failed, error: ");
        Serial.print(http.errorToString(httpCode));
        Serial.print(", response: ");
        Serial.println(http.getString());
    }
    http.end();

    if (httpCode == HTTP_CODE_OK) {
        return json;
    } else {
        return "ERROR";
    }
}

// Read the events and update hardware
void update_events(String json) {
    led_duration = 0;
    uint32_t beeper_duration = 0;
    uint32_t beeper_frequency = 0;

    // Read events and update harware
    StaticJsonDocument<JSON_BUFFER_SIZE> document;
    if (deserializeJson(document, json) == DeserializationError::Ok) {
        JsonArray events = document["events"];
        for (uint8_t i = 0; i < events.size(); i++) {
            JsonObject event = events[i];
            if (event["type"] == EVENT_TYPE_LED) {
                led_duration = event["duration"];
            }

            if (event["type"] == EVENT_TYPE_BEEPER) {
                beeper_duration = event["duration"];
                beeper_frequency = event["frequency"];
            }
        }

        if (led_duration > 0) {
            digitalWrite(LED_PIN, HIGH);
            led_time = millis();
        }

        if (beeper_duration > 0 && beeper_frequency > 0) {
            tone(BEEPER_PIN, beeper_frequency, beeper_duration);
        }
    }
}

// The start of our program
void setup() {
    // Init the serial output
    Serial.begin(9600);

    // Setup pins and DHT sensor
    dht.setup(DHT_PIN, DHTesp::DHT11);
    pinMode(LED_PIN, OUTPUT);
    pinMode(BEEPER_PIN, OUTPUT);
    pinMode(LDR_PIN, INPUT);

    // Setup the EEPROM
    EEPROM.begin(512);

    // Clear EEPROM
    for (int i = 0; i < 512; i++) {
        EEPROM.write(i, 0);
    }

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

    // Send first measurement
    update_events(send_measurement());
}

// The program loop
void loop() {
    // Handle any http clients
    webserver.handleClient();

    // Send a measurement by interval
    if (millis() - send_time >= MEASUREMENT_INTERVAL) {
        send_time = millis();
        update_events(send_measurement());
    }

    // Turn of the led when the duration is over
    if (millis() - led_time >= led_duration) {
        led_time = millis();
        digitalWrite(LED_PIN, LOW);
    }
}
