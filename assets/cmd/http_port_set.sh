#!/bin/bash

if [ $# == 0 ]; then
echo "Listen 127.0.0.1:80" > /etc/apache2/ports.conf;
fi


if [ $# > 1 ]; then
	echo "Listen $1" > /etc/apache2/ports.conf;			
fi


if [ $# == 2 ]; then
	echo "Listen $2" >> /etc/apache2/ports.conf;			
fi

if [ $# -gt 2 ]; then
exit 1
fi


echo "<IfModule ssl_module>" >> /etc/apache2/ports.conf;
echo "	Listen 443" >> /etc/apache2/ports.conf;
echo "</IfModule>" >> /etc/apache2/ports.conf;
echo "<IfModule mod_gnutls.c>" >> /etc/apache2/ports.conf;
echo "	Listen 443" >> /etc/apache2/ports.conf;
echo "</IfModule>" >> /etc/apache2/ports.conf;

sudo service apache2 restart
