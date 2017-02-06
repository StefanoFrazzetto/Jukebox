#!/bin/bash

# Set this script directory
THIS_DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )"

source ${THIS_DIR}/../functions.sh
source ${THIS_DIR}/disc_statuses.sh

DEVICE="$device"
CDPARANOIA_LOG_PATH="$cdparanoia_log_path"
LAME_LOG_PATH="$lame_log_path"
RIPPING_DIR="$ripping_dir"
ENCODING_DIR="$encoding_dir"

rmDir "$RIPPING_DIR"
mkdir -p "$RIPPING_DIR"

rmdir "$ENCODING_DIR"
mkdir -p "$ENCODING_DIR"

mkdir -p "$(dirname "$CDPARANOIA_LOG_PATH")"
mkdir -p "$(dirname "$LAME_LOG_PATH")"

## START THE RIPPING PROCESS
#
# -v --verbose
#   Be absurdly verbose about the auto-sensing and reading process. Good for setup and debugging.
#
# -B --batch
#   Cdda2wav-style batch output flag; cdparanoia will split the output into multiple files at track boundaries.
#   Output file  names  are prepended with 'track#.'
#
# -X --abort-on-skip
#   If  the read skips due to imperfect data, a scratch, or whatever, abort reading this track.
#   If output is to a file, delete the partially completed file.
#
# -d --force-cdrom-device device
#   Force the interface backend to read from device rather than the first readable CDROM drive it finds.
#   This can be  used  to  specify devices of any valid interface type (ATAPI, SCSI, or proprietary).
#
# 1:-
#   Encode the entire disc from the first track.

cdparanoia -vB -X -d ${DEVICE} 1:- ${RIPPING_DIR}/ >> ${CDPARANOIA_LOG_PATH} 2>&1


## GET THE RIPPER TRACKS
TRACKS=$(find ${RIPPING_DIR}/ -type f -name "*.wav")

## ENCODE TRACK BY TRACK
for track in ${TRACKS};
do
	track_name=$(basename "$track" | cut -f1 -d".")
	lame --vbr-new --silent "$track" ${ENCODING_DIR}/${track_name}.mp3 >> ${LAME_LOG_PATH} 2>&1
done