<?php

namespace Tests;

use Tests\DuskTestCase;
use Tests\Browser\Pages\Login;
use Exception;
use PHPUnit\Framework\Assert;
use Laravel\Dusk\Browser;


class GvvDuskTestCase extends DuskTestCase {

    public $url;

    function __construct() {
        parent::__construct();
        $this->url = env('TARGET');
    }

    /**
     * Login as a user.
     */
    public function login($browser, $username, $password, $section = "") {

        Assert::assertNotEmpty($username, "TEST_USER env var is not set");
        Assert::assertNotEmpty($password, "TEST_PASSWORD env var is not set");
        Assert::assertNotEmpty(env('TARGET'), "TARGET env var is not set");

        $browser->visit(new Login)
            ->screenshot('before_login')
            ->waitForText('Utilisateur')
            ->waitForText('Mot de passe')
            ->waitForText('Peignot')
            ->type('username', $username)
            ->type('password', $password);

        if ($section != "") {
            $browser->select('section', $section);
        }

        $browser->press('input[type="submit"]')
            ->screenshot('after_login')
            ->assertSee('Planeurs');

        sleep(2);
    }

    /**
     * Logout as a user.
     */
    public function logout($browser) {

        $url = $this->fullUrl('auth/logout');

        $browser->visit($url)
            // it's a detail but
            // the commented alternative look for the user icon and submenu
            // they are not visible without a working Internet connection
            // Invoking directly the logout url is more robust
            // $browser->click('@user_icon')
            //     ->clickLink('Quitter')
            ->waitForText('Utilisateur')
            ->assertSee('Utilisateur')
            ->assertSee('Mot de passe');
    }

    public function IsLoggedIn($browser) {
        $browser->assertSee('Membres')
            ->assertSee('Planeurs');
    }

    public function IsLoggedOut($browser) {
        $browser->assertDontSee('Planeurs');
        $browser->assertSee('Utilisateur')
            ->assertSee('Mot de passe');
    }

    public function fullUrl($suburl) {
        return $this->url . 'index.php/' . $suburl;
    }

    /**
     * Checks if the user can access a page.
     */
    public function canAccess($browser, $suburl, $mustFind = [], $mustNotFind = [], $inputValues = []) {
        $url = $this->fullUrl($suburl);
        if ($this->verbose()) {
            echo ("Visiting $url\n");
        }
        $browser->storeConsoleLog('console1.log');
        $browser->storeSource('source1.html');
        $browser->visit($url);
        $browser->pause(2000);  // wait for 2 seconds

        // You can also scroll by pixels
        $browser->script('window.scrollBy(0, 500);'); // Scroll down 500 pixels

        $browser->script('window.scrollTo(0, document.body.scrollHeight);');

        $browser->pause(1000);  // Wait for 1 second after scrolling

        $browser->waitForText('Peignot', 10);
        $browser->storeConsoleLog('console2.log');

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
        $url =  $this->fullUrl($suburl);
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

        $url =  $this->fullUrl($suburl);

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

        $counter_selector = '#DataTables_Table_0_info';
        $browser->waitFor($counter_selector);
        $browser->scrollIntoView($counter_selector);
        // TODO wait for the real event
        $browser->pause(1000);
        $counter = $browser->text($counter_selector);
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

    /**
     * Check that the installation can be reset and installed
     * Reset the dusk test data.
     *
     * @return void
     */
    public function testCheckInstallationProcedure() {
        $this->browse(function (Browser $browser) {

            $browser->visit($this->url . 'install/reset.php')
                ->assertSee("Verification de l'installation")
                ->assertSee($this->url . 'install');

            $browser->visit($this->url . 'install/?db=dusk_tests.sql');

            $browser->assertSee('Installation de GVV')
                ->assertSee("Fin de la procédure d'installation");

            $this->login($browser, env('TEST_USER'), env('TEST_PASSWORD'));
            $browser->visit($this->fullUrl('migration'))
                ->assertSee('Migration de la base de données')
                ->press("Valider")
                ->assertSee('à jour');

            // Check that the database contains expected data
            $this->assertEquals(3, $this->TableTotal($browser, "planeur/page"));
            $this->assertEquals(2, $this->TableTotal($browser, "avion/page"));
            $this->assertEquals(4, $this->TableTotal($browser, "membre/page"));
            $this->logout($browser);
        });
    }

    // Function to extract the href of the edit icon of a table row
    public function getHrefFromTableRow($browser, $pattern) {

        return $browser->script([
            "return document.evaluate(
                    \"//tr[contains(., '$pattern')]//td[1]//a\", 
                    document, 
                    null, 
                    XPathResult.FIRST_ORDERED_NODE_TYPE, 
                    null
                ).singleNodeValue.getAttribute('href');"
        ])[0];
    }


    // Function to extract the id of an element from the table view
    public function getIdFromTable($browser, $pattern) {
        $href = $this->getHrefFromTableRow($browser, $pattern);

        return basename($href);
    }

    /**
     * Function to extract a column value from a table row
     */
    public function getColumnFromTableRow($browser, $table_id, $pattern, $index) {

        $result = $browser->script(
            "return (function(tableId, pattern, index) {
                const selector = tableId + ' tbody tr';
                const row = Array.from(document.querySelectorAll(selector)).find(
                    row => row.textContent.includes(pattern)
                );
                return row?.querySelector('td:nth-child(' + index + ')')?.innerHTML;
            })(
                " . json_encode($table_id) . ",
                " . json_encode($pattern) . ",
                " . json_encode($index) . "
            );"
        )[0];
        return $result;
    }
}
