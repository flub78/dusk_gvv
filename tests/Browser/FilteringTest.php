<?php

namespace Tests\Browser;

use Laravel\Dusk\Browser;
use Tests\GvvDuskTestCase;

/**
 * Class Filtering
 * 
 * Check that the filtering mechanism works
 * 
 * php artisan dusk --color=always --browse tests/Browser/FilteringTest.php
 * 
 * @package Tests\Browser
 */
class FilteringTest extends GvvDuskTestCase {

    const PLANEUR = "1";
    const ULM = "2";
    const AVION = "3";
    const GENERAL = "4";
    const ALL = "5";

    /**
     * A basic browser test example.
     *
     * @return void
     */
    public function testBalanceFiltering() {
        global $cnt;
        $this->browse(function (Browser $browser) {

            $this->login($browser, env('TEST_USER'), env('TEST_PASSWORD'), self::PLANEUR);

            $planeur_codec_count = $this->PageTableRowCount($browser, "comptes/general") - 1;
            $this->assertGreaterThanOrEqual($planeur_codec_count, 16);


            $this->logout($browser);
        });
    }
}
