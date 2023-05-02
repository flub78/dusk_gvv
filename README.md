# DUSK for GVV

This project contains the end to end tests for GVV. 

Prerequisites;

GVV must be up and running.

    https://gvv.flub78.net/gvv

and a testadmin / password acount must exists

## Installation

    sudo apt install composer

    composer update

    sudo apt-get -y install libxpm4 libxrender1 libgtk2.0-0 libnss3 libgconf-2-4
    sudo apt-get install chromium-browser
    sudo apt-get -y install xvfb gtk2-engines-pixbuf
    sudo apt-get -y install xfonts-cyrillic xfonts-100dpi xfonts-75dpi xfonts-base         xfonts-scalable


    Xvfb -ac :0 -screen 0 1280x1024x16 &
    php artisan dusk:chrome-driver
    ChromeDriver binary successfully installed for version 112.0.5615.49.
    
    https://stackoverflow.com/questions/42040362/laravel-dusk-error-failed-to-connect-to-localhost-port-9515-connection-refused
    
    Failed to connect to localhost port 9515 after 0 ms: Connection refused
    
## Tests execution

    php artisan dusk --browse
    or
    run\tests.bat

For individual tests:

    php artisan dusk --color=always --browse tests/Browser/ExampleTest.php
    php artisan dusk --color=always --browse tests/Browser/PlanchisteAccessTest.php
    php artisan dusk --color=always --browse tests/Browser/PlaneurTest.php
    php artisan dusk --color=always --browse tests/Browser/TerrainTest.php
    php artisan dusk --color=always --browse tests/Browser/BureauAccessTest.php
    php artisan dusk --color=always --browse tests/Browser/CAAccessTest.php

If the tests are run on a Dropbox shared file system, disable synchronisation to avoid errors.

## In case of incorrect chrome-driver version

    php artisan dusk:chrome-driver

## Generating tests

    php artisan dusk:make LoginTest

## Documentation

    https://laravel.com/docs/10.x/dusk

## GVV testing

Even if it is still actively maintained, regarding automated testing GVV can be considered legacy.

* as phpunit was poorly integrated with CodeIgniter 2.x, the unit tests are written with CIUnit which cannot generate junit.xml

* I'll try to regain control with Dusk tests. They may be a little slow for some purpose but at least it is a way to get minimal automated test covergage.

* One difficulty comes the table dependencies, accounts and membres are needed to create gliders, gileders and pilots are required to create flights, etc.

## Target update

There is no automatic update of the project under test. It must be done manually or by the CI/CD pipeline.
