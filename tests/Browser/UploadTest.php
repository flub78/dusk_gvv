<?php

namespace Tests\Browser;

use Laravel\Dusk\Browser;
use Tests\GvvDuskTestCase;

/**
 * Test Terrain CRUD
 * 
 * php artisan dusk --color=always --browse tests/Browser/TerrainTest.php
 * 
 * The tests rely on the method order. 
 */
class UploadTest extends GvvDuskTestCase {

    /** Constructor */
    function __construct() {
        parent::__construct();

        // var_dump($this->terrains);
        $this->terrains = [
            ['oaci' => "LFAA", 'nom' => "Trifouillis", 'freq1' => "123.45", 'comment' => "Mon terrain"],
            ['oaci' => "LFAB", 'nom' => "Les Oies", 'freq1' => "123.45", 'comment' => "Mon second terrain"]
        ];
    }

    // protected function setUp(): void {
    //     echo "setup\n";
    // }

    // protected function tearDown(): void {
    //     echo "teardown\n";
    // }

    public static function setUpBeforeClass(): void {
        //echo "setup before class\n";
    }

    public static function tearDownAfterClass(): void {
        //echo "teardown after class\n";
    }

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
     * Test upload image
     * @depends testLogin
     */
    public function testUploadOnCreate() {
        // $this->markTestSkipped('must be revisited.');
        $this->browse(function (Browser $browser) {
            $image_path = base_path('tests/fixtures/images/asterix.jpeg');
            $this->assertFileExists($image_path);

            $this->canAccess($browser, "membre/create/", ['Fiche de membre']);

            $browser->attach('userfile', $image_path);
            $browser->press('button_photo');
            $browser->screenshot('error_upload_image');

            $browser->assertSee('Vous n\'avez pas de fiche personnelle');
        });
    }

    /**
     * Test upload image
     * @depends testLogin
     */
    public function testUploadOnEdit() {
        // $this->markTestSkipped('must be revisited.');
        $this->browse(function (Browser $browser) {

            $image_path = base_path('tests/fixtures/images/asterix.jpeg');
            $this->assertFileExists($image_path);

            $this->canAccess($browser, "membre/edit/asterix", ['Fiche de membre', 'Asterix']);

            $browser->attach('userfile', $image_path);
            $browser->screenshot('upload_image_before_upload');
            $browser->press('button_photo');
            $browser->screenshot('upload_image_after_upload');


            $browser->assertDontSee('Vous n\'avez pas de fiche personnelle');
        });
    }


    /**
     * Logout
     * @depends testUploadOnCreate
     */
    public function testLogout() {
        // $this->markTestSkipped('must be revisited.');
        $this->browse(function (Browser $browser) {
            $this->logout($browser);
        });
    }
}
