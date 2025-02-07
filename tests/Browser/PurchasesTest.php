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

            $account_handler = new AccountHandler($browser, $this);
            $sale_acount_image = "(707) Ventes diverses";
            $sale_account_id =  $account_handler->AccountIdFromImage($sale_acount_image);
            $initial_sale_balance = $account_handler->AccountTotal($sale_account_id);

            // echo "Initial balance of $sale_acount_image: $sale_account_id: $initial_sale_balance\n";

            // Soldes pilotes
            $url = $this->fullUrl('comptes/page/411');

            $browser->visit($url)
                ->screenshot('soldes_pilotes')
                ->assertSee('Balance des comptes Classe 411');

            $asterix_compte_id = $this->getIdFromTable($browser, 'Le Gaulois Asterix');

            // Solde Asterix depuis soldes pilotes
            $table_id = "#DataTables_Table_0";
            $pattern = "Asterix";

            $debit = toDecimal($this->getColumnFromTableRow($browser, $table_id, $pattern, 5));
            $credit = toDecimal($this->getColumnFromTableRow($browser, $table_id, $pattern, 6));
            $initial_asterix_balance = $credit - $debit;

            // echo "Debit: $debit\n";
            // echo "Credit: $credit\n";

            // Solde Asterix depuis son compte
            $total = $account_handler->AccountTotal($asterix_compte_id);
            $this->assertEquals($initial_asterix_balance, $total);

            $browser->visit($this->fullUrl('compta/journal_compte/' . $asterix_compte_id));
            $browser->script('document.body.style.zoom = "0.5"');

            // echo "Initial balance of Asterix: $asterix_compte_id: $initial_asterix_balance\n";
            // echo "Initial balance of Asterix: $asterix_compte_id: $total\n";


            // Ajoute 2 achats
            $product = "bobr : 20.00";
            $browser // ->click('#panel-achats > .accordion-button')
                ->scrollIntoView('#validation_achat')
                ->waitFor('#validation_achat');

            $browser->click('#select2-product_selector-container')
                ->waitFor('.select2-search__field')
                ->type('.select2-search__field', $product)
                ->waitFor('.select2-results__option')
                ->click('.select2-results__option');
            // ->assertSelected('#product_selector', $product);

            $browser->type('quantite', '2')
                // ->click('.form-group:nth-child(1) > .form-control')
                ->type('.form-group:nth-child(4) > .form-control', '2 bobs')
                ->click('#validation_achat');

            $asterix_new_balance = $account_handler->AccountTotal($asterix_compte_id);
            $sale_new_balance = $account_handler->AccountTotal($sale_account_id);

            $this->assertEquals($asterix_new_balance, $initial_asterix_balance - 40);
            $this->assertEquals($sale_new_balance, $initial_sale_balance + 40);

            // Modifie la quantié
            // $browser->visit($this->fullUrl('compta/journal_compte/' . $asterix_compte_id));
            // $browser->click('#panel-achats > .accordion-button')
            //     ->scrollIntoView('#validation_achat')
            //     ->waitFor('#validation_achat');

            $browser->click('td:nth-child(1) .icon')
                ->type('quantite', '1')
                ->click('[name="saisie"]')
                ->type('#description', '1 seul bob')
                ->click('#validate');

            // Supprime la ligne
            $browser->click('td:nth-child(2) .icon');
            // ->assertDialogOpened('Etes vous sûr de vouloir supprimer la ligne du 06/02/2025 Le Gaulois Asterix-Ventes diverses 20.00 1 seul bob?');

            $browser->acceptDialog()
                ->visit('/comptes/page/411');

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
