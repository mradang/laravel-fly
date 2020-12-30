#!/bin/bash

echo -ne "\e]0;laravel.serve.queue\a"

php artisan queue:listen --sleep=10 --tries=1 --timeout=0
