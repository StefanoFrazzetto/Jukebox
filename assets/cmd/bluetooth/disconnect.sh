#!/bin/bash

expect -c "
spawn \"bluetoothctl\"
expect \"# \"
send \"disconnect\r\"
expect \"Successful disconnected\"
send \"exit\r\"
expect eof
"

echo "done"