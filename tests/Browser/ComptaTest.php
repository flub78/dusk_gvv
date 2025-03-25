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
    public function testCreateAccountingLinesAndCheckTheBalance() {
        // $this->markTestSkipped('must be revisited.');

        $this->browse(function (Browser $browser) {

            $plane_total = 2;

            // login with planeur and see all the accounts
            $this->login($browser, env('TEST_USER'), env('TEST_PASSWORD'), self::PLANEUR);
            $browser->assertSee('Planeur');

            $planeur_codec_count = $this->PageTableRowCount($browser, "comptes/general") - 1;

            echo "\n";
            echo "planeur_codec_count = $planeur_codec_count\n";

            $this->switchSection($browser, self::ULM);
            $ulm_codec_count = $this->PageTableRowCount($browser, "comptes/general") - 1;
            echo "ulm_codec_count = $ulm_codec_count\n";

            $this->switchSection($browser, self::AVION);
            $avion_codec_count = $this->PageTableRowCount($browser, "comptes/general") - 1;
            echo "avion_codec_count = $avion_codec_count\n";

            $this->switchSection($browser, self::GENERAL);
            $general_codec_count = $this->PageTableRowCount($browser, "comptes/general") - 1;
            echo "general_codec_count = $general_codec_count\n";


            // switch to all and still see all the planes 
            $this->switchSection($browser, self::ALL);
            $all_codec_count = $this->PageTableRowCount($browser, "comptes/general") - 1;
            echo "all_codec_count = $all_codec_count\n";

            $max_codec_count = max($planeur_codec_count, $ulm_codec_count, $avion_codec_count, $general_codec_count);

            $this->assertGreaterThanOrEqual($planeur_codec_count, 16);
            $this->assertGreaterThanOrEqual($ulm_codec_count, 25);
            $this->assertGreaterThanOrEqual($general_codec_count, 16);

            $this->assertGreaterThanOrEqual($max_codec_count, $all_codec_count);

            $this->logout($browser);
        });
    }
}
