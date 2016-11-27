#!/bin/bash

input_directory=$1
file_name=$2
log_file=$3

nice mkisofs -output "$input_directory/$file_name.iso" "$input_directory" > $log_file 2>&1

exit