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
use Tests\libraries\PlaneFlightHandler;
use Tests\libraries\GliderFlightHandler;

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
            ['nom' => "Atelier de la Somme", 'codec' => '401', 'comment' => "Fournisseur"],
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
            ['ref' => 'Remorqué 500m', 'description' => 'Remorqué 500', 'prix' => '25', 'account' => 'Remorqués', 'codec' => '706'],
            ['ref' => 'Remorqué 300m', 'description' => 'Remorqué 300', 'prix' => '15', 'account' => 'Remorqués', 'codec' => '706'],
            ['ref' => 'Remorqué 100m', 'description' => 'Remorqué 100', 'prix' => '3', 'account' => 'Remorqués', 'codec' => '706'],
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

    /*************
     * Test cases
     *************/

    /**
     * Login
     */
    public function testLogin() {
        // $this->markTestSkipped('must be revisited.');
        $this->browse(function (Browser $browser) {
            $this->login($browser, env('TEST_USER'), env('TEST_PASSWORD'));
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

            $asterix_account = "(411) Le Gaulois Asterix";
            $asterix_id = $account_handler->AccountIdFromImage($asterix_account);

            // Check that an account has been created for Asterix
            $this->assertGreaterThan('-1', $asterix_id,  "Asterix account id = " . $asterix_id);

            $movements = [
                ['url' => 'compta/reglement_pilote',
                'account1' => '(512) Banque',
                'account2' => '(411) Le Gaulois Asterix',
                'amount' => '100',
                'description' => "Avance sur vols",
                'reference' => "AV-1"],

                ['url' => 'compta/reglement_pilote',
                'account1' => '(512) Banque',
                'account2' => '(411) Le Gaulois Goudurix',
                'amount' => '250.47',
                'description' => "Avance avec décimals",
                'reference' => "Petites pièces"],

                ['url' => 'compta/factu_pilote',
                'account1' => '(411) Le Gaulois Goudurix',
                'account2' => '(706) Remorqués',
                'amount' => '23',
                'description' => "Facturation manuelle de remorqués",
                'reference' => "Facture d'un autre club"],

                ['url' => 'compta/recettes',
                'account1' => '(512) Banque',
                'account2' => '(74) Subventions',
                'amount' => '500',
                'description' => "Subvention d'aide à la formation",
                'reference' => "Relevé CDN"],

                ['url' => 'compta/avoir_fournisseur',
                'account1' => '(401) Atelier de la Somme',
                'account2' => '(615) Entretien',
                'amount' => '350',
                'description' => "Trop perçu sur facture",
                'reference' => "Facture 4712"],

                ['url' => 'compta/depenses',
                'account1' => '(606) Essence plus huile',
                'account2' => '(512) Banque',
                'amount' => '125.5',
                'description' => "Achat d'essence",
                'reference' => "Chèque 413"],

                ['url' => 'compta/credit_pilote',
                'account1' => '(606) Frais de bureau',
                'account2' => '(411) Le Gaulois Panoramix',
                'amount' => '25.5',
                'description' => "Remboursement fournitures de bureau",
                'reference' => "Facture XX78"],

                ['url' => 'compta/debit_pilote',
                'account1' => '(411) Le Gaulois Goudurix',
                'account2' => '(512) Banque',
                'amount' => '27.13',
                'description' => "Remboursement de solde pilote",
                'reference' => "Chèque CDN1027"],

                ['url' => 'compta/utilisation_avoir_fournisseur',
                'account1' => '(615) Entretien',
                'account2' => '(401) Atelier de la Somme',
                'amount' => '350',
                'description' => "Utilisation avoir fournisseur",
                'reference' => "Facture 4712"],

                // ['url' => 'compta/virement',
                // 'account1' => '(512) Banque',
                // 'account2' => '(512) Banque',
                // 'amount' => '100.00',
                // 'description' => "Virement d'un compte sur lui même",
                // 'reference' => "Vous êtes fou..."]

            ];

            foreach ($movements as $movement) {
                $account_handler->AccountingLineWithCheck($movement);
            }
        });
    }

    /**
     * test plane flight creation
     * @depends testAccountMovements
     */
    public function testPlaneFlight() {
        // $this->markTestSkipped('must be revisited.');
        $this->browse(function (Browser $browser) {

            $account_handler = new AccountHandler($browser, $this);
            $plane_flight_handler = new PlaneFlightHandler($browser, $this);

            $asterix_account = "(411) Le Gaulois Asterix";
            $asterix_id = $account_handler->AccountIdFromImage($asterix_account);
            $asterix_total = $account_handler->AccountTotal($asterix_id);

            $price = 51.0;

            /* 
            TODO: move takeoff and landing times to HTML times
            TODO: Check that the plane account has been credited
            TODO: Find the flight back to delete
            TODO: check that the pilot is reimbursed after flight deletion
            
            The tests should be independant from existing data.
            Tests flights could start the day after the last flight.
            It implies the capacity to find out the last flight.

            Should I modify GVV to return information used only for testing ?
            pro - it woul make end to end test simplers and supporting more complex scenarios
            cons- it adds more code ...
            */

            $latest = $plane_flight_handler->latestFlight();
            $dateFormat = "d/m/Y";
            if ($latest) {
                $latest_date = $latest->vadate;
                $date = new \DateTime($latest_date);
                $date->modify('+1 day');
                $flightDate = $date->format($dateFormat);
            } else {
                $flightDate = date($dateFormat); 
            }

            $fligt = [
                'url' => 'vols_avion/create',
                'date' => $flightDate,
                'pilot' => 'asterix',
                'plane' => 'F-JUFA',
                'start_time' => '10.00',
                'end_time' => '10.30',
                'start_meter' => '100',
                'end_meter' => '100.5',
                'image' => $flightDate . ' 100.00 F-JUFA'
            ];

            $plane_flight_handler->CreatePlaneFlights([$fligt]);

            $asterix_new_total = $account_handler->AccountTotal($asterix_id);

            $this->assertLessThan(0.000001, $asterix_total - $price - $asterix_new_total, "Asterix account total = " . $asterix_new_total);
        });
    }

    /**
     * test glider flight creation
     * @depends testAccountMovements
     */
    public function testGliderFlight() {
        // $this->markTestSkipped('must be revisited.');
        $this->browse(function (Browser $browser) {

            $this->assertTrue(true);

            $account_handler = new AccountHandler($browser, $this);
            $glider_flight_handler = new GliderFlightHandler($browser, $this);

            $asterix_account = "(411) Le Gaulois Asterix";
            $asterix_id = $account_handler->AccountIdFromImage($asterix_account);
            $asterix_total = $account_handler->AccountTotal($asterix_id);

            $price = 51.0;

            /* 
            TODO: move takeoff and landing times to HTML times
            TODO: Check that the plane account has been credited
            TODO: Find the flight back to delete
            TODO: check that the pilot is reimbursed after flight deletion
            
            The tests should be independant from existing data.
            Tests flights could start the day after the last flight.
            It implies the capacity to find out the last flight.

            Should I modify GVV to return information used only for testing ?
            pro - it woul make end to end test simplers and supporting more complex scenarios
            cons- it adds more code ...
            */

            $latest = $glider_flight_handler->latestFlight();

            $dateFormat = "d/m/Y";
            if ($latest) {
                $latest_date = $latest->vpdate;
                $date = new \DateTime($latest_date);
                $date->modify('+1 day');
                $flightDate = $date->format($dateFormat);
            } else {
                $flightDate = date($dateFormat); 
            }

            /* for select values must be passed, not images
               for radio buttons
                <div class="me-3 mb-2">
                    Lancement:
                        Treuil<input type="radio" name="vpautonome" value="1" id="Treuil">
                        Autonome<input type="radio" name="vpautonome" value="2" id="Autonome">
                        Remorqué<input type="radio" name="vpautonome" value="3" checked="checked" id="Remorqué">
                        Extérieur<input type="radio" name="vpautonome" value="4" id="Extérieur">
                </div>
                $browser->radio('size', 'large');  // use name and value

                Only some combination are coherent instructor implies DC, R launch implies tow pilot, etc.
                no coherency controls are done.
            */
            $fligt = [
                'url' => 'vols_planeur/create',
                'date' => $flightDate,
                'pilot' => 'asterix',
                'glider' => 'F-CGAA',
                'instructor' => 'panoramix',         // implies DC
                'start_time' => '10:00',
                'end_time' => '10:30',
                'tow_pilot' => 'abraracourcix',
                'tow_plane' => 'F-JUFA',
                 // 'winch_man' => 'asterix',
                 'launch' => 'R',   // R, T, A, E
                'image' => $flightDate . ' 100.00 F-JUFA'
            ];
            return;
            $glider_flight_handler->CreateGliderFlights([$fligt]);

            $asterix_new_total = $account_handler->AccountTotal($asterix_id);

            $this->assertLessThan(0.000001, $asterix_total - $price - $asterix_new_total, "Asterix account total = " . $asterix_new_total);
        });
    }

    /**
     * Logout
     * @depends testPlaneFlight
     */
    public function testLogout() {
        // $this->markTestSkipped('must be revisited.');
        $this->browse(function (Browser $browser) {
            $this->logout($browser);
        });
    }
}
