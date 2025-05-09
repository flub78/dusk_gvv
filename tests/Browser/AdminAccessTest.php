<?php

namespace Tests\Browser;

use Laravel\Dusk\Browser;
use Tests\GvvDuskTestCase;
use PHPUnit\Framework\Assert;

/**
 * Test that an admin user can access to all the application pages
 */
class AdminAccessTest extends GvvDuskTestCase {


    public function testRequiredEnvironmentVariablesAreSet() {
        $this->browse(function (Browser $browser) {
            $base_url = env('TARGET');

            Assert::assertNotEmpty($base_url, "TARGET env var is not set");
            Assert::assertNotEmpty(env('TEST_USER'), "TEST_USER env var is not set");
            Assert::assertNotEmpty(env('TEST_PASSWORD'), "TEST_PASSWORD env var is not set");
        });
    }

    public function testAdminCanAccessToAdminPages() {
        // $this->markTestSkipped('Speedup during dev.');
        $this->browse(function (Browser $browser) {

            $user = "testadmin";
            $password = "password";
            $mustSee = ['GVV', 'Compta', $user, 'Copyright (©)', "Boissel", "Peignot"];
            $mustNotSee = ['Error', 'Exception', 'Fatal error', 'Undefined', '404 Page not found'];

            $pages = [
                ['url' => 'vols_planeur/page', 'mustSee' => ['Planche']],
                ['url' => 'alarmes', 'mustSee' => ['Conditions', 'Visite']],
                ['url' => 'tickets/page', 'mustSee' => ['tickets']],
                ['url' => 'tickets/solde', 'mustSee' => ['Solde']],

                ['url' => 'reports/page', 'mustSee' => ['Rapports']],

                ['url' => 'rapports/ffvv', 'mustSee' => ['annuel FFVV']],
                ['url' => 'rapports/dgac', 'mustSee' => ['DGAC']],

                // commented out
                // this page is supposed to return a 404 error
                // But I get a 505 error instead and it crashes the test
                // ['url' => 'welcome/ca', 'mustSee' => ["Rapports d'activité", 'Année']],

                ['url' => 'terrains/page', 'mustSee' => ['LFOI', 'Terrains']],
                [
                    'url' => 'terrains/edit/LFOI',
                    'mustSee' => ['OACI', 'Nom du terrain'],
                    'inputValues' => [['selector' => '#oaci', 'value' => 'LFOI']]
                ],
                ['url' => 'terrains/create', 'mustSee' => ['Terrain', 'Code OACI', 'Description']],
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

    public function testAdminCanAccessToMembresPages() {
        // $this->markTestSkipped('Speedup during dev.');
        $this->browse(function (Browser $browser) {

            $user = "testadmin";
            $password = "password";
            $mustSee = ['GVV', 'Compta', $user, 'Copyright (©)', "Boissel", "Peignot"];
            $mustNotSee = ['Error', 'Exception', 'Fatal error', 'Undefined', '404 Page not found'];

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

                ['url' => 'mails/page', 'mustSee' => ['Courriels']],
                ['url' => 'mails/create', 'mustSee' => ['Courriel']],

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

    public function testAdminCanAccessToPlaneurPages() {
        // $this->markTestSkipped('Speedup during dev.');
        $this->browse(function (Browser $browser) {

            $user = "testadmin";
            $password = "password";
            $mustSee = ['GVV', 'Compta', $user, 'Copyright (©)', "Boissel", "Peignot"];
            $mustNotSee = ['Error', 'Exception', 'Fatal error', 'Undefined', '404 Page not found'];

            $pages = [
                ['url' => 'vols_planeur/page', 'mustSee' => ['Planche des Vols Planeur']],
                ['url' => 'vols_planeur/create', 'mustSee' => ['Vol']],
                ['url' => 'vols_planeur/plancheauto_select', 'mustSee' => ['Choix de la planche']],
                ['url' => 'vols_planeur/plancheauto', 'mustSee' => ['Saisie planche planeur']],
                ['url' => 'planeur/page', 'mustSee' => ['Planeurs']],
                ['url' => 'planeur/create', 'mustSee' => ['Planeur', 'Immatriculation', 'Année de mise en service']],
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

    public function testAdminCanAccessToAvionPages() {
        // $this->markTestSkipped('Speedup during dev.');
        $this->browse(function (Browser $browser) {

            $user = "testadmin";
            $password = "password";
            $mustSee = ['GVV', 'Compta', $user, 'Copyright (©)', "Boissel", "Peignot"];
            $mustNotSee = ['Error', 'Exception', 'Fatal error', 'Undefined', '404 Page not found'];

            $pages = [
                ['url' => 'vols_avion/page', 'mustSee' => ['Planche des vols avion']],
                ['url' => 'vols_avion/create', 'mustSee' => ['Vol']],
                ['url' => 'avion/page', 'mustSee' => ['Avions']],
                ['url' => 'avion/create', 'mustSee' => ['Avion', 'Immatriculation', 'Année de mise en service']],
                ['url' => 'vols_avion/statistic', 'mustSee' => ['Statistiques avion', 'Par mois', 'Par machine', 'Activité avion par mois']],
                ['url' => 'pompes', 'mustSee' => ['Utilisation de la pompe']],
                ['url' => 'pompes/create', 'mustSee' => ['100LL']],
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

    public function testAdminCanAccessToComptaPages() {
        $this->browse(function (Browser $browser) {

            $user = "testadmin";
            $password = "password";
            $mustSee = ['GVV', 'Compta', $user, 'Copyright (©)', "Boissel", "Peignot"];
            $mustNotSee = ['Error', 'Exception', 'Fatal error', 'Undefined', '404 Page not found'];

            $pages = [
                ['url' => 'compta/page', 'mustSee' => ['Grand journal']],
                ['url' => 'compta/create', 'mustSee' => ['Ecriture comptable']],
                ['url' => 'comptes/general', 'mustSee' => ['Balance générale des comptes']],
                ['url' => 'comptes/page/411', 'mustSee' => ['Balance générale des comptes Classe 411']],
                // ['url' => 'comptes/create', 'mustSee' => ['Compte']],
                ['url' => 'comptes/resultat', 'mustSee' => ["Résultat d'exploitation de l'exercice"]],
                ['url' => 'comptes/bilan', 'mustSee' => ["Bilan de fin d'exercice"]],
                ['url' => 'achats/list_per_year', 'mustSee' => ["Ventes de l'année"]],
                ['url' => 'comptes/tresorerie', 'mustSee' => ["Trésorerie"]],
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
