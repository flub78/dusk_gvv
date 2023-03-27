<?php

namespace Tests\Browser;

use Illuminate\Foundation\Testing\DatabaseMigrations;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class LoginTest extends DuskTestCase
{
    /**
     * A Dusk test example.
     *
     * @return void
     */
    public function testExample()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('https://gvv.flub78.net/gvv/')
                    ->assertSee('GVV')
                    ->assertSee('Boissel')
                    ->assertSee('Peignot');        ;   
        });
    }

        /**
     * A Dusk test example.
     *
     * @return void
     */
    public function testExample2()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('https://gvv.flub78.net/gvv/')
                    ->assertSee('Utilisateur')
                    ->type('username', 'testadmin')
                    ->type('password', 'password')
                    ->press('input[type="submit"]');

            sleep(2);

            $browser->assertSee('Compta');

            $browser->screenshot('gvv_in');

        });
    }
}
