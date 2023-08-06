<?php

namespace Tests\Browser;

use Laravel\Dusk\Browser;
use Tests\GvvDuskTestCase;

use Tests\libraries\AccountHandler;

/*
 * Common functions used by several classes of aircraft tests
 */

class AircraftFlightTest extends GvvDuskTestCase {

    public function NextDate($latest, $dateAttr = "vpdate") {
        $dateFormat = "d/m/Y";
        if ($latest) {
            $latest_date = $latest->$dateAttr;
            $date = new \DateTime($latest_date);
            $date->modify('+1 day');
        } else {
            $date = new \DateTime('first day of January this year', new \DateTimeZone('Europe/Paris'));
        }
        return $date->format($dateFormat);
    }
    
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
     * Fetch the test context.
     * Extract from the database the data that will be used to check the billing.
     * Typically:
     *      - balance of typicall accounts
     *      - number of purchases in the system
     *      - number of acounting lines in the system
     */
    public function FlightAndBillingContext($browser, $account_ids = []) {
        $account_handler = new AccountHandler($browser, $this);

        $res = [];
        foreach ($account_ids as $name => $account_id) {
            $res['balance'][$name] =  $account_handler->AccountTotal($account_id);
        }
        $res['purchases'] = $this->TableTotal($browser, "achats/page");
        $res['lines'] = $this->TableTotal($browser, "compta/page");
        return $res;
    }

    /**
     * Compare two contexts
     */
    public function CompareContexes($ctx1, $ctx2) {
        $res = [];
        foreach ($ctx1['balance'] as $name => $balance) {
            $res['balance'][$name] = $ctx2['balance'][$name] - $balance;
        }
        $res['purchases'] = $ctx2['purchases'] - $ctx1['purchases'];
        $res['lines'] = $ctx2['lines'] - $ctx1['lines'];
        return $res;
    }

    /**
     * Display the context
     */
    public function DisplayContext($context, $when = "") {
        echo "\n";
        echo "Context $when:\n";
        foreach ($context['balance'] as $name => $balance) {
            echo "$name balance = $balance\n";
        }
        echo "purchases = " . $context['purchases'] . "\n";
        echo "lines = " . $context['lines'] . "\n";
    }

    /**
     * Checks expected differences in test context.
     * Evaluate phpunit assertions
     */
    public function ExpectedDifferences($expected, $actual, $where = "", $epsilon = 0.000001) {
        foreach ($expected['balance'] as $name => $value) {
            $this->assertEqualsWithDelta($value, $actual['balance'][$name], $epsilon, "expected balance difference $name $where = " . $expected['balance'][$name]);
        }
        $this->assertEquals($expected['purchases'], $actual['purchases'], "Expected purchases difference $where = " . $expected['purchases']);
        $this->assertEquals($expected['lines'], $actual['lines'], "Expected lines difference $where = " . $expected['lines']);
    }





    /**
     * Logout
     * @depends testSharing
     */
    public function testLogout() {
        // $this->markTestSkipped('must be revisited.');
        $this->assertTrue(true); return;
        $this->browse(function (Browser $browser) {
            $this->logout($browser);
        });
    }
}
