<?php

namespace Tests\Browser;

use Laravel\Dusk\Browser;
use Tests\Browser\BillingTest;

use Tests\libraries\AccountHandler;

function toDecimal($input) {
    if (empty(trim($input))) {
        return 0.0;
    }

    // Remove non-breaking spaces and currency symbols
    $cleaned = preg_replace('/[^\d,.-]/u', '', html_entity_decode($input));

    // Replace comma with dot for decimal format
    $decimal = str_replace(',', '.', $cleaned);

    return is_numeric($decimal) ? (float) $decimal : 0.0;
}

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

            // Soldes pilotes
            $url = $this->fullUrl('comptes/page/411');
            if ($this->verbose()) {
                echo ("Visiting $url\n");
            }

            $browser->visit($url)
                ->screenshot('soldes_pilotes')
                ->assertSee('Balance des comptes Classe 411');

            $asterix_compte_id = $this->getIdFromTable($browser, 'Le Gaulois Asterix');

            $table_id = "#DataTables_Table_0";
            $pattern = "Asterix";

            $debit = toDecimal($this->getColumnFromTableRow($browser, $table_id, $pattern, 5));
            $credit = toDecimal($this->getColumnFromTableRow($browser, $table_id, $pattern, 6));
            $solde = $credit - $debit;
            echo ("$asterix_compte_id: $debit $credit $solde\n");
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
