#!/bin/bash

DIRECTORY=/var/www/html/jukebox/cdparanoia/
TRACKS=0

if [ -d "$DIRECTORY" ]; then
    TRACKS=$(ls -l "$DIRECTORY" | grep .*.wav | wc -l)
fi

echo "$TRACKS"
