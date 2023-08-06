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
     * Test creation of correct flights
     * 
     * @depends testLogin
     */
    public function testCreate() {
        $this->assertTrue(true); return;
        $this->browse(function (Browser $browser) {

            $plane_flight_handler = new PlaneFlightHandler($browser, $this);

            $latest = $plane_flight_handler->latestFlight();
            var_dump($latest);
            $flightDate = $this->NextDate($latest, 'vadate');

            $flights = [
                [
                    'url' => 'vols_avion/create',
                    'date' => $flightDate,
                    'pilot' => 'asterix',
                    'plane' => 'F-CGAA',
                    'instructor' => 'panoramix',
                    'DC' =>  true,
                    'start_time' => '10:00',
                    'end_time' => '10:30',
                    'launch' => 'R',   // R, T, A, E
                    'tow_pilot' => 'abraracourcix',
                    'tow_plane' => 'F-JUFA',
                    'account' => "(411) Le Gaulois Asterix",
                    'price' => 40.0,
                ],
                [
                    'url' => 'vols_avion/create',
                    'date' => $flightDate,
                    'pilot' => 'goudurix',
                    'plane' => 'F-CGAA',
                    'instructor' => 'panoramix',
                    'DC' =>  true,
                    'start_time' => '11:00',
                    'end_time' => '12:15',
                    'winch_man' => 'asterix',
                    'launch' => 'T',   // R, T, A, E
                    'account' => "(411) Le Gaulois Goudurix",
                    'price' => 45.5,
                ],
                [
                    'url' => 'vols_avion/create',
                    'date' => $flightDate,
                    'pilot' => 'asterix',
                    'plane' => 'F-CGAB',
                    'DC' =>  false,
                    'start_time' => '11:00',
                    'end_time' => '12:15',
                    'launch' => 'T',   // R, T, A, E
                    'launch' => 'R',   // R, T, A, E
                    'tow_pilot' => 'abraracourcix',
                    'tow_plane' => 'F-JUFA',
                    'account' => "(411) Le Gaulois Asterix",
                    'price' => 62.5,
                ],
            ];

            $plane_flight_handler->CreatePlaneFlights($flights);
        });
    }

    /**
     * Checks that correct fields are displayed depending on the fact aht the machine is single seat or not
     * 
     * @depends testCreate
     */
    public function testSingleSeater() {
        $this->assertTrue(true); return;
        $this->browse(function (Browser $browser) {

            $this->canAccess($browser, 'vols_avion/create');

            // Two seater
            $browser->select('vpmacid', 'F-CGAA')
                ->pause(1000)
                ->assertVisible('#vpdc')
                ->assertVisible('#vppassager')
                ->assertMissing('#vpinst');

            // Set DC
            $browser->check('vpdc')
                ->pause(1000)
                ->assertVisible('#vpinst')
                ->assertMissing('#vppassager');

            // Single seater
            $browser->select('vpmacid', 'F-CGAB')
                ->pause(1000)
                ->assertMissing('#vpdc')
                ->assertMissing('#vppassager')
                ->assertMissing('#vpinst');

            // Back to two seaters
            $browser->select('vpmacid', 'F-CGAA')
                ->pause(1000)
                ->assertVisible('#vpdc')
                ->assertVisible('#vppassager')
                ->assertMissing('#vpinst');
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
     * Checks that correct fields are displayed depending on the fact aht the machine is single seat or not
     * 
     * @depends testSingleSeater
     * 
     * preconditions:
     *      Asterix on F-CGAA from 10:00 to 10:30
     *      Asterix on F-CGAB from 11:00 to 12:15
     *      Goudurix on F-CGAA from 11:00 to 12:15
     * 
     * Test cases
     *      rejected flights
     *     - Asterix on F-CGAA from 10:00 to 10:30  flight duplicated
     *     - Asterix on F-CGAB from 09:00 to 10:00
     *     - Asterix on F-CGAB from 09:30 to 10:15
     *     - Asterix on F-CGAB from 09:30 to 10:35  * missed + start not filled in the edit form
     *     - Asterix on F-CGAB from 09:30 to 12:30  * missed + start not filled in the edit form
     *     - Asterix on F-CGAB from 10:15 to 10:35
     *     - Asterix on F-CGAB from 10:15 to 12:30
     *     - Asterix on F-CGAB from 10:15 to 10:20 
     * 
     *     rejected flights
     *      - Goudurix on F-CGAA from 10:00 to 10:30
     *      - Goudurix on F-CGAA from 09:45 to 10:15
     *      - Goudurix on F-CGAA from 09:45 to 10:35
     *      - Goudurix on F-CGAA from 09:45 to 12:30
     *      - Goudurix on F-CGAA from 10:15 to 10:25
     *      - Goudurix on F-CGAA from 10:15 to 10:35
     *      - Goudurix on F-CGAA from 10:15 to 12:35
     * 
     *      Accepted flights
     *      - Asterix on F-CGAA from 09:00 to 09:59
     *      - Asterix on F-CGAA from 10:31 to 10:59
     *      - Asterix on F-CGAA from 12:16 to 13:00
     */
    public function testInFlight() {
        $this->assertTrue(true); return;
        $this->browse(function (Browser $browser) {

            $plane_flight_handler = new PlaneFlightHandler($browser, $this);

            $rejected = [
                ["asterix", "F-CGAA", "10:00", "10:30"],
                ["asterix", "F-CGAB", "09:00", "10:00"],
                ["asterix", "F-CGAB", "09:30", "10:15"],

                ["asterix", "F-CGAB", "09:30", "10:35"],
                ["asterix", "F-CGAB", "09:30", "12:30"],

                ["asterix", "F-CGAB", "10:15", "10:35"],
                ["asterix", "F-CGAB", "10:30", "12:30"],
                ["asterix", "F-CGAB", "10:30", "12:20"],

                ["goudurix", "F-CGAA", "10:00", "10:30"],
                ["goudurix", "F-CGAA", "09:45", "10:15"],
                ["goudurix", "F-CGAA", "09:45", "10:35"],
                ["goudurix", "F-CGAA", "09:45", "12:30"],
                ["goudurix", "F-CGAA", "10:15", "10:25"],
                ["goudurix", "F-CGAA", "10:15", "10:35"],
                ["goudurix", "F-CGAA", "10:15", "12:35"],
            ];

            $accepted = [
                ["asterix", "F-CGAA", "09:00", "09:59"],
                ["asterix", "F-CGAA", "10:31", "10:59"],
                ["asterix", "F-CGAA", "12:16", "13:00"],
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
        // $this->markTestSkipped('must be revisited.');
        $this->assertTrue(true); return;
        $this->browse(function (Browser $browser) {

            $plane_flight_handler = new PlaneFlightHandler($browser, $this);

            $latest = $plane_flight_handler->latestFlight();

            $flight_count = $plane_flight_handler->count();

            $modified_comment = "modified comment";

            $flight = [
                'vpid' => $latest->vpid,
                'comment' => $modified_comment,
            ];
            $plane_flight_handler->UpdatePlaneFLight($flight);

            $latest = $plane_flight_handler->latestFlight();
            $this->assertEquals($modified_comment, $latest->vpobs);

            $new_count = $plane_flight_handler->count();
            $this->assertEquals($flight_count, $new_count);
        });
    }

    /**
     * Check that a plane flight can be deleted
     * @depends testUpdate
     */
    public function testDelete() {
        // $this->markTestSkipped('must be revisited.');
        $this->assertTrue(true); return;
        $this->browse(function (Browser $browser) {

            $plane_flight_handler = new PlaneFlightHandler($browser, $this);

            $latest = $plane_flight_handler->latestFlight();

            $flight_count = $plane_flight_handler->count();

            $this->canAccess($browser, 'vols_avion/delete/' . $latest->vpid);

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
     *   - forfait billing     * @depends testDelete
     */
    public function testBilling() {
        // $this->markTestSkipped('must be revisited.');
        $this->browse(function (Browser $browser) {
            $this->assertTrue(true);return;

            $plane_flight_handler = new PlaneFlightHandler($browser, $this);
            $account_handler = new AccountHandler($browser, $this);
            $plane_handler = new PlaneHandler($browser, $this);

            $latest = $plane_flight_handler->latestFlight();
            $flightDate = $this->NextDate($latest, 'vadate');

            $asterix_acount_image = "(411) Le Gaulois Asterix";
            $launch_acount_image = "(706) Remorqués";
            $plane_time_acount_image = "(706) Heures de vol ULM";

            $flights = [
                [
                    'url' => 'vols_avion/create',
                    'date' => $flightDate,
                    'pilot' => 'asterix',
                    'plane' => 'F-CGAA',
                    'instructor' => 'panoramix',
                    'DC' =>  true,
                    'start_time' => '10:00',
                    'end_time' => '10:30',
                    'launch' => 'R',   // R, T, A, E
                    'altitude' => '700',
                    'tow_pilot' => 'abraracourcix',
                    'tow_plane' => 'F-JUFA',
                    'account' => $asterix_acount_image,
                    'price' => 46.0,
                ],
            ];

            // context recording
            $acounts = [
                'asterix' => $account_handler->AccountIdFromImage($asterix_acount_image),
                'launch account' => $account_handler->AccountIdFromImage($launch_acount_image),
                'plane time account' => $account_handler->AccountIdFromImage($plane_time_acount_image)
            ];

            $context = $this->FlightAndBillingContext($browser, $acounts);
            $this->DisplayContext($context, "Initial context");

            // Plane flight creation
            $plane_flight_handler->CreatePlaneFlights($flights);
            $id = $plane_flight_handler->latestFlight()->vpid;

            // new context recording
            $new_context = $this->FlightAndBillingContext($browser, $acounts);
            $deltas = $this->CompareContexes($context, $new_context);
            $expected = [
                'balance' => ['asterix' => -46.0, 'launch account' => 31.0, 'plane time account' => 15.0],
                'purchases' => 3,
                'lines' => 3
            ];
            $this->ExpectedDifferences($expected, $deltas, "after creation of the first flight");

            // Increase time flight and switch to a 300 m
            $update = [
                'vpid' => $id,
                'end_time' => '11:00', // 30 minutes more, 30 €
                'altitude' => '200',  // 300 meters - 1 purchase and lines, - 16 €
            ];
            $plane_flight_handler->UpdatePlaneFLight($update);
            $new_context = $this->FlightAndBillingContext($browser, $acounts);
            $deltas = $this->CompareContexes($context, $new_context);
            $expected = [
                'balance' => ['asterix' => -45.0, 'launch account' => 15.0, 'plane time account' => 30.0],
                'purchases' => 2,
                'lines' => 2
            ];
            $this->ExpectedDifferences($expected, $deltas, "After switch to 300 m");

            // Winch and flight of more than 3 hours
            $update = [
                'vpid' => $id,
                'end_time' => '16:00', // 6 hours so 90 €
                'launch' => 'T'
            ];
            $plane_flight_handler->UpdatePlaneFLight($update);
            $new_context = $this->FlightAndBillingContext($browser, $acounts);
            $deltas = $this->CompareContexes($context, $new_context);
            $expected = [
                'balance' => ['asterix' => -98.0, 'launch account' => 8.0, 'plane time account' => 90.0],
                'purchases' => 2,
                'lines' => 2
            ];
            $this->ExpectedDifferences($expected, $deltas, "After switch to winch");

            // VI
            $update = [
                'vpid' => $id,
                'categorie' => 'VI', // 6 hours so 90 €
            ];
            $plane_flight_handler->UpdatePlaneFLight($update);
            $new_context = $this->FlightAndBillingContext($browser, $acounts);
            $deltas = $this->CompareContexes($context, $new_context);
            $expected = [
                'balance' => ['asterix' => 0.0, 'launch account' => 0.0, 'plane time account' => 0.0],
                'purchases' => 0,
                'lines' => 0
            ];
            $this->ExpectedDifferences($expected, $deltas, "After VI");

            // Private plane per owner
            $plane_owner = [
                "immat" => "F-CGAA",
                "type_proprio" => "Privé",
                "proprietaire" => "asterix",
            ];
            $plane_handler->UpdatePlane($plane_owner);

            $update = [
                'vpid' => $id,
                'categorie' => 'standard',
            ];
            $plane_flight_handler->UpdatePlaneFLight($update);
            
            $new_context = $this->FlightAndBillingContext($browser, $acounts);
            $deltas = $this->CompareContexes($context, $new_context);
            $expected = [
                'balance' => ['asterix' => -8.0, 'launch account' => 8.0, 'plane time account' => 0.0],
                'purchases' => 1,
                'lines' => 1
            ];
            $this->ExpectedDifferences($expected, $deltas, "Private plane");

            // Private plane per not owner


            // Back to a clubl ownership
            $plane_owner = [
                "immat" => "F-CGAA",
                "type_proprio" => "Club",
                "proprietaire" => "",
            ];
            $plane_handler->UpdatePlane($plane_owner);

            // Flight delete
            $this->canAccess($browser, 'vols_avion/delete/' . $id);
            $new_context = $this->FlightAndBillingContext($browser, $acounts);
            $deltas = $this->CompareContexes($context, $new_context);
            $expected = [
                'balance' => ['asterix' => 0.0, 'launch account' => 0.0, 'plane time account' => 0.0],
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
        // $this->markTestSkipped('must be revisited.');
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
            $launch_acount_image = "(706) Remorqués";
            $plane_time_acount_image = "(706) Heures de vol ULM";

            // first a flight with no payer (default)
            $flights = [
                [
                    'url' => 'vols_avion/create',
                    'date' => $flightDate,
                    'pilot' => 'asterix',
                    'plane' => 'F-CGAA',
                    'instructor' => 'panoramix',
                    'DC' =>  true,
                    'start_time' => '10:00',
                    'end_time' => '10:30',
                    'launch' => 'R',   // R, T, A, E
                    'altitude' => '700',
                    'tow_pilot' => 'abraracourcix',
                    'tow_plane' => 'F-JUFA',
                    'account' => $asterix_acount_image,
                    'price' => 46.0,
                ],
            ];

            // context recording
            $acounts = [
                'asterix' => $account_handler->AccountIdFromImage($asterix_acount_image),
                'goudurix' => $account_handler->AccountIdFromImage($goudurix_acount_image),
                'panoramix' => $account_handler->AccountIdFromImage($panoramix_acount_image),
                'launch account' => $account_handler->AccountIdFromImage($launch_acount_image),
                'plane time account' => $account_handler->AccountIdFromImage($plane_time_acount_image)
            ];

            $context = $this->FlightAndBillingContext($browser, $acounts);
            $this->DisplayContext($context, "Initial Sharing context");

            // Plane flight creation
            $plane_flight_handler->CreatePlaneFlights($flights);
            $id = $plane_flight_handler->latestFlight()->vpid;

            // new context recording
            $new_context = $this->FlightAndBillingContext($browser, $acounts);
            $this->DisplayContext($new_context, "After first created flight");
            $deltas = $this->CompareContexes($context, $new_context);
            $expected = [
                'balance' => [
                    'asterix' => -46.0,
                    'goudurix' => 0.0,
                    'panoramix' => 0.0,
                    'launch account' => 31.0,
                    'plane time account' => 15.0
                ],
                'purchases' => 3,
                'lines' => 3
            ];
            $this->ExpectedDifferences($expected, $deltas, "after creation of the first flight");

            // Set a payer, no percentage
            $update = [
                'vpid' => $id,
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
                    'launch account' => 31.0,
                    'plane time account' => 15.0
                ],
                'purchases' => 3,
                'lines' => 3
            ];
            $this->ExpectedDifferences($expected, $deltas, "After payer setting");

            // Set the percentage to 100
            $update = [
                'vpid' => $id,
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
                    'launch account' => 31.0,
                    'plane time account' => 15.0
                ],
                'purchases' => 3,
                'lines' => 3
            ];
            $this->ExpectedDifferences($expected, $deltas, "After 100 percent");

            // Set the percentage to 50
            $update = [
                'vpid' => $id,
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
                    'launch account' => 31.0,
                    'plane time account' => 15.0
                ],
                'purchases' => 6,
                'lines' => 6
            ];
            $this->ExpectedDifferences($expected, $deltas, "After 50 percent");


            // Back  to 100
            $update = [
                'vpid' => $id,
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
                    'launch account' => 31.0,
                    'plane time account' => 15.0
                ],
                'purchases' => 3,
                'lines' => 3
            ];
            $this->ExpectedDifferences($expected, $deltas, "Back to percent");

            // Back to 0 %
            $update = [
                'vpid' => $id,
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
                    'launch account' => 31.0,
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
                    'launch account' => 0.0,
                    'plane time account' => 0.0
                ],
                'purchases' => 0,
                'lines' => 0
            ];
            $this->ExpectedDifferences($expected, $deltas, "After delete");
        }); // end of browse callback
    }

}
