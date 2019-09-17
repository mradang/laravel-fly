#!/bin/bash

log=./storage/logs/lumen.log
echo '' > $log
tail -f $log
