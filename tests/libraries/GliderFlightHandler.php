<?php

namespace Tests\libraries;

/**
 * This class manages gliderss in the Dusk tet context.
 * 
 * A gliders handler is an object to access gliders data through a browser and a Dusk test context.
 * 
 * The persistence is managed by the WEB application and as we only have access to the WEB interface methods to retreive the information may be indirect.
 */

use Tests\libraries\AccountCodeHandler;
use Illuminate\Support\Facades\Log;


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
        $fdate = new \DateTime($flight->vpdate);
        $date = $fdate->format($dateFormat);
        // echo $date . " " . $gliders_flight['date'] . "\n";

        if ($date != $gliders_flight['date']) {
            // echo "date $date does not match ". $gliders_flight['date'] . "\n";
            return false;
        }
        if ($flight->vpmacid != $gliders_flight['glider']) {
            // echo "glider does not match\n";
            return false;
        }
        if ($flight->vppilid != $gliders_flight['pilot']) {
            // echo "pilot does not match\n";
            return false;
        }
        $start_time = str_replace(".", ":", $flight->vpcdeb);
        if ($start_time != $gliders_flight['start_time']) {
            // echo "start time " . $start_time . " does not match " .  $gliders_flight['start_time'] . "\n";
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
     * Fill the Glider flight form
     * 
     * This method is used to fill the glider flight form with the data in the $flight array.
     */
    public function FillFields($flight) {

        $this->browser->script('document.body.style.zoom = "0.5"');

        if (array_key_exists('date', $flight)) {
            $this->browser->type('vpdate', $flight['date'] . "\n");
        }
        if (array_key_exists('pilot', $flight)) {
            $this->browser->select('vppilid', $flight['pilot']);
        }
        if (array_key_exists('glider', $flight)) {
            $this->browser->select('vpmacid', $flight['glider']);
        }
        if (array_key_exists('start_time', $flight)) {
            $this->browser->type('vpcdeb', $flight['start_time']);
        }
        if (array_key_exists('end_time', $flight)) {
            $this->browser->type('vpcfin', $flight['end_time']);
        }

        if (array_key_exists('DC', $flight) && $flight['DC']) {
            $this->browser->check('vpdc');
        }

        if (array_key_exists('instructor', $flight)) {
            $this->browser->select('vpinst', $flight['instructor']);
        }

        if (array_key_exists('passenger', $flight)) {
            $this->browser->select('vppassager', $flight['passenger']);
        }

        if (array_key_exists('launch', $flight)) {
            switch ($flight['launch']) {
                case 'R':
                    $this->browser->radio('vpautonome', '3');
                    break;
                case 'T':
                    $this->browser->radio('vpautonome', '1');
                    break;
                case 'A':
                    $this->browser->radio('vpautonome', '2');
                    break;
                case 'E':
                    $this->browser->radio('vpautonome', '4');
                    break;
            }
        }

        if (array_key_exists('categorie', $flight)) {
            switch ($flight['categorie']) {
                case 'VI':
                    $this->browser->radio('vpcategorie', '1');
                    break;
                case 'standard':
                    $this->browser->radio('vpcategorie', '0');
                    break;
                case 'VE':
                    $this->browser->radio('vpcategorie', '2');
                    break;
                case 'concours':
                    $this->browser->radio('vpcategorie', '3');
                    break;
            }
        }

        if (array_key_exists('payeur', $flight)) {
            $this->browser->select('payeur', $flight['payeur']);
        }

        if (array_key_exists('vplieudeco', $flight)) {
            $this->browser->select('vplieudeco', $flight['vplieudeco']);
        }

        if (array_key_exists('pourcentage', $flight)) {
            switch ($flight['pourcentage']) {
                case '0':
                    $this->browser->radio('pourcentage', '0');
                    break;
                case '50':
                    $this->browser->radio('pourcentage', '50');
                    break;
                case '100':
                    $this->browser->radio('pourcentage', '100');
                    break;
            }
        }

        if (array_key_exists('tow_pilot', $flight)) {
            $this->browser->radio('vpautonome', '3');
            $this->browser->select('pilote_remorqueur', $flight['tow_pilot']);
        }
        if (array_key_exists('tow_plane', $flight)) {
            $this->browser->radio('vpautonome', '3');
            $this->browser->select('remorqueur', $flight['tow_plane']);
        }

        if (array_key_exists('altitude', $flight)) {
            $this->browser->type('vpaltrem', $flight['altitude']);
        }

        if (array_key_exists('whinch_man', $flight)) {
            $this->browser->radio('vpautonome', '1');
            $this->browser->select('remorqueur', $flight['whinch_man']);
        }

        if (array_key_exists('comment', $flight)) {
            $this->browser->type('vpobs', $flight['comment']);
        }
        $this->browser->script('document.body.style.zoom = "1.0"');
    }

    /** 
     * Create glider flights.
     */
    public function CreateGliderFlights($list = []) {

        $account_handler = new AccountHandler($this->browser, $this->tc);

        foreach ($list as $flight) {

            Log::debug("Creating glider flight : " . var_export($flight, true));

            $flight_number = $this->tc->PageTableRowCount($this->browser, "vols_planeur/page");

            $account_id = $account_handler->AccountIdFromImage($flight['account']);
            $total = $account_handler->AccountTotal($account_id);

            $this->tc->canAccess($this->browser, $flight['url']);

            $this->FillFields($flight);

            $this->browser->screenshot('before_glider_flight');
            $this->browser->script('window.scrollTo(0,document.body.scrollHeight)');
            $this->browser->screenshot('before_glider_flight_after_scrolling');

            $this->browser
                ->scrollIntoView('#validate')
                ->waitFor('#validate')
                ->press('#validate')
                ->assertDontSee('404 Page not found')
                ->assertDontSee('existe pas dans les tarifs');

            $this->browser->screenshot('after_glider_flight');

            if (array_key_exists('error', $flight) && $flight['error']) {
                $error = $flight['error'];
                $flight_exists = false;
                $created = 0;
                $price = 0.0;
            } else {
                $flight_exists = true;
                $error = false;
                $created = 1;
                $price = $flight['price'];
            }

            // In case of duplicate the flight exists before the test
            // so it is not pertinent to check its existence
            if (!$error) {
                Log::debug("glider flight creation with no error, check that the flight exist ");
                $this->tc->assertEquals(
                    $flight_exists,
                    $this->GliderFlightExists($flight),
                    "glider flight exists: " . $flight['glider']
                );
            }

            $new_flight_number = $this->tc->PageTableRowCount($this->browser, "vols_planeur/page");

            $this->tc->assertEquals($flight_number + $created, $new_flight_number, "Flight number = " . $new_flight_number);
            Log::debug("glider flight creation with no error, check that there is one addition flight inthe list ");

            if (array_key_exists('price', $flight)) {
                $new_total = $account_handler->AccountTotal($account_id);
                $cost = $total - $new_total;

                Log::debug("glider flight creation check that the pilot account has been modified ");
                $epsilon = 0.000001;
                $this->tc->assertEqualsWithDelta($price, $cost, $epsilon, "Flight cost $cost = expected $price");
            }
        }
    }

    /**
     * Update a glider flight   
     */
    public function UpdateGliderFLight($flight) {
        $id = $flight['vpid'];
        $url = "vols_planeur/edit/$id";
        $this->tc->canAccess($this->browser, $url);

        $this->FillFields($flight);

        $this->browser->script('window.scrollTo(0,document.body.scrollHeight)');

        sleep(1);

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

    public function count() {
        $url = $this->tc->fullUrl('api/vols_planeur/count');

        $json = file_get_contents($url);
        $obj = json_decode($json);
        if ($obj) return $obj->count;
        return -1;
    }
}
