# DUSK for GVV

This project contains the end to end tests for GVV. 

Prerequisites;

GVV must be up and running.

    https://gvv.flub78.net/gvv

and a testadmin / password acount must exists

## Installation

### On Windows

    install composer
    composer update
    php artisan dusk:chrome-driver


### On Linux

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
    

## In case of incorrect chrome-driver version

The following command does not work any more with the latest versions of chromedriver

    php artisan dusk:chrome-driver

Current method

    https://googlechromelabs.github.io/chrome-for-testing/

### On Windows

    choose stable, download the win64 version.

    unzip, copy into C:\Users\frede\Dropbox\xampp\htdocs\dusk_gvv\vendor\laravel\dusk\bin

    rename as chromedriver-win.exe

### On Linux

    choose stable, download the Linux64 version.

    unzip, copy into vendor/laravel/dusk/bin

    rename as chromedriver-linux

    If chromedriver is running, restart it.


## Generating tests

    php artisan dusk:make LoginTest

## Documentation

    https://laravel.com/docs/10.x/dusk

## GVV testing

Even if it is still actively maintained, most GVV automated tests were obsolete in april 2023.

* as phpunit was poorly integrated with CodeIgniter 2.x, the initial unit tests are written with CIUnit which cannot generate junit.xml
 
* phpunit tests were limited to some libraries and helpers no controllers and they were not working anymore. A the time of the project start phpunit was not officialy supported in CodeIgniter. Some third parties have provided partial support but these project are no more maintained.
  
* End to end tests were developed in Ruby with watir. The environment was difficult to deploy, it implied to master another programing language and the tests were made obsolete by the swith of the GUI to Bootstrap.
  
* this project is an effort to regain control with Dusk tests. They may be a little slow for some purpose but at least it is a way to get minimal automated test covergage.

* One difficulty comes the table dependencies, accounts and membres are needed to create gliders, gileders and pilots are required to create flights, etc.

## Target update

There is no automatic update of the project under test. It must be done manually or by the CI/CD pipeline.

## Running Tests

    The GVV server to test must be up and running. (maybe locally). The target machine must have the correct PHP version installed (PHP 7.x in 2023). The Dusk tests require PHP > 8.1.x. This setting is done inside the test execution console before to launch the tests.

    Environment variables must be set to the correct values.
    TARGET
    TEST_USER
    TEST_PASSWORD

    Test execution does not require a local WEB server, only a WEB server for the program under test.

    php artisan dusk --browse
    or
    run\tests.bat

For individual tests:

    php artisan dusk --color=always --browse tests/Browser/ExampleTest.php
    php artisan dusk --color=always --browse tests/Browser/CIUnitTest.php
    php artisan dusk --color=always --browse tests/Browser/InstallationTest.php
    php artisan dusk --color=always --browse tests/Browser/AdminAccessTest.php
    php artisan dusk --color=always --browse tests/Browser/BureauAccessTest.php
    php artisan dusk --color=always --browse tests/Browser/CAAccessTest.php
    php artisan dusk --color=always --browse tests/Browser/PlanchisteAccessTest.php
    php artisan dusk --color=always --browse tests/Browser/PlaneurTest.php
    php artisan dusk --color=always --browse tests/Browser/TerrainTest.php

    php artisan dusk --color=always --browse tests/Browser/SmokeTest.php

    php artisan dusk --color=always --browse tests/Browser/DbInitTest.php
    php artisan dusk --color=always --browse tests/Browser/GliderFlightTest.php

If the tests are run on a Dropbox shared file system, disable synchronisation to avoid errors.

