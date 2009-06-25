function pgIsRunning {
    psql -c "" > /dev/null 2>&1
    return $?
}

function pgDbExists {
    pgDb=$1
    if [ -z $pgDb ]; then
	return 1
    fi
    if [ $z "$PGUSER" ]; then
	export PGUSER="postgres"
    fi
    psql -l -tA \
	| grep -q "^$pgDb|"
    RET=$?
    return $RET
}

function pgLanguageExists {
    pgLanguage=$1
    if [ -z $pgLanguage ]; then
	return 1
    fi
    LANG=`psql -At -c "select lanname from pg_language where lanname='$pgLanguage'" 2> /dev/null`
    RET=$?
    if [ $RET -ne 0 ]; then
	return $RET
    fi
    if [ -n "$LANG" ]; then
	return 0
    fi
    return -1
}

function pgRoleExists {
    pgRole=$1
    if [ -z $pgRole ]; then
	return 1
    fi
    ROLE=`psql -At -c "select rolname from pg_roles where rolname='$pgRole'" 2> /dev/null`
    RET=$?
    if [ $RET -ne 0 ]; then
	return $RET
    fi
    if [ -n "$ROLE" ]; then
	return 0
    fi
    return -1
}

function pgTableExists {
    dbName=$1
    psql -At -c "\d \"$dbName\"" 2> /dev/null
    RET=$?
    return $RET
fi

function pgExecuteSqlFile {
    pgFile=$1
    if [ -z $pgFile ]; then
	return 1
    fi
    psql -f "$pgFile"
    RET=$?
    if [ $RET -ne 0 ]; then
	return $RET
    fi
    return 0
}

function pgVersion {
    VERSION=`psql -At -c "SHOW SERVER_VERSION;" 2> /dev/null`
    RET=$?
    if [ $RET -ne 0 ]; then
	return $RET
    fi
    if [ -z "$VERSION" ]; then
	return -1
    fi
    perl -e 'print join("", map { sprintf("%03d", $_) } split(/\./, $ARGV[0]))' "$VERSION" 2> /dev/null
}

function pgInitTsearch2 {
    for TSEARCH2 in \
	/usr/share/postgresql/8.1/contrib/tsearch2.sql \
	/usr/share/postgresql/8.2/contrib/tsearch2.sql \
	/usr/share/pgsql/contrib/tsearch2.sql \
	; do
	if [ -f "$TSEARCH2" ]; then
	    echo "Initializing tsearch2 support with '$TSEARCH2'..."
	    psql -f "$TSEARCH2" > /dev/null
	    RET=$?
	    if [ $RET -ne 0 ]; then
		echo "Error occured while loading '$TSEARCH2' in database!"
		exit $RET
	    fi
	    return 0
	fi
    done
    echo "Could not find tsearch2.sql script !"
    return -1
}

function restartHttpd {
    if [ -n "$APACHE_INIT_SCRIPT" ]; then
	$APACHE_INIT_SCRIPT restart
	return $?
    fi
    for RC_INIT_DIR in /etc/init.d /etc/rc.d/init.d; do
	for SCRIPT in httpd apache apache2; do
	    if [ -x $RC_INIT_DIR/$SCRIPT ]; then
		$RC_INIT_DIR/$SCRIPT restart
		return $?
	    fi
	done
    done
    return 1
}

function getHttpdUsername {
    ps -eo fname,user \
	| grep "^\(httpd\|apache\|apache2\)[[:space:]]\+" \
	| awk '{print $2}' \
	| grep -v "^root$" \
	| sort -u \
	| head -1
}

function getHttpdGroupname {
    id -g `getHttpdUsername`
}

function getHttpdUID {
    id -n `getHttpdUsername`
}

function getHttpdGroupname {
    id -n -g `getHttpdUsername`
}
