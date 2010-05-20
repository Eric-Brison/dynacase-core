#!/bin/bash


# Share group table between anakeen database and freedom database

echo "drop table groups" | psql $dbfree
su - postgres -c "pg_dump -t groups anakeen  | psql $dbfree"

ankgrp=`echo "select relfilenode from pg_class where relname='groups';"  | psql anakeen anakeen | tail -3 | head -1 | sed -e"s/ //g"`

freegrp=`echo "select relfilenode from pg_class where relname='groups';"  | psql $dbfree | tail -3 | head -1 | sed -e"s/ //g"`

ankdb=`echo "select oid from pg_database where datname='anakeen'" | psql anakeen anakeen | tail -3 | head -1 | sed -e"s/ //g"`
freedb=`echo "select oid from pg_database where datname='freedom'" | psql anakeen anakeen | tail -3 | head -1 | sed -e"s/ //g"`

if  [ $ankgrp > 0 ] && [ $freegrp > 0 ] && [ $ankdb > 0 ] && [ $freedb > 0 ]; then
    echo $ankgrp $freegrp $ankdb $freedb


    pushd /var/lib/pgsql/data/base/$freedb
    /bin/rm $freegrp
    /bin/ln -s ../$ankdb/$ankgrp $freegrp

    popd
   echo "the shared group table succeeded";
else
   echo "the shared group table failed";
fi


#copy application and acl table from anakeen to freedom
echo "drop table application" | psql $dbfree
su - postgres -c "pg_dump -t application anakeen | psql $dbfree"
echo "drop table acl" | psql $dbfree
su - postgres -c "pg_dump -t acl anakeen | psql $dbfree"
#delete unused rows
echo "delete from application where objectclass != 'Y' or objectclass isnull" | psql $dbfree
echo "delete from acl  where id_application not in (select id from application)" | psql $dbfree
