vacuum full;
delete from groups where iduser = idgroup;
delete from doc where doctype='T';
select setval('seq_id_tdoc', 1000000000);
delete from dochisto where id>1000000000;
delete from docattr where docid not in (select id from doc);

delete from docperm where not exists (select 1 from docread where docid=id );

--delete from docperm where userid not in (select iduser from groups) and userid not in (select num from vgroup) and userid not in (select idgroup from groups);
cluster idx_perm on docperm;
delete from fld where dirid not in (select initid from doc2 where locked != -1) and qtype='S';
--delete from fld where childid not in (select id from doc) and qtype='S'; 
update doc set locked=0 where locked < -1;
--update doc set postitid=null where postitid > 0 and postitid not in (select id from doc27 where doctype != 'Z');
delete from only doc;
begin;
delete from docfrom;
insert INTO docfrom (id, fromid) select id, fromid from doc;
update docfrom set fromid=-1 where id in (select id from docfam);
end;
begin;
delete from docname;
insert INTO docname (name, id, fromid) select name,id, fromid from doc where name is not null and name != '' and locked != -1;
end;
vacuum full analyze;
