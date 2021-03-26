<?php

class EventsController {
    // Events index route
    public static function index() {
        // Select all events and its stations
        $events = Events::all();
        foreach ($events as $event) {
            $event->station = Stations::first($event->station_id);
        }

        // Return the events index view
        return view('events.index', [ 'events' => $events ]);
    }

    // Events create route
    public static function create() {
        // Select all the stations
        $stations = Stations::all();

        // Return the events create view
        return view('events.create', [ 'stations' => $stations ]);
    }

    // Events store route
    public static function store() {
        // Validate input
        $fields = validate([
            'station_id' => 'required|int|exists:Stations,id',
            'name' => 'required|min:2|max:48',
            'trigger' => [ 'required', 'Events::validateTrigger' ],
            'type' => 'required|int|number_between:' . Events::TYPE_LED . ',' . Events::TYPE_BEEPER,
            'frequency' => 'required|int',
            'duration' => 'required|int',
        ]);

        // Create new event
        $event = Events::create([
            'station_id' => $fields['station_id'],
            'name' => $fields['name'],
            'trigger' => $fields['trigger'],
            'type' => $fields['type'],
            'frequency' => $fields['frequency'],
            'duration' => $fields['duration']
        ]);

        // Redirect to the new event show page
        return Redirect::route('events.show', $event);
    }

    // Events show route
    public static function show($event) {
        // Select event station
        $event->station = Stations::first($event->station_id);

        // Return event show view
        return view('events.show', [ 'event' => $event ]);
    }

    // Events edit route
    public static function edit($event) {
        // Select all stations
        $stations = Stations::all();

        // Return event edit view
        return view('events.edit', [ 'event' => $event, 'stations' => $stations ]);
    }

    // Events update route
    public static function update($event) {
        // Validate input
        $fields = validate([
            'station_id' => 'required|int|exists:Stations,id',
            'name' => 'required|min:2|max:48',
            'trigger' => [ 'required', 'Events::validateTrigger' ],
            'type' => 'required|int|number_between:' . Events::TYPE_LED . ',' . Events::TYPE_BEEPER,
            'frequency' => 'required|int',
            'duration' => 'required|int',
        ]);

        // Update event
        Events::update($event, [
            'station_id' => $fields['station_id'],
            'name' => $fields['name'],
            'trigger' => $fields['trigger'],
            'type' => $fields['type'],
            'frequency' => $fields['frequency'],
            'duration' => $fields['duration'],
            'active' => 0
        ]);

        // Redirect to the event show page
        return Redirect::route('events.show', $event);
    }

    // Events delete route
    public static function delete($event) {
        // Delete the event
        Events::delete($event);

        // Redirect to the events index route
        return Redirect::route('events.index');
    }
}
