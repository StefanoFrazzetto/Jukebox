#!/bin/bash

input_directory=$1
file_name=$2

nice mkisofs -output "$input_directory/$file_name.iso" "$input_directory" > $3 2>&1

exit