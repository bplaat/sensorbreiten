<?php

class StationsController {
    // Stations index route
    public static function index() {
        // Get all stations
        $stations = Stations::all();

        // Get stations info
        $stationsInfo = [];
        foreach ($stations as $station) {
            $stationInfo = [
                'id' => $station->id,
                'name' => $station->name,
                'point' => [ $station->latitude, $station->longitude ]
            ];

            $latestMeasurementQuery = Stations::latestMeasurement($station);
            if ($latestMeasurementQuery->rowCount() == 1) {
                $latestMeasurement = $latestMeasurementQuery->fetch();
                $stationInfo['temperature'] = $latestMeasurement->temperature;
                $stationInfo['humidity'] = $latestMeasurement->humidity;
                $stationInfo['light'] = $latestMeasurement->light;
            }

            $latestOutsideMeasurementQuery = Stations::latestOutsideMeasurement($station->id);
            if ($latestOutsideMeasurementQuery->rowCount() == 1) {
                $latestOutsideMeasurement = $latestOutsideMeasurementQuery->fetch();
                $stationInfo['outside_temperature'] = $latestOutsideMeasurement->temperature;
                $stationInfo['outside_humidity'] = $latestOutsideMeasurement->humidity;
            }

            $stationsInfo[] = $stationInfo;
        }

        // Return stations index view
        return view('stations.index', [ 'stations' => $stations, 'stationsInfo' => $stationsInfo ]);
    }

    // Stations store route
    public static function store() {
        // Validate input
        $fields = validate([
            'name' => 'required|min:2|max:48',
            'latitude' => 'required|float',
            'longitude' => 'required|float'
        ]);

        // Create new station
        $station = Stations::create([
            'name' => $fields['name'],
            'key' => Stations::generateKey(),
            'latitude' => $fields['latitude'],
            'longitude' => $fields['longitude']
        ]);

        // Redirect to the new station show page
        return Redirect::route('stations.show', $station);
    }

    // Stations show route
    public static function show ($station) {
        if (request('day') != null) {
            $fields = validate([
                'day' => 'date:'
            ]);

            $day = strtotime($fields['day']);
        } else {
            $day = strtotime(date('Y-m-d'));
        }

        $meassurements = Stations::measurementsByDay($station, $day)->fetchAll();
        $labels = [];
        $temperatureData = [];
        $humidityData = [];
        $lightData = [];
        foreach ($meassurements as $meassurement) {
            $labels[] = date('H:i', strtotime($meassurement->created_at));
            $temperatureData[] = $meassurement->temperature;
            $humidityData[] = $meassurement->humidity;
            $lightData[] = $meassurement->light;
        }

        $outsideMeassurements = Stations::outsideMeasurementsByDay($station, $day)->fetchAll();
        $outsideLabels = [];
        $outsideTemperatureData = [];
        $outsideHumidityData = [];
        foreach ($outsideMeassurements as $outsideMeassurement) {
            $outsideLabels[] = date('H:i', strtotime($outsideMeassurement->created_at));
            $outsideTemperatureData[] = $outsideMeassurement->temperature;
            $outsideHumidityData[] = $outsideMeassurement->humidity;
        }

        return view('stations.show', [
            'station' => $station,
            'day' => $day,

            'labels' => $labels,
            'temperatureData' => $temperatureData,
            'humidityData' => $humidityData,
            'lightData' => $lightData,

            'outsideLabels' => $outsideLabels,
            'outsideTemperatureData' => $outsideTemperatureData,
            'outsideHumidityData' => $outsideHumidityData
        ]);
    }

    // Stations edit route
    public static function edit($station) {
        return view('stations.edit', [ 'station' => $station ]);
    }

    // Stations update route
    public static function update($station) {
        // Validate input
        $fields = validate([
            'name' => 'required|min:2|max:48',
            'latitude' => 'required|float',
            'longitude' => 'required|float'
        ]);

        // Update station
        Stations::update($station, [
            'name' => $fields['name'],
            'latitude' => $fields['latitude'],
            'longitude' => $fields['longitude']
        ]);

        // Redirect to the station show page
        return Redirect::route('stations.show', $station);
    }

    // Stations delete route
    public static function delete($station) {
        // Delete the station
        Stations::delete($station);

        // Redirect ot the stations index route
        return Redirect::route('stations.index');
    }
}
