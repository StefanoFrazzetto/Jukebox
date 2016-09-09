#!/bin/bash
device=$(less /proc/sys/dev/cdrom/info | grep 'drive name:'| awk {'print $3'})


dvd_check ()          
{
dvdstatus=$(dvd+rw-mediainfo /dev/$device | grep -e "Disc status:" | awk {'print $3'})

if [ $dvdstatus == "blank" ] 
then
  echo "<<blank DVD>>"
else
  echo "<<DVD not blank>>"
fi
}

##### Main #####
command=$(wodim -atip -v dev=/dev/$device | grep 'Current:'| awk {'print $3'} )

if [[ $command == *"Unknown"* ]]
then
  echo "<<No disc in drive>>"
exit
fi

if [[ $command == *"DVD"* ]]
then
  dvd_check
exit
fi

if [[ $command == *"CD-ROM"* ]]
then  
  echo "<<Cd not blank>>";
exit
fi

if [[ $command == *"CD"* ]]
then  
  echo "<<blank CD>>";
exit
fi

echo "<<Error>>"

