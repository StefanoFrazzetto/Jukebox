#!/bin/bash

track=$1

nice normalize-audio -m "$track" > /dev/null 2>&1
# > $2 2>&1

exit