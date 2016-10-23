#!/bin/bash

if [ $1 == 'on' ]; then
    expect -c "
    spawn \"bluetoothctl\"
    expect \"# \"
    send \"power on\r\"
    expect \"Changing power on succeeded\"
    send \"exit\r\"
    expect eof
    "
    echo "on"
fi


if [ $1 == 'off' ]; then
    expect -c "
    spawn \"bluetoothctl\"
    expect \"# \"
    send \"power off\r\"
    expect \"Changing power off succeeded\"
    send \"exit\r\"
    expect eof
    "
    echo "off"
fi