#!/bin/bash

tracks=$(find /var/www/html/jukebox/cdparanoia/ -type f -name "*.wav")
target="/var/www/html/jukebox/ripper_encoded"

mkdir -p "$target"

for track in $tracks;
do
	tr=$(basename "$track")
	trackname="${tr%.*}"
	#echo "Now converting $trackname"
	lame --vbr-new "$track" "$target/$trackname".mp3
done
exit
