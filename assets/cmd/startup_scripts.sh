#!/bin/bash

RC_LOCAL="/etc/rc.local"
THIS_PATH="/var/www/html/assets/cmd"
THIS_SCRIPT_PATH="/var/www/html/assets/cmd/startup_scripts.sh"

PHP=$(which php)
PHP_STARTUP_FILE="/var/www/html/assets/php/startup_scripts.php"


# If this script is not added to /etc/rc.local, then add it and make it executable.
if ! grep -q "$THIS_SCRIPT_PATH" "$RC_LOCAL" ; then

    echo '' > $RC_LOCAL

    # Put this script into rc.local
	echo -e "!/bin/sh -e\n# Startup script\n$THIS_SCRIPT_PATH\nexit 0" | cat - $RC_LOCAL > /tmp/out && mv /tmp/out $RC_LOCAL

	chmod +x $RC_LOCAL
	chmod +x $THIS_SCRIPT_PATH

	# Enable services autostart
	update-rc.d nodeserver defaults
	update-rc.d nodeserver enable

	update-rc.d node-sass defaults
	update-rc.d node-sass enable
fi

# Turn on the speakers after 60 seconds
( sleep 30 ; bash $THIS_PATH/int_speakers_on.sh ) &

# Execute PHP scripts.
$PHP "$PHP_STARTUP_FILE"

# MAYBE to be removed
service nodeserver start
service node-sass start

# Set volume to 88%
SPEAKERS_CMD="amixer set Master 88%"
su - bananapi -c $SPEAKERS_CMD