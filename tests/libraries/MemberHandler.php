<?php

namespace Tests\libraries;

/**
 * This class manages members in the Dusk tet context.
 * 
 * A member handler is an object to access member data through a browser and a Dusk test context.
 * 
 * The persistence is managed by the WEB application and as we only have access to the WEB interface methods to retreive the information may be indirect.
 */

class MemberHandler {

    private $browser;
    private $tc;

    /** Constructor */
    function __construct($browser, $test_context) {
        $this->browser = $browser;
        $this->tc = $test_context;
    }

    /** 
     * Check that a member exists.
     */
    public function MemberExists($member) {
        $selectValues = $this->tc->geyValuesFromSelect($this->browser, "comptes/create", "pilote");

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
    public function CreateMembers($list = []) {
        foreach ($list as $elt) {
            if (!$this->MemberExists($elt)) {

                // Create member
                $this->tc->canAccess($this->browser, "membre/create", ['Fiche de membre']);
                $this->browser
                    ->type('mlogin', $elt['id'])
                    ->type('mprenom', $elt['prenom'])
                    ->type('mnom', $elt['nom'])
                    ->type('memail', $elt['email'])
                    ->type('madresse', $elt['adresse']);

                // Sometimes I get the following error:
                // ElementClickInterceptedException: element click intercepted: Element is not clickable at point (57, 1633)
                $this->browser->script("window.scrollTo(57, 1635);");
                sleep(2);

                if (array_key_exists('treuillard', $elt)) {
                    // <input type="checkbox" name="mniveau[]" value="524288">
                    $this->browser->check('mniveau[]', '524288');
                }

                if (array_key_exists('remorqueur', $elt)) {
                    // <input type="checkbox" name="mniveau[]" value="8192">
                    $this->browser->check('mniveau[]', '8192');
                }

                if (array_key_exists('fi_avion', $elt)) {
                    $this->browser->check('mniveau[]', '131072');
                }

                if (array_key_exists('fe_avion', $elt)) {
                    $this->browser->check('mniveau[]', '262144');
                }

                if (array_key_exists('fi_planeur', $elt)) {
                    $this->browser->check('mniveau[]', '32768');
                }

                if (array_key_exists('fe_planeur', $elt)) {
                    $this->browser->check('mniveau[]', '65536');
                }

                $this->browser
                    ->type('comment', $elt['id'])
                    ->scrollIntoView('#validate')
                    ->waitFor('#validate')
                    ->press('#validate');
            }
            $this->tc->assertTrue(
                $this->MemberExists($elt),
                "member exists: " . $elt['id']
            );
        }
    }
}
