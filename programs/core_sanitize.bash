#!/bin/bash

pgservice_core=`"$WIFF_ROOT/wiff" --getValue=core_db`
if [ -z "$pgservice_core" ]; then
    echo "Error: undefined or empty CORE_DB"
    exit 1
fi

PGSERVICE=$pgservice_core psql --set ON_ERROR_STOP=on -f "$wpub/CORE/core_migration.sql" 2>&1
RET=$?
if [ $RET -ne 0 ]; then
    echo "Error: SQL error executing '$wpub/CORE/core_migration.sql': $RET"
    exit $RET
fi

exit 0