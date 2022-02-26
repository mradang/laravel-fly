#!/bin/bash

echo -ne "\e]0;laravel.serve\a"
php artisan serve --host=0.0.0.0 --port=8000
