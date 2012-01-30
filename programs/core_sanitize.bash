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


if [ -d $wpub/context/default/FDLGEN ]; then
   mv $wpub/context/default/FDLGEN/Class*php $wpub/FDLGEN
   $wpub/wsh.php --api=fdl_adoc
   /bin/rm -f $wpub/FDLGEN/default
   rmdir $wpub/context/default/FDLGEN
fi

exit 0