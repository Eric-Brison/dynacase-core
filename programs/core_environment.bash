#!/bin/bash

pgservice_core=$(php -r 'require($argv[1]."/config/dbaccess.php"); echo $pgservice_core;' "$wpub" 2> /dev/null)
if [ $? -ne 0 ]; then
    echo "Error getting pgservice_core env variable from '$wpub/config/dbaccess.php'"
    return 3
fi
if [ -z "$pgservice_core" ]; then
    echo "Error: undefined pgservice_core !"
    return 4
fi

pgservice_freedom=$(php -r 'require($argv[1]."/config/dbaccess.php"); echo $pgservice_freedom;' "$wpub" 2> /dev/null)
if [ $? -ne 0 ]; then
    echo "Error getting pgservice_freedom env variable from '$wpub/config/dbaccess.php'"
    return 3
fi
if [ -z "$pgservice_freedom" ]; then
    echo "Error: undefined pgservice_freedom !"
    return 4
fi

freedom_context=$(php -r 'require($argv[1]."/config/dbaccess.php"); echo $freedom_context;' "$wpub" 2> /dev/null)
if [ $? -ne 0 ]; then
    echo "Error getting freedom_context env variable from '$wpub/config/dbaccess.php'"
    return 3
fi
if [ -z "$freedom_context" ]; then
    echo "Error: undefined freedom_context !"
    return 4
fi

export pgservice_core
export pgservice_freedom
export freedom_context
