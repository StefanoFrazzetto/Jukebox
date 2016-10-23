#!/bin/bash

expect -c "
spawn \"bluetoothctl\"
expect \"# \"
send \"remove *\r\"
expect \"# \"
send \"exit\r\"
expect eof
"

echo "done"