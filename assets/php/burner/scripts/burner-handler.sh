#!/bin/bash

# Created by Stefano Frazzetto

scripts="/var/www/html/assets/php/burner/scripts"
status_file="/tmp/burner_status.json"
message_file="/tmp/burner_message.json"

status="Idle"

if [[ ! $input_directory || ! $device || ! $output_format ]]; then
	echo "Invalid argument(s) in burner-handler.sh"
	exit
fi

setStatus() {
	echo -e "{\"status\":\""$1"\"}" > $status_file
}

setPercentage() {
	echo -e "{\"partial\":"$1", \"total\":"$2"}" > $message_file
}

setMessage() {
	echo -e "{\"message\":\""$1"\"}" > $message_file
}

normalize() {
	# Normalize the audio
	# No output, just 
	setStatus "Normalizing"

	tracks=$(find "$input_directory" -type f -name "*.$1")

	total_tracks=$(ls -l $input_directory | grep .*.mp3 | wc -l)
	counter=0
	for track in $tracks;
	do
		counter=$((counter+1))
		setPercentage $counter $total_tracks
		$scripts/normalize-audio.sh "$track" "$output_log_dir/normalize.log"
	done
}


# If the output_format is wav, then decode all the tracks in input_directory.
# Remove all MP3 files after the process is complete.
if [ "$output_format" == wav ]; then
	setStatus "Decoding"

	$scripts/decode.sh "$input_directory" "$output_log_dir/decode.log"

	normalize "wav"

	setStatus "Burning"
	$scripts/burn-disc-wav.sh "$device" $input_directory "$output_log_dir/burn.log"
fi


# If the output_format is mp3, an iso file  - tracks.iso - is created.
# Remove all MP3 files after the process is complete.
if [ "$output_format" == mp3 ]; then
	file_name="tracks_file"

	normalize "mp3"

	setStatus "Creating ISO image"
	$scripts/mp3toiso.sh "$input_directory" $file_name $"$output_log_dir/mp3toiso.log"

	setStatus "Burning"
	$scripts/burn-disc-mp3.sh "$device" $input_directory $file_name "$output_log_dir/burn.log"
fi

#rm -rfv $input_directory

setStatus "Complete"

exit