#!/bin/bash

echo -ne "\e]0;laravel.serve\a"
php -S 0.0.0.0:8000 -t public/
