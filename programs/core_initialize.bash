#!/bin/bash

authtype=`"$WIFF_ROOT"/wiff.php --getValue=authtype`
core_db=`"$WIFF_ROOT"/wiff.php --getValue=core_db`
freedom_db=`"$WIFF_ROOT"/wiff.php --getValue=freedom_db`

if [ ! $freedom_db]; then
    freedom_db=$core_db
fi
apacheuser=`"$WIFF_ROOT"/wiff.php --getValue=apacheuser`

dbaccesstpl=$WIFF_CONTEXT_ROOT"/context/default/dbaccess.php.in"
dbaccess=$WIFF_CONTEXT_ROOT"/context/default/dbaccess.php"

prefixtpl=$WIFF_CONTEXT_ROOT"/WHAT/Lib.Prefix.php.in"
prefix=$WIFF_CONTEXT_ROOT"/WHAT/Lib.Prefix.php"
corepost=$WIFF_CONTEXT_ROOT"/CORE/CORE_post"

if [ ! -f $dbaccesstpl ]; then
    echo "file $dbaccesstpl not found" >&2
    exit 1
fi
if [ ! -f $prefixtpl ]; then
    echo "file $prefixtpl not found" >&2
    exit 1
fi
if [ ! -x $corepost ]; then
    echo "file $corepost not found or not executable" >&2
    exit 1
fi
# initialize configuration files
sed  -e"s/@AUTHTYPE@/$authtype/" -e"s/@CORE_DB@/$core_db/" -e"s/@FREEDOM_DB@/$freedom_db/" $dbaccesstpl > $dbaccess
sed  -e"s/@prefix@/$WIFF_CONTEXT_ROOT/" -e"s/@HTTPUSER@/$apacheuser" $prefixtpl > $prefix


export wpub=$WIFF_CONTEXT_ROOT # same as `wiff.php --getValue=rootdirectory`
. `dirname $0`/core_environnement


$corepost I 
RET=$?
if [ $RET -eq 0 ]; then
     $corepost U
     RET=$?
fi
exit $RET
