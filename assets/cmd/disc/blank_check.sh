#!/bin/bash
device=$(sudo less /proc/sys/dev/cdrom/info | grep 'drive name:'| awk {'print $3'})


dvd_check ()          
{
dvdstatus=$(sudo dvd+rw-mediainfo /dev/$device | grep -e "Disc status:" | awk {'print $3'})

if [ $dvdstatus == "blank" ] 
then
  echo "BLANK"
else
  echo "FULL"
fi
}

##### Main #####
command=$(sudo wodim -atip -v dev=/dev/$device | grep 'Current:'| awk {'print $3'} )

if [[ $command == *"Unknown"* ]]
then
  echo "NO_DVD"
exit
fi

if [[ $command == *"DVD"* ]]
then
  dvd_check
exit
fi

if [[ $command == *"CD-ROM"* ]]
then  
  echo "FULL";
exit
fi

if [[ $command == *"CD"* ]]
then  
  echo "BLANK";
exit
fi

echo "ERROR"

