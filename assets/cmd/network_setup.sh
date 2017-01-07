#!/bin/bash

wifi_interface=$(ls /sys/class/net | grep -v 'eth0\|lo\|tunl0')

INTERFACE_FILE=/etc/network/interfaces
WPA_SUPPLICANT=/etc/wpa_supplicant.conf;
DNS_CONF=/etc/resolv.conf

# Releases the DHCP and brings down the interface
stopInterface() {
    # $1 Interface

    # Stop ethernet connection
	sudo dhclient -r $1
    sudo ifconfig $1 down
}

# Writes the configuration to the interfaces file
writeConfig() {
    # $1 Interface
    # $2 DHCP
    # $3 IP address
    # $4 Netmask
    # $5 Gateway

    # Release the dhcp and bring the interface down
    stopInterface $1

    # Static IP
    if [ $2 == "dhcp" ]; then
        # DHCP
        echo -e "auto $1
        iface $1 inet dhcp" > "$INTERFACE_FILE"
    else
        # Check the number of arguments
        if [ $# != 5 ]; then
            echo -e "Script Requires at-least 5 Arguments For Static IP Settings."
            exit 1
        fi

        echo -e "auto lo $1
        iface lo $1 inet static
            address $3
            netmask $4
            gateway $5" > "$INTERFACE_FILE"
    fi

    sudo ifconfig eth0 up
    sudo dhclient eth0
}

setDns() {
    echo "nameserver $1
    nameserver $2" > "$DNS_CONF"
}

# Sets the wifi password
setWifiPassword() {
    # $1 Encryption
    # $2 SSID
    # $3 Password

    if [ $1 == "wpa" ]; then
        wpa_passphrase "$2" "$3" > "$WPA_SUPPLICANT"
        echo "	wpa-ssid $2" >> "$INTERFACE_FILE";
        echo "	wpa-passphrase $3" >> "$INTERFACE_FILE";

    elif [ $1 == "wep" ]; then
        wpa_passphrase "$2" "$3" > "$WPA_SUPPLICANT"
        echo "	wireless-essid $2" >> "$INTERFACE_FILE";
        echo "	wireless-key \"$3\"" >> "$INTERFACE_FILE";
    elif [ $1 == "open" ]; then
        echo "network={" > "$WPA_SUPPLICANT"
        echo "	ssid=\"$2\"" >> "$WPA_SUPPLICANT"
        echo "	key_mgmt=NONE" >> "$WPA_SUPPLICANT"
        echo "	priority=-999" >> "$WPA_SUPPLICANT"
        echo "}" >> "$WPA_SUPPLICANT"
	fi

	sudo ifconfig "$wifi_interface" down
    killall wpa_supplicant
    sudo ifconfig "$wifi_interface" up
    sudo dhclient -r "$wifi_interface"

	if [ $1 == "open" ]; then
         sudo wpa_supplicant -B -i $wifi_interface -c "$WPA_SUPPLICANT" -Dwext &
    else
         sudo wpa_supplicant -Dwext -i$wifi_interface -c"$WPA_SUPPLICANT" &
         sleep 3
    fi

    sudo dhclient "$wifi_interface"
    echo "$@" > /var/www/html/logs/connection-script.log
}


if [ $1 == "none" ]; then
    killall wpa_supplicant
    sudo dhclient -r $wifi_interface
    sudo dhclient -r eth0
    sudo hap_app stop
    echo "" > "$INTERFACE_FILE"
    exit 1
fi


network=$1
dhcp=$2

# LAN
if [ $network == "ethernet" ]; then 
    sudo hap_app "$wifi_interface"

    stopInterface "$wifi_interface"

    # Write the configuration to the interfaces file
    writeConfig "eth0" "dhcp" "192.168.0.7" "255.255.255.0" "192.168.0.1"

    # Set the DNS servers
    setDns "8.8.8.8" "8.8.4.4"

echo "$@" > /var/www/html/logs/jerrystupido.log

fi	

# WIFI
if [ $network == "wifi" ]; then 
    sudo hap_app "$wifi_interface"

    networkname=$3
    password=$4
    wifi_encryption=$5

    # Stop ethernet connection
    stopInterface "eth0"

    # Write the configuration to the interfaces file
    writeConfig "$wifi_interface" "$dhcp" "192.168.0.7" "255.255.255.0" "192.168.0.1"

    # Set wifi password
    setWifiPassword "$wifi_encryption" "$networkname" "$password"

    # Set DNS servers
    setDns "8.8.8.8" "8.8.4.4"

fi

# HOTSPOT
if [ $network == "hotspot" ]; then 
echo "" > "$INTERFACE_FILE"
	if [ $# -lt 2 ]; then
		echo -e "Script Requires 4 arguments."
		echo -e "<hotspot> <ssid> <password> <channel>"
		exit 1
	fi
sudo hap_app "$wifi_interface"
killall wpa_supplicant
sudo dhclient -r eth0

sudo hap_app "$wifi_interface" "$2" "$3" "$4"
fi