#!/bin/bash

# Remove the directory passed as first argument.
rmDir()
{
    find $1 -name ._\* -print0 | xargs -0 rm -f
    rm -rf $1
}

# Create a random string passing the length as first argument.
# If not length is provided, the default is 32.
random_string()
{
    local length=${1:-32}
    echo $(cat /dev/urandom | tr -dc 'a-zA-Z0-9' | fold -w "$length" | head -n 1)
}

# Write a message passed as first argument with red color to stdout.
write_message()
{
    RED='\033[0;31m'
    NC='\033[0m' # No Color
    echo -e "${RED}$1${NC}"
}

setDiscStatus()
{
    STATUS="$1"

    MESSAGE="$2"
    if [ -z "$MESSAGE" ]; then
        MESSAGE=""
    fi

    STATUS_FILE="$3"
    if [ -z "$STATUS_FILE" ]; then
        echo "You must provide an output file."
        exit
    fi

    echo -e "{
    \"status\": \""$STATUS"\",
    \"message\": \""$MESSAGE"\"
    }" > ${STATUS_FILE}
}