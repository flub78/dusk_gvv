<?php

namespace Tests\Browser;

use Laravel\Dusk\Browser;
use Tests\GvvDuskTestCase;

/**
 * Test Attachments
 * 
 * php artisan dusk --color=always --browse tests/Browser/AttachmentsTest.php
 * 
 * The tests rely on the methods order.
 * 
 * There is quite a lot of dependencies to pre-existing data :
 *      attachments depends on accounting lines
 *      accounting lines depends on accounts
 *      accounts depends on planc
 *  
 */
class AttachmentsTest extends GvvDuskTestCase {

    /** Constructor */
    function __construct() {
        parent::__construct();

        // var_dump($this->terrains);
        $this->terrains = [
            ['oaci' => "LFAA", 'nom' => "Trifouillis", 'freq1' => "123.45", 'comment' => "Mon terrain"],
            ['oaci' => "LFAB", 'nom' => "Les Oies", 'freq1' => "123.45", 'comment' => "Mon second terrain"]
        ];
    }

    // protected function setUp(): void {
    //     echo "setup\n";
    // }

    // protected function tearDown(): void {
    //     echo "teardown\n";
    // }

    public static function setUpBeforeClass(): void {
        //echo "setup before class\n";
    }

    public static function tearDownAfterClass(): void {
        //echo "teardown after class\n";
    }

    /**
     * Test create elements
     */
    public function createTerrain($browser, $terrains = []) {

        $total = $this->TableTotal($browser);
        foreach ($terrains as $terrain) {

            $this->canAccess($browser, "terrains/create", ['Code OACI']);
            $browser
                ->type('oaci', $terrain['oaci'])
                ->type('nom', $terrain['nom'])
                ->type('freq1', $terrain['freq1'])
                ->type('comment', $terrain['comment'])
                ->scrollIntoView('#validate')
                ->waitFor('#validate')
                ->press('#validate')
                ->assertSee('Terrains');

            $this->canAccess($browser, "terrains/page", ['Compta', 'Terrains']);
        }

        $new_total = $this->TableTotal($browser);
        $this->assertEquals($total + count($terrains), $new_total, "Terrain created, total = " . $new_total);
    }

    /**
     * Test delete elements, GVV just ignore the command when elements are selected several times
     * 
     * @depends testTerrainCRUD
     * @param Browser $browser
     * @param array $terrains
     */
    public function deleteTerrain($browser, $terrains = []) {

        foreach ($terrains as $terrain) {
            $this->canAccess($browser, "terrains/page", ['Compta', 'Terrains']);
            $total = $this->TableTotal($browser);

            $this->canAccess($browser, "terrains/delete/" . $terrain['oaci'], ['Terrains']);
            $new_total = $this->TableTotal($browser);

            $this->assertEquals($total - 1, $new_total, "Terrain deleted, total = " . $new_total);
        }
    }

    /**
     * Test create elements that already exist
     */
    public function createTerrainError($browser, $terrains = []) {

        foreach ($terrains as $terrain) {
            $this->canAccess($browser, "terrains/page", ['Compta', 'Terrains']);
            $total = $this->TableTotal($browser);

            $this->canAccess($browser, "terrains/create", ['Planeur']);

            $browser
                ->type('oaci', $terrain['oaci'])
                ->type('nom', $terrain['nom'])
                ->type('freq1', $terrain['freq1'])
                ->type('comment', $terrain['comment'])
                ->scrollIntoView('#validate')
                ->waitFor('#validate')
                ->press('#validate')
                ->assertSee("L'élément existe déjà");

            $this->canAccess($browser, "terrains/page", ['Compta', 'Terrains']);
            $new_total = $this->TableTotal($browser);

            $this->assertEquals($total, $new_total, "No Terrain created, total = " . $new_total);
        }
    }

    // Function to extract the href of the edit icon of a table row
    public function getHrefFromTableRow($browser, $pattern) {

        return $browser->script([
            "return document.evaluate(
                \"//tr[contains(., '$pattern')]//td[1]//a\", 
                document, 
                null, 
                XPathResult.FIRST_ORDERED_NODE_TYPE, 
                null
            ).singleNodeValue.getAttribute('href');"
        ])[0];
    }

    /**
     * Test cases
     */

    /**
     * Check that the installation can be reset and installed
     *
     * @return void
     */
    public function testInit() {
        parent::testInit();
    }

    /**
     * Login
     * 
     * @depends testInit
     */
    public function testLogin() {
        // $this->markTestSkipped('must be revisited.');
        $this->browse(function (Browser $browser) {
            $this->login($browser, 'testadmin', 'password');
        });
    }

    /**
     * Stat of the test
     * @depends testInit
     */
    public function testNoAttachment() {
        $this->browse(function (Browser $browser) {

            // the global attachment page with no attachments
            $url = $this->fullUrl("attachments");
            $browser->visit($url)
                ->assertSee('Justificatifs')
                ->assertSee("Affichage de l'élement 0 à 0 sur 0 éléments");

            // Les comptes de classe 606
            $url = $this->fullUrl("comptes/page/606");
            $browser->visit($url)
                ->assertSee('Balance des comptes Classe 606')
                ->assertSee("Affichage de l'élement 1 à 3 sur 3 éléments")
                ->assertSee('Essence plus huile')
                ->assertSee('Frais de bureau');

            // Le compte Essence plus huile
            // <a href="http://gvv.net/index.php/compta/journal_compte/298">Essence plus huile</a>
            $browser->waitFor('a')
                ->assertSeeLink('Essence plus huile');
            $href = $browser->attribute("a[href*='journal_compte']", 'href');
            $browser->visit($href)
                ->assertSee('Essence plus huile');

            $browser->select('year', '2023')
                ->assertSee('Chèque 413')
                ->assertSee('2023');

            // extract the edit link from the table
            $line1 = $this->getHrefFromTableRow($browser, 'Chèque 413');

            echo "line1 = $line1\n";

            $pattern = 'Chèque 413';
            $browser->visit($line1)
                ->assertSee('Ecriture comptable')
                ->assertSee('Justificatifs');

            // <a href="http://gvv.net/index.php/compta/journal_compte/297">Frais de bureau</a>
        });
    }

    /**
     * Logout
     * @depends testNoAttachment
     */
    public function testLogout() {
        // $this->markTestSkipped('must be revisited.');
        $this->browse(function (Browser $browser) {
            $this->logout($browser);
        });
    }
}
