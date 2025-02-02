#!/bin/bash
# check_chromedriver.sh
# chromedriver must be in the path

# Check if chromedriver is running
if pgrep -x "chromedriver-li" > /dev/null; then
    echo "ChromeDriver is already running"
else
    echo "Starting ChromeDriver"
    chromedriver > /dev/null 2>&1 &
    # chromedriver
fi