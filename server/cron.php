<?php

// Load needed modules
define('ROOT', dirname(__FILE__));
require_once ROOT . '/core/autoloader.php';
require_once ROOT . '/core/config.php';

// Get the outside measurement of all stations
if (config('openweathermap.api_key') != '') {
    $stations = Stations::all();
    foreach ($stations as $station) {
        $data = json_decode(file_get_contents('https://api.openweathermap.org/data/2.5/weather?appid=' . config('openweathermap.api_key') . '&lat=' . $station->latitude . '&lon=' . $station->longitude . '&units=metric'));
        OutsideMeasurements::create([
            'station_id' => $station->id,
            'temperature' => $data->main->temp,
            'humidity' => $data->main->humidity
        ]);
    }
}
