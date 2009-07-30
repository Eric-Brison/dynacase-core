#!/bin/bash

client_name = `"$WIFF_ROOT"/wiff --getValue=client_name`

if [ -n "$client_name" ]; then
    "$wpub/wsh.php" --api=set_param --param=CORE_CLIENT --value="$client_name"
fi