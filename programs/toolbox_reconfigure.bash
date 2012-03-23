#!/bin/bash

authtype=`"$WIFF_ROOT"/wiff --getValue=authtype`
core_db=`"$WIFF_ROOT"/wiff --getValue=core_db`
freedom_db=`"$WIFF_ROOT"/wiff --getValue=freedom_db`
vault_root=`"$WIFF_ROOT"/wiff --getValue=vault_root`
client_name=`"$WIFF_ROOT"/wiff --getValue=client_name`
vault_save=`"$WIFF_ROOT"/wiff --getValue=vault_save`
 
remove_profiles=`"$WIFF_ROOT"/wiff --getValue=remove_profiles`
user_login=`"$WIFF_ROOT"/wiff --getValue=user_login`
user_password=`"$WIFF_ROOT"/wiff --getValue=user_password`

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
if [ "$vault_save" == "no" ]; then
    PGSERVICE="$freedom_db" psql -c "DELETE FROM vaultdiskfsstorage;DELETE FROM vaultdiskdirstorage;DELETE FROM vaultdiskstorage; "
    RET=$?
    if [ $RET -ne 0 ]; then
	echo "Error updating vault r_path"
	exit $RET
    fi
else
    logger "update vault data"
    PGSERVICE="$freedom_db" psql -c "UPDATE vaultdiskfsstorage SET r_path = '$vault_root' || '/' || id_fs; "

    RET=$?
    if [ $RET -ne 0 ]; then
	echo "Error updating vault r_path"
        exit $RET
    fi
fi

log "Setting DateStyle to match CORE_LCDATE..."
CURRENT_DATABASE=`PGSERVICE="$core_db" psql -tA -c "SELECT current_database()"`
CORE_LCDATE=`"$WIFF_CONTEXT_ROOT/wsh.php" --api=get_param --param=CORE_LCDATE| cut -f1 -d" "`
if [ -z "$CURRENT_DATABASE" ]; then
    echo "Could not get current_database from PGSERVICE=$core_db"
    exit 1
fi
if [ -n "$CORE_LCDATE" ]; then
    if [ "$CORE_LCDATE" = "iso" ]; then
        PG_DATESTYLE="ISO, DMY"
    else
        PG_DATESTYLE="SQL, DMY"
    fi
    PGSERVICE="$core_db" psql -c "ALTER DATABASE \"$CURRENT_DATABASE\" SET DateStyle = '$PG_DATESTYLE'"
    RET=$?
    if [ $RET -ne 0 ]; then
        echo "Error setting DateStyle to '$PG_DATESTYLE' on current database \"$CURRENT_DATABASE\""
        exit $RET
    fi
fi

log "Setting session.save_path..."
if [ -f "${WIFF_CONTEXT_ROOT}/.htaccess" ]; then
    sed -i.orig -e "s;^\([[:space:]]*php_value[[:space:]][[:space:]]*session\.save_path[[:space:]][[:space:]]*\).*$;\1\"${WIFF_CONTEXT_ROOT}/session\";" "${WIFF_CONTEXT_ROOT}/.htaccess"
fi

if [ "$user_login" != "" ]; then
  "$WIFF_CONTEXT_ROOT"/wsh.php --api=fdl_resetprofiling --login="$user_login" --password="$user_password"
fi
