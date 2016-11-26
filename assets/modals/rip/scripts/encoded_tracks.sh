#!/bin/bash
# $1 is the folder name
DIRECTORY=/var/www/html/jukebox/ripper_encoded/$1/
TRACKS=0

if [ -d "$DIRECTORY" ]; then
    TRACKS=$(ls -l "$DIRECTORY" | grep .*.mp3 | wc -l)
fi

echo "$TRACKS"
