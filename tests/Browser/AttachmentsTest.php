<?php

namespace Tests\Browser;

use Laravel\Dusk\Browser;
use Tests\GvvDuskTestCase;

/**
 * Test Attachments
 * 
 * php artisan dusk --color=always --browse tests/Browser/AttachmentsTest.php
 * 
 * The tests rely on the methods order.est scenario is:
 * 
 * 1. reset the database to start with well defined data
 * 2. find the edit urls of two accounting lines
 * 3. add a text attachment to the first line
 * 4. add a pdf attachment to the first line
 * 5. add an image attachment to the second line
 * 6. add a big picture attachment to the second line
 * 
 * 7. Check that we have 4 more attachments
 * 8. Check that clicking attachments opens the attachment
 * 9. Check that the big picture has been compressed
 * 
 * 10. Replace the text attachment with another one
 * 11. Check that we get the second one when we click on it
 * 
 * 12. Delete the text attachment
 * 13. Check that we have 3 attachments in the storage
 * 14. And only 1 is displayed for the first line
 * 
 * 15. Delete all created attachments
 * 16. Check that we have 0 attachments
 * 
 */
class AttachmentsTest extends GvvDuskTestCase {

    /** Constructor */
    function __construct() {
        parent::__construct();

        $this->attachments = "";
        $this->line1 = "";
        $this->line2 = "";
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

    /**
     * Returns the number of files in UPLOAD_DIR or -1 if UPLOAD_DIR does not exist
     */
    public function filesInUploadDir() {
        if (getenv('UPLOAD_DIR') && is_dir(getenv('UPLOAD_DIR'))) {
            $files = scandir(getenv('UPLOAD_DIR'));
            $fileCount = count(array_diff($files, array('.', '..')));
            return $fileCount;
        }
        return -1;
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

    // search the edit link of an accounting line
    public function searchEditLink($browser, $pattern, $year) {

        $browser->select('year', $year)
            ->assertSee($pattern)
            ->assertSee($year);

        return $this->getHrefFromTableRow($browser, $pattern);
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
     * This test search the Dusk test database in order to find two accounting lines
     * to which It will be possible to add attachments.
     */
    public function searchLines() {
        $this->browse(function (Browser $browser) {

            // the global attachment page with no attachments
            $urlAttach = $this->fullUrl("attachments");
            $this->attachments = $urlAttach;

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

            // extract the edit link from the table
            $line1 = $this->searchEditLink($browser, 'Chèque 413', '2023');
            $this->line1 = $line1;

            $browser->visit($line1)
                ->assertSee('Ecriture comptable')
                ->assertSee('Justificatifs');

            // ============================================================
            // Les comptes de banque http://gvv.net/index.php/comptes/page/512
            $url = $this->fullUrl("comptes/page/512");
            $browser->visit($url)
                ->assertSee('Balance des comptes Classe 512')
                ->assertSee("Affichage de l'élement 1 à 2 sur 2 éléments")
                ->assertSee('Banque');

            // Le compte Banque
            // <a href="http://gvv.net/index.php/compta/journal_compte/294">Banque</a>
            $browser->waitFor('a')
                ->assertSeeLink('Banque');
            $href = $browser->attribute("a[href*='journal_compte']", 'href');
            $browser->visit($href)
                ->assertSee('Banque');

            // extract the edit link from the table
            $line2 = $this->searchEditLink($browser, 'Avance sur vols', '2023');
            $this->line2 = $line2;
        });
    }

    /**
     * testAttachmentCRUD
     * At this point two accounting lines have been identified
     * @depends testLogin
     */
    public function testAttachmentCRUD() {

        $this->searchLines();

        $this->browse(function (Browser $browser) {

            // echo "line1 = " . $this->line1 . "\n";
            // echo "line2 = " . $this->line2 . "\n";
            // echo "attachments = " . $this->attachments . "\n";

            $browser->visit($this->attachments)
                ->assertSee('Justificatifs')
                ->assertSee("Affichage de l'élement 0 à 0 sur 0 éléments");

            // Adding an attachment to the first line
            $browser->visit($this->line1);
            // click on the add icon
            $browser->click('a[href*="attachments/create"]')
                ->assertSee('Justificatifs');

            $initial_file_count = $this->filesInUploadDir();
            echo "file_count = " . $initial_file_count . "\n";
            $fixtures_dir = getcwd() . "/tests/fixtures/";
            echo "fixtures_dir = " . $fixtures_dir . "\n";
        });
    }

    /**
     * Logout
     * @depends testAttachmentCRUD
     */
    public function testLogout() {
        // $this->markTestSkipped('must be revisited.');
        $this->browse(function (Browser $browser) {
            $this->logout($browser);
        });
    }
}
