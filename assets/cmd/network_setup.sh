#!/bin/bash

wifi_interface=$(ls /sys/class/net | grep -e 'wlan*' | xargs)

INTERFACE_FILE=/etc/network/interfaces
WPA_SUPPLICANT=/etc/wpa_supplicant.conf
DNS_CONF=/etc/resolv.conf
LOG_FILE=/var/www/html/logs/network.log

# Logs the message to the log file
echo -e "\n\n" >> "$LOG_FILE"
logMessage() {
    echo -e "[$(date --rfc-3339='ns')]: $1" >> "$LOG_FILE"
}

logMessage "Network script started."
logMessage "Parameters: $@"

# Releases the DHCP and brings down the interface
stopInterface() {
    # $1 Interface

    # Stop ethernet connection
	sudo dhclient -r $1
    sudo ifconfig $1 down
}

# Sets the IP address, netmask and gateway
setConfiguration() {
    # $1 Interface
    # $2 IP address
    # $3 Netmask
    # $4 Gateway

    sudo ifconfig $1 $2 netmask $3
    sudo ip route replace default via $4
}

# Writes the configuration to the interfaces file
writeConfig() {
    # $1 Interface
    # $2 DHCP
    # $3 IP address
    # $4 Netmask
    # $5 Gateway
    # $6 Main DNS IP

    logMessage "Writing config to $INTERFACE_FILE: $1 $2 $3 $4 $5 $6"

    # Release the dhcp and bring the interface down
    stopInterface $1

    echo -e "auto lo\niface lo inet loopback\n" > "$INTERFACE_FILE"

    # Static IP
    if [ "$2" = "dhcp" ]; then
        # DHCP
        echo -e "auto $1
        iface $1 inet dhcp" >> "$INTERFACE_FILE"

        # Set the DNS servers
        setDns "8.8.8.8"

        sudo ifconfig "$1" up
        sudo dhclient "$1"
    else
        # Check the number of arguments
        if [ $# != 6 ]; then
            echo -e "This script REQUIRES 6 Arguments For STATIC IP Settings."
            exit 1
        fi

        # Write the configuration
        echo -e "auto $1\niface $1 inet static\n\taddress $3\n\tnetmask $4\n\tgateway $5" >> "$INTERFACE_FILE"

        # Set the DNS servers
        setDns "$6"

        sudo ifconfig "$1" up
        logMessage "Setting configuration: $1 $3 $4 $5"
        setConfiguration "$1" "$3" "$4" "$5"
    fi
}

setDns() {
    second_dns="8.8.4.4"
    logMessage "Setting DNS: $1 - $second_dns"
    echo -e "nameserver $1\nnameserver $second_dns" > "$DNS_CONF"
}

# Sets the wifi password
setWifiPassword() {
    # $1 Encryption
    # $2 SSID
    # $3 Password

    stopInterface "$wifi_interface"
    killall wpa_supplicant

    if [ "$1" = "wpa" ]; then
        sudo echo '[Network_script] wpa-passphrase:' >> /var/log/apache2/error.log
        wpa_passphrase "$2" "$3" > "$WPA_SUPPLICANT"
        echo -e "\twpa-essid $2" >> "$INTERFACE_FILE";
        echo -e "\twpa-psk $3" >> "$INTERFACE_FILE";

    elif [ "$1" = "wep" ]; then
        wpa_passphrase "$2" "$3" > "$WPA_SUPPLICANT"
        echo -e "\twireless-essid $2" >> "$INTERFACE_FILE";
        echo -e "\twireless-key \"$3\"" >> "$INTERFACE_FILE";
    elif [ "$1" = "open" ]; then
        echo "network={" > "$WPA_SUPPLICANT"
        echo -e "\tssid=\"$2\"" >> "$WPA_SUPPLICANT"
        echo -e "\tkey_mgmt=NONE" >> "$WPA_SUPPLICANT"
        echo -e "\tpriority=-999" >> "$WPA_SUPPLICANT"
        echo "}" >> "$WPA_SUPPLICANT"
	fi

	if [ "$1" = "open" ]; then
         sudo wpa_supplicant -B -i"$wifi_interface" -c"$WPA_SUPPLICANT" -Dwext &
    else
         sudo echo '[Network_script] wpa-supplicant:' >> /var/log/apache2/error.log
         sudo wpa_supplicant -Dwext -i"$wifi_interface" -c"$WPA_SUPPLICANT" &
         sleep 3
    fi
}



if [ "$1" = "none" ]; then
    killall wpa_supplicant
    sudo dhclient -r "$wifi_interface"
    sudo dhclient -r eth0
    sudo hap_app stop
    echo "" > "$INTERFACE_FILE"

    exit 0
fi


network=$1
dhcp=$2
ip_address=$3	#192.168.1.1
netmask=$4	    #255.255.255.0
gateway=$5	    #192.168.1.1
dns=$6		    #8.8.8.8

# LAN
if [ "$network" = "ethernet" ]; then
    logMessage "Starting ethernet connection."

    logMessage "Stopping Wi-Fi interface: $wifi_interface."
    stopInterface "$wifi_interface"

    # Write the configuration to the interfaces file
    logMessage "Writing ethernet configuration. Parameters: $network $dhcp $ip_address $netmask $gateway $dns"
    writeConfig "eth0" "$dhcp" "$ip_address" "$netmask" "$gateway" "$dns"
fi	

# WIFI
if [ "$network" = "wifi" ]; then
 	network_name=$7
 	password=$8
 	wifi_encryption=$9	#wpa

    # Stop ethernet connection
    logMessage "Stopping ethernet interface: eth0."
    stopInterface "eth0"

    # Set wifi password
    setWifiPassword "$wifi_encryption" "$network_name" "$password"

    # Write the configuration to the interfaces file
    logMessage "Writing ethernet configuration. Parameters: $network $dhcp $ip_address $netmask $gateway $dns"
    writeConfig "$wifi_interface" "$dhcp" "$ip_address" "$netmask" "$gateway" "$dns"
fi

# HOTSPOT
if [ "$network" = "hotspot" ]; then
    logMessage "Starting hotspot"

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

logMessage "Network script complete."