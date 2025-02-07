<?php

namespace Tests\Browser;

use Laravel\Dusk\Browser;
use Tests\GvvDuskTestCase;

use function PHPUnit\Framework\assertSameSize;

/*
 * Check that the application is initialized with test data
 *
 * It will be the base for feature tests.
 * 
 * Resource tests (CRUD):
 * - Check that it is possible to create a resource
 * - Check that it is possible to read a resource
 * - Check that it is possible to update a resource
 * - Check that it is possible to delete a resource
 * - Check all cases of error in creation/edition
 * - check indirect modifications (e.g. billing, etc.)
 */

class DbInitTest extends GvvDuskTestCase {
    /**
     * Check that the installation can be reset and installed
     *
     * @return void
     */
    public function testCheckInstallationProcedure() {
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
    public function testCheckThatUserCanLogin() {
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
    public function testCheckThatUserCanLogout() {
        // $this->markTestSkipped('must be revisited.');
        $this->browse(function (Browser $browser) {
            $this->logout($browser);
        });
    }
}
