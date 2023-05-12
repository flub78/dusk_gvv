<?php

namespace Tests\Browser;


use Laravel\Dusk\Browser;
use Tests\GvvDuskTestCase;

/**
 * Test user access to pages for a regular user
 *
 * @category Tests
 * @package  Tests\Browser
 * @author   <>
 * @license  http://www.gnu.org/copyleft/gpl.html GNU General Public License
 * @link     http://projets.developpez.com/projects/gvv/repository
 */
class PlanchisteAccessTest extends GvvDuskTestCase {


    public function testAdminAccess() {
        // $this->markTestSkipped('Speedup during dev.');
        $this->browse(function (Browser $browser) {

            $user = "testplanchiste";
            $password = "password";
            $mustSee = ['GVV', $user, 'Copyright (©)', "Boissel", "Peignot"];
            $mustNotSee = ['Error', 'Exception', 'Fatal error', 'Undefined', '404 Page not found'];
            $denied = ["Accès non autorisé"];

            $pages = [
                ['url' => 'vols_planeur/page', 'mustSee' => ['Planche']],
                ['url' => 'alarmes', 'mustSee' => $denied],
                ['url' => 'tickets/page', 'mustSee' => ['tickets']],
                ['url' => 'tickets/solde', 'mustSee' => $denied],

                ['url' => 'reports/page', 'mustSee' => $denied],

                ['url' => 'rapports/ffvv', 'mustSee' => $denied],
                ['url' => 'rapports/dgac', 'mustSee' =>  $denied],

                ['url' => 'terrains/page', 'mustSee' => $denied],
                [
                    'url' => 'terrains/edit/LFOI',
                    'mustSee' => $denied,
                    'inputValues' => []
                ],
                ['url' => 'terrains/create', 'mustSee' => $denied],
                ['url' => 'welcome/ca', 'mustSee' => $denied],
                ['url' => 'welcome/compta', 'mustSee' => $denied],
            ];

            $this->login($browser, $user, $password);

            foreach ($pages as $page) {
                $ms = array_merge($mustSee, $page['mustSee']);
                $mns = array_merge($mustNotSee, $page['mustNotSee'] ?? []);
                $this->canAccess($browser, $page['url'], $ms, $mns, $page['inputValues'] ?? []);
            }

            $this->logout($browser);
        });
    }

    public function testMembresAccess() {
        // $this->markTestSkipped('Speedup during dev.');
        $this->browse(function (Browser $browser) {

            $user = "testplanchiste";
            $password = "password";
            $mustSee = ['GVV', $user, 'Copyright (©)', "Boissel", "Peignot"];
            $mustNotSee = ['Error', 'Exception', 'Fatal error', 'Undefined', '404 Page not found'];
            $denied = ["Accès non autorisé"];

            $pages = [
                ['url' => 'membre/page', 'mustSee' => ['Liste des membres']],
                [
                    'url' => 'membre/create',
                    'mustSee' => ['Fiche de membre', 'Informations personelles'],
                    'mustNotSee' => ['input_field', 'licfed']
                ],
                [
                    'url' => 'membre/edit/unknown',
                    'mustSee' => ['Erreur', 'Pilote inconnu'],
                    'mustNotSee' => ['Fiche de membre', 'Informations personelles']
                ],
                ['url' => 'membre/edit', 'mustSee' => ['pas de fiche']],
                ['url' => 'licences/per_year', 'mustSee' => ['Licences']],

                ['url' => 'mails/page', 'mustSee' => $denied],
                ['url' => 'mails/create', 'mustSee' => $denied],

                ['url' => 'auth/change_password', 'mustSee' => ['Nouveau mot de passe']],

                ['url' => 'compta/mon_compte', 'mustSee' => ['pas de compte']],
            ];

            $this->login($browser, $user, $password);

            foreach ($pages as $page) {
                $ms = array_merge($mustSee, $page['mustSee']);
                $mns = array_merge($mustNotSee, $page['mustNotSee'] ?? []);
                $this->canAccess($browser, $page['url'], $ms, $mns, $page['inputValues'] ?? []);
            }

            $this->logout($browser);
        });
    }

    public function testPlaneursAccess() {
        // $this->markTestSkipped('Speedup during dev.');
        $this->browse(function (Browser $browser) {

            $user = "testplanchiste";
            $password = "password";
            $mustSee = ['GVV', $user, 'Copyright (©)', "Boissel", "Peignot"];
            $mustNotSee = ['Error', 'Exception', 'Fatal error', 'Undefined', '404 Page not found'];
            $denied = ["Accès non autorisé"];

            $pages = [
                ['url' => 'vols_planeur/page', 'mustSee' => ['Planche des Vols Planeur']],
                ['url' => 'vols_planeur/create', 'mustSee' => ['Vol', 'si le pilote paye', 'Pourcentage']],
                ['url' => 'vols_planeur/plancheauto_select', 'mustSee' => ['Choix de la planche']],
                ['url' => 'vols_planeur/plancheauto', 'mustSee' => ['Saisie planche planeur']],
                ['url' => 'planeur/page', 'mustSee' => ['Planeurs']],
                ['url' => 'planeur/create', 'mustSee' => $denied],
                ['url' => 'vols_planeur/statistic', 'mustSee' => ['Statistiques planeur', 'Par mois', 'Par machine', 'Activité planeur par mois']],
                ['url' => 'event/stats', 'mustSee' => ['formation']],
                ['url' => 'event/fai', 'mustSee' => ['performance FAI']],


            ];

            $this->login($browser, $user, $password);

            foreach ($pages as $page) {
                $ms = array_merge($mustSee, $page['mustSee']);
                $mns = array_merge($mustNotSee, $page['mustNotSee'] ?? []);
                $this->canAccess($browser, $page['url'], $ms, $mns, $page['inputValues'] ?? []);
            }

            $this->logout($browser);
        });
    }

    public function testAvionsAccess() {
        // $this->markTestSkipped('Speedup during dev.');
        $this->browse(function (Browser $browser) {

            $user = "testplanchiste";
            $password = "password";
            $mustSee = ['GVV', $user, 'Copyright (©)', "Boissel", "Peignot"];
            $mustNotSee = ['Error', 'Exception', 'Fatal error', 'Undefined', '404 Page not found'];
            $denied = ["Accès non autorisé"];

            $pages = [
                ['url' => 'vols_avion/page', 'mustSee' => ['Planche des vols avion']],
                ['url' => 'vols_avion/create', 'mustSee' => ['Vol Avion', 'avitaillement']],
                ['url' => 'avion/page', 'mustSee' => ['Avions']],
                ['url' => 'avion/create', 'mustSee' => $denied],
                ['url' => 'vols_avion/statistic', 'mustSee' => ['Statistiques avion', 'Par mois', 'Par machine', 'Activité avion par mois']],
                ['url' => 'pompes', 'mustSee' => $denied],
                ['url' => 'pompes/create', 'mustSee' => $denied],
            ];

            $this->login($browser, $user, $password);

            foreach ($pages as $page) {
                $ms = array_merge($mustSee, $page['mustSee']);
                $mns = array_merge($mustNotSee, $page['mustNotSee'] ?? []);
                $this->canAccess($browser, $page['url'], $ms, $mns, $page['inputValues'] ?? []);
            }

            $this->logout($browser);
        });
    }

    public function testComptaAccess() {
        // $this->markTestSkipped('Speedup during dev.');
        $this->browse(function (Browser $browser) {

            $user = "testplanchiste";
            $password = "password";
            $mustSee = ['GVV', $user, 'Copyright (©)', "Boissel", "Peignot"];
            $mustNotSee = ['Error', 'Exception', 'Fatal error', 'Undefined', '404 Page not found'];
            $denied = ["Accès non autorisé"];

            $pages = [
                ['url' => 'compta/page', 'mustSee' => $denied],
                ['url' => 'compta/create', 'mustSee' => $denied],
                ['url' => 'comptes/general', 'mustSee' => $denied],
                ['url' => 'comptes/page/411', 'mustSee' => $denied],
                ['url' => 'comptes/create', 'mustSee' => $denied],
                ['url' => 'comptes/resultat', 'mustSee' => $denied],
                ['url' => 'comptes/bilan', 'mustSee' => $denied],
                ['url' => 'achats/list_per_year', 'mustSee' => $denied],
                ['url' => 'comptes/tresorerie', 'mustSee' => $denied],
            ];

            $this->login($browser, $user, $password);

            foreach ($pages as $page) {
                $ms = array_merge($mustSee, $page['mustSee']);
                $mns = array_merge($mustNotSee, $page['mustNotSee'] ?? []);
                $this->canAccess($browser, $page['url'], $ms, $mns, $page['inputValues'] ?? []);
            }

            $this->logout($browser);
        });
    }
}
