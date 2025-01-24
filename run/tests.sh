rm tests\reports\junit.xml
php artisan dusk --colors=always --browse --log-junit=tests\reports\junit.xml
xunit-viewer -r tests\reports\junit.xml 
