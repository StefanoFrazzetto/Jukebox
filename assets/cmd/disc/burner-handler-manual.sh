#!/bin/bash

echo -e "\n### Burner manual startup"
read -p "### Album ID: " album_ID
read -p "### wav OR mp3? " OUTPUT_FORMAT
read -p "### Enter output logs directory: " OUTPUT_LOG_DIR
echo -e "\n\n### STARTING THE PROCES..."

DEVICE=$(less /proc/sys/dev/cdrom/info | grep 'drive name:'| awk {'print $3'})
ALBUM_DIRECTORY=/var/www/html/jukebox/${album_ID}
BURNER_DIRECTORY=/var/www/html/jukebox/burner_tmp

GENERAL_LOG="$OUTPUT_LOG_DIR/burner-status.log"

scripts="/var/www/html/assets/php/burner/scripts"

if [[ ! "$ALBUM_DIRECTORY" || ! "$OUTPUT_FORMAT" ]]; then
	echo "Invalid argument(s) in burner-handler.sh"
	exit
fi

normalize() {
	# Normalize the audio
	tracks=$(find "$BURNER_DIRECTORY" -type f -name "*.$1")
	echo "Normalizing" >> "$GENERAL_LOG"
	for track in $tracks;
	do
		${scripts}/normalize-audio.sh "$track" > "$OUTPUT_LOG_DIR/burner-normalize.log"
	done
}

# Copy the tracks
echo "### Copying requested tracks"
mkdir -p "$BURNER_DIRECTORY"
cp -R -v ${ALBUM_DIRECTORY}/*.mp3 "$BURNER_DIRECTORY"


# If the output_format is wav, then decode all the tracks in input_directory.
# Remove all MP3 files after the process is complete.
if [ "$OUTPUT_FORMAT" == wav ]; then
	echo "Decoding" > "$GENERAL_LOG"
	${scripts}/decode.sh "$BURNER_DIRECTORY" "$OUTPUT_LOG_DIR"

	normalize "wav"

	echo "Burning" > "$GENERAL_LOG"
	${scripts}/burn-disc-wav.sh "$DEVICE" "$BURNER_DIRECTORY" "$OUTPUT_LOG_DIR/burner-burning.log"
fi


# If the output_format is mp3, an iso file  - tracks.iso - is created.
# Remove all MP3 files after the process is complete.
if [ "$OUTPUT_FORMAT" == mp3 ]; then
	file_name="tracks_file"

	normalize "mp3"

	echo "ISO" > "$GENERAL_LOG"
	${scripts}/mp3toiso.sh "$BURNER_DIRECTORY" "$file_name" "$OUTPUT_LOG_DIR/burner-mp3iso.log"

	echo "Burning" > "$GENERAL_LOG"
	${scripts}/burn-disc-mp3.sh "$DEVICE" "$BURNER_DIRECTORY" "$file_name" "$OUTPUT_LOG_DIR/burner-burning.log"
fi

# rm -rfv $input_directory

exit