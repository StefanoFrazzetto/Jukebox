#!/bin/bash

COMMAND="$1"
DEVICE_MAC="$2"

case "$COMMAND" in
"scan")
    hcitool scan
    ;;
"connect")

    ;;
"disconnect")
    ;;
*)
    echo "invalid command"
    ;;
esac