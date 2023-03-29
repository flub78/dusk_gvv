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

            $browser->assertSee('Compta');
            $browser->screenshot('login');

            $this->logout($browser);
            $browser->assertDontSee('Compta');
            $browser->assertSee('Utilisateur');
            $browser->screenshot('logout');
        });
    }
}
