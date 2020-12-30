#!/bin/bash

echo -ne "\e]0;laravel.serve.log\a"

log=./storage/logs/laravel.log
echo '' > $log
tail -f $log
