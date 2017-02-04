#!/bin/bash
device=$1
input_directory=$2
log_file=$3


wodim -dao -speed=2 -eject dev="/dev/$device" ${input_directory}/*.iso > $log_file 2>&1

exit