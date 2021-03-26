#include "storage.hpp"
#include <EEPROM.h>
#include <ArduinoJson.h>
#include "config.hpp"

// Load config from EEPROM storage
void load_config() {
    // Read the contents of the EEPROM to a buffer
    char buffer[STRING_BUFFER_SIZE];
    char character;
    for (int i = 0; (character = EEPROM.read(i)); i++) {
        buffer[i] = character;
    }
    Serial.print("\n[EEPROM] Read from EEPROM: ");
    Serial.println(buffer);

    // Try to parse and read the JSON config
    if (buffer[0] == '{') {
        StaticJsonDocument<JSON_BUFFER_SIZE> document;
        if (deserializeJson(document, buffer) == DeserializationError::Ok) {
            station_api_key = String((const char*)document["station_api_key"]);
            local_wifi_ssid = String((const char *)document["local_wifi_ssid"]);
            local_wifi_password = String((const char *)document["local_wifi_password"]);
            wifi_ssid = String((const char *)document["wifi_ssid"]);
            wifi_password = String((const char *)document["wifi_password"]);
        }
    }
}

// Save config to EEPROM storage
void save_config() {
    // Stringify the default values JSON to the buffer
    char buffer[STRING_BUFFER_SIZE];
    StaticJsonDocument<JSON_BUFFER_SIZE> document;
    document["station_api_key"] = station_api_key;
    document["local_wifi_ssid"] = local_wifi_ssid;
    document["local_wifi_password"] = local_wifi_password;
    document["wifi_ssid"] = wifi_ssid;
    document["wifi_password"] = wifi_password;
    serializeJson(document, buffer);

    // Write the buffer to the EEPROM
    for (int i = 0; buffer[i]; i++) {
        EEPROM.write(i, buffer[i]);
    }
    EEPROM.commit();
    Serial.print("[EEPROM] Write to EEPROM: ");
    Serial.println(buffer);
}
