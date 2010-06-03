#!/bin/bash

authtype=`"$WIFF_ROOT"/wiff --getValue=authtype`
core_db=`"$WIFF_ROOT"/wiff --getValue=core_db`
freedom_db=`"$WIFF_ROOT"/wiff --getValue=freedom_db`
vault_root=`"$WIFF_ROOT"/wiff --getValue=vault_root`
client_name=`"$WIFF_ROOT"/wiff --getValue=client_name`

if [ -z "$freedom_db" ]; then
    freedom_db=$core_db
fi
apacheuser=`"$WIFF_ROOT"/wiff --getValue=apacheuser`

dbaccesstpl="$WIFF_CONTEXT_ROOT"/context/default/dbaccess.php.in
dbaccess="$WIFF_CONTEXT_ROOT"/context/default/dbaccess.php

prefixtpl="$WIFF_CONTEXT_ROOT"/WHAT/Lib.Prefix.php.in
prefix="$WIFF_CONTEXT_ROOT"/WHAT/Lib.Prefix.php
htaccesstpl="$WIFF_CONTEXT_ROOT"/admin/.htaccess.in
htaccess="$WIFF_CONTEXT_ROOT"/admin/.htaccess
corepost="$WIFF_CONTEXT_ROOT"/CORE/CORE_post

if [ ! -f "$dbaccesstpl" ]; then
    echo "file '$dbaccesstpl' not found" >&2
    exit 1
fi
if [ ! -f "$prefixtpl" ]; then
    echo "file '$prefixtpl' not found" >&2
    exit 1
fi
if [ ! -f "$htaccesstpl" ]; then
    echo "file '$htaccesstpl' not found" >&2
    exit 1
fi
if [ ! -x "$corepost" ]; then
    echo "file '$corepost' not found or not executable" >&2
    exit 1
fi
# rewrite configuration files
sed  -e"s;@AUTHTYPE@;$authtype;" -e"s;@CORE_DB@;$core_db;" -e"s;@FREEDOM_DB@;$freedom_db;" -e"s;@prefix@;$WIFF_CONTEXT_ROOT;" "$dbaccesstpl" > "$dbaccess"
sed  -e"s;@prefix@;$WIFF_CONTEXT_ROOT;" -e"s;@HTTPUSER@;$apacheuser;" "$prefixtpl" > "$prefix"
sed  -e"s;@prefix@;$WIFF_CONTEXT_ROOT;" "$htaccesstpl" > "$htaccess"

log "Setting CORE_DB in paramv..."
PGSERVICE="$core_db" psql -c "UPDATE paramv SET val = 'service=''$freedom_db''' WHERE name = 'FREEDOM_DB'"
PGSERVICE="$core_db" psql -c "UPDATE paramv SET val = 'service=''$core_db''' WHERE name = 'CORE_DB'"

PGSERVICE="$core_db" psql -c "UPDATE paramv SET val = '$client_name' WHERE name = 'CORE_CLIENT'"
RET=$?
if [ $RET -ne 0 ]; then
	echo "Error setting CORE_DB"
    exit $RET
fi

log "Setting CORE_PUBDIR in paramv..."
PGSERVICE="$core_db" psql -c "UPDATE paramv SET val = '$WIFF_CONTEXT_ROOT' WHERE name = 'CORE_PUBDIR'"
RET=$?
if [ $RET -ne 0 ]; then
	echo "Error setting CORE_PUBDIR"
	exit $RET
fi

log "Updating vault r_path..."
PGSERVICE="$freedom_db" psql -c "UPDATE vaultdiskfsstorage SET r_path = '$vault_root' || '/' || id_fs; "
RET=$?
if [ $RET -ne 0 ]; then
	echo "Error updating vault r_path"
	exit $RET
fi
