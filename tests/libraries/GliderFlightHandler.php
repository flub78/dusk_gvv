<?php

namespace Tests\libraries;

/**
 * This class manages gliderss in the Dusk tet context.
 * 
 * A gliders handler is an object to access gliders data through a browser and a Dusk test context.
 * 
 * The persistence is managed by the WEB application and as we only have access to the WEB interface methods to retreive the information may be indirect.
 */

class GliderFlightHandler {

    private $browser;
    private $tc;

    /** Constructor */
    function __construct($browser, $test_context) {
        $this->browser = $browser;
        $this->tc = $test_context;
    }


    /**
     * Check if a flight return by the REST API match a description in an array
     * $flight is the flight returned by the REST API
     * $gliders_flight is an array with the description of the flight
     */
    public function match($flight, $gliders_flight) {
        $dateFormat = "d/m/Y";
        $fdate = new \DateTime($flight->vadate);
        $date = $fdate->format($dateFormat);
        // echo $date . " " . $gliders_flight['date'] . "\n";
        // var_dump($flight);
        if ($date != $gliders_flight['date']) {
            echo "date $date does not match ". $gliders_flight['date'] . "\n";
            return false;
        }
        if ($flight->vamacid != $gliders_flight['glider']) {
            echo "glider does not match\n";
            return false;
        }
        if ($flight->vapilid != $gliders_flight['pilot']) {
            echo "pilot does not match\n";
            return false;
        }
        if ($flight->vacdeb != $gliders_flight['start_meter']) {
            echo "start meter " . $flight->vacdeb . " does not match " .  $gliders_flight['start_meter'] . "\n";
            return false;
        }
        return true;
    }   


    /** 
     * Check that a gliders exists.
     * 
     * This version uses a REST API to access the gliders data.
     */
    public function GliderFlightExists($glider_flight) {

        $flights = $this->allFlights();
        foreach ($flights as $flight) {
            if ($this->match($flight, $glider_flight)) {
                return true;
            }
        }
        return false;
    }

    /** 
     * Create glider flights.
     */
    public function CreateGliderFlights($list = []) {
        foreach ($list as $flight) {

            $flight_number = $total = $this->tc->TableTotal($this->browser, "vols_planeur/page");

            $this->tc->canAccess($this->browser, $flight['url']);

            $this->browser
                ->type('vadate', $flight['date'])
                ->select('vapilid', $flight['pilot'])
                ->select('vamacid', $flight['glider'])
                ->type('vahdeb', $flight['start_time'])
                ->type('vahfin', $flight['end_time'])
                ->type('vacdeb', $flight['start_meter'])
                ->type('vacfin', $flight['end_meter']);

            $this->browser->screenshot('before_glider_flight');

            $this->browser
                ->press('#validate')
                ->assertDontSee('404');

            $this->browser->screenshot('after_glider_flight');

            $this->tc->assertTrue(
                $this->GliderFlightExists($flight),
                "glider flight exists: " . $flight['glider']
            );

            $new_flight_number = $total = $this->tc->TableTotal($this->browser, "vols_planeur/page");
            
            $this->tc->assertEquals($flight_number + 1, $new_flight_number, "Flight number = " . $new_flight_number);
        }
    }

    /**
     * Get the latest flight in the database
     */
    public function latestFlight() {

        $url = $this->tc->fullUrl('api/vols_planeur/ajax_latest');
        $json = file_get_contents($url);
        $obj = json_decode($json);
        if ($obj) return $obj[0];
        return null;
    }

    /**
     * Return the flights at a given date
     */
    public function allFlights($date = "") {

        $url = $this->tc->fullUrl('api/vols_planeur/get');

        $json = file_get_contents($url);
        $obj = json_decode($json);
        if ($obj) return $obj;
        return null;
    }
}
