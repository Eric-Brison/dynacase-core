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
    LANG=`psql -At -c "select lanname from pg_language where lanname='plpgsql'" 2> /dev/null`
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
