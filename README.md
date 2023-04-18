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

    php artisan dusk --browse tests/Browser/PlanchisteAccessTest.php
    php artisan dusk --browse tests/Browser/PlaneurTest.php

## In case of incorrect chrome-driver version

    php artisan dusk:chrome-driver

## Generating tests

    php artisan dusk:make LoginTest

## Documentation

    https://laravel.com/docs/10.x/dusk
