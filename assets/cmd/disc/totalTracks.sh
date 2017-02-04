#!/bin/bash
device=$(lsblk | grep rom | cut -d' ' -f1)
total=$(cdparanoia -sQ -d /dev/$device |& grep -P -c "^\s+\d+\." | grep -E '[0-9]')

echo "$total"