#!/bin/bash
device=$1
input_directory=$2

sudo cdrecord -nocopy -eject dev="/dev/$device" -audio -pad $input_directory/*.wav > $3 2>&1

exit