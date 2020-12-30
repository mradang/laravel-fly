#!/bin/bash

echo -ne "\e]0;laravel.serve.schedule\a"

while (true)
do
    php artisan schedule:run
    sleep 60
done
