<?php

namespace Tests\Browser;

use Illuminate\Foundation\Testing\DatabaseMigrations;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class LoginTest extends DuskTestCase
{

    function __construct() {
		parent::__construct ();
		$this->url = "https://gvv.flub78.net/gvv/";
	}

    /**
     * A few checks on the home page
     *
     * @return void
     */
    public function testHome()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('https://gvv.flub78.net/gvv/')
                    ->assertSee('GVV')
                    ->assertSee('Boissel')
                    ->assertSee('Peignot');        ;   
        });
    }

    /**
     * An initial login
     *
     * @return void
     */
    public function testFirstLogin()
    {
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
