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


            /**
             * J'ai des difficultés à cliquer sut le bouton filtre.
             * Le plus simple est donc d'aller visiter directement ls pages de balance générale ou détaillée
             */


            // login with planeur and see all the accounts
            $this->login($browser, env('TEST_USER'), env('TEST_PASSWORD'), self::PLANEUR);
            $browser->assertSee('Planeur');

            $planeur_codec_count = $this->PageTableRowCount($browser, "comptes/general") - 1;
            $planeur_account_count = $this->PageTableRowCount($browser, "comptes/detail") - 1;

            echo "\n";
            echo "planeur_codec_count = $planeur_codec_count, planeur_account_count = $planeur_account_count\n";

            $this->switchSection($browser, self::ULM);
            $ulm_codec_count = $this->PageTableRowCount($browser, "comptes/general") - 1;
            $ulm_account_count = $this->PageTableRowCount($browser, "comptes/detail") - 1;

            echo "ulm_codec_count = $ulm_codec_count, ulm_account_count = $ulm_account_count\n";


            $this->switchSection($browser, self::AVION);
            $avion_codec_count = $this->PageTableRowCount($browser, "comptes/general") - 1;
            $avion_account_count = $this->PageTableRowCount($browser, "comptes/detail") - 1;

            echo "avion_codec_count = $avion_codec_count, avion_account_count = $avion_account_count\n";

            $this->switchSection($browser, self::GENERAL);
            $general_codec_count = $this->PageTableRowCount($browser, "comptes/general") - 1;
            $general_account_count = $this->PageTableRowCount($browser, "comptes/detail") - 1;

            echo "general_codec_count = $general_codec_count, general_account_count = $general_account_count\n";


            // switch to all and still see all the planes 
            $this->switchSection($browser, self::ALL);
            $all_codec_count = $this->PageTableRowCount($browser, "comptes/general") - 1;
            $all_account_count = $this->PageTableRowCount($browser, "comptes/detail") - 1;

            echo "all_codec_count = $all_codec_count, all_account_count = $all_account_count\n";

            $max_codec_count = max($planeur_codec_count, $ulm_codec_count, $avion_codec_count, $general_codec_count);
            $max_account_count = max($planeur_account_count, $ulm_account_count, $avion_account_count, $general_account_count);

            $this->assertGreaterThanOrEqual($planeur_codec_count, 16);
            $this->assertGreaterThanOrEqual($ulm_codec_count, 25);
            $this->assertGreaterThanOrEqual($general_codec_count, 16);

            $this->assertGreaterThanOrEqual($planeur_codec_count, $planeur_account_count);
            $this->assertGreaterThanOrEqual($ulm_codec_count, $ulm_account_count);
            $this->assertGreaterThanOrEqual($general_codec_count, $general_account_count);
            $this->assertGreaterThanOrEqual($all_codec_count, $all_account_count);

            $this->assertGreaterThanOrEqual($max_codec_count, $all_codec_count);
            $this->assertGreaterThanOrEqual($max_account_count, $all_account_count);

            $this->logout($browser);
        });
    }

    /**
     * Balance
     *      - verifier pour plusieurs sections et toutes les sections
     *          - et pour plusieurs codec (512 et 411)
     *              - que le solde affiché pour le codec est la somme des soldes de compte
     * 
     * Bilan
     *      - test de cloture
     *      - pour les sections cloturées vérifier l'équilibre actif/passif 
     *      - sur créance de tiers, banque, vérifier que le solde est égal à la somme des soldes de compte
     *      - vérifier que si on passe à l'année d'avant on retrouve les chiffres
     * 
     * Résultat
     *      - pour plusieurs sections et toutes
     *          - vérifier que tous les comptes de charge et produit sont affichés
     *          - verifier que les totaux correspondent aux somme des soldes
     * 
     * Ventes
     *      - pour plusieurs sections passer des écritures et vérifier qu'on les retrouve
     *      - vérifier qu'on les retrouve dans toutes les section
     * 
     */
}
