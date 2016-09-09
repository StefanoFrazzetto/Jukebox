#!/bin/bash
# Get all arguments
if [ $# -ne 9 ]; then
	echo -e "Usage: $0 <accessPoint_interface> <source_interface> <SSID> <Password> <IP_Range_Start> <IP_Range_End> <DNS> <gateway> <netmask>"
	exit 1
fi
# Get the Acess Point Interface Name
accessPoint_interface=$1
# Get the Acess Point Interface Name
source_interface=$2
# Get the SSID Name to Create
ssidName=$3
# Get the Password to Ser
Password=$4
IP_Range_Start=$5
IP_Range_End=$6
DNS=$7
gateway=$8
netmask=$9

# Setup the hostapd Configuration File
echo "interface=$accessPoint_interface" > /etc/hostapd/hostapd.conf;
echo "driver=wext" >> /etc/hostapd/hostapd.conf;
echo "ssid=$ssidName" >> /etc/hostapd/hostapd.conf;
echo "hw_mode=g" >> /etc/hostapd/hostapd.conf;
echo "channel=6" >> /etc/hostapd/hostapd.conf;
echo "macaddr_acl=0" >> /etc/hostapd/hostapd.conf;
echo "auth_algs=1" >> /etc/hostapd/hostapd.conf;
echo "ignore_broadcast_ssid=0" >> /etc/hostapd/hostapd.conf;
echo "wpa=3" >> /etc/hostapd/hostapd.conf;
echo "wpa_passphrase=$Password" >> /etc/hostapd/hostapd.conf;
echo "wpa_key_mgmt=WPA-PSK" >> /etc/hostapd/hostapd.conf;
echo "wpa_pairwise=TKIP" >> /etc/hostapd/hostapd.conf;
echo "rsn_pairwise=CCMP" >> /etc/hostapd/hostapd.conf;

# disables dnsmasq reading any other files like /etc/resolv.conf for nameservers
echo "no-resolv" > /etc/dnsmasq.conf;
# Interface to bind to
echo "interface=$accessPoint_interface" >> /etc/dnsmasq.conf;
# Specify starting_range,end_range,lease_time
echo "dhcp-range=${IP_Range_Start},${IP_Range_End},12h" >> /etc/dnsmasq.conf;
# dns addresses to send to the clients
echo "server=$DNS" >> /etc/dnsmasq.conf;


#Initial wifi interface configuration
ifconfig $accessPoint_interface up $gateway netmask $netmask
sleep 2
 
###########Start dnsmasq, modify if required##########
if [ -z "$(ps -e | grep dnsmasq)" ]
then
 dnsmasq
fi
###########
 
#Enable NAT
iptables --flush
iptables --table nat --flush
iptables --delete-chain
iptables --table nat --delete-chain
iptables --table nat --append POSTROUTING --out-interface $source_interface -j MASQUERADE
iptables --append FORWARD --in-interface $accessPoint_interface -j ACCEPT

#Uncomment the line below if facing problems while sharing PPPoE
#iptables -I FORWARD -p tcp --tcp-flags SYN,RST SYN -j TCPMSS --clamp-mss-to-pmtu
 
sysctl -w net.ipv4.ip_forward=1

#start hostapd
hostapd /etc/hostapd/hostapd.conf 1> /dev/null
killall dnsmasq
