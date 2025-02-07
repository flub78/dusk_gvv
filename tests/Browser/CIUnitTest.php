<?php

/**
 * A Browser test to check CI Unit Tests
 */

namespace Tests\Browser;

use Laravel\Dusk\Browser;
use Tests\GvvDuskTestCase;

class CIUnitTest extends GvvDuskTestCase {


    public function testCheckUnitTestsExecution() {
        $this->browse(function (Browser $browser) {

            $user = "testadmin";
            $password = "password";
            $mustSee = ['Test Name'];
            $mustNotSee = ['Error', 'Exception', 'Fatal error', '404 Page not found', 'Failed'];

            $pages = [
                ['url' => 'tests/test_helpers', 'mustSee' => ['Passed']],
                //        ['url' => 'tests/test_libraries', 'mustSee' => ['Passed']],
                ['url' => 'achats/test', 'mustSee' => ['Passed']],
                ['url' => 'admin/test', 'mustSee' => ['Passed']],
                ['url' => 'categorie/test', 'mustSee' => ['Passed']],
                ['url' => 'compta/test', 'mustSee' => ['Passed']],
                ['url' => 'comptes/test', 'mustSee' => ['Passed']],
                ['url' => 'event/test', 'mustSee' => ['Passed']],
                ['url' => 'licences/test', 'mustSee' => ['Passed']],
                ['url' => 'membre/test', 'mustSee' => ['Passed']],
                ['url' => 'plan_comptable/test', 'mustSee' => ['Passed']],
                //['url' => 'planeur/test', 'mustSee' => ['Passed']],
                ['url' => 'pompes/test', 'mustSee' => ['Passed']],
                ['url' => 'rapports/test', 'mustSee' => ['Passed']],
                ['url' => 'tarifs/test', 'mustSee' => ['Passed']],
                ['url' => 'tickets/test', 'mustSee' => ['Passed']],
                ['url' => 'types_ticket/test', 'mustSee' => ['Passed']],
            ];

            $this->login($browser, $user, $password);

            foreach ($pages as $page) {
                $ms = array_merge($mustSee, $page['mustSee']);
                $this->canAccessTest($browser, $page['url'], $ms, $mustNotSee, $page['inputValues'] ?? []);
            }

            $browser->visit($this->fullUrl('calendar'));

            $this->logout($browser);
        });
    }
}
