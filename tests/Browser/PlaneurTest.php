<?php

namespace Tests\Browser;

use Laravel\Dusk\Browser;
use Tests\GvvDuskTestCase;
use Tests\libraries\GliderHandler;


class PlaneurTest extends GvvDuskTestCase {


    public function testCheckBasicAccessFvorConnectedUsers() {

        $this->browse(function (Browser $browser) {

            $glider = [
                'immat' => 'F-CGAA',
                'type' => 'Ask21',
                'nb_places' => '2',
                'construct' => 'Alexander Schleicher',
                'prix' => 'hdv-planeur',
                'prix_forfait' => 'hdv-planeur-forfait'
            ];


            $glider_handler = new GliderHandler($browser, $this);

            $this->login($browser, 'testadmin', 'password');

            $this->canAccess($browser, "planeur/page", ['Compta', 'Planeurs']);
            $initial_total = $this->PageTableRowCount($browser);

            $glider_handler->CreateGliders([$glider]);

            $this->canAccess($browser, "planeur/page", ['Compta', 'Planeurs']);
            $new_total = $this->PageTableRowCount($browser);

            $this->assertGreaterThanOrEqual($initial_total, $new_total);

            $this->logout($browser);
        });
    }
}
