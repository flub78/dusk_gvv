<?php

namespace Tests\Browser;

// use Illuminate\Foundation\Testing\DatabaseMigrations;
use Laravel\Dusk\Browser;
use Tests\GvvDuskTestCase;

use function PHPUnit\Framework\assertSameSize;

class InstallationTest extends GvvDuskTestCase {
    /**
     * Check that the installation can be reset and installed
     *
     * @return void
     */
    public function testReset() {
        $this->browse(function (Browser $browser) {

            $user = "testadmin";
            $password = "password";

            $browser->visit($this->url . 'install/reset.php')
                ->assertSee("Verification de l'installation")
                // tables are only dropped when they exist
                // ->assertSee('drop table achats')
                // ->assertSee('drop table terrains')
                ->assertSee('Suppression des images')
                ->assertSee($this->url . 'install');

            $browser->clickLink($this->url . 'install');

            $browser->assertSee('Installation de GVV')
                ->assertSee("Fin de la procédure d'installation");

            $this->login($browser, $user, $password);
            $browser->visit($this->url . 'index.php/migration')
                ->assertSee('Migration de la base de données')
                ->press("Valider")
                ->assertSee('à jour');
            $this->logout($browser);
        });
    }
}
