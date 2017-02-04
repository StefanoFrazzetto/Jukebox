#!/bin/bash
# $1 is the folder NAME
# $2 is the file EXTENSION
DIRECTORY=/$1/
EXTENSION=$2
if [ -d "$DIRECTORY" ]; then
  count=$(ls -l "$DIRECTORY" | grep .*."$EXTENSION" | wc -l)
fi

echo $count
