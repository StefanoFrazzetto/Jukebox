#!/bin/bash

track=$1
log_file=$2

nice normalize-audio -m "$track" > $log_file 2>&1
# > $2 2>&1

exit