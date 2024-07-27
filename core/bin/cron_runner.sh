#!/bin/bash

# Path to the PHP file
php_file="cron.php"

# Navigate to the core/bin directory
cd core/bin

while true
do
  # Run the PHP file
  php $php_file

  # Sleep for 1 minute (60 seconds)
  echo "Sleeping for 60 seconds..."
  sleep 60
done