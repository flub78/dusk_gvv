<?php

namespace Tests\Browser;

use Laravel\Dusk\Browser;
use Tests\GvvDuskTestCase;

/**
 * Test of the message of the day
 */
class MotdTest extends GvvDuskTestCase {

    /**
     * A few checks on the home page
     *
     * @return void
     */
    public function testCheckThatItIToAccessHomePage() {
        $this->browse(function (Browser $browser) {
            $browser->visit($this->url)
                ->assertSee('GVV')
                ->assertSee('Boissel')
                ->assertSee('Peignot');;
        });
    }

    /**
     * An initial login
     *
     * @return void
     */
    public function testCheckLoginAndLogout() {
        $this->browse(function (Browser $browser) {

            $motd = 'Message du jour';

            $this->login($browser, 'testadmin', 'password');

            $this->IsLoggedIn($browser);

            // check that the message of the day is visible
            $browser->assertSee($motd)
                ->check('#no_mod')
                ->click('#close_mod_dialog');

            $this->logout($browser);
            $this->IsLoggedOut($browser);

            // login again
            $this->login($browser, 'testadmin', 'password');
            $browser->assertDontSee($motd);
            $this->logout($browser);
        });
    }
}
