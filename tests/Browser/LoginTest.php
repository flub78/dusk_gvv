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
            $browser->visit('https://aviation.meteo.fr/login.php#debut_page')
                    ->assertSee('AEROWEB');

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
            $browser->visit('https://aviation.meteo.fr/login.php#debut_page')
                    ->assertSee('Bienvenue')
                    ->type('login', 'flubber')
                    ->type('password', env('AERO_PASSWORD'))
                    ->press('input[type="submit"]');

            sleep(2);

            $browser->assertSee('TEMSI-WINTEM');

            $browser->screenshot('aeroweb_in');

        });
    }
}
