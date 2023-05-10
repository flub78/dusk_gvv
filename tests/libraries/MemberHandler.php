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

}