# DUSK project template

This project is just a minimal Laravel instance to deploy a Dusk end to end test suite. The idea is to use Dusk to test non Laravel projects

## Tests execution

    php artisan dusk --colors=always --browse tests/Browser/ExampleTest.php
    php artisan dusk tests/Browser/LoginTest.php
    php artisan dusk --colors=always --browse

## In case of incorrect chrome-driver version

    php artisan dusk:chrome-driver

## Generating tests

    php artisan dusk:make LoginTest

## Documentation

    https://laravel.com/docs/10.x/dusk
