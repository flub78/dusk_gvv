<?php

namespace Tests\Browser;

use Laravel\Dusk\Browser;
use Tests\GvvDuskTestCase;
use Tests\libraries\MemberHandler;


/**
 * Sections
 * 
 * - check that the user can choose a section at login
 * - CRUD fo user roles inside sections
 * - Check that a user who belongs to several sections can select a section 
 * 
 * - Checks that the resources are displayed by section
 * -    avions, comptes, écritures, vols.
 * - Checks that the resources are read-only when no section is selected.
 */
class SectionsTest extends GvvDuskTestCase {

    const PLANEUR = "1";
    const ULM = "2";
    const AVION = "3";
    const GENERAL = "4";
    const ALL = "5";

    public function switchSection($browser, $section) {
        $browser
            ->select('section', $section)
            ->screenshot("switch_to_section_$section");
    }

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

            $plane_total = 2;

            // login with planeur and see all the planes
            $this->login($browser, env('TEST_USER'), env('TEST_PASSWORD'), self::PLANEUR);
            $browser->assertSee('Planeur');
            $this->assertEquals($plane_total, $this->TableRowCount($browser, "avion/page"));

            // switch to all and still see all the planes 
            $this->switchSection($browser, self::ALL);
            $browser->select('section', self::ALL)
                ->screenshot("all_selected");
            $this->assertEquals($plane_total, $this->TableRowCount($browser, "avion/page"));

            // Checks that all the planes are available in the plane selector
            $browser->visit($this->fullUrl('vols_avion/create'))
                ->waitFor('select[name="vamacid"]')
                ->assertSelectHasOptions('vamacid', ['F-JUFA', 'F-GUFB']);

            // switch to general, no planes
            $this->switchSection($browser, self::GENERAL);

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
     * @depends testCheckThatUserCanLoginWithSection
     */
    public function testTableExtraction() {
        // $this->markTestSkipped('must be revisited.');
        $this->browse(function (Browser $browser) {

            $this->login($browser, env('TEST_USER'), env('TEST_PASSWORD'), self::PLANEUR);
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

            $this->assertEquals($this->url . "avion/edit/F-GUFB", $href, "it is possible to extract the edit url");
            $this->assertEquals($this->url . "avion/delete/F-GUFB", $delete_url, "it is possible to extract the delete url");

            $this->logout($browser);
        });
    }

    /**
     * Checks that the correct client accounts are created
     * @depends testTableExtraction
     */
    public function testMemberAccountsCreation() {
        // $this->markTestSkipped('must be revisited.');
        $this->browse(function (Browser $browser) {

            $this->login($browser, env('TEST_USER'), env('TEST_PASSWORD'), self::ULM);
            $browser->assertSee('ULM');

            // Comptes clients de la section ULM
            $comptes_ulm  = $this->TableRowCount($browser, "comptes/page/411") - 1;
            $this->switchSection($browser, self::PLANEUR);
            $comptes_planeur  = $this->TableRowCount($browser, "comptes/page/411") - 1;
            $this->switchSection($browser, self::GENERAL);
            $comptes_general  = $this->TableRowCount($browser, "comptes/page/411") - 1;
            $this->switchSection($browser, self::ALL);
            $comptes_all  = $this->TableRowCount($browser, "comptes/page/411") - 1;

            // echo "comptes_ulm: $comptes_ulm\n";
            // echo "comptes_planeur: $comptes_planeur\n";
            // echo "comptes_all: $comptes_all\n";
            // echo "comptes_general: $comptes_general\n";

            $this->assertGreaterThanOrEqual($comptes_ulm, $comptes_all);
            $this->assertGreaterThanOrEqual($comptes_planeur, $comptes_all);
            $this->assertGreaterThanOrEqual($comptes_general, $comptes_all);

            // On va créer un membre dans la section ULM
            // On active la section ULM
            $this->switchSection($browser, self::ULM);
            // On crée le membre
            $membre = [
                'id' => 'bonemine',
                'nom' => 'Le Gaulois',
                'prenom' => 'Bonemine',
                'email' => 'bonemine@flub78.net',
                'adresse' => '1 rue des menhirs',
                'code_postal' => '78000',
                'ville' => 'Village Gaulois'
            ];
            $member_handler = new MemberHandler($browser, $this);
            try {
                $member_handler->CreateMembers([$membre]);
            } catch (\Exception $e) {
                $browser->acceptDialog();

                // It is displayed
                // echo "Exception raised: " . $e->getMessage() . "\n";
            }

            // On vérifie qu'on a bien deux comptes clients en plus
            // Un dans la section ULM

            $this->switchSection($browser, self::ULM);
            $nouveau_comptes_ulm  = $this->TableRowCount($browser, "comptes/page/411") - 1;
            // echo "nouveau_comptes_ulm: $nouveau_comptes_ulm\n";
            $this->assertEquals($nouveau_comptes_ulm, $comptes_ulm + 1);

            // Un dans la section générale
            $this->switchSection($browser, self::GENERAL);
            $nouveau_comptes_general  = $this->TableRowCount($browser, "comptes/page/411") - 1;
            // echo "nouveau_comptes_general: $nouveau_comptes_general\n";
            $this->assertEquals($nouveau_comptes_general, $comptes_general + 1);

            $this->switchSection($browser, self::ALL);
            $nouveau_comptes_all  = $this->TableRowCount($browser, "comptes/page/411") - 1;
            // echo "nouveau_comptes_all: $nouveau_comptes_all\n";
            $this->assertEquals($nouveau_comptes_all, $comptes_all + 2);

            // On détruit les comptes clients
            // et le nouveau membre
            // Et on vérifie qu'on retrouve le nombre de compte initial
            // et on détruit le membre

            $this->logout($browser);
        });
    }
}
