<?php

namespace Tests\Browser;

use Laravel\Dusk\Browser;
use Tests\Browser\BillingTest;

use Tests\libraries\AccountHandler;

/*
 * 
 * Purchase tests
 * 
 *  On peut acheter 2 items
 *  Le compte du pilote est débité du prix x 2
 *  Le compte correspondant est crédité du prix x 2
 *  On peut ramener la quantité à 1
 *  Le compte du pilote est débité du prix
 *  Le compte correspondant est crédité du prix
 *  On peut supprimer l'achat
 *  Le compte pilote est ramené à son niveau initial
 *  le compte produit est ramené à son niveau initial
 */

class PurchasesTest extends BillingTest {

    /**
     * Check that the installation can be reset and installed
     *
     * @return void
     */
    public function testInit() {
        $this->assertTrue(true);

        // parent::testInit();
    }

    /**
     * Login
     * 
     * @depends testInit
     */
    public function testLogin() {
        parent::testLogin();
    }


    /**
     * Checks that a purchase is billed correctly
     * 
     * @depends testLogin
     */
    public function testBilling() {
        $this->browse(function (Browser $browser) {
            $this->assertTrue(true);

            $account_handler = new AccountHandler($browser, $this);
            $asterix_acount_image = "(411) Le Gaulois Asterix";
            $launch_acount_image = "(706) Remorqués";

            // 'url' => 'comptes/page/411'
            // $this->canAccess($browser, $page['url'], $ms, $mns, $page['inputValues'] ?? []);

            // Soldes pilotes
            $url = $this->fullUrl('comptes/page/411');
            if ($this->verbose()) {
                echo ("Visiting $url\n");
            }

            $browser->visit($url)
                ->screenshot('soldes_pilotes')
                ->assertSee('Balance des comptes Classe 411');

            $asterix_compte_id = $this->getIdFromTable($browser, 'Le Gaulois Asterix');

            $debit = "";
            $credit = "";
            $this->assertEquals($asterix_compte_id, 305);


            $browser->screenshot('debugging');


            $table_id = "#DataTables_Table_0";
            $pattern = "Asterix";
            $index = 6;

            $result = $browser->script(
                "return (function(tableId, pattern, index) {
                    const selector = tableId + ' tbody tr';
                    const row = Array.from(document.querySelectorAll(selector)).find(
                        row => row.textContent.includes(pattern)
                    );
                    return {
                        debug: {receivedTableId: tableId, receivedPattern: pattern, receivedIndex: index},
                        result: row?.querySelector('td:nth-child(' + index + ')')?.innerHTML
                    };
                })(
                    " . json_encode($table_id) . ",
                    " . json_encode($pattern) . ",
                    " . json_encode($index) . "
                );"
            )[0];


            //                 const table_id = "#DataTables_Table_0";
            // const pattern = "Asterix";
            //const index = 6;
            var_dump($result);
            // echo ("HTML: $result\n");

        }); // end of browse callback
    }



    /**
     * Logout
     * @depends testBilling
     */
    public function testLogout() {
        parent::testLogout();
    }
}
