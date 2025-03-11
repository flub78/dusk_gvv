<?php

namespace Tests\Browser;

use Laravel\Dusk\Browser;
use Tests\GvvDuskTestCase;

/**
 * basic login test
 */
class LoginTest extends GvvDuskTestCase {

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
                ->assertSee('Peignot');
        });
    }

    /**
     * An initial login
     *
     * @return void
     */
    public function testCheckLoginAndLogout() {
        $this->browse(function (Browser $browser) {

            $this->login($browser, 'testadmin', 'password');

            $this->IsLoggedIn($browser);
            $browser->screenshot('login');

            $browser->click('#close_mod_dialog');

            $this->logout($browser);
            $this->IsLoggedOut($browser);
            $browser->screenshot('logout');
        });
    }

    public function testCheckBasicAccessFvorConnectedUsers() {
        $this->browse(function (Browser $browser) {
            $this->login($browser, 'testadmin', 'password');
            $this->canAccess($browser, "vols_planeur/page", ['Compta', 'Planche']);
            $this->logout($browser);
        });
    }
}
