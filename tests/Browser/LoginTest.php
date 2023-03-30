<?php

namespace Tests\Browser;

// use Illuminate\Foundation\Testing\DatabaseMigrations;
use Laravel\Dusk\Browser;
use Tests\GvvDuskTestCase;

class LoginTest extends GvvDuskTestCase {

    /**
     * A few checks on the home page
     *
     * @return void
     */
    public function testHome() {
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
    public function testFirstLogin() {
        $this->browse(function (Browser $browser) {

            $this->login($browser, 'testadmin', 'password');

            $this->IsLoggedIn($browser);
            $browser->screenshot('login');

            $this->logout($browser);
            $this->IsLoggedOut($browser);
            $browser->screenshot('logout');
        });
    }

    public function testAccess() {
        $this->browse(function (Browser $browser) {
            $this->login($browser, 'testadmin', 'password');
            $this->canAccess($browser, "vols_planeur/page", ['Compta', 'Planche']);
            $this->logout($browser);
        });
    }
}
