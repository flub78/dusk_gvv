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
     * <select name="section" class="form-control" id="section">
     *      <option value="3">Avion</option>
     *      <option value="4">Général</option>
     *      <option value="1">Planeur</option>
     *      <option value="2">ULM</option>
     *      <option value="5">Toutes</option>
     * </select>
     */
    public function testCheckThatUserCanLoginWithSection() {
        // $this->markTestSkipped('must be revisited.');



        $this->browse(function (Browser $browser) {

            $planeur = "1";
            $ulm = "2";
            $avion = "3";
            $general = "4";
            $all = "5";

            $plane_total = 2;

            $this->login($browser, env('TEST_USER'), env('TEST_PASSWORD'), $planeur);

            $browser->assertSee('Planeur');
            $this->assertEquals($plane_total, $this->TableTotal($browser, "avion/page"));
            $this->logout($browser);
        });
    }


    // /**
    //  * Logout
    //  * @depends testCheckThatUserCanLogin
    //  */
    // public function testCheckThatUserCanLogout() {
    //     // $this->markTestSkipped('must be revisited.');
    //     $this->browse(function (Browser $browser) {
    //         $this->logout($browser);
    //     });
    // }
}
