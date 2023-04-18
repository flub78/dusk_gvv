<?php

namespace Tests\Browser;

use Laravel\Dusk\Browser;
use Tests\GvvDuskTestCase;

class PlaneurTest extends GvvDuskTestCase {

    public function testAccess() {
        $this->browse(function (Browser $browser) {
            
            $this->login($browser, 'testadmin', 'password');

            $this->canAccess($browser, "planeur/page", ['Compta', 'Planeurs']);

            $counter = $browser->text('#DataTables_Table_0_info');
            echo "Counter: $counter";
            $pattern = '/(\d+) à (\d+) sur (\d+) éléments/';
            if (preg_match($pattern, $counter, $matches)) {
                $from = $matches[1];
                $to = $matches[2];
                $total = $matches[3];
                echo "From: $from, To: $to, Total: $total";
            } else {
                echo "No match";
            }

            $this->logout($browser);
        });
    }
}
