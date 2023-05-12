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
    public function AccountTotal() {
 
    }

    /**
     * Create an accounting line
     */
    public function AccountingLine ($line) {
        
        echo "creating " . $line['description'] . "\n";

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

        $this->browser->screenshot('aaaaaaaaaaa_' . $line['url']);

        $this->browser
        ->press('#validate')
        ->assertDontSee('404');
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
                    ->press('#validate');
            }
            $this->tc->assertTrue(
                $this->AccountExists($account),
                "account exists: (" . $account['codec'] . ')' . $account['nom']
            );
        }
    }
}