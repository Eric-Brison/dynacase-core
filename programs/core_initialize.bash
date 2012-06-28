#!/bin/bash

authtype=`"$WIFF_ROOT"/wiff --getValue=authtype`
core_db=`"$WIFF_ROOT"/wiff --getValue=core_db`
freedom_db=`"$WIFF_ROOT"/wiff --getValue=freedom_db`
mod_deflate=`"$WIFF_ROOT"/wiff --getValue=mod_deflate`

if [ -z "$freedom_db" ]; then
    freedom_db=$core_db
fi
apacheuser=`"$WIFF_ROOT"/wiff --getValue=apacheuser`

dbaccesstpl="$WIFF_CONTEXT_ROOT"/context/default/dbaccess.php.in
dbaccess="$WIFF_CONTEXT_ROOT"/context/dbaccess.php

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
# initialize configuration files
sed  -e"s;@AUTHTYPE@;$authtype;" -e"s;@CORE_DB@;$core_db;" -e"s;@FREEDOM_DB@;$freedom_db;" -e"s;@prefix@;$WIFF_CONTEXT_ROOT;" "$dbaccesstpl" > "$dbaccess"
sed  -e"s;@prefix@;$WIFF_CONTEXT_ROOT;" -e"s;@HTTPUSER@;$apacheuser;" "$prefixtpl" > "$prefix"
sed  -e"s;@prefix@;$WIFF_CONTEXT_ROOT;" "$htaccesstpl" > "$htaccess"

sed -i.orig -e "s;^\([[:space:]]*php_value[[:space:]][[:space:]]*session\.save_path[[:space:]][[:space:]]*\).*$;\1\"${WIFF_CONTEXT_ROOT}/var/session\";" "$WIFF_CONTEXT_ROOT"/.htaccess

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

"$corepost" I 
RET=$?
if [ $RET -ne 0 ]; then
    echo "Error: '$corepost I' returned with exit code '$RET'"
    exit $RET
fi

"$corepost" U
RET=$?
if [ $RET -ne 0 ]; then
    echo "Error: '$corepost U' returned with exit code '$RET'"
    exit $RET;
fi

exit 0