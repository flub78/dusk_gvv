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

    public function IsLoggedIn($browser) {
        $browser->assertSee('Compta');
    }

    public function IsLoggedOut($browser) {
        $browser->assertDontSee('Compta');
        $browser->assertSee('Utilisateur');
        // $browser->assertSee('@user_icon');
    }

    public function canAccess($browser, $suburl, $mustFind = [], $mustNotFind = [], $inputValues=[]) {
        $browser->visit($this->url . 'index.php/' . $suburl);

        foreach ($mustFind as $str) {
            if ($this->verbose()) echo ($suburl . ': assertSee: ' . $str . "\n");
            $browser->assertSee($str);
        }
        foreach ($mustNotFind as $str) {
            if ($this->verbose()) echo ($suburl . ': assertDontSee: ' . $str . "\n");
            $browser->assertDontSee($str);
        }
        foreach ($inputValues as $field) {
            if ($this->verbose()) echo ($suburl . ': assertInput: ' . $field['selector'] . ', ' . $field['value'] .  "\n");
            $browser->assertInputValue($field['selector'], $field['value']);
        }

        $browser->screenshot('page_' . str_replace('/', '_', $suburl));

    }

    /**
     * Checks if the test runs in verbose mode.
     */
    public function verbose() {
        global $argv;
        if (in_array('--verbose', $argv)) return true;
        if (in_array('-v', $argv)) return true;
        return false;
    }

}
