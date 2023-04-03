<?php

namespace Tests\Browser;

// use Illuminate\Foundation\Testing\DatabaseMigrations;
use Laravel\Dusk\Browser;
use Tests\GvvDuskTestCase;

class PageAccessTest extends GvvDuskTestCase {


    public function testAdminAccess() {
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

                ['url' => 'welcome/ca', 'mustSee' => ['Administration', 'terrains']],

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

    public function testMembresAccess() {
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

    public function testPlaneurssAccess() {
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
}
