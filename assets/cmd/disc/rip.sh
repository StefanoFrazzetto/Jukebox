#!/bin/bash

output_dir=$1
target=/var/www/html/jukebox/cdparanoia/

rm -rfv "$target*"

device=$2

#echo "Device: " $device

if [ "$device" == "" ]; then
	exit
fi;

mkdir -p "$target"

total=$(cdparanoia -sQ -d /dev/$device |& grep -P -c "^\s+\d+\." | grep -E '[0-9]')
#echo "Ripping the CD"
cdparanoia -vB -X -d /dev/${device} 1:- "$target" &> /dev/null &
#echo "Waiting for the process to complete"

current=0

lasttrack=0

	while [ "$total" -ne "$current" ];
	do
		sleep 5
		current=$(find "$target" -type f | wc -l | grep -E '[0-9]')

			# if [ "$lasttrack" -ne "$current" ]; then
			# 	lasttrack=$current
			# 	echo "Ripping track $current out of $total"
			# fi
	done
#echo "Ripping complete!"

bash encode.sh "$output_dir"
exit
