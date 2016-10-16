#!/bin/bash

cat /proc/uptime | grep -oE '^([0-9]*)'