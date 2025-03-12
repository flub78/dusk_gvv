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

            // login with planeur and see all the planes
            $this->login($browser, env('TEST_USER'), env('TEST_PASSWORD'), $planeur);
            $browser->assertSee('Planeur');
            $this->assertEquals($plane_total, $this->TableRowCount($browser, "avion/page"));

            // switch to all and still see all the planes 
            $browser->select('section', $all)
                ->screenshot("all_selected_$all");
            $this->assertEquals($plane_total, $this->TableRowCount($browser, "avion/page"));

            // Checks that all the planes are available in the plane selector
            $browser->visit($this->fullUrl('vols_avion/create'))
                ->waitFor('select[name="vamacid"]')
                ->assertSelectHasOptions('vamacid', ['F-JUFA', 'F-GUFB']);

            // switch to general, no planes
            $browser->select('section', $general)
                ->screenshot("general_selected_$general");
            $this->assertEquals(0, $this->TableRowCount($browser, "avion/page"));

            // checks that there is no planes in the plane selector
            $browser->visit($this->fullUrl('vols_avion/create'))
                ->waitFor('select[name="vamacid"]')
                ->assertSelectHasOptions('vamacid', [])
                ->assertSelectMissingOptions('vamacid', ['F-JUFA']);

            $this->logout($browser);
        });
    }


    /**
     * Logout
     * @depends testCheckThatUserCanLoginWithSection
     */
    public function testTableExtraction() {
        // $this->markTestSkipped('must be revisited.');
        $this->browse(function (Browser $browser) {

            $planeur = "1";
            $ulm = "2";
            $avion = "3";
            $general = "4";
            $all = "5";

            $this->login($browser, env('TEST_USER'), env('TEST_PASSWORD'), $planeur);
            $browser->assertSee('Planeur');

            $browser->visit($this->fullUrl('avion/page'));

            // $tableData = $this->getElementHtml($browser, '#DataTables_Table_0');
            // $tableData = $this->extractTableToArray($browser, '#DataTables_Table_0');
            $table = $this->extractTableToAssociativeArray($browser, '#DataTables_Table_0');

            // var_dump($table);

            $edit_1 = $table['rows'][0]['column_0'];
            $href = $this->extractHref($edit_1);

            $delete_1 = $table['rows'][0]['column_1'];
            $delete_url = $this->extractHref($delete_1);


            // echo "edit_1: $edit_1\n";
            // echo "href: $href\n";
            // echo "delete_1: $delete_1\n";
            // echo "delete_url: $delete_url\n";

            $this->assertEquals("http://gvv.net/avion/edit/F-GUFB", $href, "it is possible to extract the edit url");
            $this->assertEquals("http://gvv.net/avion/delete/F-GUFB", $delete_url, "it is possible to extract the delete url");

            $this->logout($browser);
        });
    }
}
