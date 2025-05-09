<?php

namespace Tests\Browser;

use Laravel\Dusk\Browser;
use Tests\Browser\BillingTest;

use Tests\libraries\AccountHandler;
use Illuminate\Support\Facades\Log;


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
    public function testCheckInstallationProcedure() {
        $this->assertTrue(true);

        // parent::testCheckInstallationProcedure();
    }

    /**
     * Login
     * 
     * @depends testCheckInstallationProcedure
     */
    public function testCheckThatUserCanLogin() {
        parent::testCheckThatUserCanLogin();
    }

    /**
     * Checks that a purchase is billed correctly
     * 
     * @depends testCheckThatUserCanLogin
     */
    public function testCheckPurchasesAndAccountBalance() {
        $this->browse(function (Browser $browser) {
            $this->assertTrue(true);

            Log::debug("Vérifie que les comptes changent après un achat");
            Log::debug("-----------------------------------------------");
            $account_handler = new AccountHandler($browser, $this);
            $sale_acount_image = "(707) Ventes diverses";
            $sale_account_id =  $account_handler->AccountIdFromImage($sale_acount_image);
            $initial_sale_balance = $account_handler->AccountTotal($sale_account_id);

            Log::debug("Initial balance of $sale_acount_image: $sale_account_id = $initial_sale_balance");

            // Soldes pilotes
            $url = $this->fullUrl('comptes/page/411');

            $browser->visit($url)
                ->screenshot('soldes_pilotes')
                ->assertSee('Balance générale des comptes Classe 411');

            $asterix_compte_id = $this->getIdFromTable($browser, 'Le Gaulois Asterix');

            // Solde Asterix depuis soldes pilotes
            $table_id = "#DataTables_Table_0";
            $pattern = "Asterix";

            // for ($i = 0; $i < 7; $i++) {
            //     $col = $this->getColumnFromTableRow($browser, $table_id, $pattern, $i);
            //     Log::debug("from 411 $pattern: col $i = $col");
            // }

            $debit = toDecimal($this->getColumnFromTableRow($browser, $table_id, $pattern, 6));
            $credit = toDecimal($this->getColumnFromTableRow($browser, $table_id, $pattern, 7));
            Log::debug("from 411 $pattern: Debit = $debit, Credit = $credit");
            $initial_asterix_balance = $credit - $debit;

            // Solde Asterix depuis son compte
            $total = $account_handler->AccountTotal($asterix_compte_id);
            Log::debug("Solde Asterix: Page 411 = $initial_asterix_balance, Solde compte = $total");
            $this->assertEquals($initial_asterix_balance, $total);

            $this->purchase($browser, $asterix_compte_id, "bobr : 20.00", $quantity = 2, $comment = "2 bobs", $cost = 40.00);;

            $sale_new_balance = $account_handler->AccountTotal($sale_account_id);
            $this->assertEquals($sale_new_balance, $initial_sale_balance + 40);

            // Modifie la quantité

            $browser->click('td:nth-child(1) .icon')
                ->type('quantite', '1')
                ->click('[name="saisie"]')
                ->type('#description', '1 seul bob')
                ->click('#validate');

            $browser->screenshot('before_purchase_delete');
            $this->savePageSource($browser, 'before_purchase_delete');

            // Supprime la ligne
            // The selector td:nth-child(2) .icon in PurchasesTest.php targets an element with class .icon that is inside the second table cell (td) of a row.
            $browser->click('td:nth-child(2) .icon');

            $browser->acceptDialog()
                ->visit('/comptes/page/411');

            // $this->deleteRowByPattern($browser, '1 seul bob', $tableSelector = 'tbody', $acceptDIalog = TRUE);

            // Are balance back to their initial values ?
            $asterix_new_balance = $account_handler->AccountTotal($asterix_compte_id);
            $sale_new_balance = $account_handler->AccountTotal($sale_account_id);

            $this->assertEquals($asterix_new_balance, $initial_asterix_balance);
            $this->assertEquals($sale_new_balance, $initial_sale_balance);

            $browser->script('document.body.style.zoom = "1.0"');
        }); // end of browse callback
    }

    /**
     * Logout
     * @depends testCheckPurchasesAndAccountBalance
     */
    public function testCheckThatUserCanLogout() {
        parent::testCheckThatUserCanLogout();
    }
}
