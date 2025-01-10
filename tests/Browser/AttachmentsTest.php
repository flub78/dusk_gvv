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

    /**
     * Test cases
     */

    /**
     * Login
     */
    public function testLogin() {
        // $this->markTestSkipped('must be revisited.');
        $this->browse(function (Browser $browser) {
            $this->login($browser, 'testadmin', 'password');
        });
    }

    /**
     * Test create
     * @depends testLogin
     */
    public function testCreate() {
        // $this->markTestSkipped('must be revisited.');
        $this->browse(function (Browser $browser) {

            $total = $this->TableTotal($browser, "terrains/page");
            $this->assertGreaterThan(0, $total, "Terrains Table contains some entries");

            $this->createTerrain($browser, $this->terrains);

            $new_total = $this->TableTotal($browser, "terrains/page", ["Terrains"]);
            $this->assertEquals($new_total, $total + count($this->terrains), "Terrains Table contains more entries");
        });
    }

    /**
     * Test create errors
     * @depends testCreate
     */
    public function testCreateErrors() {
        $this->browse(function (Browser $browser) {
            $this->createTerrainError($browser, $this->terrains);
        });
    }

    /**
     * Test delete
     * @depends testCreateErrors
     */
    public function testDelete() {
        $this->browse(function (Browser $browser) {

            $total = $this->TableTotal($browser, "terrains/page");

            $this->deleteTerrain($browser, $this->terrains);

            $new_total = $this->TableTotal($browser, "terrains/page", ["Terrains"]);
            $this->assertEquals($new_total, $total - count($this->terrains), "Terrains Table contains less entries");
        });
    }

    /**
     * Logout
     * @depends testDelete
     */
    public function testLogout() {
        // $this->markTestSkipped('must be revisited.');
        $this->browse(function (Browser $browser) {
            $this->logout($browser);
        });
    }
}
