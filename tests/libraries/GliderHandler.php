<?php

namespace Tests\libraries;

/**
 * This class manages glider in the Dusk tet context.
 * 
 * A glider is an object to access glider data through a browser and a Dusk test context.
 * 
 * The persistence is managed by the WEB application and as we only have access to the WEB interface methods to retreive the information may be indirect.
 */

 class GliderHandler {

    private $browser;
    private $tc;

    /** Constructor */
    function __construct($browser, $test_context) {
        $this->browser = $browser;
        $this->tc = $test_context;
    }

    public function gliderImage($glider) {
        $res = $glider['type'] . ' - ' . $glider['immat'];
        if (array_key_exists('numc', $glider)) {
            $res .= ' - (' . $glider['numc'] . ')';
        }
        return $res;
    }

    /** 
     * Check that a glider exists.
     * 
     * As glider IDs are not public (they are generated by the database), we check that the glider image is present in a select.
     */
    public function GliderExists($glider) {
        $selectValues = $this->tc->geyValuesFromSelect($this->browser, "vols_planeur/create", "vpmacid");

        $image = $this->gliderImage($glider);

        foreach ($selectValues as $key => $value) {
            if ($value == $image) {
                return true;
            }
        }
        return false;
    }

    /** 
     * Create gliders
     */
    public function CreateGliders($list = []) {
        foreach ($list as $elt) {
            if (!$this->GliderExists($elt)) {

                // Create product
                $this->tc->canAccess($this->browser, "planeur/create", ['Planeur']);
                $this->browser
                    ->type('mpconstruc', $elt['construct'])
                    ->type('mpmodele', $elt['type'])
                    ->type('mpimmat', $elt['immat']);

                if (array_key_exists('numc', $elt)) {
                    $this->browser->type('mpnumc', $elt['numc']);
                }

                if (array_key_exists('prix', $elt)) {
                    $this->browser->select('mprix', $elt['prix']);
                }

                if (array_key_exists('prix_forfait', $elt)) {
                    $this->browser->select('mprix_forfait', $elt['prix_forfait']);
                }

                if (array_key_exists('prix_moteur', $elt)) {
                    $this->browser->select('mprix_moteur', $elt['prix_moteur']);
                }

                $this->browser->type('mpbiplace', $elt['nb_places'])
                    ->press('#validate');
            }
            $image = $this->gliderImage($elt);
            $this->tc->assertTrue(
                $this->GliderExists($elt),
                "glider exists: " . $image
            );
        }
    }
}