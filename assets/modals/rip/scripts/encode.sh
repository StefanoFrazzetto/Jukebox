#!/bin/bash

TRACKS=$(find /var/www/html/jukebox/cdparanoia/ -type f -name "*.wav")
TARGET="/var/www/html/jukebox/ripper_encoded"

mkdir -p "$TARGET"

for track in "$TRACKS";
do
	tr=$(basename "$track")
	track_name="${tr%.*}"
	#echo "Now converting $trackname"
	lame --vbr-new "$track" "$TARGET/$track_name".mp3
done
exit
