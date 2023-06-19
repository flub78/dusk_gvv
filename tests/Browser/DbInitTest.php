<?php

namespace Tests\Browser;

use Laravel\Dusk\Browser;
use Tests\GvvDuskTestCase;

use function PHPUnit\Framework\assertSameSize;

/*
 * Check that the application is initilized with test data
 *
 * It will be the base for feature tests
 */

class DbInitTest extends GvvDuskTestCase {
    /**
     * Check that the installation can be reset and installed
     *
     * @return void
     */
    public function testInit() {
        $this->browse(function (Browser $browser) {

            $user = "testadmin";
            $password = "password";

            $browser->visit($this->url . 'install/reset.php')
                ->assertSee("Verification de l'installation")
                // tables are only dropped when they exist
                // ->assertSee('drop table achats')
                // ->assertSee('drop table terrains')
                ->assertSee('Suppression des images')
                ->assertSee($this->url . 'install');

            // $browser->clickLink($this->url . 'install');

            $browser->visit($this->url . 'install/?db=dusk_tests.sql');

            $browser->assertSee('Installation de GVV')
                ->assertSee("Fin de la procédure d'installation");

            $this->login($browser, $user, $password);
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
     * Test the ...
     * 
     * @depends testLogin
     */
    public function testTest() {
        $this->browse(function (Browser $browser) {
            
            $this->assertNotNull($browser);
        });
    }

    /**
     * Logout
     * @depends testTest
     */
    public function testLogout() {
        // $this->markTestSkipped('must be revisited.');
        $this->browse(function (Browser $browser) {
            $this->logout($browser);
        });
    }
}
