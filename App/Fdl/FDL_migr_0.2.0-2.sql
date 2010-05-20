-- for anakeen database


delete from paramdef where name='UPLOAD_MAX_FILE_SIZE';
delete from paramv where name='UPLOAD_MAX_FILE_SIZE';
delete from paramdef where name='INCIDENT_SLICE_LIST';
delete from paramv where name='INCIDENT_SLICE_LIST';
update paramdef set isglob='Y' where name='FREEDOM_DB';
delete from paramv where name='FREEDOM_DB' and appid not in (select id from application where name='FDL');
update paramv set type='G' where name='FREEDOM_DB';
update paramv set type='G' where name like '%LDAP%';
update paramv set type='G' where name like 'MNOG%';
update paramv set type='G' where name like 'FULLTEXT_SEARCH';
update paramdef set isglob='Y' where name in (select name from paramv where type='G');
delete from paramv where  type='G' and appid not in (select appid from paramdef where name=paramv.name) and name in (select name from paramdef );
