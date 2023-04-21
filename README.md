# DUSK for GVV

This project contains the end to end tests for GVV. 

Prerequisites;

GVV must be up and running.

    https://gvv.flub78.net/gvv

and a testadmin / password acount must exists

## Tests execution

    php artisan dusk --browse
    or
    run\tests.bat

For individual tests:

    php artisan dusk --color=always --browse tests/Browser/PlanchisteAccessTest.php
    php artisan dusk --color=always --browse tests/Browser/PlaneurTest.php
    php artisan dusk --color=always --browse tests/Browser/TerrainTest.php
    php artisan dusk --color=always --browse tests/Browser/BureauAccessTest.php
    php artisan dusk --color=always --browse tests/Browser/CAAccessTest.php

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
