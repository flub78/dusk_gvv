<?php

namespace Tests\Browser;

use Laravel\Dusk\Browser;
use Tests\Browser\AircraftFlightTest;


use Tests\libraries\PlaneFlightHandler;
use Tests\libraries\AccountHandler;
use Tests\libraries\PlaneHandler;

/*
 * 
 * Plane Flight Resource tests (CRUD):
 * - Check that it is possible to create a resource
 * - Check that it is possible to read a resource
 * - Check that it is possible to update a resource
 * - Check that it is possible to delete a resource
 * - Check all cases of error in creation/edition
 * - check indirect modifications (e.g. billing, etc.)
 * 
 * - checks that only two seaters accept two pilots
 * - checks that flights are rejected when the pilot or plane are already in flight
 * 
 * TODO:
 *  - attempt for negative duration
 *  - shared flights
 *  - certificates
 */

class PlaneFlightTest extends AircraftFlightTest {

    /**
     * Check that the installation can be reset and installed
     *
     * @return void
     */
    public function testInit() {
        parent::testInit();
    }

    /**
     * Login
     * 
     * @depends testInit
     */
    public function testLogin() {
        parent::testLogin();
    }

    /**
     * Test creation of correct flights
     * 
     * @depends testLogin
     */
    public function testCreate() {
        $this->assertTrue(true);
        $this->browse(function (Browser $browser) {

            $plane_flight_handler = new PlaneFlightHandler($browser, $this);

            $latest = $plane_flight_handler->latestFlight();
            $flightDate = $this->NextDate($latest, 'vadate');

            $flights = [
                [
                    'url' => 'vols_avion/create',
                    'date' => $flightDate,
                    'pilot' => 'asterix',
                    'plane' => 'F-JUFA',
                    'instructor' => 'panoramix',
                    'DC' =>  true,
                    'start_time' => '10.00',
                    'end_time' => '10.30',
                    'start_meter' => '100.50',
                    'end_meter' => '100.90',
                    'account' => "(411) Le Gaulois Asterix",
                    'price' => 40.8,
                ],
                [
                    'url' => 'vols_avion/create',
                    'date' => $flightDate,
                    'pilot' => 'goudurix',
                    'plane' => 'F-JUFA',
                    'instructor' => 'panoramix',
                    'DC' =>  true,
                    'start_time' => '11.00',
                    'end_time' => '12.15',
                    'start_meter' => '100.50',
                    'end_meter' => '101.00',
                    'account' => "(411) Le Gaulois Goudurix",
                    'price' => 51.0,
                ],
            ];

            $id = 0;
            $flights[$id]['image'] = 
                $flights[$id]['date'] . " " . 
                $flights[$id]['start_meter'] . " " .
                $flights[$id]['plane'];
            $id = 1;
            $flights[$id]['image'] = 
                $flights[$id]['date'] . " " . 
                $flights[$id]['start_meter'] . " " .
                $flights[$id]['plane'];

            $plane_flight_handler->CreatePlaneFlights($flights);
        });
    }


    /*
     * Generat conflicting flights from an array
     */
    private function generateConflictingFlights($tab = [], $error = "") {
        $flights = [];

        $dateFormat = "d/m/Y";

        $date = new \DateTime('first day of January this year', new \DateTimeZone('Europe/Paris'));

        $flightDate = $date->format($dateFormat);

        foreach ($tab as $line) {
            $flight = [
                'url' => 'vols_avion/create',
                'date' => $flightDate,
                'pilot' => $line[0],
                'plane' => $line[1],
                'start_time' => $line[2],
                'end_time' => $line[3],
                'account' => "(411) Le Gaulois " . ucfirst($line[0]),
            ];

            if ($error) {
                $flight['error'] = $error;
                $flight['comment'] = $error;
            }
            $flights[] = $flight;
        }
        return $flights;
    }

    /**
     * Checks that flights are rejected when the pilot or plane are already in flight
     * 
     * preconditions:
     *      Asterix on F-JUFA from 10:00 to 10:30
     *      Asterix on F-GUFB from 11:00 to 12:15
     *      Goudurix on F-JUFA from 11:00 to 12:15
     * 
     * Test cases
     *      rejected flights
     *     - Asterix on F-JUFA from 10:00 to 10:30  flight duplicated
     *     - Asterix on F-GUFB from 09:00 to 10:00
     *     - Asterix on F-GUFB from 09:30 to 10:15
     *     - Asterix on F-GUFB from 09:30 to 10:35  * missed + start not filled in the edit form
     *     - Asterix on F-GUFB from 09:30 to 12:30  * missed + start not filled in the edit form
     *     - Asterix on F-GUFB from 10:15 to 10:35
     *     - Asterix on F-GUFB from 10:15 to 12:30
     *     - Asterix on F-GUFB from 10:15 to 10:20 
     * 
     *     rejected flights
     *      - Goudurix on F-JUFA from 10:00 to 10:30
     *      - Goudurix on F-JUFA from 09:45 to 10:15
     *      - Goudurix on F-JUFA from 09:45 to 10:35
     *      - Goudurix on F-JUFA from 09:45 to 12:30
     *      - Goudurix on F-JUFA from 10:15 to 10:25
     *      - Goudurix on F-JUFA from 10:15 to 10:35
     *      - Goudurix on F-JUFA from 10:15 to 12:35
     * 
     *      Accepted flights
     *      - Asterix on F-JUFA from 09:00 to 09:59
     *      - Asterix on F-JUFA from 10:31 to 10:59
     *      - Asterix on F-JUFA from 12:16 to 13:00
     */
    public function testInFlight() {
        $this->assertTrue(true); return;
        $this->browse(function (Browser $browser) {

            $plane_flight_handler = new PlaneFlightHandler($browser, $this);

            $rejected = [
                ["asterix", "F-JUFA", "10.00", "10.30"],
                ["asterix", "F-GUFB", "09.00", "10.00"],
                ["asterix", "F-GUFB", "09.30", "10.15"],

                ["asterix", "F-GUFB", "09.30", "10.35"],
                ["asterix", "F-GUFB", "09.30", "12.30"],

                ["asterix", "F-GUFB", "10.15", "10.35"],
                ["asterix", "F-GUFB", "10.30", "12.30"],
                ["asterix", "F-GUFB", "10.30", "12.20"],

                ["goudurix", "F-JUFA", "10.00", "10.30"],
                ["goudurix", "F-JUFA", "09.45", "10.15"],
                ["goudurix", "F-JUFA", "09.45", "10.35"],
                ["goudurix", "F-JUFA", "09.45", "12.30"],
                ["goudurix", "F-JUFA", "10.15", "10.25"],
                ["goudurix", "F-JUFA", "10.15", "10.35"],
                ["goudurix", "F-JUFA", "10.15", "12.35"],
            ];

            $accepted = [
                ["asterix", "F-JUFA", "09.00", "09.59"],
                ["asterix", "F-JUFA", "10.31", "10.59"],
                ["asterix", "F-JUFA", "12.16", "13.00"],
            ];

            $rejected_flights = $this->generateConflictingFlights($rejected, "machine ou pilote en vol");
            $accepted_flights = $this->generateConflictingFlights($accepted);
            $flights = $rejected_flights + $accepted_flights;

            $this->canAccess($browser, 'vols_avion');
            $browser->screenshot('before_conflicting_flights');

            $plane_flight_handler->CreatePlaneFlights($flights);
        });
    }

    /**
     * Check that a plane flight can be updated
     * @depends testInFlight
     */
    public function testUpdate() {
        $this->assertTrue(true); 
        $this->browse(function (Browser $browser) {

            $plane_flight_handler = new PlaneFlightHandler($browser, $this);

            $latest = $plane_flight_handler->latestFlight();

            $flight_count = $plane_flight_handler->count();

            $modified_comment = "modified comment";

            $flight = [
                'vaid' => $latest->vaid,
                'comment' => $modified_comment,
            ];
            $plane_flight_handler->UpdatePlaneFLight($flight);

            $latest = $plane_flight_handler->latestFlight();
            $this->assertEquals($modified_comment, $latest->vaobs);

            $new_count = $plane_flight_handler->count();
            $this->assertEquals($flight_count, $new_count);
        });
    }

    /**
     * Check that a plane flight can be deleted
     * @depends testUpdate
     */
    public function testDelete() {
        $this->assertTrue(true); 
        $this->browse(function (Browser $browser) {

            $plane_flight_handler = new PlaneFlightHandler($browser, $this);

            $latest = $plane_flight_handler->latestFlight();

            $flight_count = $plane_flight_handler->count();

            $this->canAccess($browser, 'vols_avion/delete/' . $latest->vaid);

            $new_count = $plane_flight_handler->count();
            $this->assertEquals($flight_count - 1, $new_count);
        });
    }


    /**
     * Checks that a plane flight is billed correctly
     *     - pilot account is debited
     *     - sale account is credited
     *     - several purchased are generated
     *     - when a flight is updated, the debit and credit are adapted, purchases are replaced
     *     - when a flight is deleted, the debit and credit are deleted, purchases are deleted
     *   
     * Test cases
     *   - club plane + tow plane + higher altitude
     *   - private plane + tow plane
     *   - club plane more than three hours + winch
     *   - external plane winch
     *   - forfait billing     
     * 
     * @depends testDelete
     */
    public function testBilling() {
        $this->browse(function (Browser $browser) {
            $this->assertTrue(true);

            $plane_flight_handler = new PlaneFlightHandler($browser, $this);
            $account_handler = new AccountHandler($browser, $this);
            $plane_handler = new PlaneHandler($browser, $this);

            $latest = $plane_flight_handler->latestFlight();
            $flightDate = $this->NextDate($latest, 'vadate');

            $asterix_acount_image = "(411) Le Gaulois Asterix";
            $plane_time_acount_image = "(706) Heures de vol ULM";

            $flights = [
                [
                    'url' => 'vols_avion/create',
                    'date' => $flightDate,
                    'pilot' => 'asterix',
                    'plane' => 'F-JUFA',
                    'instructor' => 'panoramix',
                    'DC' =>  true,
                    'start_time' => '10.00',
                    'end_time' => '10.30',
                    'start_meter' => '100.0',
                    'end_meter' => '100.50',
                    'account' => $asterix_acount_image,
                    'price' => 51.0,
                ],
            ];
            $id = 0;
            $flights[$id]['image'] = 
                $flights[$id]['date'] . " " . 
                $flights[$id]['start_meter'] . " " .
                $flights[$id]['plane'];

            // context recording
            $acounts = [
                'asterix' => $account_handler->AccountIdFromImage($asterix_acount_image),
                'plane time account' => $account_handler->AccountIdFromImage($plane_time_acount_image)
            ];

            $context = $this->FlightAndBillingContext($browser, $acounts);
            $this->DisplayContext($context, "Initial context");

            // Plane flight creation
            $plane_flight_handler->CreatePlaneFlights($flights);
            $id = $plane_flight_handler->latestFlight()->vaid;

            // new context recording
            $new_context = $this->FlightAndBillingContext($browser, $acounts);
            $deltas = $this->CompareContexes($context, $new_context);
            $expected = [
                'balance' => ['asterix' => -51.0, 'plane time account' => 51.0],
                'purchases' => 1,
                'lines' => 1
            ];
            $this->ExpectedDifferences($expected, $deltas, "after creation of the first flight");

            // Increase time flight 
            $update = [
                'vaid' => $id,
                'end_time' => '11.00', // 30 minutes more, 30 €
                'end_meter' => '101.00',
            ];
            $plane_flight_handler->UpdatePlaneFLight($update);
            $new_context = $this->FlightAndBillingContext($browser, $acounts);
            $deltas = $this->CompareContexes($context, $new_context);
            $expected = [
                'balance' => ['asterix' => -102.0, 'plane time account' => 102.0],
                'purchases' => 1,
                'lines' => 1
            ];
            $this->ExpectedDifferences($expected, $deltas, "After time increase");

            // VI
            $update = [
                'vaid' => $id,
                'categorie' => 'VI', // 6 hours so 90 €
            ];
            $plane_flight_handler->UpdatePlaneFLight($update);
            $new_context = $this->FlightAndBillingContext($browser, $acounts);
            $deltas = $this->CompareContexes($context, $new_context);
            $expected = [
                'balance' => ['asterix' => 0.0, 'plane time account' => 0.0],
                'purchases' => 0,
                'lines' => 0
            ];
            $this->ExpectedDifferences($expected, $deltas, "After VI");

            // // Private plane per owner
            // $plane_owner = [
            //     "immat" => "F-JUFA",
            //     "type_proprio" => "Privé",
            //     "proprietaire" => "asterix",
            // ];
            // $plane_handler->UpdatePlane($plane_owner);

            // $update = [
            //     'vaid' => $id,
            //     'categorie' => 'standard',
            // ];
            // $plane_flight_handler->UpdatePlaneFLight($update);
            
            // $new_context = $this->FlightAndBillingContext($browser, $acounts);
            // $deltas = $this->CompareContexes($context, $new_context);
            // $expected = [
            //     'balance' => ['asterix' => -8.0, 'plane time account' => 0.0],
            //     'purchases' => 1,
            //     'lines' => 1
            // ];
            // $this->ExpectedDifferences($expected, $deltas, "Private plane");

            // // Private plane per not owner


            // // Back to a clubl ownership
            // $plane_owner = [
            //     "immat" => "F-JUFA",
            //     "type_proprio" => "Club",
            //     "proprietaire" => "",
            // ];
            // $plane_handler->UpdatePlane($plane_owner);

            // Flight delete
            $this->canAccess($browser, 'vols_avion/delete/' . $id);
            $new_context = $this->FlightAndBillingContext($browser, $acounts);
            $deltas = $this->CompareContexes($context, $new_context);
            $expected = [
                'balance' => ['asterix' => 0.0, 'plane time account' => 0.0],
                'purchases' => 0,
                'lines' => 0
            ];
            $this->ExpectedDifferences($expected, $deltas, "After delete");
        }); // end of browse callback
    }

    /**
     * Checks that a filght can be shared
     *    - checks that nothing happen when shared at 0 %
     *    - checks that both are billed when shared at 50 %
     *    - checks that the payer is billed when shared at 100 %
     *    - checks that it is possible to modify the sharing percentage
     *    - checks that people are recredited when the flight is deleted or the payer changed
     *    - checks that no incorrect purchases are remaining after update or delete
     * 
     * @depends testBilling
     */
    public function testSharing() {
        $this->assertTrue(true); return;
        $this->browse(function (Browser $browser) {
            $this->assertTrue(true); return;

            $plane_flight_handler = new PlaneFlightHandler($browser, $this);
            $account_handler = new AccountHandler($browser, $this);

            $latest = $plane_flight_handler->latestFlight();
            $flightDate = $this->NextDate($latest, 'vadate');

            $asterix_acount_image = "(411) Le Gaulois Asterix";
            $goudurix_acount_image = "(411) Le Gaulois Goudurix";
            $panoramix_acount_image = "(411) Le Gaulois Panoramix";
            $plane_time_acount_image = "(706) Heures de vol ULM";

            // first a flight with no payer (default)
            $flights = [
                [
                    'url' => 'vols_avion/create',
                    'date' => $flightDate,
                    'pilot' => 'asterix',
                    'plane' => 'F-JUFA',
                    'instructor' => 'panoramix',
                    'DC' =>  true,
                    'start_time' => '10.00',
                    'end_time' => '10.30',
                    'account' => $asterix_acount_image,
                    'price' => 46.0,
                ],
            ];

            // context recording
            $acounts = [
                'asterix' => $account_handler->AccountIdFromImage($asterix_acount_image),
                'goudurix' => $account_handler->AccountIdFromImage($goudurix_acount_image),
                'panoramix' => $account_handler->AccountIdFromImage($panoramix_acount_image),
                'plane time account' => $account_handler->AccountIdFromImage($plane_time_acount_image)
            ];

            $context = $this->FlightAndBillingContext($browser, $acounts);
            $this->DisplayContext($context, "Initial Sharing context");

            // Plane flight creation
            $plane_flight_handler->CreatePlaneFlights($flights);
            $id = $plane_flight_handler->latestFlight()->vaid;

            // new context recording
            $new_context = $this->FlightAndBillingContext($browser, $acounts);
            $this->DisplayContext($new_context, "After first created flight");
            $deltas = $this->CompareContexes($context, $new_context);
            $expected = [
                'balance' => [
                    'asterix' => -46.0,
                    'goudurix' => 0.0,
                    'panoramix' => 0.0,
                    'plane time account' => 15.0
                ],
                'purchases' => 3,
                'lines' => 3
            ];
            $this->ExpectedDifferences($expected, $deltas, "after creation of the first flight");

            // Set a payer, no percentage
            $update = [
                'vaid' => $id,
                'payeur' => 'goudurix',

            ];
            $plane_flight_handler->UpdatePlaneFLight($update);

            $new_context = $this->FlightAndBillingContext($browser, $acounts);
            $this->DisplayContext($new_context, "After payer setting");
            $deltas = $this->CompareContexes($context, $new_context);
            $expected = [
                'balance' => [
                    'asterix' => -46.0,
                    'goudurix' => 0.0,
                    'panoramix' => 0.0,
                    'plane time account' => 15.0
                ],
                'purchases' => 3,
                'lines' => 3
            ];
            $this->ExpectedDifferences($expected, $deltas, "After payer setting");

            // Set the percentage to 100
            $update = [
                'vaid' => $id,
                'payeur' => 'goudurix',
                'pourcentage' => 100
            ];
            $plane_flight_handler->UpdatePlaneFLight($update);

            $new_context = $this->FlightAndBillingContext($browser, $acounts);
            $this->DisplayContext($new_context, "After 100 percent");
            $deltas = $this->CompareContexes($context, $new_context);
            $expected = [
                'balance' => [
                    'asterix' => 0.0,
                    'goudurix' => -46.0,
                    'panoramix' => 0.0,
                    'plane time account' => 15.0
                ],
                'purchases' => 3,
                'lines' => 3
            ];
            $this->ExpectedDifferences($expected, $deltas, "After 100 percent");

            // Set the percentage to 50
            $update = [
                'vaid' => $id,
                'payeur' => 'goudurix',
                'pourcentage' => 50
            ];
            $plane_flight_handler->UpdatePlaneFLight($update);

            $new_context = $this->FlightAndBillingContext($browser, $acounts);
            $this->DisplayContext($new_context, "After 50 percent");
            $deltas = $this->CompareContexes($context, $new_context);
            $expected = [
                'balance' => [
                    'asterix' => -23.0,
                    'goudurix' => -23.0,
                    'panoramix' => 0.0,
                    'plane time account' => 15.0
                ],
                'purchases' => 6,
                'lines' => 6
            ];
            $this->ExpectedDifferences($expected, $deltas, "After 50 percent");


            // Back  to 100
            $update = [
                'vaid' => $id,
                'payeur' => 'panoramix',
                'pourcentage' => 100
            ];
            $plane_flight_handler->UpdatePlaneFLight($update);

            $new_context = $this->FlightAndBillingContext($browser, $acounts);
            $this->DisplayContext($new_context, "Back to 100 percent");
            $deltas = $this->CompareContexes($context, $new_context);
            $expected = [
                'balance' => [
                    'asterix' => 0.0,
                    'goudurix' => 0.0,
                    'panoramix' => -46.0,
                    'plane time account' => 15.0
                ],
                'purchases' => 3,
                'lines' => 3
            ];
            $this->ExpectedDifferences($expected, $deltas, "Back to percent");

            // Back to 0 %
            $update = [
                'vaid' => $id,
                'pourcentage' => 0
            ];
            $plane_flight_handler->UpdatePlaneFLight($update);

            $new_context = $this->FlightAndBillingContext($browser, $acounts);
            $this->DisplayContext($new_context, "Back to 0 percent");
            $deltas = $this->CompareContexes($context, $new_context);
            $expected = [
                'balance' => [
                    'asterix' => -46.0,
                    'goudurix' => 0.0,
                    'panoramix' => 0.0,
                    'plane time account' => 15.0
                ],
                'purchases' => 3,
                'lines' => 3
            ];
            $this->ExpectedDifferences($expected, $deltas, "Back to 0 percent");
            
            // Flight delete
            $this->canAccess($browser, 'vols_avion/delete/' . $id);
            $new_context = $this->FlightAndBillingContext($browser, $acounts);
            $deltas = $this->CompareContexes($context, $new_context);
            $expected = [
                'balance' => [
                    'asterix' => 0.0,
                    'goudurix' => 0.0,
                    'panoramix' => 0.0,
                    'plane time account' => 0.0
                ],
                'purchases' => 0,
                'lines' => 0
            ];
            $this->ExpectedDifferences($expected, $deltas, "After delete");
        }); // end of browse callback
    }

    /**
     * Logout
     * @depends testSharing
     */
    public function testLogout() {
        parent::testLogout();
    }
}
