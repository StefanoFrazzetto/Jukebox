#!/bin/bash



if [ $# != 1 ]; then
echo "not enough arguments, 1 required i.e 50 50 60 60 90 1 5 66 77 40"
exit 1
fi

curve=$1

set_equalizer_curve() {
  curve="${*}"
  ctl=0
  for point in ${curve}
  do
    ctl=$(( ${ctl} + 1 ))
    echo cset numid=${ctl} ${point}
  done | amixer -D equal -s
}
[ "${curve}" ] && set_equalizer_curve "${curve}"

