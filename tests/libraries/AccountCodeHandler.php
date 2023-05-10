<?php

namespace Tests\libraries;

/**
 * This class manages the chart of accounts in the Dusk tet context.
 * 
 * An account code handler is an object to access account code data through a browser and a Dusk test context.
 * 
 * The persistence is managed by the WEB application and as we only have access to the WEB interface methods to retreive the information may be indirect.
 */

 class AccountCodeHandler {

    private $browser;
    private $tc;

    /** Constructor */
    function __construct($browser, $test_context) {
        $this->browser = $browser;
        $this->tc = $test_context;
    }

        /** 
     * Check that an account code exists.
     * 
     * The account code list is extracted from the dropdown select of the comptes/create page.
     */
    public function AccountCodeExists($codec) {
        $selectValues = $this->tc->geyValuesFromSelect($this->browser, "comptes/create", "codec");

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
    public function CreateAccountCodes($list = []) {
        foreach ($list as $element) {
            if (!$this->AccountCodeExists($element)) {
                // Create element
                $this->tc->canAccess($this->browser, "plan_comptable/create", ['Nouveau code comptable']);
                $this->browser
                    ->type('pcode', $element['codec'])
                    ->type('pdesc', $element['desc'])
                    ->press('#validate');
            }
            $this->tc->assertTrue(
                $this->AccountCodeExists($element),
                "code comptable exists: (" . $element['codec'] . ')' . $element['desc']
            );
        }
    }


}