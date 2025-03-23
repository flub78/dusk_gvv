<?php

namespace Tests\Browser;

use Laravel\Dusk\Browser;
use Tests\GvvDuskTestCase;
use Tests\libraries\MemberHandler;


/**
 * Sections
 * 
    Quels sont les invariants en compta
        
        La balance est à 0
            - sur la balance détaillée
            - sur les balances générales
                
        Le nombre de comptes de chaque section est inférieur ou égale au nombre de compte club
        Le nombre de codes comptable de chaque section est inférieur ou égale au nombre de codes comptable club
            
        Sur chaque entrée de balance générale on trouve le solde des comptes de balance détaillé
 */
class ComptaTest extends GvvDuskTestCase {

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
     * testCheckThatUserCanLoginWithSection
     * @depends testCheckInstallationProcedure
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
}
