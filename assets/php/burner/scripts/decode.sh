#!/bin/bash

input_directory=$1

tracks=$(find "$input_directory" -type f -name "*.mp3")

for track in $tracks;
do
	tr=$(basename "$track")
	output="${tr%.*}"

    nice lame --decode -s 44.1 "$track" > $2 2>&1
done
exit
