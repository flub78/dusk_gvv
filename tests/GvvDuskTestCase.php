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
            ->waitForText('Utilisateur')
            ->waitForText('Peignot')
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

    public function canAccess($browser, $suburl, $mustFind = [], $mustNotFind = [], $inputValues=[]) {
        $url = $this->url . 'index.php/' . $suburl;
        if ($this->verbose()) echo ("Visiting $url\n");
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

    public function canAccessTest($browser, $suburl, $mustFind = [], $mustNotFind = [], $inputValues=[]) {
        $url = $this->url . 'index.php/' . $suburl;
        if ($this->verbose()) echo ("Visiting $url\n");
        $browser->visit($url)->waitForText('Line Number');

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
        var select = document.getElementById('" . $id ."');
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

    /** 
     * Check that an account code exists.
     * 
     * The account code list is extracted from the dropdown select of the comptes/create page.
     */
    public function AccountCodeExists($browser, $codec) {
        $selectValues = $this->geyValuesFromSelect($browser, "comptes/create", "codec");

        $code = $codec['codec'];
        $desc = $codec['desc'];
        $str = "$code $desc";

        foreach($selectValues as $key => $name) {
            if ($name == $str) {
                return true;
            }
        }
        return false;
    }

    /** 
     * Create accounts
     */
    public function CreateAccountCodes($browser, $list = []) {
        foreach ($list as $element) {
            if (!$this->AccountCodeExists($browser, $element)) {
                // Create element
                $this->canAccess($browser, "plan_comptable/create", ['Nouveau code comptable']);
                $browser
                ->type('pcode', $element['codec'])
                ->type('pdesc', $element['desc'])
                ->press('#validate')
                ->assertSee('Plan comptable');
            }
            $this->assertTrue($this->AccountCodeExists($browser, $element), 
                "code comptable exists: (" . $element['codec'] . ')' . $element['desc']);
        }
    }

    /** 
     * Check that an account exists.
     * 
     * As account IDs are not public (they are generated by the database), we check that the account name is present in a select.
     */
    public function AccountExists($browser, $account) {
        // the page to create an accounting line contains a select with all the accounts
        $selectValues = $this->geyValuesFromSelect($browser, "compta/create", "compte1");

        $codec = $account['codec'];
        $nom = $account['nom'];
        $str = "($codec) $nom";

        foreach($selectValues as $key => $name) {
            if ($name == $str) {
                return true;
            }
        }
        return false;
    }

    /** 
     * Create accounts
     */
    public function CreateAccounts($browser, $accounts = []) {
        foreach ($accounts as $account) {
            if (!$this->AccountExists($browser, $account)) {
                // Create account
                $this->canAccess($browser, "comptes/create", ['Compte']);
                $browser
                ->type('nom', $account['nom'])
                ->type('desc', $account['comment'])
                ->select('codec', $account['codec'])
                ->press('#validate')
                ->assertSee('Balance');
            }
            $this->assertTrue($this->AccountExists($browser, $account), 
                "account exists: (" . $account['codec'] . ')' . $account['nom']);
        }
    }
}
