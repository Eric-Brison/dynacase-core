#!/bin/bash

pgservice_core=`"$WIFF_ROOT"/wiff --getValue=core_db`

if [ -z "$pgservice_core" ]; then
	echo "Undefined or empty pgservice_core!"
	exit 1
fi

PGSERVICE="$pgservice_core" psql --set ON_ERROR_STOP=on -f - <<'EOF'
delete from docread where name in ('PRF_CV_IGROUP', 'MSK_IGROUP_ADMIN','MSK_IGROUP_MEMBERS','PRF_ADMIN_EDIT','PRF_ADMIN_DIR','MSK_IUSER_MYACCOUNT','CV_IUSER_ACCOUNT','PRF_IUSER_OWNER','MSK_IUSER_ADMIN','PRF_ADMIN_CREATION','PRF_FAMILY_DEFAULT','PRF_ADMIN_SEARCH','MSK_IGROUP_RESTRICTION');
UPDATE doc set id=501,initid=501 where name='PRF_CV_IGROUP';
UPDATE doc set id=502,initid=502 where name='MSK_IGROUP_ADMIN';
UPDATE doc set id=503,initid=503 where name='MSK_IGROUP_MEMBERS';
UPDATE doc set id=504,initid=504 where name='PRF_ADMIN_EDIT';
UPDATE doc set id=505,initid=505 where name='PRF_ADMIN_DIR';
UPDATE doc set id=506,initid=506 where name='MSK_IUSER_ADMIN';
UPDATE doc set id=507,initid=507 where name='MSK_IUSER_MYACCOUNT';
UPDATE doc set id=508,initid=508 where name='CV_IUSER_ACCOUNT';
UPDATE doc set id=509,initid=509 where name='PRF_IUSER_OWNER';
UPDATE doc set id=510,initid=510 where name='PRF_ADMIN_CREATION';
UPDATE doc set id=511,initid=511 where name='PRF_FAMILY_DEFAULT';
UPDATE doc set id=512,initid=512 where name='PRF_ADMIN_SEARCH';
UPDATE doc set id=513,initid=513 where name='MSK_IGROUP_RESTRICTION';
EOF
RET=$?

if [ $RET -ne 0 ]; then
    echo "Error setting logical name for account families."
    exit $RET
fi

"$WIFF_CONTEXT_ROOT/wsh.php" --api=cleanContext
RET=$?
if [ $RET -ne 0 ]; then
    echo "Error: cleanContext returned with exit code '$RET'"
    exit $RET
fi