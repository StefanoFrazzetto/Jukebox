#!/bin/bash
sudo ifconfig $1 up
sudo iwlist $1 scanning | egrep 'ESSID|Encryption|Quality|IE'