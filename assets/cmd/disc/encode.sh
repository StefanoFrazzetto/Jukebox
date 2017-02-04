#!/bin/bash

TRACKS=$(find /var/www/html/jukebox/cdparanoia/ -type f -name "*.wav")
output_dir=$1

mkdir -p "$output_dir"

# Do not used quotes for $TRACKS otherwise it will be interpreted as a string
for track in $TRACKS;
do
	tr=$(basename "$track")
	track_name="${tr%.*}"
	#echo "Now converting $trackname"
	lame --vbr-new --silent "$track" "$output_dir/$track_name".mp3
done
exit
