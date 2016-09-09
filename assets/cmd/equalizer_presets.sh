#!/bin/sh

set_equalizer_curve() {
  curve="${*}"
  ctl=0
  for point in ${curve}
  do
    ctl=$(( ${ctl} + 1 ))
    echo cset numid=${ctl} ${point}
  done | amixer -D equal -s
}

profile="${1:-flat}"
case "${profile}" in
flat) curve="50 50 50 50 50 50 50 50 50 50" ;;
boosted) curve="60 57 50 40 44 44 48 53 51 45" ;;
classical) curve="42 42 42 42 42 42 55 54 54 58" ;;
club) curve="54 54 50 46 46 46 50 54 54 54" ;;
dance) curve="37 41 49 51 51 61 63 63 51 51" ;;
headphones) curve="52 42 51 64 62 57 52 44 39 36" ;;
treble) curve="35 35 35 39 46 54 61 64 65 65" ;;
bass) curve="71 71 71 62 52 39 31 31 31 29" ;;
large_hall) curve="39 39 46 46 54 62 62 62 54 54" ;;
live) curve="58 50 45 43 42 42 45 47 47 48" ;;
party) curve="45 45 55 55 55 55 55 55 45 45" ;;
pop) curve="57 48 44 43 47 56 58 58 57 57" ;;
reggae) curve="50 50 51 60 50 41 41 50 50 50" ;;
rock) curve="39 44 61 65 58 47 39 36 36 36" ;;
ska) curve="58 62 61 55 49 46 41 40 38 40" ;;
soft_rock) curve="47 47 50 53 59 61 58 53 49 39" ;;
soft) curve="51 56 59 61 59 52 45 43 41 39" ;;
techno) curve="42 45 53 62 61 53 42 39 39 40" ;;
*) echo "Unknown profile ${profile}" >&2 ;;
esac

[ "${curve}" ] && set_equalizer_curve "${curve}"