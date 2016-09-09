#!/bin/bash

input_directory=$1
device=$2
output_format=$3

scripts="/var/www/html/assets/php/burner/scripts"

if [[ ! $input_directory || ! $device || ! $output_format ]]; then
	echo "Invalid argument(s) in burner-handler.sh"
	exit
fi

normalize() {
	# Normalize the audio
	tracks=$(find "$input_directory" -type f -name "*.$1")
	echo "Normalizing" > $status_file
	for track in $tracks;
	do
		$scripts/normalize-audio.sh "$track" $output_file
	done
}


# If the output_format is wav, then decode all the tracks in input_directory.
# Remove all MP3 files after the process is complete.
if [ "$output_format" == wav ]; then
	echo "Decoding" > $status_file
	$scripts/decode.sh "$input_directory" $output_file

	normalize "wav"

	echo "Burning" > $status_file
	# $scripts/burn-disc-wav.sh "$device" $input_directory $output_file
fi


# If the output_format is mp3, an iso file  - tracks.iso - is created.
# Remove all MP3 files after the process is complete.
if [ "$output_format" == mp3 ]; then
	file_name="tracks_file"

	normalize "mp3"

	echo "ISO" > $status_file
	$scripts/mp3toiso.sh "$input_directory" $file_name $output_file

	echo "Burning" > $status_file
	# $scripts/burn-disc-mp3.sh "$device" $input_directory $file_name $output_file
fi

# rm -rfv $input_directory

exit