<?php

namespace Tests\Browser;

use Laravel\Dusk\Browser;
use Tests\GvvDuskTestCase;

/**
 * Sections
 * 
 * - check that the user can choose a section at login
 * - CRUD fo user roles inside sections
 * - Check that a user who belongs to several sections can select a section 
 * - Checks that the resources are displayed by section
 * -
 */
class SectionsTest extends GvvDuskTestCase {

    /*************
     * Test cases
     *************/

    /**
     * Login
     * @depends testCheckInstallationProcedure
     */
    public function testCheckThatUserCanLogin() {
        // $this->markTestSkipped('must be revisited.');
        $this->browse(function (Browser $browser) {
            $this->login($browser, env('TEST_USER'), env('TEST_PASSWORD'), "1");
        });
    }


    /**
     * Logout
     * @depends testCheckThatUserCanLogin
     */
    public function testCheckThatUserCanLogout() {
        // $this->markTestSkipped('must be revisited.');
        $this->browse(function (Browser $browser) {
            $this->logout($browser);
        });
    }
}
