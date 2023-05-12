<?php

namespace Tests\Browser;

use Laravel\Dusk\Browser;
use Tests\GvvDuskTestCase;

use Tests\libraries\AccountCodeHandler;
use Tests\libraries\AccountHandler;
use Tests\libraries\ProductHandler;
use Tests\libraries\GliderHandler;
use Tests\libraries\PlaneHandler;
use Tests\libraries\MemberHandler;

/**
 * The smoke test creates enough pilots, planes, terrains, flights, accounts, etc. to test a set of basic nominal cases. When the smoke test passes, it means that the application is able to handle the basic nominal cases. 
 * 
 * Dependencies
 * - Planeur depends on product for the hour price
 * - product depends on accounts
 * - airplane depends on product for the hour price
 * 
 * Pilots
 * - Goudurix is the Student
 * - Panoramix the instructor
 * - Asterix the regular pilot and whinch operator
 * - Abraracourcix the tow pilot
 * 
 * Gliders
 * - one two seaters
 * - one single seater
 * - one autonomous glider
 * 
 * Airplanes
 * - one tow plane
 * 
 * Accounts
        102 Fonds Associatifs
		215	Immobilisation
				un par planeur
				un pour le remorqueur
		411 un par membre (créé lors de la création des membres)
		512	un compte de banque
		164 Emprunt bancaire
		
		606 Frais de bureau
		606 Essence plus huile
		615 entretien
		616 Assurances
		
		706 Heures de vol planeur
		706 remorqués
		706 heures ULM
		74 Subventions
 */
class SmokeTest extends GvvDuskTestCase {

    private $accountsChart;
    private $accounts;
    private $products;
    private $members;
    private $gliders;
    private $planes;

    /** Constructor */
    function __construct() {
        parent::__construct();

        $this->accountsChart = [
            ['codec' => "164", "desc" => "Emprunts auprès des établissements de crédit"]
        ];

        $this->accounts = [
            ['nom' => "Immobilisations", 'codec' => '215', 'comment' => "Immobilisations"],
            ['nom' => "Fonds associatifs", 'codec' => '102', 'comment' => "Fonds associatifs"],
            ['nom' => "Banque", 'codec' => '512', 'comment' => "Banque"],
            ['nom' => "Emprunt", 'codec' => '164', 'comment' => "Emprunt"],
            ['nom' => "Frais de bureau", 'codec' => '606', 'comment' => "Frais de bureau"],
            ['nom' => "Essence plus huile", 'codec' => '606', 'comment' => "Essence plus huile"],
            ['nom' => "Entretien", 'codec' => '615', 'comment' => "Entretien"],
            ['nom' => "Assurances", 'codec' => '616', 'comment' => "Assurances"],
            ['nom' => "Heures de vol planeur", 'codec' => '706', 'comment' => "Heures de vol planeur"],
            ['nom' => "Remorqués", 'codec' => '706', 'comment' => "Remorqués"],
            ['nom' => "Heures de vol ULM", 'codec' => '706', 'comment' => "Heures de vol ULM"],
            ['nom' => "Subventions", 'codec' => '74', 'comment' => "Subventions"]
        ];

        $this->products = [
            ['ref' => 'remorqué', 'description' => 'Remorqué', 'prix' => '25', 'account' => 'Remorqués', 'codec' => '706'],
            ['ref' => 'remorqué-25ans', 'description' => 'Remorqué moind de 25 ans', 'prix' => '20', 'account' => 'Remorqués', 'codec' => '706'],
            ['ref' => 'treuillé', 'description' => 'Treuillée', 'prix' => '8', 'account' => 'Remorqués', 'codec' => '706'],        
            ['ref' => 'hdv-planeur', 'description' => 'Heure de vol planeur', 'prix' => '30', 'account' => 'Heures de vol planeur', 'codec' => '706'],        
            ['ref' => 'hdv-planeur-forfait', 'description' => 'Heure de vol planeur au forfait', 'prix' => '10.0', 'account' => 'Heures de vol planeur', 'codec' => '706'],        
            ['ref' => 'hdv-ULM', 'description' => 'Heure de vol ULM', 'prix' => '102', 'account' => 'Heures de vol ULM', 'codec' => '706'], 
            ['ref' => 'gratuit', 'description' => 'non facturé', 'prix' => '0', 'account' => 'Heures de vol planeur', 'codec' => '706'],           
        ];

        $this->members = [
            ['id' => 'asterix', 'nom' => 'Le Gaulois', 'prenom' => 'Asterix', 'email' => 'asterix@flub78.net',
            'adresse' => '1 rue des menhirs', 'code_postal' => '78000', 'ville' => 'Lutèce',
            'treuillard' => true ],

            ['id' => 'goudurix', 'nom' => 'Le Gaulois', 'prenom' => 'Goudurix', 'email' => 'goudurix@flub78.net',
            'adresse' => '1 rue des menhirs', 'code_postal' => '78000', 'ville' => 'Village Gaulois'],

            ['id' => 'panoramix', 'nom' => 'Le Gaulois', 'prenom' => 'Panoramix', 'email' => 'panoramix@flub78.net',
            'adresse' => '1 rue des menhirs', 'code_postal' => '78000', 'ville' => 'Lutèce',
            'fi_planeur' => true , 'fe_planeur' => true, 'fi_avion' => true , 'fe_avion' => true],

            ['id' => 'abraracourcix', 'nom' => 'Le Gaulois', 'prenom' => 'Abraracourcix', 'email' => 'abraracourcix@flub78.net',
            'adresse' => '1 rue des menhirs', 'code_postal' => '78000', 'ville' => 'Lutèce',
            'remorqueur' => true],

        ];

        $this->gliders = [
            ['immat' => 'F-CGAA', 'type' => 'Ask21', 'nb_places' => '2', 'construct' => 'Alexander Schleicher',
             'prix' => 'hdv-planeur', 'prix_forfait' => 'hdv-planeur-forfait'],
            ['immat' => 'F-CGAB', 'type' => 'Pégase', 'nb_places' => '1', 'numc' => 'EG', 'construct' => 'Centrair',
            'prix' => 'hdv-planeur', 'prix_forfait' => 'hdv-planeur-forfait'],
            ['immat' => 'F-CGAC', 'type' => 'DG800', 'nb_places' => '1', 'numc' => 'AC', 'construct' => 'DG'],
        ];

        $this->planes = [
            ['immat' => 'F-JUFA', 'type' => 'Dynamic', 'nb_places' => '2', 'construct' => 'Aeropol', 'remorqueur' => true, 'prix' => 'hdv-ULM', 'prix_dc' => 'hdv-ULM' ],
            ['immat' => 'F-GUFB', 'type' => 'DR400', 'nb_places' => '4', 'construct' => 'Robin', 'remorqueur' => false ]
        ];
    }

    // protected function setUp(): void {
    //     echo "setup\n";
    // }

    // protected function tearDown(): void {
    //     echo "teardown\n";
    // }

    // public static function setUpBeforeClass(): void {
    //     //echo "setup before class\n";
    // }

    // public static function tearDownAfterClass(): void {
    //     //echo "teardown after class\n";
    // }


    /*************
     * Test cases
     *************/

    /**
     * Login
     */
    public function testLogin() {
        // $this->markTestSkipped('must be revisited.');
        $this->browse(function (Browser $browser) {
            $this->login($browser, 'testadmin', 'password');
        });
    }

    /**
     * Test create
     * @depends testLogin
     */
    public function testCreateData() {
        // $this->markTestSkipped('must be revisited.');
        $this->browse(function (Browser $browser) {

            $account_code_handler = new AccountCodeHandler($browser, $this);
            $account_handler = new AccountHandler($browser, $this);
            $product_handler = new ProductHandler($browser, $this);
            $glider_handler = new GliderHandler($browser, $this);
            $plane_handler = new PlaneHandler($browser, $this);
            $member_handler = new MemberHandler($browser, $this);
            
            $account_code_handler->CreateAccountCodes($this->accountsChart);
            $account_handler->CreateAccounts($this->accounts);
            $product_handler->CreateProducts($this->products);
            $glider_handler->CreateGliders($this->gliders);
            $plane_handler->CreatePlanes($this->planes);
            $member_handler->CreateMembers($this->members);
        });
    }


    /**
     * Test AccountMovements
     * @depends testCreateData
     */
    public function testAccountMovements() {
        // $this->markTestSkipped('must be revisited.');

        $this->browse(function (Browser $browser) {

            $account_handler = new AccountHandler($browser, $this);

            // Check that an account has been created for Asterix
            $asterix_account = $account_handler->AccountIdFromMember($this->members[0]);
            $this->assertGreaterThan('-1', $asterix_account,  "Asterix account = " . $asterix_account);

            // Check the account total
            $abraracourcix_account = $account_handler->AccountIdFromMember($this->members[3]);
            $this->assertGreaterThanOrEqual('307', $abraracourcix_account,  "Abraracourcix account = " . $abraracourcix_account);

            // Check the bank total

            // Put money on the account
            $account_handler->AccountingLine([
                'url' => 'compta/reglement_pilote',
                'account1' => "(512) Banque",
                'account2' => "(411) Le Gaulois Asterix",
                'amount' => "100",
                'description' => "Avance sur vols",
                'reference' => "AV-1"]);

            // Check the pilot total

            // Check the bank account
        });
    }


    /**
     * Logout
     * @depends testAccountMovements
     */
    public function testLogout() {
        // $this->markTestSkipped('must be revisited.');
        $this->browse(function (Browser $browser) {
            $this->logout($browser);
        });
    }
}
