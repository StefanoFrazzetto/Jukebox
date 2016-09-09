#!/bin/bash
device=$1
input_directory=$2
file_name=$3

sudo cdrecord -dao -speed=4 -eject dev="/dev/$device" $input_directory/$file_name.iso > $4 2>&1

exit