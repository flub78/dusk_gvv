<?php

namespace Tests\libraries;

/**
 * This class manages planes in the Dusk tet context.
 * 
 * A plane handler is an object to access plane data through a browser and a Dusk test context.
 * 
 * The persistence is managed by the WEB application and as we only have access to the WEB interface methods to retreive the information may be indirect.
 */

class PlaneFlightHandler {

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
     * $plane_flight is an array with the description of the flight
     */
    public function match($flight, $plane_flight) {
        $dateFormat = "d/m/Y";
        $fdate = new \DateTime($flight->vadate);
        $date = $fdate->format($dateFormat);
        // echo $date . " " . $plane_flight['date'] . "\n";
        // var_dump($flight);
        if ($date != $plane_flight['date']) {
            // echo "date $date does not match ". $plane_flight['date'] . "\n";
            return false;
        }
        if ($flight->vamacid != $plane_flight['plane']) {
            // echo "plane does not match\n";
            return false;
        }
        if ($flight->vapilid != $plane_flight['pilot']) {
            // echo "pilot does not match\n";
            return false;
        }
        if ($flight->vacdeb != $plane_flight['start_meter']) {
            // echo "start meter " . $flight->vacdeb . " does not match " .  $plane_flight['start_meter'] . "\n";
            return false;
        }
        return true;
    }


    /** 
     * Check that a plane flight exists.
     * 
     * As plane IDs are not public (they are generated by the database), we check that the plane image is present in a select
     * Maybe that I should rather rely on the test API ?
     */
    public function PlaneFlightExists($plane_flight) {

        $flights = $this->allFlights();
        if (!$flights) return false;
        foreach ($flights as $flight) {
            if ($this->match($flight, $plane_flight)) {
                return true;
            }
        }
        return false;
    }

    /** 
     * Fill the plane flight form
     * 
     * This method is used to fill the plane flight form with the data in the $flight array.
     */
    public function FillFields($flight) {

        if (array_key_exists('date', $flight)) {
            $this->browser->type('vadate', $flight['date'] . "\n");
        }

        if (array_key_exists('pilot', $flight)) {
            $this->browser->select('vapilid', $flight['pilot']);
        }

        if (array_key_exists('plane', $flight)) {
            $this->browser->select('vamacid', $flight['plane']);
        }

        if (array_key_exists('start_time', $flight)) {
            $this->browser->type('vahdeb', $flight['start_time']);
        }

        if (array_key_exists('end_time', $flight)) {
            $this->browser->type('vahfin', $flight['end_time']);
        }

        if (array_key_exists('start_meter', $flight)) {
            $this->browser->type('vacdeb', $flight['start_meter']);
        }

        if (array_key_exists('end_meter', $flight)) {
            $this->browser->type('vacfin', $flight['end_meter']);
        }

        if (array_key_exists('categorie', $flight)) {
            switch ($flight['categorie']) {
                case 'VI':
                    $this->browser->radio('vacategorie', '1');
                    break;
                case 'standard':
                    $this->browser->radio('vacategorie', '0');
                    break;
                case 'VE':
                    $this->browser->radio('vacategorie', '2');
                    break;
                case 'remorquage':
                    $this->browser->radio('vacategorie', '3');
                    break;
            }
        }

        if (array_key_exists('comment', $flight)) {
            $this->browser->type('vaobs', $flight['comment']);
        }
    }

    /** 
     * Create plane flights
     */
    public function CreatePlaneFlights($list = []) {
        foreach ($list as $flight) {

            // var_dump($flight);

            $flight_number = $total = $this->tc->PageTableRowCount($this->browser, "vols_avion/page");

            $this->tc->canAccess($this->browser, $flight['url']);

            $this->FillFields($flight);

            $this->browser->script('window.scrollTo(0,document.body.scrollHeight)');
            $this->browser->screenshot('a_before_flight');

            $this->browser
                ->scrollIntoView('#validate')
                ->waitFor('#validate')
                ->press('#validate')
                ->assertDontSee('404 Page not found')
                ->assertDontSee('existe pas dans les tarifs');

            $this->browser->screenshot('after_flight');

            $this->tc->assertTrue(
                $this->PlaneFlightExists($flight),
                "plane flight exists: " . $flight['image']
            );

            $new_flight_number = $total = $this->tc->PageTableRowCount($this->browser, "vols_avion/page");

            $this->tc->assertEquals($flight_number + 1, $new_flight_number, "Flight number = " . $new_flight_number);
        }
    }

    /** 
     * Update plane flight
     */
    public function UpdatePlaneFlight($flight) {
        $id = $flight['vaid'];
        $url = "vols_avion/edit/$id";
        $this->tc->canAccess($this->browser, $url);

        $this->FillFields($flight);

        $this->browser->script('window.scrollTo(0,document.body.scrollHeight)');

        $this->browser->scrollIntoView('#validate')
            ->waitFor('#validate');

        $this->browser
            ->scrollIntoView('#validate')
            ->waitFor('#validate')
            ->press('#validate')
            ->assertDontSee('404 Page not found');
    }

    /**
     * Get the latest flight in the database
     */
    public function latestFlight() {

        $url = $this->tc->fullUrl('api/vols_avion/ajax_latest');
        $json = file_get_contents($url);
        $obj = json_decode($json);
        if ($obj) return $obj[0];
        return null;
    }

    /**
     * Return the flights at a given date
     */
    public function allFlights($date = "") {

        $url = $this->tc->fullUrl('api/vols_avion/get');

        $json = file_get_contents($url);
        $obj = json_decode($json);
        if ($obj) return $obj;
        return null;
    }

    public function count() {
        $url = $this->tc->fullUrl('api/vols_avion/count');

        $json = file_get_contents($url);
        $obj = json_decode($json);
        if ($obj) return $obj->count;
        return -1;
    }
}
