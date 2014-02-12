#!/bin/bash

if [ -z "$WIFF_ROOT" ]; then
    echo "Error: undefined WIFF_ROOT"
    exit 1
fi

if [ -z "$WIFF_CONTEXT_ROOT" ]; then
    echo "Error: undefined WIFF_CONTEXT_ROOT"
    exit 1
fi

pgservice_core=`"$WIFF_ROOT/wiff" --getValue=core_db`
if [ -z "$pgservice_core" ]; then
    echo "Error: undefined or empty CORE_DB"
    exit 1
fi

cat "$WIFF_CONTEXT_ROOT/CORE/core_database_utils.sql" "$WIFF_CONTEXT_ROOT/CORE/core_migration.sql" | PGSERVICE="$pgservice_core" psql --set ON_ERROR_STOP=on -f - 2>&1
RET=$?
if [ $RET -ne 0 ]; then
    echo "Error: SQL error executing '$WIFF_CONTEXT_ROOT/CORE/core_migration.sql': $RET"
    exit $RET
fi

CURRENT_DATABASE=`PGSERVICE="$pgservice_core"  psql -tA -c "SELECT current_database()"`

if [ -z "$CURRENT_DATABASE" ]; then
    echo "Could not get current_database from PGSERVICE=$pgservice_core"
    exit 1
fi
PGSERVICE="$pgservice_core" psql -c "ALTER DATABASE \"$CURRENT_DATABASE\" SET DateStyle = 'ISO,DMY'"
RET=$?
if [ $RET -ne 0 ]; then
    echo "Error: SQL error cannot set datestyle to iso': $RET"
    exit $RET
fi


log "Setting standard_conforming_strings to 'off'..."
PGSERVICE="$pgservice_core" psql -c "ALTER DATABASE \"$CURRENT_DATABASE\" SET standard_conforming_strings = 'off'"
RET=$?
if [ $RET -ne 0 ]; then
    echo "Error setting standard_conforming_strings to 'off' on current database \"$CURRENT_DATABASE\""
    exit $RET
fi

exit 0
