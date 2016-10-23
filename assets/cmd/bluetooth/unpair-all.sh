#!/bin/bash

 


expect -c "
spawn \"bluetoothctl\"
expect \"# \"
send \"remove *\r\"
expect \"Device has been removed\"
send \"exit\r\"
"

echo "done"