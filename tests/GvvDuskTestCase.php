<?php

namespace Tests;

use Tests\DuskTestCase;
use Tests\Browser\Pages\Login;
use Exception;

class GvvDuskTestCase extends DuskTestCase {

    function __construct() {
        parent::__construct();
        $this->url = env('TARGET', 'https://gvv.flub78.net/gvv/');
    }

    /**
     * Login as a user.
     */
    public function login($browser, $username, $password) {
        $browser->visit(new Login)
            ->type('username', $username)
            ->type('password', $password)
            ->press('input[type="submit"]')
            ->assertSee('Planeurs');

        sleep(2);
    }

    /**
     * Logout as a user.
     */
    public function logout($browser) {
        $browser->click('@user_icon')
            ->clickLink('Quitter')
            ->assertSee('Utilisateur');
    }

    public function IsLoggedIn($browser) {
        $browser->assertSee('Compta');
    }

    public function IsLoggedOut($browser) {
        $browser->assertDontSee('Compta');
        $browser->assertSee('Utilisateur');
    }

    public function canAccess($browser, $suburl, $mustFind = [], $mustNotFind = [], $inputValues=[]) {
        $url = $this->url . 'index.php/' . $suburl;
        if ($this->verbose()) echo ("Visiting $url\n");
        $browser->visit($url);

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

    /* returns the number of rows in the table */
    public function TableTotal($browser, $route = "", $mustSee = []) {

        if ($route != "") {
            $this->canAccess($browser, $route, $mustSee);
        }

        $counter = $browser->text('#DataTables_Table_0_info');
        // echo "Counter: $counter";
        $pattern = '/(\d+) à (\d+) sur (\d+) éléments/';
        if (preg_match($pattern, $counter, $matches)) {
            $from = $matches[1];
            $to = $matches[2];
            $total = $matches[3];
            // echo "From: $from, To: $to, Total: $total";
            return $total;
        } else {
            throw new Exception("No match for $pattern in $counter");
        }       
    }

}
