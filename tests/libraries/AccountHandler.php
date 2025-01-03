<?php

namespace Tests\libraries;

/**
 * This class manages accounts in the Dusk tet context.
 * 
 * An account handler is an object to access plane data through a browser and a Dusk test context.
 * 
 * The persistence is managed by the WEB application and as we only have access to the WEB interface methods to retreive the information may be indirect.
 */

class AccountHandler {

    private $browser;
    private $tc;

    /** Constructor */
    function __construct($browser, $test_context) {
        $this->browser = $browser;
        $this->tc = $test_context;
    }

    /** 
     * Find the account ID from its name
     * 
     * As account IDs are not public (they are generated by the database), we check that the account name is present in a select.
     * 
     * memoisable
     */
    public function AccountIdFromName($account) {
        $selectValues = $this->tc->geyValuesFromSelect($this->browser, "compta/create", "compte1");

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
    public function AccountExists($account) {
        return ($this->AccountIdFromName($account) != -1);
    }

    /**
     * Find the account ID from its image
     * 
     * memoizable
     */
    public function AccountIdFromImage($image) {
        $selectValues = $this->tc->geyValuesFromSelect($this->browser, "compta/create", "compte1");

        foreach ($selectValues as $key => $value) {
            if ($value == $image) {
                return $key;
            }
        }
        return -1;
    }

    /**
     * Find the account ID from a member
     */
    public function AccountIdFromMember($member) {

        $image = "(411) " . $member['nom'] . " " . $member['prenom'];

        return $this->AccountIdFromImage($image);
    }

    /** 
     * Returns the amount of money in an account
     */
    public function AccountTotal($account_id) {
        $this->tc->canAccess($this->browser, "compta/journal_compte/" . $account_id, ["Compte", "Solde au", "débiteur", "créditeur"]);

        $debit = $this->browser->inputValue('current_debit');
        $credit = $this->browser->inputValue('current_credit');

        // be careful to non breaking spaces
        $search = [' ', '€', ',', chr(0xC2) . chr(0xA0)];
        $replace = ['', '', '.', ''];
        $debit = str_replace($search, $replace, $debit);
        $credit = str_replace($search, $replace, $credit);

        if ($debit == "") {
            $debit = 0.0;
        } else {
            $debit = floatval($debit);
        }
        if ($credit == "") {
            $credit = 0.0;
        } else {
            $credit = floatval($credit);
        }
        $total = $credit - $debit;
        return $total;
    }

    /**
     * Create an accounting line
     */
    public function AccountingLine($line) {

        $act1 = $this->AccountIdFromImage($line['account1']);
        $act2 = $this->AccountIdFromImage($line['account2']);
        $this->tc->canAccess($this->browser, $line['url'], []);

        if (array_key_exists('date', $line)) {
            $this->browser->type('date_op', $line['date']);
        }

        $this->browser
            ->select('compte1', $act1)
            ->select('compte2', $act2)
            ->type('montant', $line['amount'])
            ->type('description', $line['description'])
            ->type('num_cheque', $line['reference']);

        if (array_key_exists('verified', $line)) {
            $this->browser->select('gel');
        }

        $this->browser
            ->scrollIntoView('#validate')
            ->waitFor('#validate')
            ->press('#validate')
            ->assertDontSee('404');
    }

    public function AccountingLineWithCheck($line) {

        $account1_id = $this->AccountIdFromImage($line['account1']);
        $account2_id = $this->AccountIdFromImage($line['account2']);

        $account1_total = $this->AccountTotal($account1_id);
        $account2_total = $this->AccountTotal($account2_id);


        $this->AccountingLine($line);

        $account1_new_total = $this->AccountTotal($account1_id);
        $account2_new_total = $this->AccountTotal($account2_id);

        // echo "account1_id = $account1_id\n";
        // echo "account2_id = $account2_id\n";
        // echo "account1_total = $account1_total\n";
        // echo "account2_total = $account2_total\n";
        // echo "account1_new_total = $account1_new_total\n";
        // echo "account2_new_total = $account2_new_total\n";

        $amount = $line['amount'];
        $this->tc->assertLessThan(
            0.000001,
            $account1_total - $amount - $account1_new_total,
            "total pour " . $line['account1']
        );

        $this->tc->assertLessThan(
            0.000001,
            $account2_total + $amount - $account2_new_total,
            "total pour " . $line['account2']
        );
    }

    /** 
     * Create accounts
     */
    public function CreateAccounts($accounts = []) {
        foreach ($accounts as $account) {
            if (!$this->AccountExists($account)) {
                // Create account
                $this->tc->canAccess($this->browser, "comptes/create", ['Compte']);
                $this->browser
                    ->type('nom', $account['nom'])
                    ->type('desc', $account['comment'])
                    ->select('codec', $account['codec'])
                    ->scrollIntoView('#validate')
                    ->waitFor('#validate')
                    ->press('#validate');
            }
            $this->tc->assertTrue(
                $this->AccountExists($account),
                "account exists: (" . $account['codec'] . ')' . $account['nom']
            );
        }
    }
}
