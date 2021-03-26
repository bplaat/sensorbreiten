<?php

class ApiMeasurementsController {
    // Api measurements store route
    public static function store() {
        // Validate input
        $fields = validate([
            'key' => 'required|size:32|exists:stations',
            'temperature' => 'required|float',
            'humidity' => 'required|float',
            'light' => 'required|float',
        ]);

        // Select station
        $station = Stations::first([ 'key' => $fields['key'] ]);

        // Create measurement
        $measurement = Measurements::create([
            'station_id' => $station->id,
            'temperature' => $fields['temperature'],
            'humidity' => $fields['humidity'],
            'light' => $fields['light']
        ]);

        // Update events
        $sendEvents = [];

        $events = Events::all([ 'station_id' => $station->id ]);
        foreach ($events as $event) {
            $latestOutsideMeasurementQuery = Stations::latestOutsideMeasurement($station);
            if ($latestOutsideMeasurementQuery->rowCount() == 1) {
                $latestOutsideMeasurement = $latestOutsideMeasurementQuery->fetch();

                if (static::runTrigger($event->trigger, time(), time() - strtotime('today'), $measurement->temperature, $measurement->humidity, $measurement->light, $latestOutsideMeasurement->temperature, $latestOutsideMeasurement->humidity)) {
                    Events::update($event, [ 'active' => true ]);

                    if ($event->type == Events::TYPE_LED) {
                        $sendEvents[] = [ 'type' => Events::TYPE_LED, 'duration' => $event->duration ];
                    }

                    if ($event->type == Events::TYPE_BEEPER) {
                        $sendEvents[] = [ 'type' => Events::TYPE_BEEPER, 'frequency' => $event->frequency, 'duration' => $event->duration ];
                    }
                } else {
                    Events::update($event->id, [ 'active' => false ]);
                }
            }
        }

        // Send response with triggered events
        return [ 'events' => $sendEvents ];
    }

    // Api measurements store route
    public static function runTrigger($_trigger, $absolute_time, $time, $temperature, $humidity, $light, $outside_temperature, $outside_humidity) {
        return eval('unset($_trigger); return ' . $_trigger . ';');
    }
}
