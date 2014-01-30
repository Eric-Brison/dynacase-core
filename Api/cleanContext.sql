\timing
delete from only family.documents;
delete from family.documents where doctype='T';
begin;
delete from docfrom;
insert INTO docfrom (id, fromid) select id, fromid from family.documents;
update docfrom set fromid=-1 where id in (select id from family.families);
end;
begin;
update family.documents set name = null where name ~ '^TEMPORARY_';
delete from docname;
insert INTO docname (name, id, fromid) select name,id, fromid from family.documents where name is not null and name != '' and locked != -1;
end;
begin;
delete from dav.sessions where to_timestamp(expires) < now();
delete from dav.locks where to_timestamp(expires) < now();
end;