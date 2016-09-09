#!/bin/bash

interface=$(ls /sys/class/net | grep -v 'eth0\|lo\|tunl0')


if [ $1 == "none" ]; then
killall wpa_supplicant
sudo dhclient -r $interface
sudo dhclient -r eth0
sudo hap_app stop
echo "sudo bash /network_setup.sh \"$1\"" > /var/www/html/assets/cmd/network_startup.sh
echo "" > /etc/network/interfaces
exit 1
fi




 


network=$1
dhcp=$2

if [ $network == "ethernet" ]; then 
sudo hap_app "$interface"
echo "" > /etc/network/interfaces
	if [ $dhcp == "dhcp" ]; then 	
	while ip route del default; do :; done
	sudo ifconfig eth0 up
	sudo dhclient eth0
	echo "sudo bash /var/www/html/assets/cmd/network_setup.sh \"$1\" \"$2\" " > /var/www/html/assets/cmd/network_startup.sh

	elif [ $dhcp == "static" ]; then 
		# Check for the Total Number of Arguments
		if [ $# != 6 ]; then
			echo -e "Script Requires at-least 6 Arguments For Static IP Settings."
			exit 1
		fi
		sudo dhclient -r eth0
		
		sudo ifconfig eth0 $3
		sudo ifconfig eth0 netmask $4
		sudo ip route replace default via $5
		
		sed -i '/nameserver/d' /etc/resolv.conf
		echo "nameserver $6" >> /etc/resolv.conf
		echo "sudo bash /var/www/html/assets/cmd/network_setup.sh \"$1\" \"$2\" \"$3\" \"$4\" \"$5\" \"$6\"  " > /var/www/html/assets/cmd/network_startup.sh
	fi

killall wpa_supplicant
sudo ifconfig eth0 up

fi	

if [ $network == "wifi" ]; then 
sudo hap_app "$interface"
echo "" > /etc/network/interfaces	
	if [ $dhcp == "dhcp" ]; then 
		while ip route del default; do :; done
		
		if [ $# != 5 ]; then
			echo -e "Script Requires 5 Arguments For DHCP Settings for WiFi.";
			echo -e "$0 <wifi> <dhcp> <password> <networkname> <Security> ";
			exit 1
		fi
		networkname=$3
		password=$4		
		Security=$5
		
		echo "auto lo $interface" > /etc/network/interfaces;
		echo "iface lo inet loopback" >> /etc/network/interfaces;
		echo "iface $interface inet dhcp" >> /etc/network/interfaces;
		
		
		if [ $Security == "wpa" ]; then 			
			wpa_passphrase $networkname $password > /etc/wpa_supplicant.conf;			
			echo "	wpa-ssid $networkname" >> /etc/network/interfaces;
			echo "	wpa-passphrase $password" >> /etc/network/interfaces;
			
		elif [ $Security == "wep" ]; then 			
			wpa_passphrase $networkname $password > /etc/wpa_supplicant.conf;			
			echo "	wireless-essid $networkname" >> /etc/network/interfaces;
			echo "	wireless-key \"$password\"" >> /etc/network/interfaces;
		elif [ $Security == "open" ]; then			
			echo "network={" > /etc/wpa_supplicant.conf;
			echo "	ssid=\"$networkname\"" >> /etc/wpa_supplicant.conf;
			echo "	key_mgmt=NONE" >> /etc/wpa_supplicant.conf;
			echo "	priority=-999" >> /etc/wpa_supplicant.conf;
			echo "}" >> /etc/wpa_supplicant.conf;
		fi
				
			
		 sudo ifconfig $interface down;
		 killall wpa_supplicant;
		 sudo ifconfig $interface up;
		 sudo dhclient -r $interface;
			if [ $Security == "open" ]; then 
				sudo wpa_supplicant -B -i $interface -c /etc/wpa_supplicant.conf -Dwext &
			else
				sudo wpa_supplicant -Dwext -i$interface -c/etc/wpa_supplicant.conf &
				sleep 3
			fi
		 
		 
		
		 sudo dhclient $interface;
		 echo "sudo bash /var/www/html/assets/cmd/network_setup.sh \"$1\" \"$2\" \"$3\" \"$4\" \"$5\"  " > /var/www/html/assets/cmd/network_startup.sh
	else
		if [ $# != 9 ]; then
			echo -e "Script Requires 9 Arguments For Static Settings for WiFi.";
			echo -e "$0 <wifi> <static> <ipaddress> <netmask> <gateway> <dns> <networkname> <password> <Security>";
			exit 1
		fi
		# Get the Rest of the Arguments
		ipaddress=$3	#192.168.1.1
		netmask=$4	#255.255.255.0
		gateway=$5	#192.168.1.1
		dns=$6		#8.8.8.8
		networkname=$7
		password=$8			
		Security=$9	#wpa
		
		echo "auto lo $interface" > /etc/network/interfaces;
		echo "iface lo inet loopback" >> /etc/network/interfaces;
		echo "iface $interface inet static" >> /etc/network/interfaces;
		echo "	address $ipaddress" >> /etc/network/interfaces;
		echo "	netmask $netmask" >> /etc/network/interfaces;
		echo "	gateway $gateway" >> /etc/network/interfaces;
		echo "	dns-nameservers $dns" >> /etc/network/interfaces;

		
		if [ $Security == "wpa" ]; then 			
			wpa_passphrase $networkname $password > /etc/wpa_supplicant.conf;			
			echo "	wpa-ssid $networkname" >> /etc/network/interfaces;
			echo "	wpa-passphrase $password" >> /etc/network/interfaces;
			
		elif [ $Security == "wep" ]; then 			
			wpa_passphrase $networkname $password > /etc/wpa_supplicant.conf;			
			echo "	wireless-essid $networkname" >> /etc/network/interfaces;
			echo "	wireless-key $password" >> /etc/network/interfaces;
		else
			echo "network={" > /etc/wpa_supplicant.conf;
			echo "	ssid=\"$networkname\"" >> /etc/wpa_supplicant.conf;
			echo "	key_mgmt=NONE" >> /etc/wpa_supplicant.conf;
			echo "	priority=-999" >> /etc/wpa_supplicant.conf;
			echo "}" >> /etc/wpa_supplicant.conf;

		fi
		

		sed -i '/nameserver/d' /etc/resolv.conf
		echo "nameserver $dns" >> /etc/resolv.conf
		
		
		killall wpa_supplicant;
		sudo ifconfig $interface up;
		sudo dhclient -r $interface;

			if [ $Security == "open" ]; then 
				 sudo wpa_supplicant -B -i $interface -c /etc/wpa_supplicant.conf -Dwext &
			else
				 sudo wpa_supplicant -Dwext -i$interface -c/etc/wpa_supplicant.conf &
				 sleep 3
			fi
		
		
		sudo ifconfig $interface $ipaddress
		sudo ip route replace default via $5
		echo "sudo bash /var/www/html/assets/cmd/network_setup.sh \"$1\" \"$2\" \"$3\" \"$4\" \"$5\" \"$6\" \"$7\" \"$8\" \"$9\"  " > /var/www/html/assets/cmd/network_startup.sh
		
	fi



sudo dhclient -r eth0
sudo ifconfig eth0 down


fi

if [ $network == "hotspot" ]; then 
echo "" > /etc/network/interfaces
	if [ $# -lt 2 ]; then
		echo -e "Script Requires 4 arguments."
		echo -e "<hotspot> <ssid> <password> <channel>"
		exit 1
	fi
sudo hap_app "$interface"
killall wpa_supplicant
sudo dhclient -r eth0

sudo hap_app "$interface" "$2" "$3" "$4"
echo "sudo bash /var/www/html/assets/cmd/network_setup.sh \"$1\" \"$2\" \"$3\" \"$4\" " > /var/www/html/assets/cmd/network_startup.sh

fi
















