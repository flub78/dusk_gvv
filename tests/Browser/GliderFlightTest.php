<?php

namespace Tests\Browser;

use Laravel\Dusk\Browser;
use Tests\GvvDuskTestCase;

use Tests\libraries\GliderFlightHandler;
use Tests\libraries\AccountHandler;

// use function PHPUnit\Framework\assertSameSize;

/*
 * 
 * Glider Flight Resource tests (CRUD):
 * - Check that it is possible to create a resource
 * - Check that it is possible to read a resource
 * - Check that it is possible to update a resource
 * - Check that it is possible to delete a resource
 * - Check all cases of error in creation/edition
 * - check indirect modifications (e.g. billing, etc.)
 * 
 * - checks that only two seaters accept two pilots
 * - checks that flights are rejected when the pilot or glider are already in flight
 * 
 * TODO:
 *  - attempt for negative duration
 *  - shared flights
 *  - certificates
 */

class GliderFlightTest extends GvvDuskTestCase {

    /**
     * Check that the installation can be reset and installed
     *
     * @return void
     */
    public function testInit() {
        $this->browse(function (Browser $browser) {

            $browser->visit($this->url . 'install/reset.php')
                ->assertSee("Verification de l'installation")
                ->assertSee($this->url . 'install');

            $browser->visit($this->url . 'install/?db=dusk_tests.sql');

            $browser->assertSee('Installation de GVV')
                ->assertSee("Fin de la procédure d'installation");

            $this->login($browser, env('TEST_USER'), env('TEST_PASSWORD'));
            $browser->visit($this->fullUrl('migration'))
                ->assertSee('Migration de la base de données')
                ->press("Valider")
                ->assertSee('à jour');

            // Check that the database contains expected data
            $this->assertEquals(3, $this->TableTotal($browser, "planeur/page"));
            $this->assertEquals(2, $this->TableTotal($browser, "avion/page"));
            $this->assertEquals(4, $this->TableTotal($browser, "membre/page"));
            $this->logout($browser);
        });
    }

    /**
     * Login
     * 
     * @depends testInit
     */
    public function testLogin() {
        // $this->markTestSkipped('must be revisited.');
        $this->browse(function (Browser $browser) {
            $this->login($browser, env('TEST_USER'), env('TEST_PASSWORD'));
        });
    }

    /**
     * Test creation of correct flights
     * 
     * @depends testLogin
     */
    public function testCreate() {
        $this->browse(function (Browser $browser) {

            $glider_flight_handler = new GliderFlightHandler($browser, $this);

            $latest = $glider_flight_handler->latestFlight();

            $dateFormat = "d/m/Y";
            if ($latest) {
                $latest_date = $latest->vpdate;
                $date = new \DateTime($latest_date);
                $date->modify('+1 day');
            } else {
                $date = new \DateTime('first day of January this year', new \DateTimeZone('Europe/Paris'));
            }
            $flightDate = $date->format($dateFormat);

            $flights = [
                [
                    'url' => 'vols_planeur/create',
                    'date' => $flightDate,
                    'pilot' => 'asterix',
                    'glider' => 'F-CGAA',
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
                    'url' => 'vols_planeur/create',
                    'date' => $flightDate,
                    'pilot' => 'goudurix',
                    'glider' => 'F-CGAA',
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
                    'url' => 'vols_planeur/create',
                    'date' => $flightDate,
                    'pilot' => 'asterix',
                    'glider' => 'F-CGAB',
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

            $glider_flight_handler->CreateGliderFlights($flights);
        });
    }

    /**
     * Checks that correct fields are displayed depending on the fact aht the machine is single seat or not
     * 
     * @depends testCreate
     */
    public function testSingleSeater() {
        $this->browse(function (Browser $browser) {

            $glider_flight_handler = new GliderFlightHandler($browser, $this);

            $latest = $glider_flight_handler->latestFlight();

            $dateFormat = "d/m/Y";
            if ($latest) {
                $latest_date = $latest->vpdate;
                $date = new \DateTime($latest_date);
                $date->modify('+1 day');
            } else {
                $date = new \DateTime('first day of January this year', new \DateTimeZone('Europe/Paris'));
            }
            $flightDate = $date->format($dateFormat);

            $flights = [
                [
                    'url' => 'vols_planeur/create',
                    'date' => $flightDate,
                    'pilot' => 'goudurix',
                    'glider' => 'F-CGAB',
                    'passenger' => 'panoramix', 
                    'start_time' => '13:00',
                    'end_time' => '13:30',
                    'winch_man' => 'asterix',
                    'launch' => 'T',   // R, T, A, E
                    'account' => "(411) Le Gaulois Goudurix",
                    'price' => 23.0,
                    'error' => "no passenger on single seater",
                ],                                
            ];

            $this->canAccess($browser, 'vols_planeur/create');

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
            'url' => 'vols_planeur/create',
            'date' => $flightDate,
            'pilot' => $line[0],
            'glider' => $line[1],
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
        $this->browse(function (Browser $browser) {

            $glider_flight_handler = new GliderFlightHandler($browser, $this);

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

            $rejected_flights = $this->generateConflictingFlights($rejected , "machine ou pilote en vol");
            $accepted_flights = $this->generateConflictingFlights($accepted);
            $flights = $rejected_flights + $accepted_flights;

            $this->canAccess($browser, 'vols_planeur');
            $browser->screenshot('before_conflicting_flights');

            $glider_flight_handler->CreateGliderFlights($flights);    
        });
    }

    /**
     * Check that a glider flight can be updated
     * @depends testInFlight
     */
    public function testUpdate() {
        // $this->markTestSkipped('must be revisited.');
        $this->browse(function (Browser $browser) {

            $glider_flight_handler = new GliderFlightHandler($browser, $this);

            $latest = $glider_flight_handler->latestFlight();
            // echo "latest flight = " . $latest->vpid . "\n";

            $flight_count = $glider_flight_handler->count();
            // echo "flight count = $flight_count\n";

            $modified_comment = "modified comment";

            $flight = [
                'vpid' => $latest->vpid,
                'comment' => $modified_comment,
            ];
            $glider_flight_handler->UpdateGliderFLight($flight);

            $latest = $glider_flight_handler->latestFlight();
            $this->assertEquals($modified_comment, $latest->vpobs);

            $new_count = $glider_flight_handler->count();
            $this->assertEquals($flight_count, $new_count);
        });
    }

    /**
     * Check that a glider flight can be deleted
     * @depends testUpdate
     */
    public function testDelete() {
        // $this->markTestSkipped('must be revisited.');
        $this->browse(function (Browser $browser) {

            $glider_flight_handler = new GliderFlightHandler($browser, $this);

            $latest = $glider_flight_handler->latestFlight();

            $flight_count = $glider_flight_handler->count();

            $this->canAccess($browser, 'vols_planeur/delete/' . $latest->vpid);

            $new_count = $glider_flight_handler->count();
            $this->assertEquals($flight_count - 1, $new_count);
        });
    }

    /**
     * Checks that a glider flight is billed correctly
     *     - pilot account is debited
     *     - sale account is credited
     *     - several purchased are generated
     *     - when a flight is updated, the debit and credit are adapted, purchases are replaced
     *     - when a flight is deleted, the debit and credit are deleted, purchases are deleted
     *   
     * @depends testDelete
     */
    public function testBilling() {
        // $this->markTestSkipped('must be revisited.');
        $this->browse(function (Browser $browser) {
            $this->assertTrue(true);

            $glider_flight_handler = new GliderFlightHandler($browser, $this);
            $account_handler = new AccountHandler($browser, $this);

            $latest = $glider_flight_handler->latestFlight();

            $dateFormat = "d/m/Y";
            if ($latest) {
                $latest_date = $latest->vpdate;
                $date = new \DateTime($latest_date);
                $date->modify('+1 day');
            } else {
                $date = new \DateTime('first day of January this year', new \DateTimeZone('Europe/Paris'));
            }
            $flightDate = $date->format($dateFormat);

            /**
             * Test cases
             *   - club glider + tow plane + higher altitude
             *   - private glider + tow plane
             *   - club glider more than three hours + winch
             *   - external glider winch
             *   - forfait billing
             */

            $asterix_acount_image = "(411) Le Gaulois Asterix";
            $launch_acount_image = "(706) Remorqués";
            $glider_time_acount_image = "(706) Heures de vol planeur";

            $flights = [
                [
                    'url' => 'vols_planeur/create',
                    'date' => $flightDate,
                    'pilot' => 'asterix',
                    'glider' => 'F-CGAA',
                    'instructor' => 'panoramix',
                    'DC' =>  true,       
                    'start_time' => '10:00',
                    'end_time' => '10:30',
                    'launch' => 'R',   // R, T, A, E
                    'tow_pilot' => 'abraracourcix',
                    'tow_plane' => 'F-JUFA',
                    'account' => $asterix_acount_image,
                    'price' => 40.0,
                ],
            ];

            // context recording
            $asterix_account_id = $account_handler->AccountIdFromImage($asterix_acount_image);
            $launch_account_id = $account_handler->AccountIdFromImage($launch_acount_image);
            $glider_time_account_id = $account_handler->AccountIdFromImage($glider_time_acount_image);

            $asterix_balance = $account_handler->AccountTotal($asterix_account_id);
            $launch_balance = $account_handler->AccountTotal($launch_account_id);
            $glider_time_balance = $account_handler->AccountTotal($glider_time_account_id);

            $purchases_count = $this->TableTotal($browser, "achats/page");
            $lines_count = $this->TableTotal($browser, "compta/page");
 
            // Glider flight creation
            $glider_flight_handler->CreateGliderFlights($flights);

            // new context recording
            $new_purchases_count = $this->TableTotal($browser, "achats/page");
            $new_lines_count = $this->TableTotal($browser, "compta/page");

            $asterix_new_balance = $account_handler->AccountTotal($asterix_account_id);
            $launch_new_balance = $account_handler->AccountTotal($launch_account_id);
            $glider_time_new_balance = $account_handler->AccountTotal($glider_time_account_id);

            echo "\n";
            echo "asterix balance = $asterix_balance\n";
            echo "asterix new balance" . $asterix_new_balance . "\n";
            echo "launch balance = $launch_balance\n";
            echo "launch new balance" . $launch_new_balance . "\n";
            echo "glider time balance = $glider_time_balance\n";
            echo "glider time new balance" . $glider_time_new_balance . "\n";

            echo "created purchase = " . ($new_purchases_count - $purchases_count) . "\n";
            echo "created lines = " . ($new_lines_count - $lines_count) . "\n";
            
            $launch_cost = $launch_new_balance - $launch_balance;
            $time_cost = $glider_time_new_balance - $glider_time_balance;
            $asterix_cost = $asterix_new_balance - $asterix_balance;

            echo "launch cost = $launch_cost\n";
            echo "time cost = $time_cost\n";
            echo "asterix cost = $asterix_cost\n";

            // assertions
            $epsilon = 0.000001;
            $this->assertEquals(2, $new_purchases_count - $purchases_count, "wrong number of purchases");
            $this->assertEquals(2, $new_lines_count - $lines_count, "wrong number of lines");
            $this->assertEqualsWithDelta(25.0, $launch_cost, $epsilon, "wrong launch cost $launch_cost");
            $this->assertEqualsWithDelta(15.0, $time_cost, $epsilon, "wrong time cost = $time_cost");
            $this->assertEqualsWithDelta(-40.0, $asterix_cost, $epsilon, "wrong asterix cost = $asterix_cost");

            // flight update
 
            // Flight delete
            
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
        $this->browse(function (Browser $browser) {
            $this->assertTrue(true);
        });
    }

    /**
     * Logout
     * @depends testSharing
     */
    public function testLogout() {
        // $this->markTestSkipped('must be revisited.');
        $this->browse(function (Browser $browser) {
            $this->logout($browser);
        });
    }
}
