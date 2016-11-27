#!/bin/bash
device=$1
input_directory=$2
log_file=$3

wodim speed=1 -nocopy -eject dev="/dev/$device" -pad -audio ${input_directory}/*.wav > $log_file 2>&1

exit