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

            // Général: "Balance générale des comptes section ULM"
            // Détaillé : "Balance détaillée des comptes section ULM"

            $browser->script('window.scrollTo(0, 0);');

            // Ensure filter accordion is closed, then open it
            $browser->waitFor('#filter_button')
                // ->click('#filter_button')
                // ->script('document.getElementById("filter_button").click();')
                ->with('#filter_button', function ($button) {
                    $button->click();
                })
                ->pause(500);

            // Wait for accordion to open
            $browser->script('window.scrollTo(0, 0);');

            // Verify filter accordion is now open
            // ->assertVisible('#panelsStayOpen-collapseOne')
            $browser->screenshot('filter_accordion_open')
                ->assertSee('Balance générale des comptes')
                ->assertSee('section Planeur');

            $this->logout($browser);
        });
    }
}
