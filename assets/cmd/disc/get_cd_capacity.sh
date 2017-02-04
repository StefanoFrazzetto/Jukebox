#!/bin/bash
#device=$(lsblk | grep rom | cut -d' ' -f1)
#device=$(less /proc/sys/dev/cdrom/info | grep 'drive name:'| awk {'print $3'})
capacity=$(sudo cdrwtool -i -d /dev/$1 | grep track_size | awk {'print $4'} | tr -d '()')
echo "$capacity"


