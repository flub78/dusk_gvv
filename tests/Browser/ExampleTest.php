<?php

namespace Tests\Browser;

use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

/**
 * Class ExampleTest
 * 
 * Check that the environment is working
 * 
 * php artisan dusk --color=always --browse tests/Browser/ExampleTest.php
 * 
 * @package Tests\Browser
 */
class ExampleTest extends DuskTestCase {


    /**
     * A basic browser test example.
     *
     * @return void
     */
    public function testBasicExample() {
        global $cnt;
        $this->browse(function (Browser $browser) {
            $browser->visit('http://aeroclub-abbeville.fr/')
                ->assertSee('Buigny');
            $browser->screenshot('Aeroclub');
        });
    }

    /**
     * A test to check access to the Google search page.
     *
     * @return void
     */
    public function testGoogleSearch() {
        global $cnt;

        $this->browse(function (Browser $browser) {
            $browser->visit('https://www.google.com/')
                ->assertSee('Google');
            $browser->screenshot('Google');
        });
    }

    /**
     * A test to check access to GVV on Oracle Cloud.
     */
    public function testGvv() {
        global $cnt;
        $this->browse(function (Browser $browser) {
            $browser->visit('https://gvv-live.flub78.net/index.php/')
                ->assertSee('GVV');
            $browser->screenshot('GVV');
        });
    }
}
