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

}