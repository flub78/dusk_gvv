<?php

namespace Tests\Browser;

use Laravel\Dusk\Browser;
use Tests\GvvDuskTestCase;
use Illuminate\Support\Facades\Log;


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

    /**
     * Returns the number of files in UPLOAD_DIR or -1 if UPLOAD_DIR does not exist
     */
    public function filesInUploadDir() {
        $upload_dir = getenv('INSTALL_DIR') . 'uploads/attachments/' . date('Y') . '/';
        if (is_dir($upload_dir)) {
            $files = scandir($upload_dir);
            $fileCount = count(array_diff($files, array('.', '..')));
            return $fileCount;
        }
        return -1;
    }

    // search the edit link of an accounting line
    public function searchEditLink($browser, $pattern, $year) {

        $browser->select('year', $year)
            ->assertSee($pattern)
            ->assertSee($year);

        $href = $this->getHrefFromTableRow($browser, $pattern);
        return $href;
    }

    /**
     * Test cases
     */

    /**
     * Check that the installation can be reset and installed
     *
     * @return void
     */
    public function testCheckInstallationProcedure() {
        parent::testCheckInstallationProcedure();
    }

    /**
     * Login
     * 
     * @depends testCheckInstallationProcedure
     */
    public function testCheckThatUserCanLogin() {
        // $this->markTestSkipped('must be revisited.');
        $this->browse(function (Browser $browser) {
            $this->login($browser, 'testadmin', 'password');
        });
    }

    /**
     * This function search two accounting lines
     * to which It will be possible to add attachments.
     */
    public function searchLines() {
        $this->browse(function (Browser $browser) {

            // store the attachment page url for later
            $urlAttach = $this->fullUrl("attachments");
            $this->attachments = $urlAttach;

            // Affiche les comptes de classe 606
            Log::debug("Affiche les comptes de classe 606");
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
            Log::debug("href: " . $href);

            $browser->visit($href)
                ->assertValue('input[name="desc"]', 'Essence plus huile');

            // extract the edit link from the table
            $line1 = $this->searchEditLink($browser, 'Chèque 413', '2023');
            $this->line1 = $line1;
            Log::debug("Ligne comptable : " . $line1);

            $browser->visit($line1)
                ->assertSee('Ecriture comptable')
                ->assertSee('Justificatifs');

            // ============================================================
            // Les comptes de banque http://gvv.net/comptes/page/512
            $url = $this->fullUrl("comptes/page/512");
            $browser->visit($url)
                ->assertSee('Balance des comptes Classe 512')
                ->assertSee("Affichage de l'élement 1 à 2 sur 2 éléments")
                ->assertSee('Banque');

            // Le compte Banque
            // <a href="http://gvv.net/compta/journal_compte/294">Banque</a>
            $browser->waitFor('a')
                ->assertSeeLink('Banque');
            $href = $browser->attribute("a[href*='journal_compte']", 'href');
            $browser->visit($href)
                ->assertValue('input[name="desc"]', 'Banque');

            // extract the edit link from the table
            $line2 = $this->searchEditLink($browser, 'Avance sur vols', '2023');
            $this->line2 = $line2;
        });
    }

    /**
     * testAttachmentCRUD
     * At this point two accounting lines have been identified
     * @depends testCheckThatUserCanLogin
     */
    public function testAttachmentCRUD() {

        $this->searchLines();

        $this->browse(function (Browser $browser) {

            $browser->visit($this->attachments)
                ->assertSee('Justificatifs')
                ->assertSee("Affichage de l'élement 0 à 0 sur 0 éléments");

            // Adding an attachment to the first line
            $browser->visit($this->line1);
            // click on the add icon
            $browser->click('a[href*="attachments/create"]')
                ->assertSee('Justificatifs');

            // $initial_file_count = $this->filesInUploadDir();
            // $fixtures_dir = getcwd() . "/tests/fixtures/";
        });
    }

    /**
     * Logout
     * @depends testAttachmentCRUD
     */
    public function testCheckThatUserCanLogout() {
        // $this->markTestSkipped('must be revisited.');
        $this->browse(function (Browser $browser) {
            $this->logout($browser);
        });
    }
}
