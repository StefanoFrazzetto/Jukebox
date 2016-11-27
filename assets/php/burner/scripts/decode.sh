#!/bin/bash

input_directory=$1
status_file_dir=$2

tracks=$(find "$input_directory" -type f -name "*.mp3")

for track in $tracks;
do
	tr=$(basename "$track")
	output="${tr%.*}"

    nice lame --decode -s 44.1 "$track" > "${status_file_dir}/burner-decode.log" 2>&1
done
exit
