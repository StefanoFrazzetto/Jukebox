#!/bin/bash
rippedTracks=$(ls -l /var/www/html/jukebox/cdparanoia/ | grep .*.wav | wc -l)

echo $rippedTracks
