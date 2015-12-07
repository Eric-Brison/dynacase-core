#!/bin/bash

authtype=`"$WIFF_ROOT"/wiff --getValue=authtype`
core_db=`"$WIFF_ROOT"/wiff --getValue=core_db`
freedom_db=`"$WIFF_ROOT"/wiff --getValue=freedom_db`
mod_deflate=`"$WIFF_ROOT"/wiff --getValue=mod_deflate`

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
# initialize configuration files

. "$WIFF_CONTEXT_ROOT/libutil.sh"

(
    set -e
    cp "$dbaccesstpl" "$dbaccess" && installUtils replace -f "$dbaccess" +s +e ".@AUTHTYPE@." "$authtype" ".@CORE_DB@." "$core_db" ".@FREEDOM_DB@." "$freedom_db" -q "@prefix@" "$WIFF_CONTEXT_ROOT"
    V=$(installUtils doublequote -q "$WIFF_CONTEXT_ROOT")
    cp "$htaccesstpl" "$htaccess" && installUtils replace -f "$htaccess" +em '@prefix@(.*)$' "\"$V\$1\""
    V=$(installUtils doublequote -q "$WIFF_CONTEXT_ROOT")
    installUtils replace -f .htaccess +em '^(\s*php_value\s+session\.save_path\s+).*$' "\$1\"$V/var/session\""
)
RET=$?
if [ $RET -ne 0 ]; then
    echo "Error regenerating files from templates."
    exit $RET
fi

if [ "$mod_deflate" = "yes" ]; then
    cat <<EOF >> "$WIFF_CONTEXT_ROOT/.htaccess"

<IfModule mod_deflate.c>
	SetOutputFilter DEFLATE
</IfModule>

EOF
else
    cat <<EOF >> "$WIFF_CONTEXT_ROOT/.htaccess"

#<IfModule mod_deflate.c>
#	SetOutputFilter DEFLATE
#</IfModule>

EOF
fi

export wpub=$WIFF_CONTEXT_ROOT # same as `wiff --getValue=rootdirectory`
. "$WIFF_CONTEXT_ROOT"/programs/core_environment
