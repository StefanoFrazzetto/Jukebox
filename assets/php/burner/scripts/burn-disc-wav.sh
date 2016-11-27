#!/bin/bash
device=$1
input_directory=$2

wodim speed=1 -nocopy -eject dev="/dev/$device" -pad -audio ${input_directory}/*.wav > /tmp/burner.log 2>&1

exit