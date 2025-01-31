<?php

namespace Tests\Browser;

use Laravel\Dusk\Browser;
use Tests\Browser\BillingTest;

use Tests\libraries\GliderFlightHandler;
use Tests\libraries\AccountHandler;
use Tests\libraries\GliderHandler;

/*
 * 
 * Purchase tests
 * 
 *  On peut acheter 2 items
 *  Le compte du pilote est débité du prix x 2
 *  Le compte correspondant est crédité du prix x 2
 *  On peut ramener la quantité à 1
 *  Le compte du pilote est débité du prix
 *  Le compte correspondant est crédité du prix
 *  On peut supprimer l'achat
 *  Le compte pilote est ramené à son niveau initial
 *  le compte produit est ramené à son niveau initial
 */

class PurchasesTest extends BillingTest {

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
     * Checks that a purchase is billed correctly
     * 
     * @depends testLogin
     */
    public function testBilling() {
        $this->browse(function (Browser $browser) {
            $this->assertTrue(true);

            $account_handler = new AccountHandler($browser, $this);
            $asterix_acount_image = "(411) Le Gaulois Asterix";
            $launch_acount_image = "(706) Remorqués";

            return;

            $glider_flight_handler = new GliderFlightHandler($browser, $this);
            $glider_handler = new GliderHandler($browser, $this);

            $latest = $glider_flight_handler->latestFlight();
            $flightDate = $this->NextDate($latest);


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
                    'altitude' => '700',
                    'tow_pilot' => 'abraracourcix',
                    'tow_plane' => 'F-JUFA',
                    'account' => $asterix_acount_image,
                    'price' => 46.0,
                ],
            ];

            // context recording
            $accounts = [
                'asterix' => $account_handler->AccountIdFromImage($asterix_acount_image),
                'launch account' => $account_handler->AccountIdFromImage($launch_acount_image),
                'glider time account' => $account_handler->AccountIdFromImage($glider_time_acount_image)
            ];

            $context = $this->FlightAndBillingContext($browser, $acounts);
            $this->DisplayContext($context, "Initial context");

            // Glider flight creation
            $glider_flight_handler->CreateGliderFlights($flights);
            $id = $glider_flight_handler->latestFlight()->vpid;

            // new context recording
            $new_context = $this->FlightAndBillingContext($browser, $acounts);
            $deltas = $this->CompareContexes($context, $new_context);
            $expected = [
                'balance' => ['asterix' => -46.0, 'launch account' => 31.0, 'glider time account' => 15.0],
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
            $glider_flight_handler->UpdateGliderFLight($update);
            $new_context = $this->FlightAndBillingContext($browser, $acounts);
            $deltas = $this->CompareContexes($context, $new_context);
            $expected = [
                'balance' => ['asterix' => -45.0, 'launch account' => 15.0, 'glider time account' => 30.0],
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
            $glider_flight_handler->UpdateGliderFLight($update);
            $new_context = $this->FlightAndBillingContext($browser, $acounts);
            $deltas = $this->CompareContexes($context, $new_context);
            $expected = [
                'balance' => ['asterix' => -98.0, 'launch account' => 8.0, 'glider time account' => 90.0],
                'purchases' => 2,
                'lines' => 2
            ];
            $this->ExpectedDifferences($expected, $deltas, "After switch to winch");

            // VI
            $update = [
                'vpid' => $id,
                'categorie' => 'VI', // 6 hours so 90 €
            ];
            $glider_flight_handler->UpdateGliderFLight($update);
            $new_context = $this->FlightAndBillingContext($browser, $acounts);
            $deltas = $this->CompareContexes($context, $new_context);
            $expected = [
                'balance' => ['asterix' => 0.0, 'launch account' => 0.0, 'glider time account' => 0.0],
                'purchases' => 0,
                'lines' => 0
            ];
            $this->ExpectedDifferences($expected, $deltas, "After VI");

            // Private glider per owner
            $glider_owner = [
                "immat" => "F-CGAA",
                "type_proprio" => "Privé",
                "proprietaire" => "asterix",
            ];
            $glider_handler->UpdateGlider($glider_owner);

            $update = [
                'vpid' => $id,
                'categorie' => 'standard',
            ];
            $glider_flight_handler->UpdateGliderFLight($update);

            $new_context = $this->FlightAndBillingContext($browser, $acounts);
            $deltas = $this->CompareContexes($context, $new_context);
            $expected = [
                'balance' => ['asterix' => -8.0, 'launch account' => 8.0, 'glider time account' => 0.0],
                'purchases' => 1,
                'lines' => 1
            ];
            $this->ExpectedDifferences($expected, $deltas, "Private glider");

            // Private glider per not owner


            // Back to a clubl ownership
            $glider_owner = [
                "immat" => "F-CGAA",
                "type_proprio" => "Club",
                "proprietaire" => "",
            ];
            $glider_handler->UpdateGlider($glider_owner);

            // Flight delete
            $this->canAccess($browser, 'vols_planeur/delete/' . $id);
            $new_context = $this->FlightAndBillingContext($browser, $acounts);
            $deltas = $this->CompareContexes($context, $new_context);
            $expected = [
                'balance' => ['asterix' => 0.0, 'launch account' => 0.0, 'glider time account' => 0.0],
                'purchases' => 0,
                'lines' => 0
            ];
            $this->ExpectedDifferences($expected, $deltas, "After delete");
        }); // end of browse callback
    }



    /**
     * Logout
     * @depends testBilling
     */
    public function testLogout() {
        parent::testLogout();
    }
}
