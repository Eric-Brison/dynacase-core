function pgIsRunning {
    psql -c "" > /dev/null 2>&1
    return $?
}

function pgDbExists {
    pgDb=$1
    if [ -z "$pgDb" ]; then
	return 1
    fi
    if [ -z "$PGUSER" ]; then
	export PGUSER="postgres"
    fi
    psql -l -tA \
	| grep -q "^$pgDb|"
    RET=$?
    return $RET
}

function pgLanguageExists {
    pgLanguage=$1
    if [ -z "$pgLanguage" ]; then
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
    if [ -z "$pgRole" ]; then
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
    tableName=$1
    if [ -z "$tableName" ]; then
	return 1
    fi
    TABLE=`psql -At -c "select tablename from pg_tables where tablename='$tableName'" 2> /dev/null`
    RET=$?
    if [ $RET -ne 0 ]; then
	return $RET;
    fi
    if [ -n "$TABLE" ]; then
	return 0
    fi
    return -1
}

function pgTableIndexExists {
    tableName=$1
    indexName=$2
    if [ -z "$tableName" -o -z "$indexName" ]; then
	return 1
    fi
    INDEX=`psql -At -c "SELECT indexname FROM pg_indexes WHERE tablename = '$tableName' AND indexname = '$indexName'" 2> /dev/null`
    RET=$?
    if [ $RET -ne 0 ]; then
	return $RET
    fi
    if [ -n "$INDEX" ]; then
	return 0
    fi
    return -1
}

function pgExecuteSqlFile {
    pgFile=$1
    if [ -z "$pgFile" ]; then
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
    php -r 'print(join(array_walk(explode(".",$argv[1]),function($e){$e=printf("%03d",$e);})));' "$VERSION" 2> /dev/null
}



function restartHttpd {
    if [ -n "$APACHE_INIT_SCRIPT" ]; then
	"$APACHE_INIT_SCRIPT" restart
	return $?
    fi
    for RC_INIT_DIR in /etc/init.d /etc/rc.d/init.d; do
	for SCRIPT in httpd apache apache2; do
	    if [ -x "$RC_INIT_DIR/$SCRIPT" ]; then
		"$RC_INIT_DIR/$SCRIPT" restart
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

function versionCompare {
    php -r 'print(version_compare($argv[1],$argv[2]));' "$1" "$2"
}

function getContextRoot {
    if [ -n "$wpub" -a -f "$wpub/wsh.php" ]; then
	echo "$wpub"
    elif [ -n "$WIFF_CONTEXT_ROOT" -a -f "$WIFF_CONTEXT_ROOT/wsh.php" ]; then
	echo "$WIFF_CONTEXT_ROOT"
    else
	echo ""
    fi
}

function getInstallUtilsLocation {
    local _CONTEXT_ROOT=`getContextRoot`
    if [ -z "$_CONTEXT_ROOT" ]; then
	echo ""
	return
    fi
    local _LOC="$_CONTEXT_ROOT/WHAT/Class.InstallUtils.php"
    if [ ! -f "$_LOC" ]; then
	echo ""
	return
    fi
    echo "$_LOC"
}

function installUtils {
    local _INSTALL_UTILS=`getInstallUtilsLocation`
    if [ -z "$_INSTALL_UTILS" ]; then
	echo "InstallUtils not found." 1>&2
	return 1
    fi
    local _FUNCTION=$1
    if [ $# -gt 0 ]; then
	shift
    fi
    php "$_INSTALL_UTILS" "$_FUNCTION" "$@"
}

# vim: tabstop=8 softtabstop=4 shiftwidth=4 noexpandtab
