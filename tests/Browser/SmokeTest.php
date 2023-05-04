<?php

namespace Tests\Browser;

use Laravel\Dusk\Browser;
use Tests\GvvDuskTestCase;

/**
 * The smoke test creates enough pilots, planes, terrains, flights, accounts, etc. to test a set of basic nominal cases.
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

    /** Constructor */
    function __construct() {
        parent::__construct();

        $this->initial_nb_pilots = 0;
        $this->initial_nb_accounts = 0;
        $this->initial_nb_products = 0;
        $this->initial_nb_planes = 0;
        $this->initial_nb_gliders = 0;
        $this->initial_nb_glider_flights = 0;
        $this->initial_nb_plane_flights = 0;
        $this->initial_nb_terrains = 0;

        $this->terrains = [
            ['oaci' => "LFAA", 'nom' => "Trifouillis", 'freq1' => "123.45", 'comment' => "Mon terrain"],
            ['oaci' => "LFAB", 'nom' => "Les Oies", 'freq1' => "123.45", 'comment' => "Mon second terrain"]
        ];

        $this->accounts = [
            ['nom' => "Immobilisations", 'codec' => '215', 'comment' => "Immobilisations"],
            ['nom' => "Fonds associatifs", 'codec' => '102', 'comment' => "Fonds associatifs"],
            ['nom' => "Banque", 'codec' => '512', 'comment' => "Banque"],
        //    ['nom' => "Emprunt", 'codec' => '164', 'comment' => "Emprunt"],
            ['nom' => "Frais de bureau", 'codec' => '606', 'comment' => "Frais de bureau"],
            ['nom' => "Essence plus huile", 'codec' => '606', 'comment' => "Essence plus huile"],
            ['nom' => "Entretien", 'codec' => '615', 'comment' => "Entretien"],
            ['nom' => "Assurances", 'codec' => '616', 'comment' => "Assurances"],
            ['nom' => "Heures de vol planeur", 'codec' => '706', 'comment' => "Heures de vol planeur"],
            ['nom' => "Heures de vol remorqués", 'codec' => '706', 'comment' => "Heures de vol remorqués"],
            ['nom' => "Heures de vol ULM", 'codec' => '706', 'comment' => "Heures de vol ULM"],
            ['nom' => "Subventions", 'codec' => '74', 'comment' => "Subventions"]
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

    /**
     * Test create elements
     */
    public function createTerrain($browser, $terrains = []) {

        $total = $this->TableTotal($browser);
        foreach ($terrains as $terrain) {

            $this->canAccess($browser, "terrains/create", ['Code OACI']);
            $browser
                ->type('oaci', $terrain['oaci'])
                ->type('nom', $terrain['nom'])
                ->type('freq1', $terrain['freq1'])
                ->type('comment', $terrain['comment'])
                ->press('#validate')
                ->assertSee('Terrains');

            $this->canAccess($browser, "terrains/page", ['Compta', 'Terrains']);
        }

        $new_total = $this->TableTotal($browser);
        $this->assertEquals($total + count($terrains), $new_total, "Terrain created, total = " . $new_total);
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

            $this->CreateAccounts($browser, $this->accounts);

        });
    }

    /**
     * Logout
     * @depends testCreateData
     */
    public function testLogout() {
        // $this->markTestSkipped('must be revisited.');
        $this->browse(function (Browser $browser) {
            $this->logout($browser);
        });
    }
}
