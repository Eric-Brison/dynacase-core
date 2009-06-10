#!/bin/bash
ctx=default
pgservice_core=`php -r "require('$wpub/context/$ctx/dbaccess.php');echo\\\$pgservice_core;" 2> /dev/null`
if [ $? -ne 0 ]; then
    echo "Error getting pgservice_core env variable from '$wpub/context/$ctx/dbaccess.php'"
    return 3
fi
if [ -z $pgservice_core ]; then
    echo "Error: undefined pgservice_core !"
    return 4
fi

pgservice_freedom=`php -r "require('$wpub/context/$ctx/dbaccess.php');echo\\\$pgservice_freedom;" 2> /dev/null`
if [ $? -ne 0 ]; then
    echo "Error getting pgservice_freedom env variable from '$wpub/context/$ctx/dbaccess.php'"
    return 3
fi
if [ -z $pgservice_freedom ]; then
    echo "Error: undefined pgservice_freedom !"
    return 4
fi

freedom_context=`php -r "require('$wpub/context/$ctx/dbaccess.php');echo\\\$freedom_context;" 2> /dev/null`
if [ $? -ne 0 ]; then
    echo "Error getting freedom_context env variable from '$wpub/context/$ctx/dbaccess.php'"
    return 3
fi
if [ -z $freedom_context ]; then
    echo "Error: undefined freedom_context !"
    return 4
fi

export pgservice_core
export pgservice_freedom
export freedom_context
