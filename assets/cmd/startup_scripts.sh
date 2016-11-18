#!/bin/bash

RC_LOCAL="/etc/rc.local"
SCRIPTS_PATH=/var/www/html/assets/cmd
THIS_SCRIPT_PATH="$SCRIPTS_PATH/startup_scripts.sh"

PHP=$(which php)
PHP_STARTUP_FILE="/var/www/html/assets/php/startup_scripts.php"

WEB_FOLDER=/var/www/html
WEB_FOLDER_ASSETS="$WEB_FOLDER/assets"

SCREEN_CALIB_CONF="/usr/share/X11/xorg.conf.d/10-evdev.conf"


# If this script is not added to /etc/rc.local, then add it and make it executable.
if ! grep -q "$THIS_SCRIPT_PATH" "$RC_LOCAL" ; then
	echo -e "#!/bin/bash\n\n# Startup script\n$THIS_SCRIPT_PATH\n" | cat - $RC_LOCAL > /tmp/out && mv /tmp/out $RC_LOCAL
	chmod +x $THIS_SCRIPT_PATH

	# Enable services autostart
	update-rc.d nodeserver defaults
	update-rc.d nodeserver enable

	update-rc.d node-sass defaults
	update-rc.d node-sass enable

	# Change permissions and owner of webfiles
	chown -R bananapi:bananapi "$WEB_FOLDER"
	find "$WEB_FOLDER_ASSETS" -type d -exec chmod 755 {} +
	find "$WEB_FOLDER_ASSETS" -type f -exec chmod 744 {} +
fi

# Make the screen calibrator configuration file writable by anyone
chmod 777 "$SCREEN_CALIB_CONF"

# Turn on the speakers
source "$SCRIPTS_PATH/int_speakers_on.sh"

# Set volume to 88%
SPEAKERS_CMD="amixer set Master 88%"
su - bananapi -c $SPEAKERS_CMD

# Execute PHP scripts.
$PHP "$PHP_STARTUP_FILE"
