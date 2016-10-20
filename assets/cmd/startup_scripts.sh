#!/usr/bin/env bash

RC_LOCAL=/etc/rc.local
THIS_SCRIPT_PATH=/var/www/html/assets/cmd/startup_script.sh

PHP=$(which php)
PHP_STARTUP_FILE=/var/www/html/assets/php/startup_scripts.php

# If this script is not added to /etc/rc.local, then add it and make it executable.
if ! grep -q "$THIS_SCRIPT_PATH" "$RC_LOCAL" ; then
    echo -e "# Startup script\n$THIS_SCRIPT_PATH\n" | cat - "$RC_LOCAL" > /tmp/out && mv /tmp/out "$RC_LOCAL"
    chmod +x "$THIS_SCRIPT_PATH"
fi

./int_speakers_on.sh

# Execute PHP scripts.
$PHP "$PHP_STARTUP_FILE"