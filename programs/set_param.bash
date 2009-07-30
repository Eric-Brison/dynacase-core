#!/bin/bash

if [ -z "$1" ]; then
	echo "Usage: $0 <db_param_name> <wiff_param_name>"
	exit 1	
fi

value=`"$WIFF_ROOT"/wiff --getValue="$2"`

if [ -n "$value" ]; then
    "$wpub/wsh.php" --api=set_param --param="$1" --value="$value"
fi