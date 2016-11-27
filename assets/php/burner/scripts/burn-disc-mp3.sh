#!/bin/bash
device=$1
input_directory=$2
file_name=$3

wodim -dao -speed=2 -eject dev="/dev/$device" ${input_directory}/${file_name}.iso > /tmp/burner.log 2>&1

exit