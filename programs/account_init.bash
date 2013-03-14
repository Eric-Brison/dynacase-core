#!/bin/bash

pgservice_core=`"$WIFF_ROOT"/wiff --getValue=core_db`

if [ -z "$pgservice_core" ]; then
	echo "Undefined or empty pgservice_core!"
	exit 1
fi

PGSERVICE="$pgservice_core" psql --set ON_ERROR_STOP=on -f - <<'EOF'
UPDATE doc set id=501 where name='PRF_CV_IGROUP';
UPDATE doc set id=502 where name='MSK_IGROUP_ADMIN';
UPDATE doc set id=503 where name='MSK_IGROUP_MEMBERS';
UPDATE doc set id=504 where name='PRF_ADMIN_EDIT';
UPDATE doc set id=505 where name='PRF_ADMIN_DIR';
UPDATE doc set id=506 where name='MSK_IUSER_ADMIN';
UPDATE doc set id=507 where name='MSK_IUSER_MYACCOUNT';
UPDATE doc set id=508 where name='CV_IUSER_ACCOUNT';
UPDATE doc set id=509 where name='PRF_IUSER_OWNER';
UPDATE doc set id=510 where name='PRF_ADMIN_CREATION';
UPDATE doc set id=511 where name='PRF_FAMILY_DEFAULT';
UPDATE doc set id=512 where name='PRF_ADMIN_SEARCH';
UPDATE doc set id=513 where name='MSK_IGROUP_RESTRICTION';
begin;
delete from docfrom;
insert INTO docfrom (id, fromid) select id, fromid from doc;
update docfrom set fromid=-1 where id in (select id from docfam);
end;
EOF

if [ $RET -ne 0 ]; then
    echo "Error setting logical name for account families."
    exit $RET
fi