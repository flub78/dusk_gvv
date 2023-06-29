<?php

namespace Tests\Browser;

use Laravel\Dusk\Browser;
use Tests\GvvDuskTestCase;

use Tests\libraries\GliderFlightHandler;

use function PHPUnit\Framework\assertSameSize;

/*
 * 
 * Glider Flight Resource tests (CRUD):
 * - Check that it is possible to create a resource
 * - Check that it is possible to read a resource
 * - Check that it is possible to update a resource
 * - Check that it is possible to delete a resource
 * - Check all cases of error in creation/edition
 * - check indirect modifications (e.g. billing, etc.)
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
                    'tow_pilot' => 'abraracourcix',
                    'tow_plane' => 'F-JUFA',
                    // 'winch_man' => 'asterix',
                    'launch' => 'R',   // R, T, A, E
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

    /**
     * Logout
     * @depends testSingleSeater
     */
    public function testLogout() {
        // $this->markTestSkipped('must be revisited.');
        $this->browse(function (Browser $browser) {
            $this->logout($browser);
        });
    }
}
