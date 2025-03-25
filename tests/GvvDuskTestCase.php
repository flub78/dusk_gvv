<?php

namespace Tests;

use Tests\DuskTestCase;
use Tests\Browser\Pages\Login;
use Exception;
use PHPUnit\Framework\Assert;
use Laravel\Dusk\Browser;
use Illuminate\Support\Facades\Log;
use ReflectionClass;
use Tests\libraries\AccountHandler;


/**
 * Base test case class for Dusk browser testing in the GVV application
 * 
 * Provides common utility methods for browser-based testing, including:
 * - Environment and login/logout handling
 * - Page access verification
 * - Table data extraction
 * - Logging and test lifecycle management
 * 
 * Extends Laravel Dusk's base test case with custom functionality specific to the GVV application
 */
class GvvDuskTestCase extends DuskTestCase {

    public $url;

    function __construct() {
        parent::__construct();
        $this->url = env('TARGET');
    }

    protected function setUp(): void {
        parent::setup();
        $testClass = (new ReflectionClass($this))->getShortName();
        $testName = $this->getName();
        Log::info("test started: {$testClass}::{$testName}");
    }

    /**
     * Tear down the test environment.
     *
     * @return void
     */
    protected function tearDown(): void {
        // Log test completion
        $testClass = (new ReflectionClass($this))->getShortName();
        $testName = $this->getName();
        $status = $this->getStatus() ? 'FAILED' : 'PASSED';
        Log::info("test completed: {$testClass}::{$testName} - {$status}");

        parent::tearDown();
    }

    /**
     * Validates that required environment variables for testing are set.
     * 
     * Checks that TARGET, TEST_USER, and TEST_PASSWORD environment variables
     * are not empty, which are critical for running browser-based tests.
     * 
     * @throws AssertionError if any required environment variable is not set
     */
    public function check_environement() {
        Assert::assertNotEmpty(env('TARGET'), "TARGET env var is not set");
        Assert::assertNotEmpty(env('TEST_USER'), "TEST_USER env var is not set");
        Assert::assertNotEmpty(env('TEST_PASSWORD'), "TEST_PASSWORD env var is not set");
    }

    /**
     * Login as a user.
     */
    public function login($browser, $username, $password, $section = "1") {

        Log::info("Login as $username, section $section");

        $this->check_environement();

        $browser->visit(new Login)
            ->screenshot('before_login')
            ->waitForText('Utilisateur')
            ->waitForText('Mot de passe')
            ->waitForText('Peignot')
            ->type('username', $username)
            ->type('password', $password);

        if ($section != "") {
            $browser->select('section', $section)
                ->screenshot('after_select_section');
        }

        $browser->press('input[type="submit"]')
            ->screenshot('after_login');

        $section_images = [];
        $section_images[1] = "Planeur";
        $section_images[2] = "ULM";
        $section_images[3] = "Avion";
        $section_images[4] = "Général";
        $section_images[5] = "Toutes";
        $browser->assertSee($section_images[$section]);

        sleep(2);
    }

    /**
     * Logout.
     */
    public function logout($browser) {

        $url = $this->fullUrl('auth/logout');
        Log::info("Logout");

        $browser->visit($url)
            // it's a detail but
            // the commented alternative look for the user icon and submenu
            // they are not visible without a working Internet connection
            // Invoking directly the logout url is more robust
            // $browser->click('@user_icon')
            //     ->clickLink('Quitter')
            ->waitForText('Utilisateur')
            ->assertSee('Utilisateur')
            ->assertSee('Mot de passe');
    }

    /**
     * Verifies that the user is successfully logged in by checking for expected elements.
     *
     * @param \Laravel\Dusk\Browser $browser The browser instance used for testing
     */
    public function IsLoggedIn($browser) {
        $browser->assertSee('Membres')
            ->assertSee('Planeurs');
    }

    /**
     * Verifies that the user is successfully logged out by checking for login page elements.
     *
     * @param \Laravel\Dusk\Browser $browser The browser instance used for testing
     */
    public function IsLoggedOut($browser) {
        $browser->assertDontSee('Planeurs');
        $browser->assertSee('Utilisateur')
            ->assertSee('Mot de passe');
    }

    /**
     * Constructs a full URL by combining the base URL with a given suburl.
     *
     * @param string $suburl The subpath or endpoint to be appended to the base URL
     * @return string The complete URL
     */
    public function fullUrl($suburl) {
        return $this->url . $suburl;
        // return $this->url . 'index.php/' . $suburl;
    }

    /**
     * Checks if the user can access a page.
     */
    public function canAccess($browser, $suburl, $mustFind = [], $mustNotFind = [], $inputValues = []) {
        $url = $this->fullUrl($suburl);
        if ($this->verbose()) {
            echo ("Visiting $url\n");
        }
        Log::info("visiting : $url");
        $browser->storeConsoleLog('console1.log');
        $browser->storeSource('source1.html');
        $browser->visit($url);
        $browser->pause(2000);  // wait for 2 seconds

        // You can also scroll by pixels
        $browser->script('window.scrollBy(0, 500);'); // Scroll down 500 pixels

        $browser->script('window.scrollTo(0, document.body.scrollHeight);');

        $browser->pause(1000);  // Wait for 1 second after scrolling

        $browser->waitForText('Peignot', 10);
        $browser->storeConsoleLog('console2.log');

        foreach ($mustFind as $str) {
            if ($this->verbose()) echo ($suburl . ': assertSee: ' . $str . "\n");
            $browser
                ->maximize()
                // ->screenshot('assertSee_' . $str)
                // ->assertSourceHas($str)
                ->waitForText($str)
                ->assertSee($str);
        }
        foreach ($mustNotFind as $str) {
            if ($this->verbose()) echo ($suburl . ': assertDontSee: ' . $str . "\n");
            $browser->assertDontSee($str);
        }
        foreach ($inputValues as $field) {
            if ($this->verbose()) echo ($suburl . ': assertInput: ' . $field['selector'] . ', ' . $field['value'] .  "\n");
            $browser->assertInputValue($field['selector'], $field['value']);
        }

        $browser->screenshot('page_' . str_replace('/', '_', $suburl));
    }

    /**
     * Checks if the user can access a test page.
     */
    public function canAccessTest($browser, $suburl, $mustFind = [], $mustNotFind = [], $inputValues = []) {
        $url =  $this->fullUrl($suburl);
        if ($this->verbose()) echo ("Visiting $url\n");
        $browser->visit($url);
        sleep(2);

        foreach ($mustFind as $str) {
            if ($this->verbose()) echo ($suburl . ': assertSee: ' . $str . "\n");
            $browser->assertSee($str);
        }
        foreach ($mustNotFind as $str) {
            if ($this->verbose()) echo ($suburl . ': assertDontSee: ' . $str . "\n");
            $browser->assertDontSee($str);
        }
        $browser->screenshot('page_' . str_replace('/', '_', $suburl));
    }

    /**
     * Get json data from a page.
     */
    public function getJsonData($browser, $suburl) {

        $url =  $this->fullUrl($suburl);

        // $json = $browser->script('return JSON.stringify(window.data);')[0];
        echo "url = $url\n";
        $content = file_get_contents($url);
        echo "content = $content\n";
        $json = json_decode($content, true);
        var_dump($json);

        return $json;
    }

    /**
     * Checks if the test runs in verbose mode.
     */
    public function verbose() {
        global $argv;
        if (in_array('--verbose', $argv)) return true;
        if (in_array('-v', $argv)) return true;
        return false;
    }

    /**
     * Retrieves the total number of rows in a table on a web page.
     *
     * @param Browser $browser The browser instance used for navigation
     * @param string $route Optional route to navigate to before counting rows
     * @param array $mustSee Optional array of strings that must be visible on the page
     * @return int Total number of rows in the table
     */
    public function PageTableRowCount($browser, $route = "", $mustSee = []) {

        if ($route != "") {
            $this->canAccess($browser, $route, $mustSee);
        }

        $counter_selector = '#DataTables_Table_0_info';
        $browser->waitFor($counter_selector);
        $browser->scrollIntoView($counter_selector);
        // TODO wait for the real event
        $browser->pause(1000);
        $counter = $browser->text($counter_selector);
        // echo "Counter: $counter";
        $pattern = '/(\d+) à (\d+) sur (\d+) éléments/';
        if (preg_match($pattern, $counter, $matches)) {
            $from = $matches[1];
            $to = $matches[2];
            $total = $matches[3];
            // echo "From: $from, To: $to, Total: $total";
            return $total;
        } else {
            throw new Exception("No match for $pattern in $counter");
        }
    }

    /*
     * Extract the values of a select from an HTML page
     * returns an array of values => text
     */
    public function geyValuesFromSelect($browser, $page, $id) {
        $this->canAccess($browser, $page, []);

        $js = "
        var result = [];
        var select = document.getElementById('" . $id . "');
        var options = select.options;
        for (var i = 0; i < options.length; i++) {
            text = options[i].text;
            value = options[i].value;
            result.push(value + ',' + text);
        }
        return result;";

        $sel = [];
        $results = $browser->script($js)[0];
        foreach ($results as $result) {
            $values = explode(',', $result);
            $sel[$values[0]] = $values[1];
        }
        return $sel;
    }

    /**
     * Check that the installation can be reset and installed
     * Reset the dusk test data.
     *
     * @return void
     */
    public function testCheckInstallationProcedure() {

        $this->check_environement();
        Log::info("Test: testCheckInstallationProcedure");

        $this->browse(function (Browser $browser) {

            $url = $this->url . 'install/reset.php';
            // echo "Visiting $url\n";
            $browser->visit($url)
                ->assertSee("Verification de l'installation")
                ->assertSee($this->url . 'install');

            $browser->visit($this->url . '/install/?db=dusk_tests.sql');

            $browser->assertSee('Installation de GVV')
                ->assertSee("Fin de la procédure d'installation");

            $this->login($browser, env('TEST_USER'), env('TEST_PASSWORD'), '1');
            $browser->visit($this->fullUrl('migration'))
                ->assertSee('Migration de la base de données')
                ->press("Valider")
                ->assertSee('à jour');

            // Check that the database contains expected data
            $this->assertEquals(3, $this->PageTableRowCount($browser, "planeur/page"));
            $this->assertEquals(2, $this->PageTableRowCount($browser, "avion/page"));
            $this->assertEquals(4, $this->PageTableRowCount($browser, "membre/page"));
            $this->logout($browser);
        });
    }

    /**
     * Function to extract the href of the edit icon of a table row
     * $rank = 1    edit link
     * $rank = 2    delete link
     * $rank = 3    clone link
     */
    public function getHrefFromTableRow($browser, $pattern, $rank = "1") {

        $script = "return document.evaluate(
                    \"//tr[contains(., '$pattern')]//td[" . $rank . "]//a\", 
                    document, 
                    null, 
                    XPathResult.FIRST_ORDERED_NODE_TYPE, 
                    null
                ).singleNodeValue.getAttribute('href');";

        return $browser->script([
            $script
        ])[0];
    }

    // Function to extract the id of an element from the table view
    public function getIdFromTable($browser, $pattern) {
        $href = $this->getHrefFromTableRow($browser, $pattern);

        return basename($href);
    }

    /**
     * Function to extract a column value from a table row
     */
    public function getColumnFromTableRow($browser, $table_id, $pattern, $index) {

        $result = $browser->script(
            "return (function(tableId, pattern, index) {
                const selector = tableId + ' tbody tr';
                const row = Array.from(document.querySelectorAll(selector)).find(
                    row => row.textContent.includes(pattern)
                );
                return row?.querySelector('td:nth-child(' + index + ')')?.innerHTML;
            })(
                " . json_encode($table_id) . ",
                " . json_encode($pattern) . ",
                " . json_encode($index) . "
            );"
        )[0];
        return $result;
    }

    protected function savePageSource(Browser $browser, $filename = null) {
        $filename = $filename ?? 'page_source_' . time();
        $browser->storeSource($filename);
    }

    /**
     * Delete a table row that matches a specific pattern.
     *
     * @param \Laravel\Dusk\Browser $browser The browser instance
     * @param string|array $pattern The pattern(s) to match in the row
     * @param string $tableSelector Optional custom table selector, defaults to 'tbody'
     * @return void
     */
    public function deleteRowByPattern(Browser $browser, $pattern, $tableSelector = 'tbody', $acceptDIalog = TRUE) {
        // Convert single pattern to array
        $patterns = is_array($pattern) ? $pattern : [$pattern];

        $browser->waitFor($tableSelector)
            ->with($tableSelector, function ($table) use ($patterns) {
                // Build the selector by combining patterns
                $selector = 'tr';
                foreach ($patterns as $pattern) {
                    $selector .= ':contains("' . $pattern . '")';
                }

                // Find the matching row and click its delete button
                $table->whenAvailable($selector, function ($row) {
                    $row->element('a[href*="/delete/"]')->click();
                });
            });

        if ($acceptDIalog) $browser->acceptDialog();
    }

    /**
     * Extract data from an HTML table using Laravel Dusk and convert it to an array
     * 
     * @param \Laravel\Dusk\Browser $browser The Dusk browser instance
     * @param string $tableSelector The CSS selector for the table
     * @param bool $includeHeaders Whether to include table headers in the result (default: true)
     * @return array The table data as a PHP array
     */
    // function extractTableToArray($browser, $tableSelector, $includeHeaders = true) {
    //     // Initialize the result array
    //     $tableData = [];

    //     // Check if the table exists
    //     if (!$browser->element($tableSelector)) {
    //         throw new \Exception("Table with selector '{$tableSelector}' not found");
    //     }

    //     // Use JavaScript to extract the table data
    //     return $browser->script("
    //     return (function() {
    //         const table = document.querySelector('" . addslashes($tableSelector) . "');
    //         if (!table) {
    //             return [];
    //         }

    //         const result = [];

    //         // Get headers if requested
    //         if (" . ($includeHeaders ? 'true' : 'false') . ") {
    //             const headerCells = table.querySelectorAll('thead > tr > th, tr > th');
    //             if (headerCells.length > 0) {
    //                 const headerRow = [];
    //                 headerCells.forEach(cell => {
    //                     headerRow.push(cell.innerText.trim());
    //                 });
    //                 if (headerRow.length > 0) {
    //                     result.push(headerRow);
    //                 }
    //             }
    //         }

    //         // Get data rows
    //         const rows = table.querySelectorAll('tbody > tr, tr');
    //         for (let i = 0; i < rows.length; i++) {
    //             const row = rows[i];
    //             // Skip header rows
    //             if (row.querySelector('th')) {
    //                 continue;
    //             }

    //             const rowData = [];
    //             const cells = row.querySelectorAll('td');
    //             for (let j = 0; j < cells.length; j++) {
    //                 rowData.push(cells[j].innerText.trim());
    //             }

    //             if (rowData.length > 0) {
    //                 result.push(rowData);
    //             }
    //         }

    //         return result;
    //     })();
    // ")[0];
    // }

    /**
     * Extract data from an HTML table using Laravel Dusk and convert it to an array with innerHTML
     * 
     * @param \Laravel\Dusk\Browser $browser The Dusk browser instance
     * @param string $tableSelector The CSS selector for the table
     * @param bool $includeHeaders Whether to include table headers in the result (default: true)
     * @return array The table data as a PHP array with innerHTML of each cell
     */
    function extractTableToArray($browser, $tableSelector, $includeHeaders = true) {
        // Check if the table exists
        if (!$browser->element($tableSelector)) {
            throw new \Exception("Table with selector '{$tableSelector}' not found");
        }

        // Use JavaScript to extract the table data with innerHTML
        return $browser->script("
        return (function() {
            const table = document.querySelector('" . addslashes($tableSelector) . "');
            if (!table) {
                return [];
            }
            
            const result = [];
            
            // Get headers if requested
            if (" . ($includeHeaders ? 'true' : 'false') . ") {
                const headerCells = table.querySelectorAll('thead > tr > th, tr > th');
                if (headerCells.length > 0) {
                    const headerRow = [];
                    headerCells.forEach(cell => {
                        headerRow.push(cell.innerHTML);
                    });
                    if (headerRow.length > 0) {
                        result.push(headerRow);
                    }
                }
            }
            
            // Get data rows
            const rows = table.querySelectorAll('tbody > tr, tr');
            for (let i = 0; i < rows.length; i++) {
                const row = rows[i];
                // Skip header rows
                if (row.querySelector('th')) {
                    continue;
                }
                
                const rowData = [];
                const cells = row.querySelectorAll('td');
                for (let j = 0; j < cells.length; j++) {
                    rowData.push(cells[j].innerHTML);
                }
                
                if (rowData.length > 0) {
                    result.push(rowData);
                }
            }
            
            return result;
        })();
    ")[0];
    }

    /**
     * Extract data from an HTML table using Laravel Dusk and convert it to an associative array
     * using column headers as keys
     * 
     * @param \Laravel\Dusk\Browser $browser The Dusk browser instance
     * @param string $tableSelector The CSS selector for the table
     * @param string $contentType The type of content to extract: 'innerHTML', 'outerHTML', or 'text' (default: 'innerHTML')
     * @return array The table data as an associative array with column headers as keys
     * array:2 [
  "headers" => array:12 [
    0 => "column_0"
    1 => "column_1"
    2 => "Modèle"
    3 => "Constructeur"
    4 => "Immatriculation"
    5 => "Section"
    6 => "Places"
    7 => "Remorqueur"
    8 => "Pirvé"
    9 => "Actif"
    10 => "Vols"
    11 => "Année"
  ]
  "rows" => array:2 [
    0 => array:12 [
      "Actif" => "<img class="icon" src="http://gvv.net/themes/binary-news/images/tick.png" alt="">"
      "Année" => "0"
      "Constructeur" => "Robin"
      "Immatriculation" => "F-GUFB"
      "Modèle" => "DR400"
      "Pirvé" => ""
      "Places" => "4"
      "Remorqueur" => "<img class="icon" src="http://gvv.net/themes/binary-news/images/tick.png" alt="">"
      "Section" => "Planeur"
      "Vols" => "<a href="http://gvv.net/vols_avion/vols_de_la_machine/F-GUFB">vols</a>"
      "column_0" => "<a href="http://gvv.net/avion/edit/F-GUFB"><img class="icon" src="http://gvv.net/themes/binary-news/images/pencil.png" title="Changer" alt=""></a>"
      "column_1" => "<a href="http://gvv.net/avion/delete/F-GUFB" onclick="return confirm('Etes vous sûr de vouloir supprimer F-GUFB?')"><img class="icon" src="http://gvv.net/themes/binary-news/images/delete.png" title="Supprimer" alt=""></a>"
    ]
      ...
     */
    function extractTableToAssociativeArray($browser, $tableSelector, $contentType = 'innerHTML') {
        // Validate content type
        if (!in_array($contentType, ['innerHTML', 'outerHTML', 'text'])) {
            throw new \InvalidArgumentException("Content type must be 'innerHTML', 'outerHTML', or 'text'");
        }

        // Check if the table exists
        if (!$browser->element($tableSelector)) {
            throw new \Exception("Table with selector '{$tableSelector}' not found");
        }

        // JavaScript property to use based on content type
        $jsProperty = $contentType === 'text' ? 'innerText' : $contentType;

        // Use JavaScript to extract the table data
        return $browser->script("
        return (function() {
            const table = document.querySelector('" . addslashes($tableSelector) . "');
            if (!table) {
                return { headers: [], rows: [] };
            }
            
            // Get headers first - we need these for the associative array keys
            const headerCells = table.querySelectorAll('thead > tr > th, tr > th');
            if (headerCells.length === 0) {
                return { error: 'No header cells found in table. Cannot create associative array.' };
            }
            
            // Extract header texts for keys (using trim for text to create cleaner keys)
            const headers = [];
            let emptyColumnCounter = 0;
            headerCells.forEach(cell => {
                // Use innerText for keys even if we're extracting innerHTML for values
                // This makes the keys more usable and consistent

                const headerText = cell.innerText.trim();
                if (headerText === '') {
                    // Use numerical index for empty headers
                    headers.push('column_' + emptyColumnCounter);
                    emptyColumnCounter++;
                } else {
                    // Reset counter when we encounter a non-empty header
                    emptyColumnCounter = 0;
                    headers.push(headerText);
                }
            });
            
            // Get data rows
            const result = [];
            const rows = table.querySelectorAll('tbody > tr, tr');
            
            for (let i = 0; i < rows.length; i++) {
                const row = rows[i];
                // Skip header rows
                if (row.querySelector('th')) {
                    continue;
                }
                
                const cells = row.querySelectorAll('td');
                // Skip if number of cells doesn't match number of headers
                if (cells.length !== headers.length) {
                    continue;
                }
                
                const rowData = {};
                for (let j = 0; j < cells.length; j++) {
                    // Use header text as the key for each cell
                    rowData[headers[j]] = cells[j]['" . $jsProperty . "'];
                }
                
                result.push(rowData);
            }
            
            return { headers: headers, rows: result };
        })();
    ")[0];
    }


    /**
     * Extracts the href attribute value from an HTML string.
     *
     * @param string $html The HTML string to extract the href from.
     * @return string|null The href value if found, or null if not found.
     */
    function extractHref($html) {
        if (preg_match('/<a\s+[^>]*href=["\']([^"\']+)["\']/i', $html, $matches)) {
            return $matches[1];
        }
        return null;
    }

    /**
     * Get the HTML content of an element using Laravel Dusk
     *
     * @param \Laravel\Dusk\Browser $browser The Dusk browser instance
     * @param string $selector The CSS selector for the element
     * @return string|null The HTML content of the element or null if not found
     * @throws \Exception If the element is not found and $throwException is true
     */
    function getElementHtml($browser, $selector, $throwException = true) {
        try {
            // Check if element exists
            if (!$browser->element($selector)) {
                if ($throwException) {
                    throw new \Exception("Element with selector '{$selector}' not found");
                }
                return null;
            }

            // Use JavaScript to get the outer HTML of the element
            return $browser->script("
            return (function() {
                const element = document.querySelector('" . addslashes($selector) . "');
                return element ? element.outerHTML : null;
            })();
        ")[0];
        } catch (\Exception $e) {
            if ($throwException) {
                throw $e;
            }
            return null;
        }
    }

    /**
     * Purchase something
     **/
    protected function purchase($browser, $account_id, $product, $quantity = 1, $comment = "", $cost = 0) {

        $account_handler = new AccountHandler($browser, $this);

        $solde_initial = $account_handler->AccountTotal($account_id);

        $browser->visit($this->fullUrl('compta/journal_compte/' . $account_id));
        $browser->script('document.body.style.zoom = "0.5"');

        // Achat
        Log::debug("purchase account=$account_id, product=$product, quantity=$quantity, cost=$cost, comment=$comment");
        $browser // ->click('#panel-achats > .accordion-button')
            ->scrollIntoView('#validation_achat')
            ->waitFor('#validation_achat');

        $browser->click('#select2-product_selector-container')
            ->waitFor('.select2-search__field')
            ->type('.select2-search__field', $product)
            ->waitFor('.select2-results__option')
            ->click('.select2-results__option');
        // ->assertSelected('#product_selector', $product);

        $browser->type('quantite', $quantity);
        // ->click('.form-group:nth-child(1) > .form-control')
        if ($comment) $browser->type('.form-group:nth-child(4) > .form-control', $comment);

        $browser->click('#validation_achat');

        $nouveau_solde = $account_handler->AccountTotal($account_id);

        if ($cost) {
            echo "cost=$cost, solde_initial=$solde_initial, nouveau_solde=$nouveau_solde\n";
            $this->assertEquals($nouveau_solde, $solde_initial - $cost);
        }
    }
}
