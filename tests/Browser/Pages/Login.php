<?php

namespace Tests\Browser\Pages;

use Laravel\Dusk\Browser;
use Laravel\Dusk\Page;

class Login extends Page {
    /**
     * Get the URL for the page.
     *
     * @return string
     */
    public function url() {
        $base_url = env('TARGET');
        return $base_url . 'index.php/auth/login';
    }

    /**
     * Assert that the browser is on the page.
     *
     * @param  Browser  $browser
     * @return void
     */
    public function assert(Browser $browser) {
        $browser->assertSee('Utilisateur');
    }

    /**
     * Get the element shortcuts for the page.
     *
     * @return array
     */
    public function elements() {
        return [
            '@element' => '#selector',
        ];
    }
}
