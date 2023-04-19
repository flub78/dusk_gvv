<?php

namespace Tests\Browser;

use Laravel\Dusk\Browser;
use Tests\GvvDuskTestCase;

class PlaneurTest extends GvvDuskTestCase {

    public function createGlider($browser) {

        $this->canAccess($browser, "planeur/create", ['Planeur']);

        $glider = ['mpconstruc' => 'Test Glider', 'type' => 'planeur', 'seats' => 1, 'weight' => 100, 'max_weight' => 100, 'max_fuel' => 100, 'max_fuel_type' => 'L', 'max_fuel_weight' => 100, 'max_fuel_weight_type' => 'L'];

        $browser
            ->type('mpconstruc', "construction amateur")  
            ->select('mpmodele', "planeur")
            ->type('mpimmat', "F-TEST")
            ->type('mpnumc', "UP")
            ->type('mpbiplace', 2)
            ->check('mptreuil')
            ->press('submit')
            ->assertSee('Enregistré');
    }

    public function testAccess() {
        $this->browse(function (Browser $browser) {

            $this->login($browser, 'testadmin', 'password');

            $this->canAccess($browser, "planeur/page", ['Compta', 'Planeurs']);

            $total = $this->TableTotal($browser);
            $this->assertEquals(0, $total, "Glider Table should be empty"); 

            $this->logout($browser);
        });
    }
}
