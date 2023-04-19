<?php

namespace Tests\Browser;

use Laravel\Dusk\Browser;
use Tests\GvvDuskTestCase;

class TerrainTest extends GvvDuskTestCase {

    public function createTerrain($browser) {

        $this->canAccess($browser, "terrains/page", ['Compta', 'Terrains']);
        $total = $this->TableTotal($browser);

        $this->canAccess($browser, "terrains/create", ['Planeur']);

        $glider = ['mpconstruc' => 'Test Glider', 'type' => 'planeur', 'seats' => 1, 'weight' => 100, 'max_weight' => 100, 'max_fuel' => 100, 'max_fuel_type' => 'L', 'max_fuel_weight' => 100, 'max_fuel_weight_type' => 'L'];

        $browser
            ->type('oaci', "LFAA")
            ->type('nom', "Trifouillis")
            ->type('freq1', "123.45")
            ->type('comment', "Mon terrain")
            ->press('#validate')
            ->assertSee('Terrains');

        $this->canAccess($browser, "terrains/page", ['Compta', 'Terrains']);
        $new_total = $this->TableTotal($browser);

        $this->assertEquals($total + 1, $new_total, "Terrain created, total = " . $new_total);
    }

    public function deleteTerrain($browser) {

        $this->canAccess($browser, "terrains/page", ['Compta', 'Terrains']);
        $total = $this->TableTotal($browser);

        $this->canAccess($browser, "terrains/delete/" . "LFAA", ['Terrains']);
        $new_total = $this->TableTotal($browser);

        $this->assertEquals($total -1, $new_total, "Terrain deleted, total = " . $new_total);
    }

    public function createTerrainError($browser) {

        $this->canAccess($browser, "terrains/page", ['Compta', 'Terrains']);
        $total = $this->TableTotal($browser);

        $this->canAccess($browser, "terrains/create", ['Planeur']);

        $glider = ['mpconstruc' => 'Test Glider', 'type' => 'planeur', 'seats' => 1, 'weight' => 100, 'max_weight' => 100, 'max_fuel' => 100, 'max_fuel_type' => 'L', 'max_fuel_weight' => 100, 'max_fuel_weight_type' => 'L'];

        $browser
            ->type('oaci', "LFAA")
            ->type('nom', "Trifouillis")
            ->type('freq1', "123.45")
            ->type('comment', "Mon terrain")
            ->press('#validate')
            ->assertSee("L'élément existe déjà");

        $this->canAccess($browser, "terrains/page", ['Compta', 'Terrains']);
        $new_total = $this->TableTotal($browser);

        $this->assertEquals($total, $new_total, "No Terrain created, total = " . $new_total);
    }

    public function testTerrainCRUD() {
        $this->browse(function (Browser $browser) {

            $this->login($browser, 'testadmin', 'password');

            $this->canAccess($browser, "terrains/page", ['Compta', 'Terrains']);

            $total = $this->TableTotal($browser);
            $this->assertGreaterThan(0, $total, "Terrains Table contains some entries");

            $this->createTerrain($browser);
            $this->createTerrainError($browser);
            $this->deleteTerrain($browser);

            $this->logout($browser);
        });
    }
}
