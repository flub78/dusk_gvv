<?php

namespace Tests;

use Tests\DuskTestCase;

class GvvDuskTestCase extends DuskTestCase {

    function __construct() {
        parent::__construct();
        $this->url = env('TARGET', 'https://gvv.flub78.net/gvv/');
    }

    /**
     * Login as a user.
     */
    public function login($browser, $username, $password) {
        $browser->visit($this->url)
            ->assertSee('Utilisateur')
            ->type('username', $username)
            ->type('password', $password)
            ->press('input[type="submit"]');

        sleep(2);
    }

    /**
     * Logout as a user.
     */
    public function logout($browser) {
        // $browser->visit($this->url . "index.php/auth/logout");

        $browser->click('@user_icon')
            ->clickLink('Quitter');
    }
}
