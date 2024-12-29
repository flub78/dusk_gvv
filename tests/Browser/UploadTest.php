<?php

namespace Tests\Browser;

use Laravel\Dusk\Browser;
use Tests\GvvDuskTestCase;

/**
 * Test upload image
 * 
 * php artisan dusk --color=always --browse tests/Browser/UploadTest.php
 * 
 * The tests rely on the method order. 
 */
class UploadTest extends GvvDuskTestCase {

    /** Constructor */
    function __construct() {
        parent::__construct();
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

            $upload_dir = "/var/www/html/gvv.net/uploads/";
            $initial_count = 0;
            if (is_dir($upload_dir)) {
                $initial_count = count(glob($upload_dir . '*'));
            }

            // Check that the test image exists
            $image_path = base_path('tests/fixtures/images/asterix.jpeg');
            $this->assertFileExists($image_path);

            // load the membre/create page
            $this->canAccess($browser, "membre/create/", ['Fiche de membre']);

            $browser->attach('userfile', $image_path);
            $browser->press('button_photo');
            $browser->screenshot('error_upload_image');

            // Check that an error has been reported
            $browser->assertSee('Vous n\'avez pas de fiche personnelle');

            if (is_dir($upload_dir)) {
                $final_count = count(glob($upload_dir . '*'));
                $this->assertEquals($initial_count, $final_count, "no file left in case of error");
            }
        });
    }

    /**
     * Test upload image
     * @depends testLogin
     */
    public function testUploadOnEdit() {
        // $this->markTestSkipped('must be revisited.');
        $this->browse(function (Browser $browser) {

            $upload_dir = "/var/www/html/gvv.net/uploads/";
            $initial_count = 0;

            // if the upload directory exists, count the number of files in it
            if (is_dir($upload_dir)) {
                $initial_count = count(glob($upload_dir . '*'));
            }

            $image_path = base_path('tests/fixtures/images/asterix.jpeg');
            $this->assertFileExists($image_path);

            // load the page of an existing member
            $this->canAccess($browser, "membre/edit/asterix", ['Fiche de membre', 'Asterix']);

            // Check if there is already an image

            $existing_photo = $browser->resolver->find('#photo');
            if ($existing_photo) {
                // There is a photo, adapt the test behavior
                $browser->assertVisible('#delete_photo');
                $browser->assertPresent('#delete_photo');

                $browser->press('#delete_photo');

                if (is_dir($upload_dir)) {
                    $new_count = count(glob($upload_dir . '*'));
                    $this->assertEquals($initial_count - 1, $new_count);
                }
            } else {
                // There is no photo
                $browser->assertNotPresent('#delete_photo');
                $new_count = $initial_count;
            }

            // Upload the image
            $browser->attach('userfile', $image_path);
            $browser->screenshot('upload_image_before_upload');
            $browser->press('button_photo');
            $browser->screenshot('upload_image_after_upload');

            $browser->assertDontSee('Vous n\'avez pas de fiche personnelle');

            $browser->assertVisible('#photo');
            $browser->assertVisible('#delete_photo');

            if (is_dir($upload_dir)) {
                $count_after_upload = count(glob($upload_dir . '*'));
                $this->assertEquals($new_count + 1, $count_after_upload);
            }

            // if there was no prexisting photo
            if (!$existing_photo) {
                // delete the photo that has just been uploaded
                $browser->press('#delete_photo');

                $browser->assertNotPresent('#photo');
                $browser->assertNotPresent('#delete_photo');

                if (is_dir($upload_dir)) {
                    $count_after_delete = count(glob($upload_dir . '*'));
                    $this->assertEquals($count_after_upload - 1, $count_after_delete);
                }
            }

            if (is_dir($upload_dir)) {
                $final_count = count(glob($upload_dir . '*'));
                $this->assertEquals($final_count, $initial_count);
            }
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
