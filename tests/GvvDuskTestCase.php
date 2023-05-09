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

    /**
     * Checks if the user can access a page.
     */
    public function canAccess($browser, $suburl, $mustFind = [], $mustNotFind = [], $inputValues = []) {
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

    /********************************************************************** */
    /* Chart of accounts */
    /********************************************************************** */

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

        foreach ($selectValues as $key => $name) {
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
                    ->press('#validate');
            }
            $this->assertTrue(
                $this->AccountCodeExists($browser, $element),
                "code comptable exists: (" . $element['codec'] . ')' . $element['desc']
            );
        }
    }

    /**********************************************************************/
    /* Accounts */
    /**********************************************************************/

    /** 
     * Find the account ID from its name
     * 
     * As account IDs are not public (they are generated by the database), we check that the account name is present in a select.
     */
    public function AccountIdFromName($browser, $account) {
        $selectValues = $this->geyValuesFromSelect($browser, "compta/create", "compte1");

        $codec = $account['codec'];
        $nom = $account['nom'];
        $str = "($codec) $nom";

        foreach ($selectValues as $key => $value) {
            if ($value == $str) {
                return $key;
            }
        }
        return -1;
    }

    /** 
     * Check that an account exists.
     */
    public function AccountExists($browser, $account) {
        return ($this->AccountIdFromName($browser, $account) != -1);
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
                    ->press('#validate');
            }
            $this->assertTrue(
                $this->AccountExists($browser, $account),
                "account exists: (" . $account['codec'] . ')' . $account['nom']
            );
        }
    }

    /**********************************************************************/
    /* Products */
    /**********************************************************************/

    /** 
     * Check that a product exists.
     * 
     * As product IDs are not public (they are generated by the database), we check that the product image is present in a select.
     */
    public function ProductExists($browser, $product) {
        $selectValues = $this->geyValuesFromSelect($browser, "planeur/create", "mprix");

        $ref = $product['ref'];
        $price = number_format($product['prix'], 2);
        $str = "$ref : $price";

        foreach ($selectValues as $key => $value) {
            if ($value == $str) {
                return true;
            }
        }
        return false;
    }

    /** 
     * Create products
     */
    public function CreateProducts($browser, $list = []) {
        foreach ($list as $elt) {
            if (!$this->ProductExists($browser, $elt)) {

                $account = [
                    'nom'   => $elt['account'],
                    'codec' => $elt['codec']
                ];
                $account_id = $this->AccountIdFromName($browser, $account);

                // Create product
                $this->canAccess($browser, "tarifs/create", ['Tarif']);
                $browser
                    ->type('reference', $elt['ref'])
                    ->type('description', $elt['description'])
                    ->type('prix', $elt['prix'])
                    ->select('compte', $account_id)
                    ->press('#validate');
            }
            $this->assertTrue(
                $this->ProductExists($browser, $elt),
                "product exists: (" . $elt['ref'] . ')' . $elt['prix']
            );
        }
    }

    /**********************************************************************/
    /* Gliders */
    /**********************************************************************/
    public function gliderImage($glider) {
        $res = $glider['type'] . ' - ' . $glider['immat'];
        if (array_key_exists('numc', $glider)) {
            $res .= ' - (' . $glider['numc'] . ')';
        }
        return $res;
    }

    /** 
     * Check that a glider exists.
     * 
     * As glider IDs are not public (they are generated by the database), we check that the glider image is present in a select.
     */
    public function GliderExists($browser, $glider) {
        $selectValues = $this->geyValuesFromSelect($browser, "vols_planeur/create", "vpmacid");

        $image = $this->gliderImage($glider);

        foreach ($selectValues as $key => $value) {
            if ($value == $image) {
                return true;
            }
        }
        return false;
    }

    /** 
     * Create gliders
     */
    public function CreateGliders($browser, $list = []) {
        foreach ($list as $elt) {
            if (!$this->GliderExists($browser, $elt)) {

                // Create product
                $this->canAccess($browser, "planeur/create", ['Planeur']);
                $browser
                    ->type('mpconstruc', $elt['construct'])
                    ->type('mpmodele', $elt['type'])
                    ->type('mpimmat', $elt['immat']);

                if (array_key_exists('numc', $elt)) {
                    $browser->type('mpnumc', $elt['numc']);
                }

                if (array_key_exists('prix', $elt)) {
                    $browser->select('mprix', $elt['prix']);
                }

                if (array_key_exists('prix_forfait', $elt)) {
                    $browser->select('mprix_forfait', $elt['prix_forfait']);
                }

                if (array_key_exists('prix_moteur', $elt)) {
                    $browser->select('mprix_moteur', $elt['prix_moteur']);
                }

                $browser->type('mpbiplace', $elt['nb_places'])
                    ->press('#validate');
            }
            $image = $this->gliderImage($elt);
            $this->assertTrue(
                $this->GliderExists($browser, $elt),
                "glider exists: " . $image
            );
        }
    }

    /**********************************************************************/
    /* Planes */
    /**********************************************************************/

    /** 
     * Check that a plane exists.
     * 
     * As plane IDs are not public (they are generated by the database), we check that the plane image is present in a select.
     */
    public function PlaneExists($browser, $plane) {
        $selectValues = $this->geyValuesFromSelect($browser, "vols_avion/create", "vamacid");

        foreach ($selectValues as $key => $value) {
            if ($value == $plane['immat']) {
                return true;
            }
        }
        return false;
    }

    /** 
     * Create planes
     */
    public function CreatePlanes($browser, $list = []) {
        foreach ($list as $elt) {
            if (!$this->PlaneExists($browser, $elt)) {

                // Create product
                $this->canAccess($browser, "avion/create", ['Avion']);
                $browser
                    ->type('macconstruc', $elt['construct'])
                    ->type('macmodele', $elt['type'])
                    ->type('macimmat', $elt['immat']);

                if (array_key_exists('prix', $elt)) {
                    $browser->select('maprix', $elt['prix']);
                }

                if (array_key_exists('prix_dc', $elt)) {
                    $browser->select('maprixdc', $elt['prix_dc']);
                }

                $browser->type('macplaces', $elt['nb_places'])
                    ->press('#validate');
            }
            $this->assertTrue(
                $this->PlaneExists($browser, $elt),
                "plane exists: " . $elt['immat']
            );
        }
    }

    /**********************************************************************/
    /* Members */
    /**********************************************************************/

    /** 
     * Check that a member exists.
     */
    public function MemberExists($browser, $member) {
        $selectValues = $this->geyValuesFromSelect($browser, "comptes/create", "pilote");

        foreach ($selectValues as $key => $value) {
            if ($value == $member['nom'] . ' ' . $member['prenom']) {
                return true;
            }
        }
        return false;
    }

    /** 
     * Create members
     */
    public function CreateMembers($browser, $list = []) {
        foreach ($list as $elt) {
            if (!$this->MemberExists($browser, $elt)) {

                // Create member
                $this->canAccess($browser, "membre/create", ['Fiche de membre']);
                $browser
                    ->type('mlogin', $elt['id'])
                    ->type('mprenom', $elt['prenom'])
                    ->type('mnom', $elt['nom'])
                    ->type('memail', $elt['email'])
                    ->type('madresse', $elt['adresse']);

                // Sometimes I get the following error:
                // ElementClickInterceptedException: element click intercepted: Element is not clickable at point (57, 1633)
                $browser->script("window.scrollTo(57, 1635);");
                sleep(2);
                
                if (array_key_exists('treuillard', $elt)) {
                    // <input type="checkbox" name="mniveau[]" value="524288">
                    $browser->check('mniveau[]', '524288');
                }

                if (array_key_exists('remorqueur', $elt)) {
                    // <input type="checkbox" name="mniveau[]" value="8192">
                    $browser->check('mniveau[]', '8192');
                }

                if (array_key_exists('fi_avion', $elt)) {
                    $browser->check('mniveau[]', '131072');
                }

                if (array_key_exists('fe_avion', $elt)) {
                    $browser->check('mniveau[]', '262144');
                }

                if (array_key_exists('fi_planeur', $elt)) {
                    $browser->check('mniveau[]', '32768');
                }

                if (array_key_exists('fe_planeur', $elt)) {
                    $browser->check('mniveau[]', '65536');
                }

                $browser
                    ->type('comment', $elt['id'])
                    // ->scrollIntoView('#validate') does not change anything
                    ->press('#validate');
            }
            $this->assertTrue(
                $this->MemberExists($browser, $elt),
                "member exists: " . $elt['id']
            );
        }
    }
}
