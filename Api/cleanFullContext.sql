--vacuum full;
delete from groups where iduser = idgroup;
delete from family.documents where doctype='T';
select setval('seq_id_tdoc', 1000000000);
delete from dochisto where id>1000000000;
delete from docattr where docid not in (select id from family.documents);

delete from docperm where not exists (select 1 from docread where docid=id );
delete from docpermext where not exists (select 1 from docread where docid=id);

--delete from docperm where userid not in (select iduser from groups) and userid not in (select num from vgroup) and userid not in (select idgroup from groups);
-- cluster idx_perm on docperm;
delete from fld where dirid not in (select initid from family.dir where locked != -1) and qtype='S';
--delete from fld where childid not in (select id from family.documents) and qtype='S';
update family.documents set locked=0 where locked < -1;
--update doc set postitid=null where postitid > 0 and postitid not in (select id from doc27 where doctype != 'Z');
delete from only family.documents;
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
-- assumed by autovacuum
--vacuum full analyze;
