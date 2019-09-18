#!/bin/bash

log=./storage/logs/laravel.log
echo '' > $log
tail -f $log
