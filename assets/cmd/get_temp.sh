#!/bin/bash
gpio mode 1 in

COUNTER=0
Timeout=0

         while [ `gpio read 1` == 1 ] ; do
		sleep 0.001
		if [ $Timeout -gt 2000 ]; then
			echo "error $Timeout"
			exit 1
		fi

		((Timeout++))
         done

Timeout=0

         while [ `gpio read 1` == 0 ] ; do
		sleep 0.001
				if [ $Timeout -gt 2000 ]; then
			echo "error $Timeout"
			exit 1
		fi
		((Timeout++))
         done

Timeout=0

         while [ `gpio read 1` == 1 ] ; do
		sleep 0.001
		
		
		if [ $Timeout -gt 2000 ]; then
			echo "error $Timeout"
			exit 1
		fi

		((Timeout++))
		
         done

echo $Timeout

