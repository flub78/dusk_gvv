<?php

namespace Tests;

use Tests\DuskTestCase;
use Tests\Browser\Pages\Login;
use Exception;

class GvvDuskTestCase extends DuskTestCase {

    public $url;

    function __construct() {
        parent::__construct();
        $this->url = env('TARGET', 'https://gvv.flub78.net/gvv/');
    }

    /**
     * Login as a user.
     */
    public function login($browser, $username, $password) {
        $browser->visit(new Login)
            ->screenshot('before_login')
            ->waitForText('Utilisateur')
            ->waitForText('Peignot')
            ->type('username', $username)
            ->type('password', $password)
            ->press('input[type="submit"]')
            ->screenshot('after_login')
            ->assertSee('Planeurs');

        sleep(2);
    }

    /**
     * Logout as a user.
     */
    public function logout($browser) {
        $browser->click('@user_icon')
            ->clickLink('Quitter')
            ->waitForText('Utilisateur')
            ->assertSee('Utilisateur');
    }

    public function IsLoggedIn($browser) {
        $browser->assertSee('Compta');
    }

    public function IsLoggedOut($browser) {
        $browser->assertDontSee('Planeurs');
        $browser->assertSee('Utilisateur');
    }

    /**
     * Checks if the user can access a page.
     */
    public function canAccess($browser, $suburl, $mustFind = [], $mustNotFind = [], $inputValues = []) {
        $url = $this->url . 'index.php/' . $suburl;
        if ($this->verbose()) {
            echo ("Visiting $url\n");
        }
        $browser->visit($url)
            ->waitForText('Peignot');

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
     * Checks if the user can access a test page.
     */
    public function canAccessTest($browser, $suburl, $mustFind = [], $mustNotFind = [], $inputValues = []) {
        $url = $this->url . 'index.php/' . $suburl;
        if ($this->verbose()) echo ("Visiting $url\n");
        $browser->visit($url);
        sleep(2);

        foreach ($mustFind as $str) {
            if ($this->verbose()) echo ($suburl . ': assertSee: ' . $str . "\n");
            $browser->assertSee($str);
        }
        foreach ($mustNotFind as $str) {
            if ($this->verbose()) echo ($suburl . ': assertDontSee: ' . $str . "\n");
            $browser->assertDontSee($str);
        }
        $browser->screenshot('page_' . str_replace('/', '_', $suburl));
    }

    /**
     * Get json data from a page.
     */
    public function getJsonData($browser, $suburl) {

        $url = $this->url . 'index.php/' . $suburl;
        
        // if ($this->verbose()) echo ("Visiting $url\n");

        // $browser->visit($url);

        // $json = $browser->script('return JSON.stringify(window.data);')[0];
        echo "url = $url\n";
        $content = file_get_contents($url);
        echo "content = $content\n";
        $json = json_decode($content, true);
        var_dump($json);

        return $json;
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

    /*
     * Extract the values of a select from an HTML page
     * returns an array of values => text
     */
    public function geyValuesFromSelect($browser, $page, $id) {
        $this->canAccess($browser, $page, []);

        $js = "
        var result = [];
        var select = document.getElementById('" . $id . "');
        var options = select.options;
        for (var i = 0; i < options.length; i++) {
            text = options[i].text;
            value = options[i].value;
            result.push(value + ',' + text);
        }
        return result;";

        $sel = [];
        $results = $browser->script($js)[0];
        foreach ($results as $result) {
            $values = explode(',', $result);
            $sel[$values[0]] = $values[1];
        }
        return $sel;
    }

}
