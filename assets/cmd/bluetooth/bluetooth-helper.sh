#!/bin/bash

COMMAND=$0
DEVICE_MAC=$1

case "$COMMAND" in
"scan")
    sudo hcitool scan
    ;;
"connect")

    ;;
"disconnect")
    ;;
*)
    echo "invalid command"
    ;;
esac

echo "done"