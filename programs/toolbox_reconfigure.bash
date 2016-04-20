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

dbaccesstpl="$WIFF_CONTEXT_ROOT"/config/dbaccess.php.in
dbaccess="$WIFF_CONTEXT_ROOT"/config/dbaccess.php

htaccesstpl="$WIFF_CONTEXT_ROOT"/supervisor/.htaccess.in
htaccess="$WIFF_CONTEXT_ROOT"/supervisor/.htaccess
corepost="$WIFF_CONTEXT_ROOT"/CORE/CORE_post

if [ ! -f "$dbaccesstpl" ]; then
    echo "file '$dbaccesstpl' not found" >&2
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

. "$WIFF_CONTEXT_ROOT/libutil.sh"

(
    set -e
    cp "$dbaccesstpl" "$dbaccess" && installUtils replace -f "$dbaccess" +s +e ".@AUTHTYPE@." "$authtype" ".@CORE_DB@." "$core_db" ".@FREEDOM_DB@." "$freedom_db" -q "@prefix@" "$WIFF_CONTEXT_ROOT"
    V=$(installUtils doublequote -q "$WIFF_CONTEXT_ROOT")
    cp "$htaccesstpl" "$htaccess" && installUtils replace -f "$htaccess" +em '@prefix@(.*)$' "\"$V\$1\""
)
RET=$?
if [ $RET -ne 0 ]; then
    echo "Error regenerating files from templates."
    exit $RET
fi

V=$(installUtils pg_escape_string "$client_name")
PGSERVICE="$core_db" psql -c "UPDATE paramv SET val = '$V' WHERE name = 'CORE_CLIENT'"
RET=$?
if [ $RET -ne 0 ]; then
	echo "Error setting CORE_CLIENT"
    exit $RET
fi

log "Updating vault r_path..."
if [ "$vault_save" == "no" ]; then
    PGSERVICE="$freedom_db" psql -c "UPDATE vaultdiskdirstorage set free_entries = 0 where free_entries > 0;"
    RET=$?
    if [ $RET -ne 0 ]; then
	echo "Error reinitializing vault table"
	exit $RET
    fi
fi
logger "update vault data"
V=$(installUtils pg_escape_string "$vault_root")
PGSERVICE="$freedom_db" psql -c "UPDATE vaultdiskfsstorage SET r_path = '$V' || '/' || id_fs; "

RET=$?
if [ $RET -ne 0 ]; then
echo "Error updating vault r_path"
    exit $RET
fi


log "Setting DateStyle to match CORE_LCDATE..."
CURRENT_DATABASE=`PGSERVICE="$core_db" psql -tA -c "SELECT current_database()"`
CURRENT_DATABASE_QUOTED=$(echo "$CURRENT_DATABASE" | sed -e 's/"/""/g')
CORE_LCDATE=`"$WIFF_CONTEXT_ROOT/wsh.php" --api=getApplicationParameter --param=CORE_LCDATE| cut -f1 -d" "`
if [ -z "$CURRENT_DATABASE" ]; then
    echo "Could not get current_database from PGSERVICE=$core_db"
    exit 1
fi
PGSERVICE="$core_db" psql -c "ALTER DATABASE \"$CURRENT_DATABASE_QUOTED\" SET DateStyle = 'ISO, DMY'"
RET=$?
if [ $RET -ne 0 ]; then
    echo "Error setting DateStyle to 'ISO, DMY' on current database \"$CURRENT_DATABASE\""
    exit $RET
fi

log "Setting standard_conforming_strings to 'off'..."
PGSERVICE="$core_db" psql -c "ALTER DATABASE \"$CURRENT_DATABASE_QUOTED\" SET standard_conforming_strings = 'off'"
RET=$?
if [ $RET -ne 0 ]; then
    echo "Error setting standard_conforming_strings to 'off' on current database \"$CURRENT_DATABASE\""
    exit $RET
fi

log "Setting session.save_path..."
if [ -f "${WIFF_CONTEXT_ROOT}/.htaccess" ]; then
    V=$(installUtils doublequote -q "$WIFF_CONTEXT_ROOT")
    installUtils replace -f .htaccess +em '^(\s*php_value\s+session\.save_path\s+).*$' "\$1\"$V/var/session\""
fi

log "Re-creating var subdirs..."
for SUBDIR in cache/file cache/image session tmp upload; do
    DIR="${WIFF_CONTEXT_ROOT}/var/${SUBDIR}"
    if [ ! -e "$DIR" ]; then
        mkdir -p "$DIR"
        RET=$?
        if [ $RET -ne 0 ]; then
            echo "Error creating directory '${WIFF_CONTEXT_ROOT}/${SUBDIR}'."
            exit $RET
        fi
    fi
done

if [ "$user_login" != "" ]; then
  "$WIFF_CONTEXT_ROOT"/wsh.php --api=fdl_resetprofiling --login="$user_login" --password="$user_password"
fi

# vim: set tabstop=8 softtabstop=4 shiftwidth=4 noexpandtab:
