<?php

namespace Tests\Browser;

use Laravel\Dusk\Browser;
use Tests\GvvDuskTestCase;

/**
 * Test Terrain CRUD
 * 
 * php artisan dusk --color=always --browse tests/Browser/TerrainTest.php
 */
class TerrainTest extends GvvDuskTestCase {

    /** Constructor */
    function __construct() {
        parent::__construct();
        
        $this->terrains = [
            ['oaci' => "LFAA", 'nom' => "Trifouillis", 'freq1' => "123.45", 'comment' => "Mon terrain"],
            ['oaci' => "LFAB", 'nom' => "Les Oies", 'freq1' => "123.45", 'comment' => "Mon second terrain"]
        ];

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
                ->press('#validate')
                ->assertSee("L'élément existe déjà");

            $this->canAccess($browser, "terrains/page", ['Compta', 'Terrains']);
            $new_total = $this->TableTotal($browser);

            $this->assertEquals($total, $new_total, "No Terrain created, total = " . $new_total);
        }
    }

    /**
     * Test Creation, Read, Update and Delete of a Terrain
     */
    public function testTerrainCRUD() {
        $this->browse(function (Browser $browser) {

            $this->login($browser, 'testadmin', 'password');

            $this->canAccess($browser, "terrains/page", ['Compta', 'Terrains']);

            $terrains = [
                ['oaci' => "LFAA", 'nom' => "Trifouillis", 'freq1' => "123.45", 'comment' => "Mon terrain"],
                ['oaci' => "LFAB", 'nom' => "Les Oies", 'freq1' => "123.45", 'comment' => "Mon second terrain"]
            ];

            $total = $this->TableTotal($browser);
            $this->assertGreaterThan(0, $total, "Terrains Table contains some entries");

            $this->createTerrain($browser, $terrains);
            $this->createTerrainError($browser, $terrains);
            $this->deleteTerrain($browser, $terrains);

            $this->logout($browser);
        });
    }

    /**
     * For test readibility, it is easier to rely on test sequence.
     * Test independence can be achieved at the class level
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

    public function testCreate() {
        // $this->markTestSkipped('must be revisited.');
        $this->browse(function (Browser $browser) {

            $this->canAccess($browser, "terrains/page", ['Compta', 'Terrains']);
            $total = $this->TableTotal($browser);

            $this->assertEquals(12, $total, "Terrains Table contains some entries");
            $this->createTerrain($browser, $this->terrains);
            $this->canAccess($browser, "terrains/page", ['Compta', 'Terrains']);
            $this->assertEquals(14, $this->TableTotal($browser), "Terrains Table contains some entries");
        });        
    }

    public function testDelete() {
        // $this->markTestSkipped('must be revisited.');
        $this->browse(function (Browser $browser) {
            $this->canAccess($browser, "terrains/page", ['Compta', 'Terrains']);
            $this->assertEquals(14, $this->TableTotal($browser), "Terrains Table contains some entries");
            // $this->deleteTerrain($browser, $this->terrains);
        });        
    }

    /**
     * Logout
     */
    public function testLogout() {
        // $this->markTestSkipped('must be revisited.');
        $this->browse(function (Browser $browser) {
            $this->logout($browser);
        });
    }
}
