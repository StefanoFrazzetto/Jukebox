grep 'Listen' /etc/apache2/ports.conf | grep -v 'Listen 443' | awk {'print $2'}
